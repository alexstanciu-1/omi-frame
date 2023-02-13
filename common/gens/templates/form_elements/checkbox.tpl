<div class="js-form-grp">
	<div class="qc-checkbox">
		<input{{<?= $_data_value ?> ? " checked" : ""}}<?= $_extra_attrs ?> 
	class="qc-form-element qc-checkbox-inp<?= $_is_mandatory ? ' q-mandatory' : '' ?>" 
	xg-property-value='<?= $xg_tag ?>' type="checkbox" id="{{$_unk = uniqid()}}" 
	name-x="{{<?= $_data_property ?>}}" value='{{<?= $_data_value ?>}}' />
		<label for="{{$_unk}}"></label>
	</div>
	<?php 
	
	include(dirname(__FILE__)."/validation_info.tpl");
	
	?>
</div>