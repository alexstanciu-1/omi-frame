<tr class="hidden <?= $property ?>">
	<td colspan="100%">
		<div class="hidden px-4 lg:px-8 py-2 lg:py-4 js-<?= $property ?>">
			<h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">{{_L('<?= $property ?>')}}</h3>
			<div>
				@include(<?= $include_method ?>, $settings, $data-><?= $property ?>, $bind_params, $grid_mode, $id, $vars_path ? $vars_path."[<?= $property ?>]" : "<?= $property ?>", $_qengine_args)
			</div>
		</div>
	</td>
</tr>