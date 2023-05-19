<?php

if ($_TYPE_FLAGS['steps'])
{
	?><div class="qc-comp-step">
	<ul><?php
	foreach ($_TYPE_FLAGS['steps'] ?: [] as $step_name => $step_cfg)
	{
		?>
<li><a href='{{<?= var_export($step_name, true) ?>}}/{{$grid_mode}}/{{$data->getId()}}'>{{<?= var_export($step_cfg['@caption'], true) ?>}}</a></li>
<?php
	}
	?>
	</ul>
</div><?php
	
	unset($step_name);
}

