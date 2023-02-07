<?php 
$hasValidationErr = ($validationAlert && (strlen($validationAlert) > 0));

if ($hasValidationErr || $_q_info) : ?>
	<?php if ($hasValidationErr) : ?> 
		<div class="small-block">
			<?php if (($___blocked = ($_force_block || $blockWhenData || $blockWhenRecord))) : ?>
				@if (!$data-><?= $property ?>)
			<?php endif ?>
				<?= $validationAlert ?>
			<?php if ($___blocked) : ?>
				@endif
			<?php endif ?>
		</div>
	<?php elseif (!$_q_info) :  ?>
		&nbsp;
	<?php endif  ?>
	<?php if ($_q_info) : ?>
		<span class="small-block hint">
			<b><?= _T('0000000000051','Hint') ?>:</b> <?= _L($_q_info) ?></span>
	<?php endif; ?>
<?php endif; ?>