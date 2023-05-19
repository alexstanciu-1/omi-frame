<select multiple name-x="{{<?= $_data_property ?>}}[]" <?= $_extra_attrs ?> class="qc-form-element" >
	<?php foreach ($_set_vals as $val) : ?>
	<option value="<?= $val ?>" {{in_array("<?= htmlentities($val) ?>", <?= $_data_value ?>) ? ' selected ' : ''}} ><?= 
			(isset($_set_captions[$val]) ? $_set_captions[$val] : $val) ?></option>
	<?php endforeach; ?>
	<?php 
	include(static::GetTemplate("form_elements/validation_info.tpl")); ?>
</select>
