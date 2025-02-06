<?php if ($_is_bool) : ?>
	<?php /* @if (isset(<?= $_data_value ?>)) */ ?>
		<div class="mt-1 mb-3 block">
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{(((<?= $_data_value ?>) !== null) && <?= $_data_value ?>) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}}">{{(((<?= $_data_value ?>) !== null) && <?= $_data_value ?>) ?  _T('<?= q_property_to_trans($config["__view__"], 'prop-value', $path) ?>=Active', 'Active') : _T('<?= q_property_to_trans($config["__view__"], 'prop-value', $path) ?>=Inactive', 'Inactive')}}</span>
		</div>
	<?php /* @endif */ ?>
<?php elseif ($_is_date && $_date_format) : ?>
	<span class="qc-scalar-view mt-1 mb-3 block" qc-xg-property-value="<?= $xg_tag ?>">{{<?= $_data_value ?> ? date("<?= $_date_format ?>", strtotime(<?= $_data_value ?>)) : ''}}</span>
<?php elseif ($_is_enum) : ?>
	<?php if ($_enum_styles) : ?>
		@code
			<?= qArrayToCode($_enum_styles, "_enum_styles", false, null, 0, true) ?>
		@endcode
		<span class="qc-scalar-view mt-1 mb-3 block" qc-xg-property-value="<?= $xg_tag ?>"{{$_enum_styles[<?= $_data_value ?>] ? " style='{$_enum_styles[<?= $_data_value ?>]}'" : ""}}>
	<?php endif; 
			if ($_enum_captions) : ?>
				@code
					<?= qArrayToCode($_enum_captions, "_enum_captions", false, null, 0, true) ?>
				@endcode				
				<span class="mt-1 mb-3 block">
					{{_L(<?= $_data_value ?>)}}
				</span>
			<?php else: ?>
				<?php if ($_PROP_FLAGS['view.style'] == 'style_1'):  ?>
					<span class="mt-1 mb-3 block">
						<span class="capitalize px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">{{<?= $_data_value ?>}}</span>
					</span>
				<?php else: ?>
					<span class="mt-1 mb-3 block">{{_T('<?= q_property_to_trans($config["__view__"], 'prop-value', $path) ?>=' . <?= $_data_value ?>, <?= $_data_value ?>)}}</span>
				<?php endif; ?>
			<?php endif; 
	if ($_enum_styles) : ?>
		</span>
	<?php endif; ?>
<?php else : ?>
    <span class="qc-scalar-view mt-1 mb-3 block" qc-xg-property-value="<?= $xg_tag ?>">
        <?php if ($_is_file) : ?>
			@php $extension = end(explode('.', <?= $_data_value ?>));
			@php $acceptedExt = ['png', 'jpg', 'svg', 'webp'];
			
			@if (in_array($extension, $acceptedExt))
				<img class="max-w-xs" src="{{$data->getFullPath('<?= $property ?>')}}" />
			@else
				{{<?= $_data_value ?>}}
			@endif
			
            @if (<?= $_data_value ?>)
                <a class="text-indigo-700 cursor-pointer qc-in-view-download" target="_blank" href="{{$data->getFullPath('<?= $property ?>')}}">Download</a>
            @endif
		<?php else : ?>
			{{<?= $_data_value ?>}}
        <?php endif; ?>
    </span>
<?php endif;
if (!$_is_password) : ?><input type='hidden' name-x="{{<?= $_data_property ?>}}" value='{{<?= $_data_value ?>}}' /><?php endif;