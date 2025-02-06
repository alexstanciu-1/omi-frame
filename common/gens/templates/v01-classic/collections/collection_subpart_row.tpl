<tr xg-item='<?= $xg_tag ?>' class="item qc-xg-item qc-coll-subpart-itm qc-coll-itm"
	q-args='$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = "<?= $vars_post_path ?>", $_qengine_args = null, $_col_widths = null'
	<?= $_is_scalar ? " qc-coll-scalar-itm" : "" ?>>
	@code
		if (is_scalar($data))
			$data = \QApi::QueryById("#<?= addslashes(implode(";#", $src_from_types)) ?>", $data, '<?= qImplodeEntity($selector) ?>');
		$_indx = $_qengine_args && isset($_qengine_args["_k"]) ? $_qengine_args["_k"] : null;
		$_is_first = ($_indx === 0);
		$_is_mandatory = <?= $propIsMandatory  ? "true" : "false" ?>;
		$_hide_rm = ($_is_mandatory && $_is_first);
	@endcode
	<?php if (!$read_only) : ?>		
		<td class='qc-order-handle-wrapper'>
			
			<div class='qc-handle'>
				<ul class='actions'>
					<li>
						<a class='fa fa-th-large js-handle' href='javascript: void(0);'></a>
						<ul class='dropdown bg-white shadow-1'>
							<li>
								<a class='mdt-c' href='javascript: void(0);'>{{_L('First')}}</a>
							</li>
							<li>
								<a class='mdt-c' href='javascript: void(0);'>{{_L('Up')}}</a>
							</li>
							<li>
								<a class='mdt-c' href='javascript: void(0);'>{{_L('Down')}}</a>
							</li>
							<li>
								<a class='mdt-c' href='javascript: void(0);'>{{_L('Last')}}</a>
							</li>
						</ul>
					</li>
				</ul>
			</div>
			<?= $hiddens ?>
		</td>
		
	<?php endif; ?>
	<td class='qc-collection-chk' style="display: none;"><input class="qc-collection-chk-inp" type="checkbox" /></td>
	<?php if ($properties && (count($properties) > 0)) : 
		foreach ($properties as $propertyData): 
			list($xg_prop_tag, $str_property) = $propertyData;
		?>
			<td xg-property='<?= $xg_prop_tag ?>' class="qc-xg-property form-input-border">
				<?= $str_property ?>
			</td>
	<?php endforeach;
	endif; if (!$read_only) : ?>
		<td class='qc-actions-wr'>
			@if (!$_hide_rm)
				<ul class='actions'>
					<?php if ($_PROP_FLAGS['collection.popup.edit']) { ?>
					<li class="itm-config qc-itm-cfg qc-popup-prop-wr">
						<input class="qc-popup-prop-id" type="hidden" value="{{$data->Id}}" />
						<input class="qc-popup-prop-ty" type="hidden" value="{{$data ? q_get_class($data) : ''}}" />
						<a data-view='<?= htmlentities($_PROP_FLAGS['collection.popup.edit']) ?>' data-params='{}' data-mode="edit" class="nowrap qc-popup-prop-setup" title="">
							<i class="fa fa-cog"></i>
						</a>
					</li>
					<?php }
						if ((!isset($_PROP_FLAGS['collection.hide.delete'])) || is_string($_PROP_FLAGS['collection.hide.delete']) || (!$_PROP_FLAGS['collection.hide.delete'])) {
					?>
					<?php if (isset($_PROP_FLAGS['collection.hide.delete']) && is_string($_PROP_FLAGS['collection.hide.delete'])) { ?>
						@if (!<?= $_PROP_FLAGS['collection.hide.delete'] ?>)
					<?php } ?>
					<li>
						<a title="{{_L('Delete')}}" class="mdt-a nowrap qc-ref-ctrl-delete qc-delete" data-vars-path="{{$_qengine_args['_tsp'][<?= $isOneToMany ? '1' : '0' ?>]}}">
							<i class="zmdi zmdi-delete"></i>
						</a>
					</li>
					<?php if (isset($_PROP_FLAGS['collection.hide.delete']) && is_string($_PROP_FLAGS['collection.hide.delete'])) { ?>
						@endif
					<?php } ?>
					<?php } ?>
				</ul>
			@endif
		</td>
	<?php endif; ?>
</tr>
