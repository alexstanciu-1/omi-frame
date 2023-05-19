
<div class="js-form-grp">
	
	<?php if ($blockWhenData) : ?>
		@if (!(<?= $_data_value ?>))
	<?php elseif ($blockWhenRecord) : ?>
		@if (!$_qengine_args || !$_qengine_args['mainData'] || !$_qengine_args['mainData']->Id)
	<?php elseif ($_force_block) : ?>
		@if (false)
	<?php endif; ?>

	<?php
	$_is_in_dd_collection = ($__iscollection && !$_is_subpart);
	$vPath = $_is_in_dd_collection ? $_data_property : $_data_property; 
	$data = $_is_in_dd_collection ? $_data_value : $_data_value;

	/*
	if ($attrs == "null")
		$attrs = "\" q-path='\" . (\$vars_path ? \$vars_path . \"[Customer]\" : \"Customer\") . \"'\"";
	else
		$attrs .= "\" q-path='\" . (\$vars_path ? \$vars_path . \"[Customer]\" : \"Customer\") . \"'\"";
	*/

	?>
		<div q-path='{{<?= $vPath ?>}}' 
			 class='flex qc-dd-wr relative items-center qc-ref-ctrl <?= $qc_avoid_duplicates_cls.($_has_controller ? ' qc-with-ctrl-dd-deprecated' : '') ?> <?= $_dd_insert_full_data ? ' qc-dd-insert-full-data' : '' ?>'>
			<div class="<?= $_has_controller ? 'flex-1 mr-3' : 'w-full' ?>">
				@include (<?= $_ddToLoad ?>, "<?= $esc_dd_property ?>", "<?= $esc_caption_selector ?>", <?= $binds ?>, (<?= $data ?>) ? <?= $data ?>->getModelCaption() : "<?= qaddslashes("Select") ?>", (<?= $data ?>) ? <?= $data ?>->toJSON() : null, <?= $vPath ?>."[Id]", (<?= $data ?>) ? <?= $data ?>->getId() : null, <?= $vPath ?>."[_ty]", (<?= $data ?>) ? get_class(<?= $data ?>) : null, "name-x", "qc-form-element<?= $propIsMandatory ? " q-mandatory" : "" ?>", <?= $attrs ?>)
			</div>
			<?php if ($_has_controller) : ?>
				@include(referenceControlActions, "<?= $_view_to_load ?>", <?= $data ?><?= ($_PROP_FLAGS["dropdown.action_args"] !== null) ? ", ".$_PROP_FLAGS["dropdown.action_args"] : '' ?>)
			<?php endif; ?>
		</div>
		
		<?php include(static::GetTemplate("form_elements/validation_info.tpl", $config)); ?>

		<?php if (($___block = ($_force_block || $blockWhenData || $blockWhenRecord))) : ?>
			@elseif (<?= $data ?>)
				<div class='qc-dd-rep padding-view qc-dd-rep-prop-<?= $property ?>' q-path='{{<?= $vPath ?>}}'>
					{{$data-><?= $property ?>->getModelCaption()}}
					<input type='hidden' value='{{<?= $data ?>->getId()}}' name='{{<?= $vPath ?>}}[Id]' class='qc-dd-rep-input-id' />
					<input type='hidden' value='{{get_class(<?= $data ?>)}}' name='{{<?= $vPath ?>}}[_ty]' class='qc-dd-rep-input-ty' />		
					<input class='qc-dd-full-data' type='hidden' value='{{(<?= $data ?>) ? (<?= $data ?>->toJSON()) : ""}}' />
				</div>
			@endif
		<?php endif ?>
</div>