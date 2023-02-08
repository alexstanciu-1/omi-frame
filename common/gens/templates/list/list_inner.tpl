<div class='qc-inner' q-args="$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = '', $_qengine_args = null">
	<div class="page-header">
		<h2 class='page-title _fleft js-panel-reset' data-href="{{$this->getUrlForTag()}}">{{_L($this->caption ?: '<?= $viewCaption ?>') . ($this->show_caption_action ? " "._T('5aeae40bb1664', 'List') : "")}}</h2>
		<?php include(static::GetTemplate("list_top_actions.tpl", $config)) ?>
	</div>
	@php $limit_offset = $data ? count($data) : 0;
	@php $qc = $data ? $data->getQueryCount() : 0;
	<div class='page-body'>
		<div xg-list='<?= $xg_tag ?>' class='list qc-main-list js-sortable qc-list-vars-path-index-container table-responsive'>
			<table class='table-hover table-order js-itms-table'>
				<thead class='disabled qc-coll-headings-wrapper'>
					<tr class='qc-coll-headings'>
						<th class='qc-pos-heading'>
							<strong>#</strong>
						</th>
						<?php
	
							if ($config['@view.checkboxes']) :

								?>
									<th class="qc-checkboxes-placehodler">
									</th>
								<?php

							endif;

						?>
						<?php

							foreach ($_headings_data as $tmp_k => $headingData)
							{
								list($label, $caption, $cssCls, $oby, $h_prop) = $headingData;
								?>
								<th xg-security-property='<?= $h_prop->name ?>' <?= $oby ? " data-order='{$oby}'" : "" ?> class="qc-heading<?= ($oby ? " js-order-by order{{\$bind_params['{$oby}'] ? ' ' . strtolower(\$bind_params['{$oby}']) : ''}}" : "").$cssCls ?>" xg-property-label='<?= $label ?>'>
									<strong>{{_L("<?= $caption ?>")}}</strong>
								</th>
								<?php
							}

						?>
						<th class='qc-action-heading'>
							<strong>&nbsp;</strong>
						</th>
					</tr>

					<tr class="table-heading-search js-search-fields-row">
						<th class="qc-pos-heading">&nbsp;
							<?php

							foreach ($_oby_data ?: [] as $oby)
							{
								list($input_name, $bind_val_index) = $oby; 

								?>
									<input sync-identifier='<?= $input_name ?>' class='js-oby-field js-keepin-sync' type='hidden' name='<?= $input_name ?>' oby-indx="<?= $input_name ?>" value='{{$bind_params<?= $bind_val_index ?>}}' />
								<?php
							}

							?>

							<input type='hidden' class='js-limit-field js-limit-offset' name='LIMIT[]' value='{{$limit_offset}}' />
							<input type='hidden' class='js-limit-field js-limit-length' name='LIMIT[]' value='{{$this->rowsOnPage}}' />

						</th>
						<?php
	
						if ($config['@view.checkboxes']) :

							?>
								<th class="qc-checkboxes-placehodler">
								</th>
							<?php

						endif;

						?>
						<?php

							foreach ($_headings_data ?: [] as $headingData)
							{
								list($label, $caption, $cssCls, $oby, $h_prop, $_finfo, $h_search_data, $h_qsearch_data) = $headingData;
								?>
									<th xg-security-property='<?= $h_prop->name ?>'>
										<?php
											if ($h_qsearch_data && $h_qsearch_data[1])
												echo $h_qsearch_data[1];
										?>
									</th>
								<?php
							}
						?>
						<th class='qc-action-heading'>
							<strong>&nbsp;</strong>
						</th>
					</tr>
				</thead>
				@php $item_k_max = -1;
				@php $pos = 0;
				@if ($data && count($data) > 0)
					@each ($data as $item_k => $item)
						@php $pos++;
						@include(<?= $dd_include_method ?>, $settings, $item, $bind_params, $grid_mode, $id, "", ["_rowi" => [$vars_path."[_rowi][{$item_k}]", $data->_rowi[$item_k]], "_k" => $item_k, "mainData" => $_qengine_args ? $_qengine_args['mainData'] : null, "_pos" => $pos, "_tsp" => [$vars_path."[_tsp][{$item_k}]"]]);
						@php $item_k_max = max($item_k_max, $item_k);
					@end
					@php $item_k_max++;
				@end
				@php $item_k_max++;
				@if (!$data || (count($data) === 0))
					@include(<?= $noResultsMethod ?>)
				@endif
				<tbody class='qc-list-vars-path-index-row js-prepend-search-results'  style='display: none;'>
					<tr class='qc-list-vars-path-index' data-next-crt-no='{{isset($pos) ? ++$pos : 1}}' data-form-vars-path-index='{{$item_k_max}}'>
						<td colspan="100%"></td>
					</tr>
				</tbody>
			</table>
		</div>
		@if ((is_object($data) && $data->_show_more) || (count($data) >= $this->rowsOnPage))
			<div class='qc-collection-more-wrapper'>
				<button onclick="javascript: void(0);" class='qc-collection-more btn btn-info' 
					type="button"
					data-offset='{{$data ? count($data) : 0}}' 
					data-length='{{$this->rowsOnPage}}' 
					data-from='{{$this->from}}'
					data-selector='{{$this->getSelectorForMode($this->grid_mode)}}'
					data-grid-mode='{{$this->grid_mode}}'
					data-parent-prefix-url='{{$this->parentPrefixUrl}}'
					data-form-vars-path='{{$vars_path ?: ""}}' 
					data-form-render="<?= $add_render ?>">
					{{_L('More')}}
				</button>
			</div>
		@endif
	</div>
</div>