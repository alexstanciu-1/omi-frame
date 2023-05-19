<div class="js-form-grp mt-1 relative mb-3">
	<input<?= $_field_style ? ' style="' . $_field_style . '"' : '' ?>
		<?= $placeholder ? ' placeholder="' . $placeholder . '"' : '' ?> 
		<?= $_extra_attrs ?>
		xg-property-value='<?= $xg_tag ?>'
		<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{{\"". qaddslashes($_q_valid) . "\"}}'" : "" ?>
		<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{{\"". qaddslashes($_q_fix) . "\"}}'" : "" ?>
		<?= $_is_password ? " readonly onfocus='this.removeAttribute(\"readonly\");'" : "" ?>
		type='<?= htmlentities($_is_password ? "password" : ($_PROP_FLAGS["input.type"] ? (string)$_PROP_FLAGS["input.type"] : "text")) ?>' 
		class="form-input block w-full sm:text-sm sm:leading-5 js-form-element-input qc-form-element qc-input <?= $_is_mandatory ? ' q-mandatory' : '' ?>"
		name-x="{{<?= $_data_property ?>}}" 
		value='{{<?= $_is_password ? "''" : $_data_value ?>}}' 
		<?= (isset($_PROP_FLAGS["input.min"])) ? 'min="' . (string)$_PROP_FLAGS["input.min"] . '"' : ''; ?>
		<?= (isset($_PROP_FLAGS["input.max"])) ? 'max="' . (string)$_PROP_FLAGS["input.max"] . '"' : ''; ?>
		
		/>
		<input type='hidden' name="{{<?= $_data_property_id_ ?>}}"  value='{{<?= $_data_value_id_ ?>}}' />
		<!-- <div class="form-input-bar"></div> -->
	<?php 
	include(static::GetTemplate("form_elements/validation_info.tpl", $config));
	?>
</div>