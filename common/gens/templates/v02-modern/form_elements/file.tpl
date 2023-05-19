<div class="js-form-grp form-input-focus max-w-md">
	<div class="relative mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md qc-file-field">
		<!-- <input type="text" class="file-path validate form-input" value="{{<?= $_data_value ?>}}" /> -->
		@if (<?= $_data_value ?>)
			<div class="image-preview js-file-wrapper">
				@php $fp = $data->getFullPath("<?= $property ?>");
				@php $ext = pathinfo($fp, PATHINFO_EXTENSION);
				
				@if (in_array(strtolower($ext), ['png', 'bmp', 'jpg', 'jpeg', 'gif']))
					<img src="{{$fp}}" class="w-full max-w-sm" />
				@else
					<a href="{{$fp}}" class="text-indigo-700 cursor-pointer js-file-field-container">{{<?= $_data_value?> }}</a>
					<div class="relative text-center">
						<button class="block mt-2 font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition duration-150 ease-in-out w-full">
							{{_T(113, 'Upload a file')}}
						</button>
						<input class="absolute top-0 left-0 right-0 bottom-0 w-full opacity-0 qc-form-element qc-file<?= $_is_mandatory ? ' q-mandatory' : '' ?>" 
							<?= $_extra_attrs ?>
							value="{{<?= $_data_value ?>}}"  type="file" xg-property-value='<?= $xg_tag ?>' name-x="{{<?= $_data_property ?>}}" 
							<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{{\"". qaddslashes($_q_valid) . "\"}}'" : "" ?>
							<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{{\"". qaddslashes($_q_fix) . "\"}}'" : "" ?> />
					</div>
				@endif
			</div>
		@else
			<div class="js-file-field-container">
				<div class="text-center qc-file-btn">
					<svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
						<path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
					</svg>
					<p class="mt-1 text-sm text-gray-600">
						<button class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition duration-150 ease-in-out">
							{{_T(113, 'Upload a file')}}
						</button>
						{{_T(114, 'or drag and drop')}}
					</p>
				</div>
			</div>
		@endif
		
		@if (!$ext || in_array(strtolower($ext), ['png', 'bmp', 'jpg', 'jpeg', 'gif']))
			<input class="absolute top-0 left-0 right-0 bottom-0 w-full opacity-0 qc-form-element qc-file<?= $_is_mandatory ? ' q-mandatory' : '' ?>" 
				<?= $_extra_attrs ?>
				value="{{<?= $_data_value ?>}}"  type="file" xg-property-value='<?= $xg_tag ?>' name-x="{{<?= $_data_property ?>}}" 
				<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{{\"". qaddslashes($_q_valid) . "\"}}'" : "" ?>
				<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{{\"". qaddslashes($_q_fix) . "\"}}'" : "" ?> />
		@endif
	</div>
	
	@if (<?= $_data_value ?>)
		
	@endif
	
	<?php include(static::GetTemplate("form_elements/validation_info.tpl", $config)); ?>
</div>

