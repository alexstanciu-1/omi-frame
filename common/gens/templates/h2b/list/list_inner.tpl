<div class='qc-inner' q-args="$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = '', $_qengine_args = null">
	<?php
		$search_metadata = $config["cfg"]["::"]['@search'] ?? [];
	?>
	@code
		$caption_list = !empty('<?= $listCaption ?>') ? '<?= $listCaption ?>' : null;
	@endcode
	<div class="page-header">
		<h2 class='page-title js-panel-reset' data-href="{{$this->getUrlForTag()}}">
			<a href="javascript://" class="qc-back-btn btn-back" style="visibility: hidden;">
				<i class="zmdi zmdi-long-arrow-left"></i>
			</a>
			@if ($caption_list)
				{{_L($caption_list)}}
			@else
				{{_L($this->caption ?: '<?= $viewCaption ?>')}}
			@endif
		</h2>
		<?php include(static::GetTemplate("list_top_actions.tpl", $config)) ?>
	</div>
	@php $limit_offset = $data ? count($data) : 0;
	@php $qc = $data ? $data->getQueryCount() : 0;
	<div class='page-body page-body-margin'>
		<form class="xg-form row" xg-form='<?= $xg_form ?>' enctype='multipart/form-data' method='POST' autocomplete='off'>
		<?php if ($search_metadata) { ?>
		<div class="col-md-3 shrc-ctrl js-search-fields-row js-shrc-ctrl">
			<div class="shrc-ctrl-inner">
			<?php

			$search_hiddens = [];
			foreach ($search_metadata as $sm_key => $sm_data)
			{
				if (isset($sm_data['@type']))
				{
					list ($split_1, $split_2) = preg_split("/(\\s*\\-\\s*)/uis", $sm_data['@type'], 2, PREG_SPLIT_NO_EMPTY);
					$search_type = $split_2 ?: $split_1;
					$search_data_type = isset($sm_data['@data-type']) ? $sm_data['@data-type'] : ($split_2 ? $split_1 : null);
					if (!$search_data_type)
						$search_data_type = 'string'; # int, float, string, date, datetime
					$sm_input_type = static::Get_Search_2_Input_Type($search_type, $search_data_type, $sm_data);
					include(static::GetTemplate("search_2/{$search_type}.tpl", $config));
				}
			}
			
			foreach ($search_hiddens as $sh_name => $sh_attrs)
			{
				?><input type="hidden" name="<?= $sh_name ?>" value="<?= $sh_attrs['value'] ?? '' ?>" class="qc-input js-srch-hidden js-search-field js-keepin-sync" sync-identifier="" /><?php
			}

			# <textarea>
			/*$def_vars = get_defined_vars();
			echo htmlentities(json_encode([$search_metadata, $def_vars['_PROP_FLAGS'], $def_vars['_TYPE_FLAGS'], $def_vars['_LIST_PROP_FLAGS'],
					array_keys($def_vars)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS | JSON_UNESCAPED_LINE_TERMINATORS | JSON_PRETTY_PRINT));*/
			# </textarea>
			?>
			</div>
		</div>
		<?php } ?>
		<div class="list-wrapper js-list-wrapper">
			<div xg-list='<?= $xg_tag ?>' class='list qc-main-list js-sortable qc-list-vars-path-index-container table-responsive'>
				<table class='table-hover table-order js-itms-table table-striped tbody-striped'>
					<thead class='disabled qc-coll-headings-wrapper'>
						<tr class="table-heading-search js-search-fields-row">
							<?php if ($_TYPE_FLAGS['list_checkboxes']): 
							?><th class='qc-chk-heading'>
								<strong>
									<div class='qc-checkbox'>
										<input class='qc-chkcollitm-pick-bulk' type='checkbox' id='{{$unk = uniqid()}}' />
										<label for={{$unk}}></label>
									</div>
								</strong>
							</th><?php endif; ?>
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

								foreach ($_headings_data ?: [] as $headingData)
								{
									list($label, $caption, $cssCls, $oby, $h_prop, $_finfo, $h_search_data, $h_qsearch_data) = $headingData;
									?>
										<th xg-security="$grid_mode, $settings['model:property'], $vars_path" <?= (!$oby) ? 'style="padding-left: 0;"' : '' ?> xg-security-property='<?= $h_prop->name ?>' <?= $oby ? " data-order='{$oby}'" : "" ?> class="qc-heading<?= ($oby ? " js-order-by order{{\$bind_params['{$oby}'] ? ' ' . strtolower(\$bind_params['{$oby}']) : ''}}" : "").$cssCls ?>" xg-property-label='<?= $label ?>'>
											<?php
												if ($h_qsearch_data && $h_qsearch_data[1])
													echo $h_qsearch_data[1];
												else
												{
													?>
													<div class="table-heading-caption">{{_L('<?= $caption ?>')}}</div>
													<?php
												}
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
			
			<div class='qc-collection-more-wrapper' style="{{!((is_object($data) && $data->_show_more) || (count($data ?? []) >= $this->rowsOnPage)) ? 'display: none;' : ''}}">
				<button onclick="return false;" class='qc-collection-more btn btn-info btn-border btn-more' 
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
		</div>
</form>
	</div>
</div>