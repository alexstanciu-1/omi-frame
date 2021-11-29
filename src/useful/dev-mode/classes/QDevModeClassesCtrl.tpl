<div class="QDevModeClassesCtrl flexbox">
	<div class="col" style="padding-top: 20px; float: left; padding-right: 5px; border-right: 1px solid #7080ac; margin-right: 20px;">
		<?php /* qRender("QDevModeSelectCtrl", "render", []); */ ?>
		@php $binds = ["Owner" => ($_town = \Omi\App::GetCurrentOwner()) ? $_town->getId() : 0, "Gby_Id" => true, 'OBY_Name' => "ASC", "hideNoneOption" => true];
        @include(\Omi\View\DropDown, "Properties", "Code,Name", $binds)
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