<div class="js-form-grp">
	<label class="switch" >
		<input {{<?= $_data_value ?> ? " checked" : ""}} <?= $_extra_attrs ?> type="checkbox" style="display:none;" id="{{$_unk = uniqid()}}" class="qc-form-element" value='{{<?= $_data_value ?>}}' xg-property-value='<?= $xg_tag ?>' name-x="{{<?= $_data_property ?>}}" />
		<span class="slider round"></span>
	</label>
	<?php 
	
	include(dirname(__FILE__)."/validation_info.tpl");
	
	?>
</div>