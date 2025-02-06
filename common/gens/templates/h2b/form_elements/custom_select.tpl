<div class="js-form-grp">
	<?php 
		$unqid = $md5_seed__ ?: uniqid();
		if (isset($_cust_dd_value))
			$_data_value = $_cust_dd_value;
		if (isset($_cust_dd_value_caption))
			$_data_value_caption = $_cust_dd_value_caption;
		
		$tmp_input_value_str = "{$_data_value} ?: ".($_default ? "'{$_default}'" : "''");

	?>
	<div class="qc-radio-dropdown full-width"<?= $_field_style ? ' style="' . $_field_style . '"' : '' ?>>
		<?php if ($_enum_captions) : ?>
			@code
			<?= qArrayToCode($_enum_captions, "_enum_captions", false, null, 0, true) ?>
			@endcode
		<?php endif; ?>
		<span>{{_L(<?= $_data_value_caption ? ($_enum_captions ? "(\$_enum_captions[{$_data_value_caption}] ?: {$_data_value_caption})" : $_data_value_caption) : "\"Select\"" ?>)}}</span>
		<ul class='dropdown'>
			<?php foreach ($_enum_vals as $val) : ?>
				<li>
					<input data-id="<?= $unqid ?>" type="radio" id="{{$unk = uniqid()}}" name="<?= $wval_name ?>" value="<?= $val ?>"{{(<?= $_data_value ?> == "<?= $val ?>") ? ' checked' : ''}} />
					<label for="{{$unk}}"><?= _L(isset($_enum_captions[$val]) ? $_enum_captions[$val] : $val) ?></label>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<input class="qc-form-element qc-dropdown-hidden<?= $_is_mandatory ? ' q-mandatory' : '' ?>" xg-property-value='<?= $xg_tag ?>' type="hidden" id="<?= $unqid ?>" 
		   <?= $_extra_attrs ?>
			<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{{\"" . qaddslashes($_q_valid) . "\"}}'" : "" ?>
			<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{{\"" . qaddslashes($_q_fix) . "\"}}'" : "" ?>
			name{{<?= $_data_value_raw ?> == (<?= $tmp_input_value_str ?>) ? '-x' : ''}}="{{<?= $_data_property ?>}}" value="{{<?= $tmp_input_value_str ?>}}" />

	<?php 

		include(dirname(__FILE__)."/validation_info.tpl");

	?>
</div>