<div class="js-form-grp">
	<?php 
		$unqid = $md5_seed__ ?: uniqid();
		if (isset($_cust_dd_value))
			$_data_value = $_cust_dd_value;
		if (isset($_cust_dd_value_caption))
			$_data_value_caption = $_cust_dd_value_caption;
		
		$tmp_input_value_str = "{$_data_value} ?? ".($_default ? "'{$_default}'" : "''");

	?>
    
	<div class="relative" <?= $_field_style ? ' style="' . $_field_style . '"' : '' ?>>
		<?php if ($_enum_captions) : ?>
			@code
            	<?= qArrayToCode($_enum_captions, "_enum_captions", false, null, 0, true) ?>
			@endcode
		<?php endif; ?>
	
		<div class="qc-radio-dropdown"<?= $_field_style ? ' style="' . $_field_style . '"' : '' ?>>
			<span class="cursor-pointer mt-1 form-select block w-full pl-3 pr-10 py-2 text-base leading-6 border-gray-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 sm:text-sm sm:leading-5"
				>{{_L(<?= $_data_value_caption ? ($_enum_captions ? "(\$_enum_captions[{$_data_value_caption}] ?: ({$_data_value_caption} ?: 'Select'))" : "({$_data_value_caption} ?: 'Select')") : "\"Select\"" ?>)}}</span>
			<ul class="hidden dropdown rounded-md bg-white shadow-xs origin-top-right absolute right-0 w-full rounded-md shadow-lg z-10">
				<?php foreach ($_enum_vals as $val) : ?>
					<li class="text-sm leading-5 text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:bg-gray-100 focus:text-gray-900">
						<input class="hidden" data-id="<?= $unqid ?>" type="radio" id="{{$unk = uniqid()}}" name="<?= $wval_name ?>" value="<?= $val ?>"{{(<?= $_data_value ?> == "<?= $val ?>") ? ' checked' : ''}} />
						<label class="block px-4 py-2 cursor-pointer" for="{{$unk}}" qc-data-label-val="{{'<?= isset($_enum_captions[$val]) ? $_enum_captions[$val] : $val ?>'}}">
							{{_T('<?= q_property_to_trans($config["__view__"], 'prop-value', $path, $val) ?>', '<?= isset($_enum_captions[$val]) ? $_enum_captions[$val] : $val ?>')}}
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>		
		<input class="qc-form-element qc-dropdown-hidden<?= $_is_mandatory ? ' q-mandatory' : '' ?>" xg-property-value='<?= $xg_tag ?>' type="hidden" id="<?= $unqid ?>" 
			<?= $_extra_attrs ?>
			<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{{\"" . qaddslashes($_q_valid) . "\"}}'" : "" ?>
			<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{{\"" . qaddslashes($_q_fix) . "\"}}'" : "" ?>
			name{{<?= $_data_value_raw ?> == (<?= $tmp_input_value_str ?>) ? '-x' : ''}}="{{<?= $_data_property ?>}}" value="{{<?= $tmp_input_value_str ?>}}" />
		<?php include(static::GetTemplate("form_elements/validation_info.tpl", $config)); ?>
	</div>
</div>