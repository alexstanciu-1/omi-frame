<div class="js-form-grp">
	
	<input<?= $_field_style ? ' style="' . $_field_style . '"' : '' ?>
		<?= $placeholder ? ' placeholder="' . $placeholder . '"' : '' ?> 
		<?= $_extra_attrs ?>
		xg-property-value='<?= $xg_tag ?>'
		<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{{\"". (qaddslashes($_q_valid)) . "\"}}'" : "" ?>
		<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{{\"". (qaddslashes($_q_fix)) . "\"}}'" : "" ?>
		<?= $_is_password ? " readonly onfocus='this.removeAttribute(\"readonly\");'" : "" ?>
		type='<?= $_is_password ? "password" : "text" ?>' 
		class='qc-form-element full-width qc-input <?= $_is_mandatory ? ' q-mandatory' : '' ?>'
		name-x="{{<?= $_data_property ?>}}" 
		value='{{<?= $_is_password ? "''" : $_data_value ?>}}'
		<?= $_is_password ? " autocomplete='new-password' " : "" ?>
		/>

	<?php 
	include(dirname(__FILE__)."/validation_info.tpl");
	if ($_PROP_FLAGS['dd.binds'])
	{
		echo json_encode($_PROP_FLAGS['dd.binds']);
	}
	?>

</div>