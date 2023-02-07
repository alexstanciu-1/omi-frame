<tr class='row-expand <?= $property ?>'>
	<td colspan='100%'>
		<?php if (!$_hide_sub_block_caption) : ?>
		<h3 class='p-left-1'>{{_L('<?= $property ?>')}}</h3>
		<?php endif; ?>
		<div class='row if-not-60-fill-all'>
			@include(<?= $include_method ?>, $settings, $data-><?= $property ?>, $bind_params, $grid_mode, $id, $vars_path ? $vars_path."[<?= $property ?>]" : "<?= $property ?>", $_qengine_args)
		</div>
	</td>
</tr>