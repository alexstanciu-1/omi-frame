<div class="js-form-grp">	
	<?php $unqid = $md5_seed__ ?: uniqid(); ?>
	<div class="qc-radio">
		<?php foreach ($_enum_vals as $val) : ?>
			<input data-id="<?= $unqid ?>"
				<?= $_extra_attrs ?>
				type="radio" id='{{$_unk = uniqid()}}' 
				name='<?= $wval_name ?>'
				value="<?= $val ?>"{{(<?= $_data_value ?> == "<?= $val ?>") ? ' checked' : ''}} />
			<label for='{{$_unk}}'>{{_L('<?= (isset($_enum_captions[$val]) ? $_enum_captions[$val] : $val) ?>')}}</label>
		<?php endforeach; ?>
	</div>
	<input class="qc-form-element qc-radio-grp-hidden<?= $_is_mandatory ? ' q-mandatory' : '' ?>" id="<?= $unqid ?>" xg-property-value='<?= $xg_tag ?>' type="hidden"
		<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{{\"" . (qaddslashes($_q_valid)) . "\"}}'" : "" ?>
		<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{{\"" . (qaddslashes($_q_fix)) . "\"}}'" : "" ?>
		name-x="{{<?= $_data_property ?>}}" value="{{<?= $_data_value ?>}}" />

	<?php include(dirname(__FILE__)."/validation_info.tpl"); ?>
</div>