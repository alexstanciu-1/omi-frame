<div class="QDevModeGeneratedCtrl">
	<ul><?php
	
		$generated = $this->getGenerated();
		if ($generated)
		{
			foreach ($this->getGenerated() as $wf => $caption)
			{
				$selected = ($this->watchFolder === $wf);
				if ($selected):
					?><li class="selected"><?= $caption ?></li><?php
				else:
					?><li><a href="<?= QDevModePage::GetUrl("generateditem", $wf) ?>"><?= $caption ?></a></li><?php
				endif;
			}
		}
		
	?></ul>
	<?php
	
	if ($this->watchFolder && is_dir($this->watchFolder) && file_exists($this->watchFolder."generate.php"))
	{
		?><div>
		<h4>Config Generators</h4>
		
		<button onclick="jQuery(this).nextAll('.batch-add-generators').first().toggle()">Batch Add Generators</button>
		<button onclick="$ctrl(this).call('syncPreConfigs', [getUrlVariable('folder')]);">Sync Pre Configs</button>
		<?php $this->renderItem() ?>
		
		<h4>List</h4>
		<?php
			$config = $this->getPreConfig();
			if ($config && $config["batches"])
			{
				foreach ($config["batches"] as $batch)
					$this->renderItem($batch);
			}
		?>
		</div><?php
	}
	?>
</div>