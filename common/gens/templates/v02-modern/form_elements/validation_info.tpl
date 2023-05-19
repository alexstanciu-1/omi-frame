<?php 
$hasValidationErr = ($validationAlert && (strlen($validationAlert) > 0));

if ($hasValidationErr || $_q_info) : ?>
	<?php if ($hasValidationErr) : ?> 
		<div class="text-xs mt-1">
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
		<span class="text-xs tpl-hint-<?= $property ?>"><b>Hint:</b> {{_L(<?= var_export($_q_info, true)?>)}}</span>
	<?php endif; ?>
<?php endif; ?>