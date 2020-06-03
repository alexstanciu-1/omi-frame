<div class="QDevModeSecurity_Model_Ctrl">
	<h3>SECURITY 2.0 MODEL</h3>
	
	<div class='searchable_heading'><h5><?= $this->showClass ?></h5>
		<qCtrl qCtrl="QDevModeSelectModelCtrl" tag="classesDropDown" name="classesDropDown">
			<?php
				// If this PHP code block is not present then it is created and an instance of the control is created
				$this->addControl($classesDropDown);
				$classesDropDown->init();
				$classesDropDown->render();
			?>
			<init qArgs="$recursive = true"><?php
				$this->url_tag = "security_model_item";
				$this->drop_down = true;
			?></init>
		</qCtrl>
		<br/><br/>
	</div>
	
</div>