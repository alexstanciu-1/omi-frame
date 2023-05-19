<qParent>
	<qReplace q=".qc-prop-Connection_Active">
		<div>
			<label class="block text-sm font-medium leading-5 text-gray-700 label-for-Password qc-xg-property-label">Testare conexiune</label>
			<div class="mt-1 relative mb-3 text-sm">
				@if ($data->Mail_Sender && $data->Mail_Sender->Connection_Active)
					<div class="text-green-500 font-bold border rounded-md p-2 border-green-500 mb-2">Conexiune activa</div>
				@else 
					<div class="text-red-500 font-bold border rounded-md p-2 border-red-500 mb-2">Conexiune inactiva</div>
				@endif
			
				<a href="javascript: void(0);" class="qc-submit-btn underline text-blue-600 block">Testeaza conexiunea</a>
			</div>
		</div>
	</qReplace>
	
	<qInner q=".tpl-page-heaeding-title">Cont email</qInner>
	<qInner q=".tpl-page-breadcrumb .qc-back-btn">Cont email</qInner>
</qParent>
