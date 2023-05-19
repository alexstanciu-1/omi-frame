<urls implements="\QIUrlController">
	<index>
		<load><?php
			if ($_GET["RecoverCode"])
			{				
				if ($_POST["__submitted"])
				{
					unset($_POST["__submitted"]);
					\Omi\User::EditProfile($_POST);
					die();
				}

				// handle activation
				$recoverCode = filter_input(INPUT_GET, "RecoverCode");
				$confirm_result = \Omi\User::ConfirmPasswordRecovery($recoverCode);
				if ($confirm_result)
				{
					// switch render
					$this->setArguments([$user], "renderChangePassword");
					$this->setRenderMethod("renderChangePassword");
				}
			}
			else if ($_POST['__submitted'] && ($email = filter_input(INPUT_POST, "email")))
			{
				# $recoverPassword = \Omi\User::RecoverPassword($email, \QWebRequest::GetRequestFullUrl()."?RecoverCode=");
				# $this->setArguments([$email, $recoverPassword], "renderForm");
			}
			return $this;
		?></load>
	</index>
</urls>
