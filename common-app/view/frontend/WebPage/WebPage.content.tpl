<virtual q-args="$userData = null">	
	@if (($property = \Omi\View\Controller::TFH_Get_PropertyFilter()) && isset($property->Id) && (($Check_Property_DB_Locations = \Omi\TFH\Property::Check_Property_DB_Locations($property->Id)) !== true))
		<div class="grid lg:mb-8 grid-cols-1 md:grid-cols-1 gap-8 gap-y-8 qc-boxes-row-0">
			<div class="flex flex-col gap-y-8 qc-boxes-row-1-col-0">
				<div class="flex-1 rounded-lg bg-white p-4 lg:p-8 shadow js-details-box__main  qc-boxes-row-0-col-0-row-0">
					<span style="color: red;">Calendar Indexing Error for this property. Please contact administrator.</span>
					@if ($Check_Property_DB_Locations !== false)
						@php qvar_dumpk($Check_Property_DB_Locations);
					@endif
				</div>
			</div>
		</div>
	@endif
	
    <main class="flex-1 relative z-9 relative {{$userData ? 'pb-16' : ''}}">
        <?php		
            if ($this->contentCtrl)
                $this->contentCtrl->render();
        ?>
    </main>
</virtual>