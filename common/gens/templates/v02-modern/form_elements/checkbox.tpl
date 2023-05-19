<div class="js-form-grp">
	<label for="{{$_unk = uniqid()}}" class="cursor-pointer">
		<input{{<?= $_data_value ?> ? " checked" : ""}}<?= $_extra_attrs ?> 
            class="<?= $_PROP_FLAGS['checkbox.extraLabel'] ? 'hidden ' : '' ?>form-checkbox my-2 h-4 w-4 text-indigo-600 transition duration-150 ease-in-out qc-form-element qc-checkbox-inp<?= $_is_mandatory ? ' q-mandatory' : '' ?>" 
            xg-property-value='<?= $xg_tag ?>' type="checkbox" id="{{$_unk}}" 
            name-x="{{<?= $_data_property ?>}}" value='{{<?= $_data_value ?>}}' />
		
			<?php if ($_PROP_FLAGS['checkbox.extraLabel']) : ?>
				<span class="form-checkbox-switch flex flex-wrap text-sm">
					<span class="checked p-2 rounded block">
						<?= "{{_L('".$_PROP_FLAGS['checkbox.extraLabel']."')}}" ?>
					</span>
					<span class="unchecked p-2 rounded block">
						{{_T(88, 'No')}}
					</span>
				</span>
			<?php endif; ?>
	</label>
	
	<?php 
		include(static::GetTemplate("form_elements/validation_info.tpl", $config));
	?>
</div>