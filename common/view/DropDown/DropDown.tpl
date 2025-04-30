<div class="qc-dd omi-control relative js-dd q-hide-on-click-away <?= ($picker_name ? ' qc-with-picker-dd' : '') . ($cssClass ? " ".trim($cssClass) : "") ?>" 
	 jsFunc="render($from, $selector, $binds, $caption, $full_data, $input_id_name, $input_id_default, $input_type_name, 
		$input_type_default, $input_name_name, $inputs_extra_class, $input_data_name, $input_data_default, $picker_prop)" 
	 q-args="$from = null, $selector = null, $binds = null, $caption = null, $full_data = null, $input_id_name = null, $input_id_default = null, 
		$input_type_name = null, $input_type_default = null, $input_name_name = 'name', $inputs_extra_class = null, $attrs = null, 
		$picker_name = null, $cssClass = null, $picker_placeholder = null"
	<?= ($attrs ? " " . $attrs : "") ?>>
	
	<input class="qc-dd-from" type="hidden" value="{{$from}}" />
	<input class="qc-dd-selector" type="hidden" value="{{$selector}}" />
	
	<?php
		if (is_array($binds['_props_']))
		{
			foreach ($binds['_props_'] as $k => $v)
				$this->$k = $v;
		}
		
		$noItemCaption = $binds['noItemCaption'] ?? 'Select';
		$search_for_placeholder = $binds['search_for_placeholder'] ?? "Search for {$from} here";
	?>

	@if ($binds)
		<input class="qc-dd-binds" type="hidden" value="{{json_encode($binds)}}" />
	@endif

	@php $input_name_name = $input_name_name ?: "name";
	@if ($input_id_name)
		<input type="hidden" class="qc-dd-input-id<?= $inputs_extra_class ? " " . $inputs_extra_class : "" ?>" <?= $input_name_name ?>="{{$input_id_name}}" value="{{$input_id_default}}" />
	@endif

	@if ($input_type_name)
		<input type="hidden" class="qc-dd-input-ty<?= $inputs_extra_class ? " " . $inputs_extra_class : "" ?>" <?= $input_name_name ?>="{{$input_type_name}}" value="{{$input_type_default}}" />
	@endif

	<input class="qc-dd-full-data" type="hidden" value="<?= isset($full_data) ? htmlspecialchars($full_data, ENT_QUOTES | ENT_HTML5, 'UTF-8') : '' ?>" />

	<?php
		if ($picker_name)
		{
			?><input type="text" name-x="<?= $picker_name ?>" class="qc-dd-pick cursor-pointer form-select block w-full pl-3 pr-10 py-2 text-base leading-6 border-gray-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 sm:text-sm sm:leading-5<?= $inputs_extra_class ? " " . $inputs_extra_class : "" ?>" 
				   value="<?= ($caption && ($caption != $this->noItemCaption)) ? $caption : '' ?>" placeholder="<?= $picker_placeholder ?: '' ?>" /><?php
		}
		else
		{
			?><div class="qc-dd-pick cursor-pointer form-select block w-full pl-3 pr-10 py-2 text-base leading-6 border-gray-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 sm:text-sm sm:leading-5"><?= _L($caption ? (($caption === 'Select') ? $noItemCaption : $caption) : "Select") ?></div><?php
		}
	?>
	
	<div class="qc-dd-box q-hide-on-click-away-container rounded-md bg-white shadow-xs hidden origin-top-right absolute right-0 w-full rounded-md shadow-lg z-10">
		<div class="qc-dd-search">
            <input type="text" value="" placeholder="{{$search_for_placeholder}}" class="mt-1 form-input block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
        </div>
		<div class="qc-dd-items">

		</div>
	</div>
</div>