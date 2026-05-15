@code
<?php
$__tfh_ml_languages = tfh_get_languages_with_labels();
$__tfh_ml_default_language = tfh_get_default_language();
$__tfh_ml_languages_json = htmlspecialchars(json_encode($__tfh_ml_languages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES | ENT_HTML5, 'UTF-8');
$__tfh_ml_default_language_attr = htmlspecialchars($__tfh_ml_default_language, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$__tfh_ml_widget_id = 'qc-ml-' . substr(sha1(($md5_seed__ ?: $property) . '|' . ($path ?? $property) . '|' . $__tfh_ml_input_tag . '|' . $__tfh_ml_input_type), 0, 12);
$__tfh_ml_input_tag = $_is_textarea ? 'textarea' : 'input';
$__tfh_ml_input_type = $_is_password ? 'password' : (($_PROP_FLAGS["input.type"] ? (string)$_PROP_FLAGS["input.type"] : "text"));
$__tfh_ml_widget_class = "qc-multilang-field" . ($_is_textarea ? " qc-multilang-textarea" : " qc-multilang-text");
?>
@endcode
<div class="<?= $__tfh_ml_widget_class ?>" data-ml-widget-id="<?= $__tfh_ml_widget_id ?>" data-ml-default-language="<?= $__tfh_ml_default_language_attr ?>" data-ml-languages="<?= $__tfh_ml_languages_json ?>" data-ml-input-tag="<?= $__tfh_ml_input_tag ?>" data-ml-input-type="<?= htmlspecialchars($__tfh_ml_input_type, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>" data-ml-use-editor="<?= $useEditor ? '1' : '0' ?>">
	<div class="qc-ml-panels"></div>
	<div class="mt-2 qc-multilang-toolbar">
		<button type="button" class="qc-ml-add-language inline-flex items-center gap-1 text-xs text-gray-600 hover:text-gray-900">
			<span aria-hidden="true">+</span>
			<span><?= htmlspecialchars(_L('Add language'), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></span>
		</button>
	</div>
	<input type="hidden"
		<?= $_extra_attrs ?>
		xg-property-value='<?= $xg_tag ?>'
		<?= ($_q_valid && (strlen($_q_valid) > 0)) ? " q-valid='{{\"". qaddslashes($_q_valid) . "\"}}'" : "" ?>
		<?= ($_q_fix && (strlen($_q_fix) > 0)) ? " q-fix='{{\"". qaddslashes($_q_fix) . "\"}}'" : "" ?>
		class="qc-form-element js-form-element-input qc-multilang-store<?= $_is_mandatory ? ' q-mandatory' : '' ?>"
		name-x="{{<?= $_data_property ?>}}"
		value='{{<?= $_is_password ? "''" : $_data_value ?>}}' />
	<input type='hidden' name="{{<?= $_data_property_id_ ?>}}" value='{{<?= $_data_value_id_ ?>}}' />
</div>
<?php
	include(static::GetTemplate('form_elements/validation_info.tpl', $config));
?>
