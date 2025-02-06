
<div class="js-form-grp"<?= $_field_style ? ' style="' . $_field_style . '"' : '' ?>>
	
	<?php if ($blockWhenData) : ?>
		@if (!$data || !$data-><?= $property ?>)
	<?php elseif ($blockWhenRecord) : ?>
		@if (!$_qengine_args || !$_qengine_args['mainData'] || !$_qengine_args['mainData']->Id)
	<?php elseif ($_force_block) : ?>
		@if (false)
	<?php endif; ?>

	<?php
	$_is_in_dd_collection = ($__iscollection && !$_is_subpart);
	$vPath = $_is_in_dd_collection ? "\$vars_path" : "(\$vars_path ? \$vars_path.'[{$property}]' : '{$property}')"; 
	$data = $_is_in_dd_collection ? "\$data" : "\$data->{$property}";

	/*
	if ($attrs == "null")
		$attrs = "\" q-path='\" . (\$vars_path ? \$vars_path . \"[Customer]\" : \"Customer\") . \"'\"";
	else
		$attrs .= "\" q-path='\" . (\$vars_path ? \$vars_path . \"[Customer]\" : \"Customer\") . \"'\"";
	*/

	?>
		<div q-path='{{$vars_path ? $vars_path."[<?= $property ?>]" : "<?= $property ?>"}}' 
			 class='full-width qc-dd-wr qc-ref-ctrl <?= $qc_avoid_duplicates_cls.($_has_controller ? ' qc-with-ctrl-dd-deprecated' : '') ?> <?= $_dd_insert_full_data ? ' qc-dd-insert-full-data' : '' ?>'>
			<div class="<?= $_has_controller ? 'col-md-9-deprecated' : '' ?>">
				@include (<?= $_ddToLoad ?>, "<?= $esc_dd_property ?>", "<?= $esc_caption_selector ?>", <?= $binds ?>, isset(<?= $data ?>) ? <?= $data ?>->getModelCaption() : "<?= qaddslashes("Select") ?>", isset(<?= $data ?>) ? <?= $data ?>->toJSON() : null, <?= $vPath ?>."[Id]", isset(<?= $data ?>) ? <?= $data ?>->getId() : null, <?= $vPath ?>."[_ty]", isset(<?= $data ?>) ? q_get_class(<?= $data ?>) : null, "name-x", "qc-form-element<?= $propIsMandatory ? " q-mandatory" : "" ?>", <?= $attrs ?>)
			</div>
			<?php if ($_has_controller) : ?>
				<div class="col-md-3-deprecated">
					@include(referenceControlActions, "<?= $_view_to_load ?>", $data-><?= $property ?><?= ($_PROP_FLAGS["dropdown.action_args"] !== null) ? ", ".$_PROP_FLAGS["dropdown.action_args"] : '' ?>)
				</div>
			<?php endif; ?>
		</div>
		
		<?php include(dirname(dirname(__FILE__))."/form_elements/validation_info.tpl"); ?>

		<?php if (($___block = ($_force_block || $blockWhenData || $blockWhenRecord))) : ?>
			@elseif ($data-><?= $property ?>)
				<div class='qc-dd-rep padding-view qc-dd-rep-prop-<?= $property ?>' q-path='{{$vars_path ? $vars_path."[<?= $property ?>]" : "<?= $property ?>"}}'>
					{{$data-><?= $property ?>->getModelCaption()}}
					<input type='hidden' value='{{$data-><?= $property ?>->getId()}}' name='{{$vars_path ? $vars_path."[<?= $property ?>][Id]" : "<?= $property ?>[Id]"}}' class='qc-dd-rep-input-id' />
					<input type='hidden' value='{{q_get_class($data-><?= $property ?>)}}' name='{{$vars_path ? $vars_path."[<?= $property ?>][_ty]" : "<?= $property ?>[_ty]"}}' class='qc-dd-rep-input-ty' />		
					<input class='qc-dd-full-data' type='hidden' value='<?= "<?= \$data->{$property} ? htmlspecialchars(\$data->{$property}->toJSON(), ENT_QUOTES | ENT_HTML5, 'UTF-8') : \"\" ?>" ?>' />
				</div>
			@endif
		<?php endif ?>
</div>