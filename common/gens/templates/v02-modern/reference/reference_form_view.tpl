<div class='qc-in-view-ref-ctrl mt-1 mb-3 block'>
	<?php if (!$_is_subpart) : 
		include(static::GetTemplate('reference/reference_list_view.tpl', $config));
	else : ?>
		@include(<?= $include_method ?>, $settings, <?= $_data_value ?>, $bind_params, $grid_mode, $id, <?= $_data_property ?>, $_qengine_args)
	<?php endif;
	$vPath = $_is_in_dd_collection ? "\$vars_path" : "(\$vars_path ? \$vars_path.'[{$property}]' : '{$property}')"; 
	$data = $_is_in_dd_collection ? "\$data" : "\$data->{$property}";
	?>
	<input type="hidden" name-x="{{<?= $vPath ?>}}[Id]" value="{{<?= $data.'->Id' ?>}}" />
	<input type="hidden" name-x="{{<?= $vPath ?>}}[_ty]" value="{{q_get_class(<?= $data ?>)}}" />
</div>