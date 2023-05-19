<qParent>
	<qPrepend q=".tpl-top-actions-buttons">		
		@if ($grid_mode == 'edit')
			<a href="javascript: void(0);" data-registration-id="{{$data->Id}}" class="js-send-confirmation-email mr-4 inline-flex items-center px-4 py-2 border border-indigo-300 text-sm leading-5 font-medium rounded-md text-indigo-700 bg-white hover:text-indigo-500 focus:outline-none focus:shadow-outline-indigo focus:border-indigo-300 active:text-indigo-800 active:bg-indigo-50 active:text-indigo-800 transition duration-150 ease-in-out">
				{{_T(363, 'Send confirmation email')}}
			</a>
		
			<a href="javascript: void(0);" data-registration-id="{{$data->Id}}" class="js-activate-account mr-4 inline-flex items-center px-4 py-2 border border-indigo-300 text-sm leading-5 font-medium rounded-md text-indigo-700 bg-white hover:text-indigo-500 focus:outline-none focus:shadow-outline-indigo focus:border-indigo-300 active:text-indigo-800 active:bg-indigo-50 active:text-indigo-800 transition duration-150 ease-in-out">
				{{_T(203, 'Activate account')}}
			</a>
		@endif
	</qPrepend>
</qParent>