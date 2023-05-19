
<div class="srch-intervals">
	<?php $sm_inp_name = "SRCH_{$sm_data['@filed']}"; ?>
	<label><?= $sm_data['@filed'] ?></label>
	<select class="srch-intervals-select qc-input js-keepin-sync js-srch-handle" data-srch-handle="<?= $sm_inp_name.'_tag' ?>" >
		<?php
		
		foreach ($sm_data['@options'] as $sm_k => $sm_v)
		{
			$sm_tag = is_numeric($sm_k) ? $sm_v : $sm_k;
			# list ($sm_interval_min, $sm_interval_max, $sm_interval_tag) = static::Get_Search_Interval_Info($sm_tag);
			?><option data-tag="<?= $sm_tag ?>" data-target="<?= $sm_inp_name ?>" {{ ($_GET['<?= $sm_inp_name.'_tag' ?>'] == <?= var_export($sm_v, true) ?>) ? ' selected ' : '' }}><?= $sm_v ?></option><?php
		}
		
		$search_hiddens[$sm_inp_name.'_tag']['value'] = "";
		
		?>
	</select>
</div>

