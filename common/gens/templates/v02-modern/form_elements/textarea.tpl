<div class="js-form-grp">
	<textarea<?= $_field_style ? ' style="' . $_field_style . '"' : '' ?>
		<?= $placeholder ? ' placeholder="' . $placeholder . '"' : '' ?> 
		<?= $_extra_attrs ?>
		<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{{\"" . qaddslashes($_q_valid) . "\"}}'" : "" ?>
		<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{{\"" . qaddslashes($_q_fix) . "\"}}'" : "" ?>
		xg-property-value='<?= $xg_tag ?>'
		class="form-textarea mt-1 block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5 qc-form-element js-form-element-input qc-textarea<?= ($_is_mandatory ? ' q-mandatory' : '') . 
			($useEditor  ? ' qc-trumbowyg ' : '') ?>"
		name-x="{{<?= $_data_property ?>}}">{{<?= $_data_value ?>}}</textarea>

	<?php 
		include(static::GetTemplate('form_elements/validation_info.tpl', $config));
	?>	
</div>