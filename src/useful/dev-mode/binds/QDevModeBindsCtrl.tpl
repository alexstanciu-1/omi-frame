<div class="QDevModeBindsCtrl flexbox">
	<div class="col" style="padding-top: 20px; float: left; padding-right: 5px; border-right: 1px solid #7080ac; margin-right: 20px;">
		<div>
			<input type="text" class="classesSearch" placeholder="type to search class" autocomplete="off" />
		</div>
		<ul class="classesList">
			<?php

			$ext_by_inf = QAutoload::GetExtendedByList();
			
			foreach ($this->autoloadData as $class => $path)
			{
				if (substr($class, -strlen("_GenTrait")) === "_GenTrait")
					continue;
				
				if (!QModelQuery::GetTypesCache($class))
					continue;
		
				$short_class = (($p = strrpos($class, "\\")) !== false) ? substr($class, $p + 1) : $class;

				?><li data-classname="<?= strtolower($class) ?>"><a href="<?= $this->parent->url("binditem", $class) ?>"><?= $short_class ?></a></li><?php
			}

			?>
		</ul>
	</div>
	<div class="col"><?php
	
		if (!$this->showClass)
			$this->showClass = QApp::GetDataClass();
		
		if (!$this->bindsSelector)
			$this->bindsSelector = $this->getBindsSelector($this->showClass, 2);
		if (!$this->generatedBinds)
			$this->generatedBinds = $this->getGeneratedBinds($this->showClass, $this->bindsSelector);
		
		echo "<h4>Generate binds for: {$this->showClass}</h4>";
		
		?><div class='flexbox'>
			<div class="col">
				<label>For depth:</label> <input class="bindsSelectorDepth" autocomplete="off" style="width: 60px;" type="number" value="2" /> 
				<button data-classname="<?= htmlspecialchars($this->showClass) ?>" onclick="$ctrl(this).generateSelector(jQuery(this).data('classname'));">Update selector</button>
				<textarea class="bindsSelector" autocomplete="off" style="min-width: 300px; min-height: 500px;"><?= htmlspecialchars($this->bindsSelector) ?></textarea><br/>
				<button data-classname="<?= htmlspecialchars($this->showClass) ?>" onclick="$ctrl(this).generateBinds(jQuery(this).data('classname'));">Generate</button>
			</div>
			<div class="col" style="padding-left: 40px;">
				<textarea class="bindsGeneratedHtml" autocomplete="off" style="min-width: 800px; min-height: 600px;"><?= htmlspecialchars($this->generatedBinds) ?></textarea>
			</div>
		</div><?php
	
	?></div>
</div>