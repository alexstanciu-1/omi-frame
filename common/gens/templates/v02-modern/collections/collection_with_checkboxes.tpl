<div xg-list-full='<?= $xg_tag ?>' class='qc-list qc-checkboxes-coll' 
	q-args="$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = '', $_qengine_args = null">

	@php $_collData = [];
	@php $_dataRowi = $data ? $data->_rowi : null;
	@if ($data && (count($data) > 0))
		@each ($data as $k => $itm)
			@php $_collData[$itm->getId()] = [$itm, $k];
		@endeach
	@endif
	
	<?php # if (!$read_only) : ?>
		@php $data = <?= $chkCollCustomQ ? $chkCollCustomQ : '\QApi::Query("' . $chkCollFromProp->name . '", "' . $chkCollSelector . '", ' . $chkCollBinds . ');' ?>
	<?php # endif; ?>
	
	<div xg-list="<?= $xg_tag ?>" class="list qc-collection js-sortable">
		<table class="coll-table min-w-full divide-y divide-cool-gray-200 table-order">
			<thead class="qc-coll-headings-wrapper divide-y">
				<tr class="qc-coll-headings">
					<?php # if (!$read_only) : ?>
						<!-- <th class="qc-order-handle-heading">
							<strong>
								<i class="fa fa-th-large m-left-10"></i>
							</strong>
						</th> -->
					
						<th class="qc-chk-heading coll-prop-label-wr bg-cool-gray-50">
							<label for="{{$unq = uniqid()}}" class="coll-prop-label cursor-pointer">
								<input class="qc-chkcollitm-pick-bulk form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out cursor-not-allowed bg-cool-gray-50" disabled type="checkbox" id="{{$unq}}" />
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
					<?php # endif;
						if ($_headings_data && (count($_headings_data) > 0))
						{
							foreach ($_headings_data as $headingData)
							{
								list($label, $caption, $cssCls, $oby, $h_prop) = $headingData;
								?>
									<th xg-property-label='<?= $label ?>' class="px-6 py-4 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase sticky top-0 z-1">
										{{_T('<?= q_property_to_trans($config["__view__"], 'prop-label', $path . '.' . $h_prop->name) ?>', "<?= $caption ?>")}}
									</th>
								<?php
							}
						}
					?>
				</tr>
			</thead>
			@php $item_k_max = -1;
			@php $pos = 0;
			@php $hasData = false;
			@if ($data)
				@each ($data as $item_k => $item)
					@php $pos++;
					@php list($_collItm, $_collItmK) = ($_collData && $item && isset($_collData[$item->getId()])) ? $_collData[$item->getId()] : [null, uniqid()];
					@php $_itmRowi = $_collItm ? [$vars_path."[_rowi][{$_collItmK}]", $_dataRowi[$_collItmK]] : null;
					@include(<?= $dd_include_method ?>, $settings, $item, $bind_params, $grid_mode, $id, $vars_path ? $vars_path."[{$_collItmK}]" : "[{$_collItmK}]", ["_rowi" => $_itmRowi, "_collItm" => $_collItm, "_k" => $_collItmK, "_pos" => $pos, "mainData" => $_qengine_args ? $_qengine_args['mainData'] : null, "_tsp" => [$vars_path."[_tsp][{$_collItmK}]", ($vars_path ? $vars_path."[{$_collItmK}]" : "[{$_collItmK}]")."[_ts]"]]);
					@php $item_k_max = max($item_k_max, $item_k);
					@php $hasData = true;
				@end
				@php $item_k_max++;
			@end
			@if (!$hasData)
				<tbody class='qc-ddcoll-no-data'>
					<tr>
						<?php if ($read_only) { ?>
						<td colspan='100%'>Empty.</td>
						<?php } else { ?>
						<td colspan='100%'>No data to pick from!</td>
						<?php } ?>
					</tr>
				</tbody>
			@endif
			@php $item_k_max++;
		</table>
	</div>
</div>