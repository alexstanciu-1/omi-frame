<div class="QDevModeSecurity_Type_Ctrl">
	<?php $this->showClass = $this->showClass ?: QApp::GetDataClass() ?>
	<h3>SECURITY 2.0 TYPE</h3>
	
	<div class='searchable_heading'><h5><?= $this->showClass ?></h5>
		<qCtrl qCtrl="QDevModeSelectCtrl" tag="classesDropDown" name="classesDropDown">
			<?php
				// If this PHP code block is not present then it is created and an instance of the control is created
				$this->addControl($classesDropDown);
				$classesDropDown->init();
				$classesDropDown->render();
			?>
			<init qArgs="$recursive = true"><?php
				$this->url_tag = "security_type_item";
				$this->drop_down = true;
				$this->class_filter = function ($class_name)
				{
					// check that is model and not a control
					return \QModelQuery::GetTypesCache($class_name) ? true : false;
				};
			?></init>
		</qCtrl>
		<br/><br/>
	</div>
	
</div>