<?php 
	$data_to_use = $useSelectedData ? "\$selectedData" : "\$data"; 
	$xg_id_tag = "Id(".(is_array($parent_model) ? implode(",", $parent_model) : $parent_model).")|ro=y,list=".($list_mode ? "y" : "n");
	$xg_ty_tag = "_ty(".(is_array($parent_model) ? implode(",", $parent_model) : $parent_model).")|ro=y,list=".($list_mode ? "y" : "n");
	$xg_ts_tag = "_ts(".(is_array($parent_model) ? implode(",", $parent_model) : $parent_model).")|ro=y,list=".($list_mode ? "y" : "n");
?>

<input xg-property-value='<?= $xg_id_tag ?>' type="hidden" class='qc-hidden-id' 
	name="{{$vars_path ? $vars_path.'[Id]' : 'Id'}}" 
	value="{{($data === null) ? '' : $data->Id}}" />

<?php if (!$is_top) : ?>
	<input xg-property-value='<?= $xg_ty_tag ?>' type="hidden" class='qc-hidden-ty' 
		name="{{$vars_path ? $vars_path.'[_ty]' : '_ty'}}" 
		value="{{($data === null) ? '' : q_get_class($data)}}" />
<?php endif; ?>