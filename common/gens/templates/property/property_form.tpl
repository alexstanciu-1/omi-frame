<?php if ($config['withSecurity']) { ?>
@if ($this->allowView($data, '<?= $property ?>', $vars_path))
	<?php } ?>
	<?php
		$formInputClass = '';
		if ((!$_PROP_FLAGS['struct.subpart'] && $_PROP_FLAGS['__is_pure_reference']) || 
			$_PROP_FLAGS['type.bool'] ||
			$config['__readonly'] ||
			$_PROP_FLAGS['block.whenData'] || 
			$_PROP_FLAGS['type.file'] || 
			$_PROP_FLAGS['block.whenRecord'] || 
				($_PROP_FLAGS['type.enum'] && (($_PROP_FLAGS['enum.display'] == 'dropdown') || ($_PROP_FLAGS['enum.vals'] > 2))))
		{
			$formInputClass = 'form-input-focus';
		}
	?>
<?= $_PROP_FLAGS["display.".($read_only ? 'view' : 'form')."-css-row-before"] ? '<div class="row">' : "" ?>
<?php if (!$config['inside_custom_group_layout']) { ?>
<!--<div class="row form-row-margin">-->
<!-- <div class="col-lg-<?= ($config['inside_custom_group_layout_width']) ?: '6'; ?> col-md-12 form-row-margin js-container-<?= $property ?>"> -->
<?php } ?>
	<div xg-property='<?= $property ?>' class="form-row-margin row qc-xg-property js-container-<?= $property ?> qc-prop-<?= $property ?> <?= $formInputClass ?> <?= $_PROP_FLAGS["display.".($read_only ? 'view' : 'form')."-css-classes"] ?>">
		
		<?php if (!$removeLabel) : ?>
			<?php if (!$hideLabel) : ?>
				<div class="col-sm-3">
					<label class="label-padding col-form-label label-for-<?= $property ?> qc-xg-property-label<?= $propIsMandatory ? ' nowrap' : '' ?>">
						{{_L('<?= $propCaption ?>')}}
						<?php if ($propIsMandatory) : ?>
							<span class="required">*</span>
						<?php endif; ?>
						<?php if ($info && false) : ?>
							<span class="qc-tooltip-wrapper">
								<div class="qc-tooltip info">
									<i class="fa fa-info-circle"></i>
									<span class="tooltip"><?= $info ?></span>
								</div>
							</span>
						<?php endif; ?>
					</label>
				</div>
			<?php endif ?>
		<?php endif; ?>
		<div class="col-md-<?= ($removeLabel || $hideLabel) ? '12' : '9' ?> form-input-border prop-wrapper-<?= $property ?>">
			<?php
			if ($config['withSecurity']) {
				if ($config['__readonly']) { ?>
					@if ($this->allowView($data, '<?= $property ?>', $vars_path))
						<?= $__field_content ?>
					@endif
				<?php }
				else { ?>
					@if ($this->allowEdit($data, '<?= $property ?>', $vars_path))
						<?= $__field_content ?>
					@else
						<?= $__field_content_readonly ?>
					@endif
				<?php }
			} else { ?>
				<?= $__field_content ?>
			<?php } ?>
		</div>
	</div>
<?php if (!$config['inside_custom_group_layout']) { ?>
<!--</div>-->
<?php } ?>
<?= $_PROP_FLAGS["display.".($read_only ? 'view' : 'form')."-css-row-after"] ? '</div>' : "" ?>
<?php if ($config['withSecurity']) { ?>
@endif
<?php } ?>
