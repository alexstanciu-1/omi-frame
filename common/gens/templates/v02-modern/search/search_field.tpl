<?php if (!$params) : ?>
	<div class="qc-checkbox f-left">
		<input sync-identifier='<?= $input_name ?>' class="js-search-field js-keepin-sync" type="checkbox" name="<?= $input_name ?>" id="<?= $input_name ?>" value="1" />
		<label for="<?= $input_name ?>"></label>
	</div>
<?php
	elseif ($_is_enum) :
		$_multi = ($operation === "IN");

		if ($_multi)
		{
			$bind_val_index .= "[0]";
			$input_name .= "[0]";
		}

		?>
		@code
			<?= qArrayToCode($_enum_vals, "_enum_vals", false, null, 0, true) ?>
			<?= qArrayToCode($_enum_captions, "_enum_captions", false, null, 0, true) ?>

			$_enum_selected = $bind_params<?= $bind_val_index ?> ?: [];

			<?php if ($_multi) : ?>
				$_enum_value = "";
				foreach ($_enum_selected ?: [] as $itm)
				{
					$_enum_value .= ((strlen($_enum_value) > 0) ? ", " : "") . (($_enum_captions && $_enum_captions[$itm]) ? _L($_enum_captions[$itm]) : $itm);
				}
			<?php else : ?>
				$_enum_value = $bind_params<?= $bind_val_index ?> ? ($_enum_captions ? $_enum_captions[$bind_params<?= $bind_val_index ?>] : $bind_params<?= $bind_val_index ?>) : null;
			<?php endif; ?>

			$enum_dd_params = [
				"_qis_form_element" => true,
				"_is_mandatory" => <?= $_is_mandatory ? "true" : "false" ?>,
				"_xg_tag" => "",
				"_extra_attrs" => "",
				"_q_valid" => "",
				"_q_fix" => "",
				"_q_info" => "",
				"_prop_name" => "<?= qaddslashes($input_name) ?>",
				"_validation" => ""
			];

			$enum_dd_props = [
				"dd_cls" => "max-w-30",
				"dd_style" => ""
			];

			$_enum_multi = <?= $_force_heading ? "[".($_multi ? "true" : "false")."]" : ($_multi ? "true" : "false") ?>;

		@endcode
		
		<div class="table-heading-caption">{{_L("<?= qaddslashes($propCaption) ?>")}}</div>
		@include (\Omi\View\DropDownEnum, $_enum_vals, $_enum_captions, $_enum_selected, $_enum_value ?: "<?= qaddslashes($propCaption) ?>", $enum_dd_params, $enum_dd_props, $_enum_multi, "js-keepin-sync js-search-field", "js-search-field");
<?php elseif (isset($type) && ((strtolower($type[0]) !== $type[0]) || ($identifiers_path && (strtolower(end($identifiers_path)) === "id")))) : 
	switch ($operation) :
		case "=" :
		case "<" :
		case "<=" :
		case ">=" :
		case "<>" :
		case "LIKE":
		{
			?>
			@include (\Omi\View\DropDown<?= $_isTree ? "Tree" : ""?>, "<?= $esc_dd_property ?>", "<?= $esc_caption_selector ?>", [], "<?= $esc_dd_caption ?>", null, "<?= $bind_path[0] ?>", isset($bind_params<?= $bind_val_index ?>) ? $bind_params<?= $bind_val_index ?> : null, null, null, 'name', null, null, null, 'js-search-field js-keepin-sync')
			<?php
			break;
		}
		case "IN":
		{
			?>
			@include (\Omi\View\DropDown<?= $_isTree ? "Tree" : "" ?>, "<?= $esc_dd_property ?>", "<?= $esc_caption_selector ?>", [], "<?= $esc_dd_caption ?>", null, "<?= $bind_path[0] ?>", isset($bind_params<?= $bind_val_index ?>) ? $bind_params<?= $bind_val_index ?> : null, null, null, 'name', null, null, null, 'js-search-field js-keepin-sync')
			<?php
			break;
		}
		case "IS_A" :
		{
			die("IS_A");
			break;
		}
		case "BETWEEN" :
		{
			die("between");
			break;
		}
		default:
		{
			//var_dump($operation);
			//throw new \Exception("unknown OP");
		}
	endswitch;	
elseif ($type == "boolean") : ?>
	<div class='js-radio-grp css-radio-grp'>
		<div class="qc-radio">
			<input sync-identifier='<?= $input_name ?>-yes' class="js-search-field js-keepin-sync" type="radio" name="<?= $input_name ?>" id="<?=  ($qsearch ? 'qsf-' : '' ) . $input_name ?>-yes" value="1" {{(isset($bind_params<?= $bind_val_index ?>) && $bind_params<?= $bind_val_index ?> == 1) ? checked : ''}} />
			<label for="<?= ($qsearch ? 'qsf-' : '' ) . $input_name ?>-yes"><?= _L('Yes') ?></label>
		</div>
		<div class="qc-radio">
			<input sync-identifier='<?= $input_name ?>-no' class="js-search-field js-keepin-sync" type="radio" name="<?= $input_name ?>" id="<?= ($qsearch ? 'qsf-' : '' ) . $input_name ?>-no" value="0" {{(isset($bind_params<?= $bind_val_index ?>) && $bind_params<?= $bind_val_index ?> == 0) ? checked : ''}} />
			<label for="<?= ($qsearch ? 'qsf-' : '' ) . $input_name ?>-no"><?= _L('No') ?></label>
		</div>
		<div class="qc-radio">
			<input sync-identifier='<?= $input_name ?>-na' class="js-search-field js-keepin-sync" type="radio" name="<?= $input_name ?>" id="<?= ($qsearch ? 'qsf-' : '' ) . $input_name ?>-na" value="" {{!isset($bind_params<?= $bind_val_index ?>) ? checked : ''}} />
			<label for="<?= ($qsearch ? 'qsf-' : '' ) . $input_name ?>-na"><?= _L('N/A') ?></label>
		</div>
	</div>
<?php elseif (($type == "date") || ($type == "datetime")) : ?>
    <div class="table-heading-search-field relative">
        <input sync-identifier='<?= $input_name ?>' autocomplete="off" class="form-input qc-input js-search-field js-keepin-sync" placeholder="<?= _L('Search by') ?> <?= _L($propCaption) ?>" type='text' name='<?= $input_name ?>' value='{{$bind_params<?= $bind_val_index ?>}}' />
    </div>
<?php else :
    switch ($operation) :
        case "=" : 
        case "<" : 
        case "<=" : 
        case ">=" : 
        case "<>" : 
        {
            ?>
				<div class="table-heading-search-field relative">
                    <input sync-identifier='<?= $input_name ?>' autocomplete="off" class="form-input js-search-field js-keepin-sync" placeholder="<?= _L('Search by') ?> <?= _L($propCaption) ?>" name='<?= $input_name ?>' type="text" value="{{$bind_params<?= $bind_val_index ?>}}" />
                    <svg class="h-5 w-5 absolute right-0 top-1/2 text-gray-500 table-search-icon mr-4 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            <?php
            break;
        }
        case "LIKE" :
        {					
            ?>
                <div class="table-heading-search-field relative">
                    <input sync-identifier='<?= $input_name ?>' autocomplete="off" class="form-input js-search-field js-keepin-sync" placeholder="<?= _L($propCaption) ?>" name='<?= $input_name ?>' type='text' value='{{isset($bind_params<?= $bind_val_index ?>) ? str_replace("%", "", $bind_params<?= $bind_val_index ?>) : ""}}' />
                    <svg class="h-5 w-5 absolute right-0 top-1/2 text-gray-500 table-search-icon mr-4 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            <?php
            break;
        }
        case "IN":
        {
            ?>
				<div class="table-heading-search-field relative">
                    <input sync-identifier='<?= $input_name ?>' autocomplete="off" class="form-input js-search-field js-keepin-sync" placeholder="<?= _L('Search by') ?> <?= _L($propCaption) ?>" name='<?= $input_name ?>' type="text" value="{{$bind_params<?= $bind_val_index ?>}}" />
                    <svg class="h-5 w-5 absolute right-0 top-1/2 text-gray-500 table-search-icon mr-4 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            <?php
            break;
        }
        case "IS_A" :
        {
            break;
        }
        case "BETWEEN" :
        {
            die("between");
            break;
        }
        default:
        {
            //throw new \Exception("unknown OP");
        }
    endswitch;
endif;