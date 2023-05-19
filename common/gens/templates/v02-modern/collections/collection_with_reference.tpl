<div xg-list-full='<?= $xg_tag ?>' class='qc-list qc-subpart-coll' 
	q-args="$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = '', $_qengine_args = null">
	<div xg-list='<?= $xg_tag ?>' class='list qc-collection min-w-full overflow-x-auto overflow-hidden js-sortable qc-list-vars-path-index-container'>
		<table class="min-w-full divide-cool-gray-200">
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
			<tbody class='item-wrap qc-collection-add-wrapper'>
				<tr>
					<td colspan='100%' class="px-6 pl-0 pr-0 text-right py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500 qc-coll-add-cell">
                        <!-- <input class='qc-add-rows form-input' type='number' value='1' /> -->
                        <button class='qc-collection-add inline-flex items-center px-4 py-2 border border-indigo-300 text-sm leading-5 font-medium rounded-md text-indigo-700 bg-white hover:text-indigo-500 focus:outline-none focus:shadow-outline-indigo focus:border-indigo-300 active:text-indigo-800 active:bg-indigo-50 active:text-indigo-800 transition duration-150 ease-in-out' onclick='return false;' data-form-vars-path="{{$vars_path ?: ''}}" data-form-render="<?= $add_render ?>">
                            {{_L('Add')}}
                        </button>
					</td>
				</tr>
			</tbody>
			<?php endif; ?>
		</table>
	</div>
</div>