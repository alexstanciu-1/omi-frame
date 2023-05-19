<qParent>
	<qReplace q=".tpl-hint-Password">
		<span class="text-xs"><b>Hint:</b> {{_T(207, 'The password must have at least 8 characters at least one lowercase letter at least one uppercase letter at least one number at least one non-alphanumeric character (E.g. :%&*#@)')}}</span>
	</qReplace>
	
	<qAppend q=".qc-boxes-row-0-col-1-row-0">
		@if ($grid_mode == 'edit')
			@php $properties = null;
			
			@if ($properties)
				<h4 class="text-lg font-medium leading-6 text-gray-900 mb-4 mt-8">{{_T(1, 'Properties')}}</h4>

				@foreach ($properties as $property)
					<div class="mb-2">
						<a href="Properties/edit/{{$property->Id}}" class="underline text-blue-600">{{$property->Name}}</a>
					</div>
				@endforeach
			@endif
		@endif
	</qAppend>
	
	<qreplace q="[xg-property-value^='Api_Key']">
		<input xg-property-value="Api_Key(Omi\User)|ro=n,list=n" type="text" class="form-input block w-full sm:text-sm sm:leading-5 js-form-element-input qc-form-element qc-input" name-x="Api_Key" value="{{$data->Api_Key}}" />
	</qReplace>
</qParent>
