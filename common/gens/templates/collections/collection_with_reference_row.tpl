<tr xg-item='<?= $xg_tag ?>' class="item qc-xg-item qc-reference-itm qc-coll-itm"
	q-args='$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = "<?= $vars_post_path ?>", $_qengine_args = null, $_col_widths = null'>
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
	<?php endif ?>
	<td xg-property='<?= $xg_prop_tag ?>' class="qc-xg-property qc-ref-cell">
        <?php include(static::GetTemplate("reference/" . ($read_only ? "reference_in_collection_list_view.tpl" : "reference_dropdown.tpl"), $config)); ?>
	</td>
	<?php if (!$read_only) : ?>
		<td class='qc-actions-wr'>
			@if (!$_hide_rm)
				<ul class='actions'>
					<li>
						<a title="{{_L('Delete')}}" class="mdt-a nowrap qc-ref-ctrl-delete qc-delete"{{$_qengine_args['_tsp'] ? " data-vars-path='".$_qengine_args['_tsp'][0]."'" : ""}} data-obj-id="{{$data->Id}}" data-obj-ty="{{get_class($data)}}" data-obj-path="{{$vars_path}}">
							<i class="zmdi zmdi-delete"></i>
						</a>
					</li>
				</ul>
			@endif
		</td>
	<?php endif; ?>
</tr>
