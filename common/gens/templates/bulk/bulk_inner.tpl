<div class='qc-inner' q-args="$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = '', $_qengine_args = null">
	@code
	$caption_list = !empty('<?= $listCaption ?>') ? '<?= $listCaption ?>' : null;
	@endcode
	<div class="page-header">
		<h2 class='page-title'>
			<a href="javascript://" class="qc-back-btn btn-back" style="visibility: hidden;">
				<i class="zmdi zmdi-long-arrow-left"></i>
			</a>
			@if ($caption_list)
			{{_L($caption_list)}}
			@else
			{{_L($this->caption ?: '<?= $viewCaption ?>')}}
			@endif
		</h2>
		<?php include(static::GetTemplate("list_top_actions.tpl", $config)); ?>
	</div>
	@php $limit_offset = $data ? count($data) : 0;
	@php $qc = $data ? $data->getQueryCount() : 0;
	<div class='page-body page-body-margin'>
		<form class="xg-form" xg-form='<?= $xg_form ?>' enctype='multipart/form-data' method='POST' autocomplete='off'>
			<div class="list-wrapper">
				<input type="hidden" value="1" name="__submitted" />
				<div xg-list='<?= $xg_tag ?>' class='list qc-main-list js-sortable qc-list-vars-path-index-container table-responsive'>
					<table class='table-hover table-order js-itms-table table-striped tbody-striped'>
						<thead class='disabled qc-coll-headings-wrapper'>
							<tr class="table-heading-search js-search-fields-row">
								@if (false)
								<th class='qc-pos-heading'>
									<strong>#</strong>
								</th>
								@endif
								<?php if (!($config && $config['__settings__'] && $config['__settings__']['bulk'] && $config['__settings__']['bulk']['hide_pickers'])) : ?>
									<th class='qc-chk-heading'>
										<strong>
											<div class='qc-checkbox'>
												<input class='qc-chkcollitm-pick-bulk' type='checkbox' id='{{$unk = uniqid()}}' />
												<label for={{$unk}}></label>
											</div>
										</strong>
									</th>
								<?php
								endif;
								foreach ($_headings_data ?: [] as $headingData)
								{
									list($label, $caption, $cssCls, $oby, $h_prop, $_finfo, $h_search_data, $h_qsearch_data) = $headingData;
									?>
										<th xg-security="$grid_mode, $settings['model:property'], $vars_path" <?= (!$oby) ? 'style="padding-left: 0;"' : '' ?> xg-security-property='<?= $h_prop->name ?>' <?= $oby ? " data-order='{$oby}'" : "" ?> class="qc-heading<?= ($oby ? " js-order-by order{{\$bind_params['{$oby}'] ? ' ' . strtolower(\$bind_params['{$oby}']) : ''}}" : "").$cssCls ?>" xg-property-label='<?= $label ?>'>
											<?php
												# if ($h_qsearch_data && $h_qsearch_data[1])
												#	echo $h_qsearch_data[1];
												# else
												{
													?>
													<div class="table-heading-caption">{{_L('<?= $caption ?>')}}</div>
													<?php
												}
											?>
										</th>
									<?php
								}
								if (!($config["__settings__"] && $config["__settings__"]["bulk"] && $config["__settings__"]["bulk"]["hide_delete"])) {
									?>
									<th class='qc-action-heading'>
										<strong></strong>
									</th>
<?php } ?>
							</tr>
						</thead>
<?php if ($has_bulk_edit_props) : ?>
							<tbody class='qc-edit-props-body'>
								@include(<?= $bulk_method ?>, $settings, null, $bind_params, $grid_mode, $id, $vars_path, $_qengine_args)
							</tbody>
<?php endif; ?>
						@php $vars_path = $this->from;
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
						<?php if (isset($config["__settings__"]["bulk"]["hide_default_row"]) && (!$config["__settings__"]["bulk"]["hide_default_row"])) : ?>
							@include(<?= $dd_include_method ?>, $settings, null, $bind_params, $grid_mode, $id, $vars_path ? $vars_path."[{$item_k_max}]" : "[{$item_k_max}]", ["_k" => $item_k_max, "_pos" => ++$pos, "mainData" => $_qengine_args ? $_qengine_args['mainData'] : null]);
<?php endif; ?>
						<tbody class='qc-list-vars-path-index-row' style='display: none;'>
							<tr class='qc-list-vars-path-index' data-next-crt-no='{{isset($pos) ? ++$pos : 1}}' data-form-vars-path-index='{{$item_k_max}}'></tr>
						</tbody>
<?php if (!($config["__settings__"] && $config["__settings__"]["bulk"] && $config["__settings__"]["bulk"]["hide_add"])) : ?>
							<tbody class='item-wrap qc-collection-add-wrapper'>
								<tr>
									<td></td>
									<td colspan='100%' class='qc-coll-add-cell'>
										<div class="collection-add-wrapper">
											<input class='qc-add-rows' type='number' value='1' />
											<button class='qc-collection-add' onclick='return false;' data-form-vars-path="{{$vars_path ?: ''}}" data-form-render="<?= $add_render ?>">
												{{_L('Add')}}
											</button>
										</div>
									</td>
								</tr>
							</tbody>
<?php endif; ?>
					</table>
				</div>
				@if (count($data) >= $this->rowsOnPage)
				<div class='qc-collection-more-wrapper'>
					<button onclick="javascript: void(0);" class='qc-collection-more btn btn-info btn-border btn-more' 
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
			<button class="qc-submit-btn btn btn-warning" onclick="return false;">Save</button>
	</div>
</form>
</div>
</div>