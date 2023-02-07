<div xg-list-full='<?= $xg_tag ?>' class='qc-list qc-subpart-coll' 
	q-args="$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = '', $_qengine_args = null">
	<div xg-list='<?= $xg_tag ?>' class='list qc-collection js-sortable qc-list-vars-path-index-container'>
		<table class='coll-table'>
			<thead class='disabled qc-coll-headings-wrapper'>
				<!-- 
				<tr class='qc-coll-headings'>
					<?php if (!$read_only) : ?>
						<th class='qc-order-handle-heading'>
							<strong><i class="fa fa-th-large m-left-10"></i></strong>
						</th>
					<?php endif; ?>
					<th class='qc-ref-heading'>
						<strong>{{_L('#')}}</strong>
					</th>
					<?php if (!$read_only) : ?>
						<th class='qc-action-heading'>
							<strong></strong>
						</th>
					<?php endif; ?>
				</tr>
				-->
			</thead>
			@php $item_k_max = -1;
			@php $pos = 0;
			@php $hasData = false;
			@if ($data)
				@each ($data as $item_k => $item)
					@php $pos++;
					@include(<?= $dd_include_method ?>, $settings, $item, $bind_params, $grid_mode, $id, $vars_path ? $vars_path."[{$item_k}]" : "[{$item_k}]", ["_rowi" => [$vars_path."[_rowi][{$item_k}]", $data->_rowi[$item_k]], "_k" => $item_k, "mainData" => $_qengine_args ? $_qengine_args['mainData'] : null, "_pos" => $pos, "_tsp" => [$vars_path."[_tsp][{$item_k}]"]]);
					@php $item_k_max = max($item_k_max, $item_k);
					@php $hasData = true;
				@end
				@php $item_k_max++;
			@end
			@php $item_k_max++;
			@if(!$hasData && <?= $_no_default_row ? 'false' : 'true' ?>)
				@include(<?= $dd_include_method ?>, $settings, null, $bind_params, $grid_mode, $id, $vars_path ? $vars_path."[{$item_k_max}]" : "[{$item_k_max}]", ["_k" => $item_k_max, "mainData" => $_qengine_args ? $_qengine_args['mainData'] : null])
			@endif
			<?php if (!$read_only) : ?>
			<tbody class='qc-list-vars-path-index-row' style='display: none;'>
				<tr class='qc-list-vars-path-index' data-next-crt-no='{{isset($pos) ? ++$pos : 1}}' data-form-vars-path-index='{{$item_k_max}}'>
					<td colspan="100%"></td>
				</tr>
			</tbody>
			<?php
			if ((!isset($_PROP_FLAGS['collection.hide.add'])) || is_string($_PROP_FLAGS['collection.hide.add']) || (!$_PROP_FLAGS['collection.hide.add'])) {
			?>
			<?php if (isset($_PROP_FLAGS['collection.hide.add']) && is_string($_PROP_FLAGS['collection.hide.add'])) { ?>
					@if (!<?= $_PROP_FLAGS['collection.hide.add'] ?>)
			<?php } ?>
			<tbody class='item-wrap qc-collection-add-wrapper'>
				<tr>
					<td colspan='100%' class='qc-coll-add-cell'>
						<div class="input-group max-w-30">
							<input class='qc-add-rows form-input' type='number' value='1' />
							<button class='qc-collection-add btn btn-info btn-border' onclick='return false;' data-form-vars-path="{{$vars_path ?: ''}}" data-form-render="<?= $add_render ?>">
								{{_L('Add')}}
							</button>
						</div>
					</td>
				</tr>
			</tbody>
			<?php if (isset($_PROP_FLAGS['collection.hide.add']) && is_string($_PROP_FLAGS['collection.hide.add'])) { ?>
				@endif
			<?php } ?>
			<?php } endif; ?>
		</table>
	</div>
</div>