<div class="js-form-grp">
	<input<?= $placeholder ? ' placeholder="' . $placeholder . '"' : '' ?> 
		<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{{\"" . qaddslashes($_q_valid) . "\"}}'" : "" ?>
		<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{{\"" . qaddslashes($_q_fix) . "\"}}'" : "" ?>
		data-format="<?= $_date_format ?>" 
		xg-property-value="<?= $xg_tag ?>" 
		name-x="{{<?= $_data_property ?>}}"
		type="date" 
		class="form-input block w-full sm:text-sm sm:leading-5 js-form-element-input qc-form-element qc-input <?= $useDatepicker ? 'flatpickr' : '' ?> <?= $_is_mandatory ? ' q-mandatory' : '' ?>" 
		value="{{<?= $_data_value ?>}}" data-value="{{date('d-m-Y', strtotime(<?= $_data_value ?>))}}" />	
	<?php 
		include(static::GetTemplate("form_elements/validation_info.tpl", $config));
	?>
</div>