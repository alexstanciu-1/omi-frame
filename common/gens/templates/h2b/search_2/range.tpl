
<div class="srch-intervals"><?php		
		$sm_inp_name = "SRCH_{$sm_data['@filed']}";
		$search_hiddens[$sm_inp_name.'_min']['value'] = "{{\$_GET['".$sm_inp_name."_min'] ?: ".($sm_data['@default'][0] ?? "''")."}}";
		$search_hiddens[$sm_inp_name.'_max']['value'] = "{{\$_GET['".$sm_inp_name."_max'] ?: ".($sm_data['@default'][1] ?? "''")."}}";
	?>
	<label><?= $sm_data['@filed'] ?></label>
	<div class="row">
		<div class="col-lg-6">
			<input sync-identifier="<?= $sm_data['@filed'] ?>" data-srch-handle="<?= $sm_inp_name.'_min' ?>" autocomplete="off" class="qc-input js-keepin-sync js-srch-handle<?= static::Is_Date_Input($sm_input_type) ? ' flatpickr ' : '' ?>" 
				placeholder="<?= $sm_data['@filed'] ?>" 
				type="<?= $sm_input_type ?>" value="{{$_GET['<?= $sm_inp_name.'_min' ?>'] ?: <?= $sm_data['@default'][0] ?? "''" ?>}}" />
		</div>
		<div class="col-lg-6">
			<input sync-identifier="<?= $sm_data['@filed'] ?>" data-srch-handle="<?= $sm_inp_name.'_max' ?>" autocomplete="off" class="qc-input js-keepin-sync js-srch-handle<?= static::Is_Date_Input($sm_input_type) ? ' flatpickr ' : '' ?>" 
				placeholder="<?= $sm_data['@filed'] ?>" 
				type="<?= $sm_input_type ?>" value="{{$_GET['<?= $sm_inp_name.'_max' ?>'] ?: <?= $sm_data['@default'][1] ?? "''" ?>}}" />
		</div>
	</div>
</div>
