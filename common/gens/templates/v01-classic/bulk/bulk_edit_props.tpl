<tr class="item qc-xg-props"
	q-args='$settings = null, $data = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = "<?= $vars_post_path ?>", $_qengine_args = null, $_col_widths = null'>
	<td class='qc-pos-heading'></td>
	<td class='qc-chk-heading'></td>
	<?php if ($bulk_edit_props && (count($bulk_edit_props) > 0)) : 
		foreach ($bulk_edit_props as $edit_prop): 
			list($xg_prop_tag, $str_property) = $edit_prop; ?>
			<td xg-property='<?= $xg_prop_tag ?>' class="qc-xg-property">
				<?= $str_property ?>
			</td>
	<?php endforeach;
	endif; ?>
	<?php if (!($config["__settings__"] &&  $config["__settings__"]["bulk"] && $config["__settings__"]["bulk"]["hide_delete"])) : ?>
		<td class='qc-actions-wr'></td>
	<?php endif; ?>
</tr>
