<?php if ($_is_bool) : ?>
	<?php /* @if (isset(<?= $_data_value ?>)) */ ?>
		<div class="padding-view">
			<i class="fa {{(((<?= $_data_value ?>) !== null) && <?= $_data_value ?>) ? 'fa-check success' : 'fa-ban alert'}}"></i>
		</div>
	<?php /* @endif */ ?>
<?php elseif ($_is_date && $_date_format) : ?>
	<span class="qc-scalar-view padding-view" qc-xg-property-value="<?= $xg_tag ?>">{{<?= $_data_value ?> ? date("<?= $_date_format ?>", strtotime(<?= $_data_value ?>)) : ''}}</span>
<?php elseif ($_is_enum) : ?>
	<?php if ($_enum_styles) : ?>
		@code
		<?= qArrayToCode($_enum_styles, "_enum_styles", false, null, 0, true) ?>
		@endcode
		<span class="qc-scalar-view padding-view" qc-xg-property-value="<?= $xg_tag ?>"{{$_enum_styles[<?= $_data_value ?>] ? " style='{$_enum_styles[<?= $_data_value ?>]}'" : ""}}>
		<?php endif; if ($_enum_captions) : ?>
			@code
			<?= qArrayToCode($_enum_captions, "_enum_captions", false, null, 0, true) ?>
			@endcode
			{{_L($_enum_captions[<?= $_data_value ?>])}}
		<?php else: ?>
			<span class="padding-view">
				{{<?= $_data_value ?>}}
			</span>
		<?php endif; if ($_enum_styles) : ?>
		</span>
	<?php endif; ?>
<?php else : ?>
<span class="qc-scalar-view padding-view" qc-xg-property-value="<?= $xg_tag ?>">{{<?= $_data_value ?>}}
	<?php if ($_is_file) : ?>
		@if (<?= $_data_value ?>)
			<a class="fa fa-download qc-in-view-download" target="_blank" href="{{<?= $_data_value ?>->getFullPath_URL_Escaped('<?= $property ?>')}}"></a>
		@endif
	<?php endif; ?>
</span>
<?php endif; ?>