<urls implements="\QIUrlController" q-namespace="Omi\View">
	<url tag="login">
		<get translate="login" />
		<test><?= ($url->current() === "login") || (!\QApi::Call("\\Omi\\User::CheckLogin")) ?></test>
		<load><?php
		
			if ($_POST["__submitted"] && ($user_or_email = filter_input(INPUT_POST, "user")) && ($password = filter_input(INPUT_POST, "pass")))
			{
				$login = \QApi::Call("Omi\\User::Login", $user_or_email, $password);
				if (($login === true) || ($login instanceof \QUser))
				{
					if ($url->current() === "login")
						header("Location: ".dirname(\QWebRequest::GetRequestFullUrl(false)));
					else
						header("Location: ".\QWebRequest::GetRequestFullUrl(true));
					return true;
				}
			}
			
			return $this;
			
		?></load>
	</url>
</urls>
