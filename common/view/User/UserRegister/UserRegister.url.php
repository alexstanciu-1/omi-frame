<urls implements="\QIUrlController">
	<index>
		<load><?php
			if ($_GET["ActivationCode"])
			{
				// handle activation
				$activation_code = filter_input(INPUT_GET, "ActivationCode");
				$confirm_result = \QApi::Call("Omi\\User::RegisterConfirm", $activation_code);

				// switch render
				$this->setArguments([$activation_code, $confirm_result], "renderActivationCode");
				$this->setRenderMethod("renderActivationCode");
			}
			else if ($_POST["__submitted"])
			{
				unset($_POST["__submitted"]);
				$form_data = $_POST;
				$register_result = \QApi::Call("Omi\\User::Register", $form_data, \QWebRequest::GetRequestFullUrl()."?ActivationCode=");
				
				// switch render
				$this->setArguments([$register_result, $form_data], "renderAfterReg");
				$this->setRenderMethod("renderAfterReg");
			}
		
			return $this;
		?></load>
	</index>
</urls>
