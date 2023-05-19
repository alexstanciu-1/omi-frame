<div class="srch-options">
	<label><?= $sm_data['@filed'] ?></label>
<?php
	$sm_inp_name = "SRCH_{$sm_data['@filed']}";
	$operation = "IN";

	$_multi = ($operation === "IN");

	$bind_val_index = "";
	$_is_mandatory = false;
	$input_name = $sm_inp_name;
	$_force_heading = true;
	
	if ($_multi)
	{
		$bind_val_index .= "[0]";
		$input_name .= "[0]";
	}
	
	$_enum_vals = array_keys($sm_data['@options']);
	$_enum_captions = $sm_data['@options'];

	?>
	@code
		<?= qArrayToCode($_enum_vals, "_enum_vals", false, null, 0, true) ?>
		<?= qArrayToCode($_enum_captions, "_enum_captions", false, null, 0, true) ?>

		$_enum_selected = $bind_params<?= $bind_val_index ?> ?: [];

		<?php if ($_multi) : ?>
			$_enum_value = "";
			foreach ($_enum_selected ?: [] as $itm)
			{
				$_enum_value .= ((strlen($_enum_value) > 0) ? ", " : "") . (($_enum_captions && $_enum_captions[$itm]) ? $_enum_captions[$itm] : $itm);
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

	@include (\Omi\View\DropDownEnum, $_enum_vals, $_enum_captions, $_enum_selected, $_enum_value, $enum_dd_params, $enum_dd_props, $_enum_multi, "js-keepin-sync js-search-field", "js-search-field");
</div>
