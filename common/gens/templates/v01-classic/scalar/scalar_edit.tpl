<?php if ($config['inside_custom_group']): ?>
	@if (<?= $_data_value_id_ ?>)
		<input type="hidden" value="{{<?= $_data_value_id_ ?>}}" name="{{<?= $_data_property_id_ ?>}}" />
	@endif
<?php endif; ?>
<?php if ($blockWhenData) : ?>
	@if ((<?= $_data_value ?>) === null)
<?php elseif ($blockWhenRecord) : ?>
	@if (!$_qengine_args || !$_qengine_args['mainData'] || !$_qengine_args['mainData']->Id)
<?php elseif ($_force_block) : ?>
	@if (false)
<?php endif;
	include(dirname(__FILE__)."/scalar_edit_inner.tpl");
if ($_force_block || $blockWhenData || $blockWhenRecord) : ?>
	@else
		<div class='qc-blocked-when-data padding-view'>
			<?php if ($_is_bool) : ?>
				{{(<?= $_data_value ?> ? _L('Yes') : _L('No'))}}
			<?php elseif ($_is_password) : ?>
				{{"**********"}}
			<?php elseif ($_is_enum) : ?>	
				<?php if ($_enum_styles) : ?>
					@code
					<?= qArrayToCode($_enum_styles, "_enum_styles", false, null, 0, true) ?>
					@endcode
					<span class="qc-scalar-view" qc-xg-property-value="<?= $xg_tag ?>"{{$_enum_styles[<?= $_data_value ?>] ? " style='{$_enum_styles[<?= $_data_value ?>]}'" : ""}}>
				<?php endif; if ($_enum_captions) : ?>
					@code
					<?= qArrayToCode($_enum_captions, "_enum_captions", false, null, 0, true) ?>
					@endcode
					{{$_enum_captions[<?= $_data_value ?>]}}
				<?php else: ?>
					{{<?= $_data_value ?>}}
				<?php endif; if ($_enum_styles) : ?>
				</span>
				<?php endif; ?>
			<?php else : ?>		
				{{<?= $_data_value ?>}}
			<?php endif; ?>
			<input type='hidden' name="{{<?= $_data_property ?>}}" value='{{<?= $_data_value ?>}}' <?= $_is_password ? " autocomplete='new-password' " : "" ?> />
		</div>
	
	@endif
<?php endif; ?>