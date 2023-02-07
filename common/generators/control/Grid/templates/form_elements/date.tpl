<div class="js-form-grp">
	<input<?= $placeholder ? ' placeholder="' . $placeholder . '"' : '' ?> 
		data-format="<?= $_date_format ?>" 
		xg-property-value="<?= $xg_tag ?>" 
		type="text" 
		class="datepickr full-width form-input js-form-element-input" 
		value="{{<?= $_date_show_val ?>}}" />

	<input type="hidden" readonly="readonly"
		<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{{\"" . qaddslashes($_q_valid) . "\"}}'" : "" ?>
		<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{{\"" . qaddslashes($_q_fix) . "\"}}'" : "" ?>
		class="qc-form-element qc-date<?= $_is_mandatory ? ' q-mandatory' : '' ?>" name-x="{{<?= $_data_property ?>}}" value="{{<?= $_data_value ?>}}" />
	
	
	<?php 

	include(static::GetTemplate("form_elements/validation_info.tpl", $config));

	?>

</div>