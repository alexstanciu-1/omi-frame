<div class="js-form-grp form-input-focus">
	<div class="input-group qc-file-field input-file">
		<input type="text" class="file-path validate form-input" value="{{<?= $_data_value ?>}}" />
		<div class="btn btn-warning qc-file-btn">
			<span>{{_L('File')}}</span>
			<input class="qc-form-element qc-file<?= $_is_mandatory ? ' q-mandatory' : '' ?>" 
				<?= $_extra_attrs ?>
				value="{{<?= $_data_value ?>}}"  type="file" xg-property-value='<?= $xg_tag ?>' name-x="{{<?= $_data_property ?>}}" 
				<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{{\"". qaddslashes($_q_valid) . "\"}}'" : "" ?>
				<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{{\"". qaddslashes($_q_fix) . "\"}}'" : "" ?>
			/>
		</div>
		@if (<?= $_data_value ?>)
			<a target='_blank' class="f-download qc-download btn btn-warning btn-bordered download" href='{{<?= ($_data_value_parent ?: '$data') ?>->getFullPath_URL_Escaped("<?= $property ?>")}}'><i class="fa fa-download"></i></a>
		@endif
	</div>
	@if (<?= $_data_value ?>)
		<div class="image-preview">
			@php $fp = <?= ($_data_value_parent ?: '$data') ?>->getFullPath("<?= $property ?>");
			@php $ext = pathinfo($fp, PATHINFO_EXTENSION);
			@if (in_array(strtolower($ext), ['png', 'bmp', 'jpg', 'jpeg', 'gif']))
			<img src='{{\QApp::GetWebPath($fp)}}' />
			@endif
		</div>
	@endif
	<div class="preview-container"></div>
	<?php  include(static::GetTemplate("form_elements/validation_info.tpl", $config)); ?>
</div>

