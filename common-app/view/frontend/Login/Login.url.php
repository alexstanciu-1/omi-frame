<urls implements="\QIUrlController">
	<url tag="login">
		<get translate="login" />
		<test><?= ($url->current() === "login") || (!\QApi::Call("\\Omi\\User::CheckLogin")) ?></test>
		<load><?php
			if ($_POST["__submitted"])
			{				
				if (($user_or_email = filter_input(INPUT_POST, "user")) && ($password = filter_input(INPUT_POST, "pass")))
				{					
					$login = \QApi::Call("Omi\\User::Login", $user_or_email, $password);
					
					$this->error = $login;

					if (($login === true) || ($login instanceof \QUser))
					{
						if ($url->current() === "login")
							header("Location: " . dirname(\QWebRequest::GetRequestFullUrl(false)));
						else
							header("Location: " . \QWebRequest::GetRequestFullUrl(true));
						return true;
					}
				}
				else
					$this->error = _T('5a2fb9e2bdceb', 'Username and password are mandatory');
			}
			return $this;
		?></load>
	</url>
</urls>