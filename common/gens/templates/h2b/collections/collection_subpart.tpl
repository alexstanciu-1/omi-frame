<div xg-list-full='<?= $xg_tag ?>' class='qc-list qc-subpart-coll' 
	q-args="$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = '', $_qengine_args = null">
	<div xg-list='<?= $xg_tag ?>' class='list qc-collection js-sortable qc-list-vars-path-index-container'>
		<table class='coll-table'>
			<thead class='disabled qc-coll-headings-wrapper'>
				<tr class='qc-coll-headings'>
					<th class='qc-order-handle-heading'>
						<strong><i class="fa fa-th-large m-left-10"></i></strong>
					</th>
					<th class='qc-collection-chk qc-collection-chk-heading' style="display: none;">
					</th>
					<?php
						if ($_headings_data && (count($_headings_data) > 0))
						{
							foreach ($_headings_data as $headingData)
							{
								//$xg_prop_tag, $caption, "", $heading_order, $h_prop, $_finfo, $heading_search_data, $heading_qsearch_data, $_LIST_PROP_FLAGS
								list($label, $caption, $cssCls, $oby, $h_prop, $prop_info, $heading_search_data, $heading_qsearch_data, $_LIST_PROP_FLAGS) = $headingData;
								?>
								<th class="coll-prop-label-wr" xg-property-label='<?= $label ?>'>
									<label class="coll-prop-label">
										<strong>
											{{_L("<?= $caption ?>")}}
											<?php if ($_LIST_PROP_FLAGS && $_LIST_PROP_FLAGS["mandatory"]) : ?>
												<span class="required">*</span>
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
					<?php if (defined('Q_GENS_WITH_LINKED_EDIT') && Q_GENS_WITH_LINKED_EDIT && (!$read_only)) : ?>
						<th class='qc-action-heading'>
							linked<br/>edit <input type="checkbox" class="qc-collection-chk-toggle js-click" data-js-action="collection_chk_toggle" title="Enable Link Edit" />
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
				<?php
				if ((!isset($_PROP_FLAGS['collection.hide.add'])) || is_string($_PROP_FLAGS['collection.hide.add']) || (!$_PROP_FLAGS['collection.hide.add'])) {
				?>
				<?php if (isset($_PROP_FLAGS['collection.hide.add']) && is_string($_PROP_FLAGS['collection.hide.add'])) { ?>
						@if (!<?= $_PROP_FLAGS['collection.hide.add'] ?>)
				<?php } ?>
				<tbody class='item-wrap qc-collection-add-wrapper'>
					<tr>
						<td colspan='100%' class='qc-coll-add-cell'>
							<div class="input-group">
								<input class='qc-add-rows form-input' style="display: none;" type="number" value="1" />
								<button class='qc-collection-add btn btn-warning btn-border' onclick='return false;' data-form-vars-path="{{$vars_path ?: ''}}" data-form-render="<?= $add_render ?>">
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