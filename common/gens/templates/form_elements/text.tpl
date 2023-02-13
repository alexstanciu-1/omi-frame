<div class="js-form-grp<?= $_PROP_FLAGS['dd.binds'] ? ' q-hide-on-click-away-overwrite' : '' ?>">
	<?php
	
	$inputs_extra_class = "form-input js-form-element-input qc-form-element full-width qc-input ".
							($_is_mandatory ? ' q-mandatory' : '').
							($_PROP_FLAGS['dd.binds'] ? ' qc-text-with-dd' : '');
	
	if (!$_PROP_FLAGS['dd.binds'])
	{
	?>
	<input<?= $_field_style ? ' style="' . $_field_style . '"' : '' ?>
		<?= $placeholder ? ' placeholder="' . $placeholder . '"' : '' ?> 
		<?= $_extra_attrs ?>
		xg-property-value='<?= $xg_tag ?>'
		<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{{\"". qaddslashes($_q_valid) . "\"}}'" : "" ?>
		<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{{\"". qaddslashes($_q_fix) . "\"}}'" : "" ?>
		<?= $_is_password ? " readonly onfocus='this.removeAttribute(\"readonly\");'" : "" ?>
		type='<?= $_is_password ? "password" : "text" ?>' 
		class="<?= $inputs_extra_class ?>"
		name-x="{{<?= $_data_property ?>}}" 
		value='{{<?= $_is_password ? "''" : $_data_value ?>}}' 
		/>
		<input type='hidden' name="{{<?= $_data_property_id_ ?>}}"  value='{{<?= $_data_value_id_ ?>}}' />
		<!-- <div class="form-input-bar"></div> -->
	<?php 
	}
	else
	{
		# $from = null, $selector = null, $binds = null, $caption = null, $full_data = null, $input_id_name = null, $input_id_default = null, 
		# $input_type_name = null, $input_type_default = null, $input_name_name = 'name', $inputs_extra_class = null, $attrs = null, $picker_name = null
		?>
		@include(\Omi\View\DropDown, <?= var_export($config["from"], true) ?>, "Id", <?= $_PROP_FLAGS['dd.binds'] ?>, <?= $_data_value ?>, null, null, null,	null, null, null, <?= var_export($inputs_extra_class, true) ?>, null, <?= $_data_property ?>)
		<?php
	}
	include(static::GetTemplate("form_elements/validation_info.tpl", $config));
	?>
</div>