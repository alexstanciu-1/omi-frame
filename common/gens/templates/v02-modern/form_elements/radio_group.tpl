<div class="js-form-grp">	
	<?php $isInline = $_PROP_FLAGS["label.display"] === 'inline' ?>
	<?php $unqid = $md5_seed__ ?: uniqid(); ?>
	<div class="qc-radio flex flex-wrap">
		<?php $i = 0; ?>
		<?php $numItems = count($_enum_vals); ?>
		@php $_tmp_rg_name = "_rdrpl_" . uniqid();
		<?php foreach ($_enum_vals as $val) : ?>
			<label for="{{$unk = uniqid()}}" class="flex items-center <?= (++$i === $numItems) ? '' : 'mr-4' ?> <?= !$isInline ? 'mb-2 mt-2' : ''; ?> cursor-pointer">
				<input data-id="{{$_tmp_rg_name}}" class="form-radio <?= $isInline ? 'hidden' : '' ?> h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" type="radio" id="{{$unk}}" name="{{$_tmp_rg_name}}" value="<?= $val ?>"{{(<?= $_data_value ?> == "<?= $val ?>") ? ' checked' : ''}} />
				<div class="pl-2 <?= $isInline ? 'pl-4 form-radio-box border py-2 px-4 rounded border-gray-500 text-gray-700' : '' ?>">
					<span class="block text-sm leading-5 font-medium capitalize">
						{{_L(<?= var_export( isset($_enum_captions[$val]) ? $_enum_captions[$val] : $val, true) ?>)}}
					</span>
				</div>
			</label>
		<?php endforeach; ?>
	</div>
	<input class="qc-form-element qc-radio-grp-hidden<?= $_is_mandatory ? ' q-mandatory' : '' ?>" id="{{$_tmp_rg_name}}" xg-property-value='<?= $xg_tag ?>' type="hidden"
		<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{{\"" . (qaddslashes($_q_valid)) . "\"}}'" : "" ?>
		<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{{\"" . (qaddslashes($_q_fix)) . "\"}}'" : "" ?>
		name-x="{{<?= $_data_property ?>}}" value="{{<?= $_data_value ?>}}" />

	<?php 

	include(static::GetTemplate("form_elements/validation_info.tpl"));

	?>
	
</div>