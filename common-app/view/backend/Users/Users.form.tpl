<qParent>
	<qWrap q=".qc-comp-step">
		<start>
			@php $is_property_owner = \Omi\User::Is_Property_Owner();
			<?php if (!$is_property_owner) : ?>
		</start>
		<end>
			<?php endif; ?>
		</end>
	</qWrap>
	
	<qReplace q=".tpl-hint-Password">
		<span class="text-xs"><b>Hint:</b> {{_T(207, 'The password must have at least 8 characters at least one lowercase letter at least one uppercase letter at least one number at least one non-alphanumeric character (E.g. :%&*#@)')}}</span>
	</qReplace>
	
	<qReplace q=".tpl-hint-Password">
		<span class="text-xs"><b>Hint:</b> {{_T(208, 'The API Key must be 40 characters long')}}</span>
	</qReplace>
	
	<qWrap q="[xg-property^='TFH_API_System']">
		<start>			
			@php $is_H2B_Channel = false;
			<?php if ($is_H2B_Channel) : ?>
				<?php echo ''; ?>
			<?php else : ?>
		</start>
		<end>
			<?php endif; ?>
		</end>
	</qWrap>
	
	<qWrap q="[xg-property^='Api_Key']">
		<start>
			@php $is_H2B_Channel = false;
			<?php if (!$is_H2B_Channel) : ?>
		</start>
		<end>
			<?php endif; ?>
		</end>
	</qWrap>
	
	<qAppend q=".qc-boxes-row-0-col-1-row-0">
		@if ($grid_mode == 'edit')
			@php $properties = null;
		@endif
	</qAppend>
	
	<qReplace q="[xg-property-value^='Api_Key']">
		<input xg-property-value="Api_Key(Omi\User)|ro=n,list=n" type="text" class="form-input block w-full sm:text-sm sm:leading-5 js-form-element-input qc-form-element qc-input" name-x="Api_Key" value="{{$data->Api_Key}}" />
	</qReplace>
</qParent>
