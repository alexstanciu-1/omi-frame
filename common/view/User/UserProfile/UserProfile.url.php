<urls implements="\QIUrlController">
	<index>
		<load><?php
					
			if ($_POST["__submitted"])
			{				
				unset($_POST["__submitted"]);
				//qvardump($_POST);
				//die();
				\QApi::Call("Omi\\User::EditProfile", $_POST);
				header("Location: " . \QWebRequest::GetRequestFullUrl(false));
				die;
			}

			$this->setArguments([\QApi::Call("\\Omi\\User::GetCurrentUser")]);
			return $this;
		?></load>
	</index>
</urls>