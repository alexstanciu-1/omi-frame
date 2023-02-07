
<div class="srch-intervals"><?php		
		$sm_inp_name = "SRCH_{$sm_data['@filed']}";
		$search_hiddens[$sm_inp_name.'[like]']['value'] = "";
	?>
	<label><?= $sm_data['@filed'] ?></label>
	<input sync-identifier="<?= $sm_data['@filed'] ?>" autocomplete="off" class="qc-input js-search-field js-keepin-sync" 
		placeholder="<?= $sm_data['@filed'] ?>" 
		type="<?= $sm_input_type ?>" value="" />
</div>
