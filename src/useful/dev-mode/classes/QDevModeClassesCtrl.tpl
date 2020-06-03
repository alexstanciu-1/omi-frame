<div class="QDevModeClassesCtrl flexbox">
	<div class="col" style="padding-top: 20px; float: left; padding-right: 5px; border-right: 1px solid #7080ac; margin-right: 20px;">
		<?php /* qRender("QDevModeSelectCtrl", "render", []); */ ?>
		<qCtrl qCtrl="QDevModeSelectCtrl" tag="classesDropDown" name="classesDropDown">
			<?php
				// If this PHP code block is not present then it is created and an instance of the control is created
				$this->addControl($classesDropDown);
				$classesDropDown->init();
				$classesDropDown->render();
			?>
			<init qArgs="$recursive = true"><?php
				$this->url_tag = "classitem";
			?></init>
		</qCtrl>
	</div>
	<div class="col"><?php
		
		$cache_path = $this->getDocsCachePath();
		if ($cache_path && file_exists($cache_path))
		{
			readfile($cache_path);
		}
		else
		{
			$this->renderDocs();
		}
	
	?></div>
</div>