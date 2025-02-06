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
    
	<td xg-property='<?= $xg_prop_tag ?>' class="qc-xg-property qc-ref-cell pl-0 px-6 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500">
		<?php if (!$read_only) : ?>
			<?= $hiddens ?>
		<?php endif ?>
        <?php include(static::GetTemplate("reference/" . ($read_only ? "reference_in_collection_list_view.tpl" : "reference_dropdown.tpl"), $config)); ?>
	</td>
    
	<?php if (!$read_only) : ?>
        <td class="qc-actions-wr pr-0 px-6 px-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500" style="width: 1%;">
			@if (!$_hide_rm)
                <a href="javascript: void(0);" title="{{_L('Delete')}}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm leading-5 font-medium rounded-md text-red-500 bg-white hover:text-red-700 hover:border-red-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:text-gray-800 active:bg-gray-50 active:text-gray-800 transition duration-150 ease-in-out qc-ref-ctrl-delete qc-delete" <?= '<?= $_qengine_args["_tsp"] ? " data-vars-path=\\"".htmlspecialchars($_qengine_args["_tsp"][0], ENT_QUOTES | ENT_HTML5, "UTF-8")."\\" " : "" ?>' ?> data-obj-id="{{$data->Id}}" data-obj-ty="{{q_get_class($data)}}" data-obj-path="{{$vars_path}}">{{_L('Delete')}}</a>
			@endif
		</td>
	<?php endif; ?>
</tr>
