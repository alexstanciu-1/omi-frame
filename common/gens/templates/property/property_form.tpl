<div xg-property='<?= $property ?>' class="qc-xg-property table-row qc-prop-<?= $property ?> <?= $_PROP_FLAGS["display.".($read_only ? 'view' : 'form')."-css-classes"] ?>">
	<?php if (!$removeLabel) : ?>
		<div class="table-cell prop-label-wr fill">
			<?php if (!$hideLabel) : ?>
				<label class='prop-label col-form-label label-for-<?= $property ?> qc-xg-property-label<?= $propIsMandatory ? ' nowrap' : '' ?>'>
					{{_L('<?= qaddslashes($propCaption) ?>')}}
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
			<?php endif ?>
		</div>
	<?php endif; ?>
	<div class="prop-wrapper-<?= $property ?> fill<?= $removeLabel ? '' : ' table-cell cell-fill' ?>">
		<?= $__field_content ?>
	</div>
</div>