<qParent>
	<qReplace q="[qc-xg-property-value^='Password(']">
		<span class="qc-scalar-view mt-1 mb-3 block">XXXXXX</span>
	</qreplace>
	
	<qAppend q=".qc-boxes-row-0-col-1-row-0">
		@if ($grid_mode == 'view')
			@php $properties = $properties = \QQuery('Properties.{Name, API_Managed_User.* WHERE API_Managed_User.Id=?}', $data->Id)->Properties;
			
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
</qParent>
