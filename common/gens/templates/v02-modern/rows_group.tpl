<tbody class="bg-white border-b divide-y divide-cool-gray-200 qc-ref-ctrl js-itm" data-form-render="<?= $esc_include_method ?>" data-form-vars-path="{{$vars_path ?: ''}}" 
	   q-args='$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = "", $_qengine_args = null'>
	@include(<?= $include_method ?>, $settings, $data, $bind_params, $grid_mode, $id, $vars_path ?: "", $_qengine_args)
        
	<?= $renderedSubBlocks ?>
        
</tbody>