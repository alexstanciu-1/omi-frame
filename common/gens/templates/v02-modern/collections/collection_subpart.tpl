<div xg-list-full='<?= $xg_tag ?>' class='qc-list qc-subpart-coll' 
	q-args="$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = '', $_qengine_args = null">
	<div xg-list='<?= $xg_tag ?>' class='list qc-collection min-w-full overflow-x-auto overflow-hidden js-sortable qc-list-vars-path-index-container'>
		<table class="min-w-full divide-cool-gray-200">
			<thead class='disabled qc-coll-headings-wrapper'>
				<tr class='qc-coll-headings'>
					<th class='qc-order-handle-heading'>
						<strong><i class="fa fa-th-large m-left-10"></i></strong>
					</th>
					<?php
						if ($_headings_data && (count($_headings_data) > 0))
						{
							foreach ($_headings_data as $headingData)
							{
								//$xg_prop_tag, $caption, "", $heading_order, $h_prop, $_finfo, $heading_search_data, $heading_qsearch_data, $_LIST_PROP_FLAGS
								list($label, $caption, $cssCls, $oby, $h_prop, $prop_info, $heading_search_data, $heading_qsearch_data, $_LIST_PROP_FLAGS) = $headingData;
								?>
								<th class="coll-prop-label-wr px-6 py-3 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase" xg-property-label='<?= $label ?>'>
									<label class="coll-prop-label">
										<strong>
											{{_T('<?= q_property_to_trans($config["__view__"], 'prop-label', $path . '.' . $h_prop->name) ?>', '<?= $caption ?>')}}
											<?php if ($_LIST_PROP_FLAGS && $_LIST_PROP_FLAGS["mandatory"]) : ?>
												<span class="text-red-500 required text-2xl">*</span>
											<?php endif; ?>
										</strong>	
									</label>
									<?php if ($prop_info) : ?>
										<div class="qc-coll-tooltip-wrapper">
											<div class="qc-tooltip info">
												<i class="fa fa-info-circle"></i>
												<span class="tooltip"><?= $prop_info ?></span>
											</div>
										</div>
									<?php endif; ?>
								</th>
								<?php
							}
						}
					?>
					<?php if (!$read_only) : ?>
						<th class='qc-action-heading px-6 py-3 bg-cool-gray-50'>
							<strong></strong>
						</th>
					<?php endif; ?>
				</tr>
			</thead>
			@php $item_k_max = -1;
			@php $pos = 0;
			@php $hasData = false;
			@if ($data)
				@each ($data as $item_k => $item)
                    @php $pos++;
                    @include(<?= $dd_include_method ?>, $settings, $item, $bind_params, $grid_mode, $id, $vars_path ? $vars_path."[{$item_k}]" : "[{$item_k}]", ["_rowi" => [$vars_path."[_rowi][{$item_k}]", $data->_rowi[$item_k]], "_k" => $item_k, "mainData" => $_qengine_args ? $_qengine_args['mainData'] : null, "_pos" => $pos, "_tsp" => [$vars_path."[_tsp][{$item_k}]", ($vars_path ? $vars_path."[{$item_k}]" : "[{$item_k}]")."[_ts]"]]);
                    @php $item_k_max = max($item_k_max, $item_k);
                    @php $hasData = true;
				@end
				@php $item_k_max++;
			@end
			@php $item_k_max++;
			<?php if (!$read_only) : ?>
				@if(!$hasData && <?= $_no_default_row ? 'false' : 'true' ?>)
                                    @include(<?= $dd_include_method ?>, $settings, null, $bind_params, $grid_mode, $id, $vars_path ? $vars_path."[{$item_k_max}]" : "[{$item_k_max}]", ["_k" => $item_k_max, "mainData" => $_qengine_args ? $_qengine_args['mainData'] : null])
				@endif
				<tbody class='qc-list-vars-path-index-row' style='display: none;'>
                    <tr class='qc-list-vars-path-index' data-next-crt-no='{{isset($pos) ? ++$pos : 1}}' data-form-vars-path-index='{{$item_k_max}}'>
                        <td colspan="100%"></td>
                    </tr>
				</tbody>
				<tbody class='item-wrap qc-collection-add-wrapper'>
					<tr>
						<td colspan='100%' class='qc-coll-add-cell pl-0 pr-0 text-right px-6 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500'>
							<div class="input-group max-w-30">
								<input class='qc-add-rows form-input' style="display: none;" type="number" value="1" />
								<button class='qc-collection-add inline-flex items-center px-4 py-2 border border-indigo-300 text-sm leading-5 font-medium rounded-md text-indigo-700 bg-white hover:text-indigo-500 focus:outline-none focus:shadow-outline-indigo focus:border-indigo-300 active:text-indigo-800 active:bg-indigo-50 active:text-indigo-800 transition duration-150 ease-in-out' onclick='return false;' data-form-vars-path="{{$vars_path ?: ''}}" data-form-render="<?= $add_render ?>">
                                    {{_L('Add')}}
								</button>
							</div>
						</td>
					</tr>
				</tbody>
			<?php endif; ?>
		</table>
	</div>
</div>