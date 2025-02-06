<div xg-list-full='<?= $xg_tag ?>' class='qc-list qc-checkboxes-coll' 
	 q-args="$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = '', $_qengine_args = null">
	<div class='qc-grid-properties' data-properties='{{$this->getJsProperties()}}'></div>
	<div class='sidebar'>
		<div class='sidebar-content'>
			<?= $search_str ?>
		</div>
	</div>
	@include(<?= $_inner_tpl ?>, $settings, $data, $bind_params, $grid_mode, $id, $vars_path, $_qengine_args);
</div>