<urls implements="\QIUrlController">
	<prefix><?= ((\QModel::GetLanguage_Dim() === \QModel::GetDefaultLanguage_Dim()) ? "" : \QModel::GetLanguage_Dim())	?></prefix>
	<?php
	
		if ((!($isAsyncReq = \QWebRequest::IsAsyncRequest())) && !\QWebRequest::IsRemoteRequest())
		{
			$isSpecialUrl = in_array(
				$url->current(), 
				[
					"login", 
					"recover-password", 
					'create-account', 
					'terms-and-conditions', 
					'privacy-policy', 
					"~REST-API", 
					"echosign"
				]
			);
			
			// check login
			$this->userIsLoggedIn = \QApi::Call('\Omi\User::CheckLogin');
			
			$this->onFirstLogIn = ($this->userIsLoggedIn && $this->userIsLoggedIn->LoggedToSystem);

			if (!$this->userIsLoggedIn && !$isSpecialUrl)
			{
				$redirect_to = \QWebRequest::GetRequestFullUrl(true);
				$login_url = \QWebRequest::GetBaseHref().$this->getUrlForTag("login");
				
				header("Location: " . $login_url . "?_after_login_=".rawurlencode(base64_encode($redirect_to)));
				die();
			}
			
			$loggedInUser = \Omi\User::GetCurrentUser();
			if (isset($loggedInUser->Owner->Id))
			{
				$enforce_terms = defined('Q_ENFORCE_TERMS_FOR_LOGIN') && Q_ENFORCE_TERMS_FOR_LOGIN;
				
				if ($enforce_terms && !$loggedInUser->Owner->wasSet('Terms_Accepted'))
					$loggedInUser->Owner->populate('Terms_Accepted');
				
				if ((!$enforce_terms) || $loggedInUser->Owner->Terms_Accepted)
				{
					# make sure we do not go to create account
					if ($url->current() === $this->getUrlForTag("createaccount"))
					{
						header("Location: " . \QWebRequest::GetBaseHref());
						die();
					}
				}
				else if ($url->current() !== $this->getUrlForTag("createaccount")) # terms not accepted
				{
					header("Location: " . \QWebRequest::GetBaseHref().$this->getUrlForTag("createaccount"));
					die();
				}
			}
			
			$this->setupLanguage($url);
			if (!\QWebRequest::IsFastAjax())
			{
				# $this->setupTranslations();
				$this->setupWebPage();

				$this->Branding = null;
			}
		}
		
		if (defined('Q_SETUP_SUB_DOMAIN_URL') && Q_SETUP_SUB_DOMAIN_URL)
			static::Setup_Sub_Sub_Domain();
	?>
	
	<!-- LOGIN =============================================================== -->
	<url tag="login">
		<get translate="login" />
		<test><?= ($url->current() === "login") ?></test>
		<load><?php
		
			$current_user = \Omi\User::GetCurrentUser();
			if ($current_user)
			{
				header('Location: ' . \QWebRequest::GetBaseHref());
				die;
			}
		
			# detect dummy subdomain and remove it
			if (defined('Q_SESSION_DOMAIN') && Q_SESSION_DOMAIN)
			{
				$sd = strtolower(trim(Q_SESSION_DOMAIN, " \\r\\n.")); # bit of cleanup
				$http_host = strtolower(trim($_SERVER['HTTP_HOST']));
				if (($http_host !== $sd) && (substr($http_host, -strlen($sd)) === $sd))
				{
					header("Location: " . \QWebRequest::GetRequestFullUrl(true, $sd));
					die;
				}
			}
			
			$error = null;
			if ($_POST["__submitted"])
			{
				$remember = (filter_input(INPUT_POST, 'remember') == 'on');
				if (($user_or_email = trim(filter_input(INPUT_POST, "user"))) && ($password = trim(filter_input(INPUT_POST, "pass"))))
				{
					$users = QQuery("Users.{Id, Active, Confirmed_Activation, Type WHERE (Username=? OR Email=?) AND Password=MD5(?) ORDER BY Active DESC}", 
									[$user_or_email, $user_or_email, $password])->Users;
					
					if ($users && (q_count($users) == 1))
						$user = q_reset($users);
					
					if (defined('Q_IS_H2B') && Q_IS_H2B && !$user)
						$error = 'Username sau parola sunt gresite!';
					else if ((defined('Q_IS_H2B') && Q_IS_H2B) ? ($user && ($user->Confirmed_Activation) || ($user->Type == 'H2B_Superadmin')) : true)
					{
						$login = \QApi::Call("Omi\\User::Login", $user_or_email, $password, null, $remember);
						if (($login === true) || ($login instanceof \QUser))
						{	
							if (isset($_GET['_after_login_']))
							{
								header("Location: " . base64_decode( $_GET['_after_login_']) );
							}
							else
								header("Location: ".($url->current() ? dirname(\QWebRequest::GetRequestFullUrl(true)) : \QWebRequest::GetRequestFullUrl(true)));
							die();
						}

						$error = $login;
					}
					else if (defined('Q_IS_H2B') && Q_IS_H2B)
						$error = "Contul trebuie activat accesand link-ul de activare din email!";
				}
				else
					$error = "Username and password are mandatory!";
			}

			$this->webPage->content = new \Omi\View\Login();
			$this->webPage->content->parentUrl = $this;
			$this->webPage->content->error = $error;
			$this->webPage->content->setArguments([true], "render");
			return $this->webPage->content;
			
		?></load>
	</url>
	
	<!-- LOGOUT =============================================================== -->
	<url tag="logout">
		<get translate="logout" />
		<load><?php
			\Omi\User::Logout();
			
			header("Location: " . (defined('Q_REQUEST_BASE') ? Q_REQUEST_BASE : \QWebRequest::GetBaseHref()).$this->url("login"));
			return true;
		?></load>
	</url>
	
	
	<!-- RECOVER PASSWORD ============================================================= -->
	<url tag="recoverpassword">
		<get translate="recover-password" />
		<load><?php 
		
			$mail_sent = false;
			$rcv_email_was_sent = false;
			
			$this->webPage->content = new \Omi\View\UserPasswordRecovery();
			$this->webPage->content->setArguments([true], "render");
		
			if ($_GET["RecoverCode"])
			{
				if ($_POST["__submitted"])
				{
					unset($_POST["__submitted"]);
					if (!(empty($_POST["Id"])))
					{
						$usrId = $_POST["Id"];
						\QApi::Call("Omi\\User::EditProfile", $_POST, true);
						
						$user = \QApi::QueryById("Users", $usrId);
						
						if ($user)
							\Omi\User::LoginUser($user);
						
						header("Location: " . \QWebRequest::GetBaseHref());
						die();					
					}
					else
					{
						$this->setArguments([$email, $recoverPassword], "renderForm");
						return true;
					}
				}
				
				// handle activation
				$recoverCode = filter_input(INPUT_GET, "RecoverCode");
				$user = \QApi::Call("Omi\\User::GetUserByRecoverPasswordCode", $recoverCode);
				$confirm_result = \QApi::Call("Omi\\User::ConfirmPasswordRecovery", $recoverCode);				
				if ($confirm_result)
				{
					// switch render
					$this->webPage->content->setArguments([$user], "renderChangePassword");
					$this->webPage->content->setRenderMethod("renderChangePassword");
				}
			}
			else if ($_POST['__submitted'])
			{
				if (($email = trim(filter_input(INPUT_POST, "email"))) || ($user_or_email = trim(filter_input(INPUT_POST, "username"))))
				{
					if (!$user_or_email)
						$user_or_email = $email;
					else if (!$email)
						$email = $user_or_email;
					
					// $rcv_email_was_sent = \Omi\User::RecoverPassword($email, $user_or_email);
					$recoverPassword = \QApi::Call("Omi\\User::RecoverPassword", $email, $user_or_email, null, \QWebRequest::GetRequestFullUrl()."?RecoverCode=");
					
					if ($recoverPassword === true)
						$mail_sent = true;
					else
						$error = ($recoverPassword !== false) ? $recoverPassword : true;
				}
				else
					$error = "Username and email are mandatory!";
			}
			
			
			$this->webPage->content->parentUrl = $this;
			$this->webPage->content->error = $error;
			$this->webPage->content->rcv_email_was_sent = $rcv_email_was_sent;
			$this->webPage->content->mail_sent = $mail_sent;			
			
			return $this->webPage->content;		

		?></load>
	</url>

	<!-- CREATE ACCOUNT ============================================================= -->
	<url tag="createaccount">
		<get translate="create-account" />
		<load><?php 
			$user = \Omi\User::GetCurrentUser();
			
			# if ($user)
			{
				# header('Location: ' . \QWebRequest::GetBaseHref());
				# die;
			}
		
			$this->webPage->content = new \Omi\App\View\UserCreateAccount();
			$this->webPage->content->setArguments([true], "render");
			
			// add the control
			$this->addControl($this->webPage->content);
			# $url->next();

			$this->webPage->content->loadFromUrl($url, $this);
			
			return $this->webPage->content;		

		?></load>
	</url>
	
	<url tag="termsandconsitions">
		<get translate="terms-and-conditions" />
		<load><?php 
			$this->webPage->content = new \Omi\App\View\TermsAndConditions();
			$this->webPage->content->setArguments([true], "render");
		
			return $this->webPage->content;
		?></load>
	</url>
	
	<url tag="privacypolicy">
		<get translate="privacy-policy" />
		<load><?php 
			$this->webPage->content = new \Omi\App\View\PrivacyPolicy();
			$this->webPage->content->setArguments([true], "render");
		
			return $this->webPage->content;		

		?></load>
	</url>

	<index>
		<load>
			<?php
				
				$this->webPage->content = new Home();
				// add the control
				# $this->webPage->content->setArguments([\QApi::Call('GetDashboardData')]);
				$this->addControl($this->webPage->content);
				return true;
			?>
		</load>
	</index>

	<!--
	<url tag="lang">
		<get param.0="lang" noprefix><?= ($lang === \QModel::GetDefaultLanguage_Dim()) ? "" : $lang; ?></get>
		<load><?php  ?></load>
	</url>
	-->
	
	<url tag="p-adminitem">
		<get param.0="class"><?= urlencode($class) ?></get>
		<test><?= $url->current() ?></test>
		<load><?php
			return $this->processAdminUrl($url, $testResult);
		?></load>
	</url>
	
	<notfound>
		<load>
			<?php
				header("HTTP/1.0 404 Not Found");
				$this->webPage->content = new NotFound();
			?>
		</load>
	</notfound>

	<unload>
		<?php
			if ($this->webPage->content && ((!$this->webPage->children) || (!in_array($this->webPage->content, 
					is_array($this->webPage->children) ? $this->webPage->children : $this->webPage->children->getArrayCopy()))))
				$this->webPage->addControl($this->webPage->contentCtrl = $this->webPage->content);
			
			$userData = $this->userIsLoggedIn ? \Omi\User::GetCurrentUser() : null;
			
			$this->webPage->controller = $this;
			$this->webPage->setArguments([$userData, $this->Branding], "renderBody");
			$this->webPage->setArguments([$this->Branding], "renderHead");
			$this->webPage->init();
			$this->webPage->render();
		?>
	</unload>
</urls>