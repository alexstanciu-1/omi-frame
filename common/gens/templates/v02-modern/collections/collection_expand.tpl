<div class='w-full dd-toggle-panel data-show-<?= $property ?>'>
	@include(<?= $include_method ?>, $settings, $data-><?= $property ?>, $bind_params, $grid_mode,  $id, $vars_path ? $vars_path."[<?= $property ?>]" : "<?= $property ?>", $_qengine_args)
</div>