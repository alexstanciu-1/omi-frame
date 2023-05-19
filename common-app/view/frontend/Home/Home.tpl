<div class="py-2 px-2" style="min-height: 100%;">
	<?php
		// is agency
		$is_H2B_Channel = false;
		$isSuperadmin = \Omi\User::Is_Superadmin();
		$is_H2B_Superadmin = $isSuperadmin;
		$isPropertyOwner = false;
		
		$active_properties = null;
		
		$accountConfigurations = null;
	?>
	
	@if ($isPropertyOwner || $is_H2B_Channel)
		@if ($isPropertyOwner && $accountConfiguration->Active)
			<div class="flex flex-col mx-auto px-4 sm:px-6 lg:px-8 mt-8 hidden">
				<h4 class="text-xl leading-6 font-medium text-gray-700 mb-2">{{_T(34, 'Configuration')}}</h4>
				<div class="flex-1 rounded-lg bg-white p-4 lg:p-8 shadow lg:pt-0 pt-0">
					<div class="border-b flex justify-end">
						<a href="javascript: void(0);" class="p-4 align-right inline-block js-close-account-configuration">
							<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
							</svg>
						</a>
					</div>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
						<div class="flex flex-col">
							<label for="set_company_data" class="cursor-pointer mb-4">
								1. 
								<input class="form-checkbox my-2 h-4 w-4 text-indigo-600 transition duration-150 ease-in-out js-account-configuration-check" type="checkbox" id="set_company_data" name="Set_Company_Data" value="1" {{($accountConfiguration->Set_Company_Data) ? 'checked' : ''}} />
								{{_T(287, 'Set company data')}}
								<a href="Property_Owners" class="inline-block">
									<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
									</svg>
								</a>
							</label>
							<label for="create_property" class="cursor-pointer mb-4">
								2. 
								<input class="form-checkbox my-2 h-4 w-4 text-indigo-600 transition duration-150 ease-in-out js-account-configuration-check" type="checkbox" id="create_property" name="Create_Property" value="1" {{($accountConfiguration->Create_Property) ? 'checked' : ''}} />
								{{_T(288, 'Create a property')}}
								<a href="Properties/add" class="inline-block">
									<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
									</svg>
								</a>
							</label>
							<label for="set_age_intervals" class="cursor-pointer mb-4">
								3. 
								<input class="form-checkbox my-2 h-4 w-4 text-indigo-600 transition duration-150 ease-in-out js-account-configuration-check" type="checkbox" id="set_age_intervals" name="Set_Age_Intervals" value="1" {{($accountConfiguration->Set_Age_Intervals) ? 'checked' : ''}} />
								{{_T(289, 'Set age intervals')}}
								<a href="Age_Intervals_App" class="inline-block">
									<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
									</svg>
								</a>
							</label>
							<label for="create_occupancy" class="cursor-pointer mb-4">
								4. 
								<input class="form-checkbox my-2 h-4 w-4 text-indigo-600 transition duration-150 ease-in-out js-account-configuration-check" type="checkbox" id="create_occupancy" name="Create_Occupancy" value="1" {{($accountConfiguration->Create_Occupancy) ? 'checked' : ''}} />
								{{_T(290, 'Create occupancy per room')}}
								<a href="Room_Occupancies/add" class="inline-block">
									<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
									</svg>
								</a>
							</label>
							<label for="create_room" class="cursor-pointer mb-4">
								5. 
								<input class="form-checkbox my-2 h-4 w-4 text-indigo-600 transition duration-150 ease-in-out js-account-configuration-check" type="checkbox" id="create_room" name="Create_Room" value="1" {{($accountConfiguration->Create_Room) ? 'checked' : ''}} />
								{{_T(291, 'Create room')}}
								<a href="Properties_Rooms/add" class="inline-block">
									<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
									</svg>
								</a>
							</label>
						</div>
						<div class="flex flex-col">
							<label for="create_meal_services" class="cursor-pointer mb-4">
								6. 
								<input class="form-checkbox my-2 h-4 w-4 text-indigo-600 transition duration-150 ease-in-out js-account-configuration-check" type="checkbox" id="create_meal_services" name="Create_Meal_Services" value="1" {{($accountConfiguration->Create_Meal_Services) ? 'checked' : ''}} />
								{{_T(292, 'Create meal service')}}
								<a href="Services/add" class="inline-block">
									<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
									</svg>
								</a>
							</label>
							<label for="set_payment_policy" class="cursor-pointer mb-4">
								7. 
								<input class="form-checkbox my-2 h-4 w-4 text-indigo-600 transition duration-150 ease-in-out js-account-configuration-check" type="checkbox" id="set_payment_policy" name="Set_Payment_Policy" value="1" {{($accountConfiguration->Set_Payment_Policy) ? 'checked' : ''}} />
								{{_T(293, 'Create a payment policy')}}
								<a href="Payment_Policies/add" class="inline-block">
									<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
									</svg>
								</a>
							</label>
							<label for="set_cancellation_policy" class="cursor-pointer mb-4">
								8. 
								<input class="form-checkbox my-2 h-4 w-4 text-indigo-600 transition duration-150 ease-in-out js-account-configuration-check" type="checkbox" id="set_cancellation_policy" name="Set_Cancellation_Policy" value="1" {{($accountConfiguration->Set_Cancellation_Policy) ? 'checked' : ''}} />
								{{_T(294, 'Create a cancellation policy')}}
								<a href="Cancellation_Policies/add" class="inline-block">
									<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
									</svg>
								</a>
							</label>
							<label for="create_rate_plan" class="cursor-pointer mb-4">
								9. 
								<input class="form-checkbox my-2 h-4 w-4 text-indigo-600 transition duration-150 ease-in-out js-account-configuration-check" type="checkbox" id="create_rate_plan" name="Create_Rate_Plan" value="1" {{($accountConfiguration->Create_Rate_Plan) ? 'checked' : ''}} />
								{{_T(295, 'Create a rate plan')}}
								<a href="Rate_Plans/add" class="inline-block">
									<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
									</svg>
								</a>
							</label>
							<label for="add_rate_set_request" class="cursor-pointer mb-4">
								10. 
								<input class="form-checkbox my-2 h-4 w-4 text-indigo-600 transition duration-150 ease-in-out js-account-configuration-check" type="checkbox" id="add_rate_set_request" name="Add_Rate_Set_Request" value="1" {{($accountConfiguration->Add_Rate_Set_Request) ? 'checked' : ''}} />
								{{_T(296, 'Create add rate set requests')}}
								<a href="Rate_Set_Requests" class="inline-block">
									<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
									</svg>
								</a>
							</label>
						</div>
					</div>					
				</div>
			</div>
		@endif
		
		@if (($isPropertyOwner || $is_H2B_Superadmin) && $inactive_properties)
			<div class="mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-8">
				<div class="flex flex-col gap-y-4">
					<h4 class="text-xl leading-6 font-medium text-gray-700 mb-2">{{_T(345, 'To Setup Properties')}}</h4>
					<div class="flex flex-col mt-2" style="transform: translateZ(0);">
						<div class="align-middle min-w-full overflow-x-auto shadow overflow-hidden sm:rounded-lg">
							<table class="min-w-full divide-y divide-cool-gray-200 js-itms-table table-order">
								<thead class="divide-y">
									<tr class="table-heading-search">
										<th class="px-6 py-4 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase tracking-wider sticky top-0 z-1 w-96">
											<div class="table-heading-caption">
												Name
											</div>
										</th>
										<th class="text-center px-2 py-4 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase tracking-wider sticky top-0 z-1">
											<div class="table-heading-caption compact relative" data-tippy-content="Intervale de varste">
												Intervale de vârsta
											</div>
										</th>
										<th class="text-center px-2 py-4 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase tracking-wider sticky top-0 z-1">
											<div class="table-heading-caption" data-tippy-content="Camere setate">
												Camere
											</div>
										</th>
										
										<th class="text-center px-2 py-4 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase tracking-wider sticky top-0 z-1">
											<div class="table-heading-caption" data-tippy-content="Ocupari">
												Ocupari
											</div>
										</th>
										
										<th class="text-center px-2 py-4 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase tracking-wider sticky top-0 z-1">
											<div class="table-heading-caption" data-tippy-content="Camere fara ocupare setata">
												C.F.O.S.
											</div>
										</th>
										
										<th class="text-center px-2 py-4 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase tracking-wider sticky top-0 z-1">
											<div class="table-heading-caption" data-tippy-content="Servicii">
												Servicii
											</div>
										</th>
										
										<th class="text-center px-2 py-4 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase tracking-wider sticky top-0 z-1">
											<div class="table-heading-caption" data-tippy-content="Politici de plata">
												Politici de plata
											</div>
										</th>
										<th class="text-center px-2 py-4 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase tracking-wider sticky top-0 z-1">
											<div class="table-heading-caption" data-tippy-content="Politici de anulare">
												Politici de anulare
											</div>
										</th>
										<th class="text-center px-2 py-4 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase tracking-wider sticky top-0 z-1">
											<div class="table-heading-caption" data-tippy-content="Planuri tarifare">
												Planuri Tarifare
											</div>
										</th>
										<th class="text-center px-2 py-4 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase tracking-wider sticky top-0 z-1">
											<div class="table-heading-caption" data-tippy-content="Planuri tarifare active">
												Planuri tarifare active
											</div>
										</th>
										
										<th class="text-center px-2 py-4 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase tracking-wider sticky top-0 z-1">
											<div class="table-heading-caption" data-tippy-content="Contract">
												Contract
											</div>
										</th>
										
										<th class="qc-heading px-2 py-4 bg-cool-gray-50 text-left text-xs leading-4 font-medium text-cool-gray-500 uppercase tracking-wider sticky top-0 z-1">
											
										</th>
									</tr>
								</thead>
								@foreach ($inactive_properties as $inactive_property)
									@php $age_intervals = \Omi\Comm\Age_Interval::Get_Age_Intervals_For_Property($inactive_property->Id, false);			
									
									@php $hasAgeIntervals = false;
									@foreach ($age_intervals as $age_interval)
										@if ($age_interval->To && $age_interval->Active)
											@php $hasAgeIntervals = true;
											@php break;
										@endif
									@endforeach
									
									<tbody class="bg-white border-b divide-y divide-cool-gray-200 qc-ref-ctrl js-itm">
										<tr class="bg-white">
											<td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500">
												{{$inactive_property->Name}}
											</td>
											
											<td class="text-center px-2 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500 ">
												@if ($hasAgeIntervals)
													<a href="Age_Intervals_App" class="font-normal p-2 rounded bg-green-200">Da</a>
												@else
													<a href="Age_Intervals_App" class="font-normal p-2 rounded bg-red-200">Nu</a>
												@endif
											</td>
											
											<td class="text-center px-2 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500 ">
												@php $rooms = \QQuery('Properties_Rooms.{Name WHERE Property.Id=?}', [$inactive_property->Id])->Properties_Rooms;				
												<a href="Properties_Rooms" class="font-normal p-2 rounded {{(count($rooms) == 0) ? 'bg-red-200' : 'bg-green-200'}}">{{(count($rooms) > 0) ? 'Da' : 'Nu'}}</a>
											</td>
											
											<!-- ROOM OCCUPANCY :: BEGIN -->
											<td class="text-center px-2 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500 ">
												@php $owner = $inactive_property->Owner;
												@php $occupancies = \QQuery('Room_Occupancies.{Name WHERE Owner.Id=?}', [$owner->Id])->Room_Occupancies;
												<a href="Room_Occupancies" class="font-normal p-2 rounded {{(count($occupancies) == 0) ? 'bg-red-200' : 'bg-green-200'}}">{{(count($occupancies) > 0) ? 'Da' : 'Nu'}}</a>
											</td>
											<!-- ROOM OCCUPANCY :: END -->
											
											<!-- ROOM WITHOUT OCCUPANCY :: BEGIN -->
											<td class="text-center px-2 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500 ">
												@php $rooms = \QQuery('Properties_Rooms.{Name, Occupancy WHERE Property.Id=?}', [$inactive_property->Id])->Properties_Rooms;				
												@php $roomCount = 0;
												@foreach ($rooms ?: [] as $room)
													@if (!$room->Occupancy)
														@php $roomCount++;
													@endif
												@endforeach

												<a href="Properties_Rooms" class="font-normal p-2 rounded {{($roomCount != 0) ? 'bg-red-200' : 'bg-green-200'}}">{{$roomCount ? 'Da' : 'Nu'}}</a>
											</td>
											<!-- ROOM WITHOUT OCCUPANCY :: END -->
											
											<td class="text-center px-2 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500 ">
												@php $owner = $inactive_property->Owner;
												@php $services = \QQuery('Offers.{Name WHERE Owner.Id=?}', [$owner->Id])->Offers;				
												<a href="Services" class="font-normal p-2 rounded {{(count($services) == 0) ? 'bg-red-200' : 'bg-green-200'}}">{{(count($services) > 0) ? 'Da' : 'Nu'}}</a>
											</td>
											
											<td class="text-center px-2 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500 ">
												@php $owner = $inactive_property->Owner;
												@php $paymentPolicies = \QQuery('Payment_Policies.{Name WHERE Owner.Id=?}', [$owner->Id])->Payment_Policies;

												<a href="Payment_Policies" class="font-normal p-2 rounded {{(count($paymentPolicies) == 0) ? 'bg-red-200' : 'bg-green-200'}}">{{(count($paymentPolicies) > 0) ? 'Da' : 'Nu'}}</a>
											</td>
											<td class="text-center px-2 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500 ">
												@php $owner = $inactive_property->Owner;
												@php $cancellationPolicies = \QQuery('Cancellation_Policies.{Name WHERE Owner.Id=?}', [$owner->Id])->Cancellation_Policies;

												<a href="Cancellation_Policies" class="font-normal p-2 rounded {{(count($cancellationPolicies) == 0) ? 'bg-red-200' : 'bg-green-200'}}">{{(count($cancellationPolicies) > 0) ? 'Da' : 'Nu'}}</a>
											</td>
											<td class="text-center px-2 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500 ">
												@php $owner = $inactive_property->Owner;
												@php $ratePlans = \QQuery('Rate_Plans.{Name WHERE Owner.Id=?}', [$owner->Id])->Rate_Plans;

												<a href="Rate_Plans" class="font-normal p-2 rounded {{(count($ratePlans) == 0) ? 'bg-red-200' : 'bg-green-200'}}">{{(count($ratePlans) > 0) ? 'Da' : 'Nu'}}</a>
											</td>
											
											<td class="text-center px-2 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500 ">
												@php $owner = $inactive_property->Owner;
												@php $activeRatePlans = \QQuery('Rate_Plans.{Name WHERE Active=1 AND Owner.Id=?}', [$owner->Id])->Rate_Plans;

												<a href="Rate_Plans" class="font-normal p-2 rounded {{(count($activeRatePlans) == 0) ? 'bg-red-200' : 'bg-green-200'}}">{{(count($activeRatePlans) > 0) ? 'Da' : 'Nu'}}</a>
											</td>
											
											<td class="text-center px-2 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500 ">
												@php $inactive_property->populate('Contract.File');
								
												<a href="Property_View_Contract/view/{{$inactive_property->Id}}?download_contract={{$inactive_property->Contract->Id}}" class="font-normal p-2 rounded {{($inactive_property->Contract->File) ? 'bg-green-200' : 'bg-red-200'}}">{{$inactive_property->Contract->File ? 'Da' : 'Nu'}}</a>
											</td>
											
											<td class="text-center px-2 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500 ">
												@php $inactive_property->populate('Requested_Activation_By_H2B');
												
												@if ($hasAgeIntervals && (count($rooms) > 0) && !$roomCount && (count($services) > 0) && (count($occupancies) > 0) && (count($paymentPolicies) > 0) && (count($cancellationPolicies) > 0) && (count($ratePlans) > 0) && (count($activeRatePlans) > 0) && $inactive_property->Contract->File)
													@if ($inactive_property->Requested_Activation_By_H2B)
														<p>In curs de setare</p>
													@else
														<a href="javascript: void(0);" data-property="{{$inactive_property->Id}}" class="js-activate-property mr-4 inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:text-indigo-500 focus:outline-none focus:shadow-outline-indigo focus:border-indigo-300 active:text-indigo-800 active:bg-indigo-50 active:text-indigo-800 transition duration-150 ease-in-out">Solicitare verificare si activare</a>
													@endif
												@endif
											</td>
										</tr>
									</tbody>
									
									<tbody class="bg-white border-b divide-y divide-cool-gray-200">
										<tr class="bg-white">
											<td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500" colspan="4">
												<p class="font-normal">Date pentru sincronizare</p>
											</td>
											<td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500" colspan="4">
												<p class="font-normal">ID: {{$inactive_property->Id}}</p>
											</td>
											<td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-500" colspan="4">
												@php $inactive_property->populate('API_Managed_User.Api_Key');
												
												<p class="font-normal">Parola: {{$inactive_property->API_Managed_User->Api_Key}}</p>
											</td>
										</tr>
									</tbody>
								@endforeach
							</table>
						</div>
					</div>
				</div>
			</div>
		@endif
		
		<div class="mx-auto px-4 sm:px-6 lg:px-8 mt-8">
			<div class="grid grid-cols-1 md:grid-cols-2 mb-8 gap-8 gap-y-8">
				<!-- LATEST BOOKINGS :: BEGIN -->
				<div class="flex flex-col gap-y-4">
					<h4 class="text-xl leading-6 font-medium text-gray-700 mb-2">{{_T(39, 'Bookings')}}</h4>
					<div class="flex-1 rounded-lg bg-white p-4 lg:p-8 shadow">
						@php $orders = \QApi::Query('Orders', 'Reference, Channel.Name, Status,  Buyer.{Firstname, Name}, Total_Price, Currency_Code', ['OBY_Date' => 'DESC', 'LIMIT' => [0, 5]]);

						@if ($orders)
							<ul class="divide-y divide-gray-200">
								@foreach ($orders as $order)
									<li class="py-2">
										<div class="ml-3">
											<a href="Orders/view/{{$order->Id}}" class="py-2 grid grid-cols-2 md:grid-cols-4 gap-x-8 text-sm font-medium text-gray-900">
												<span>{{$order->Buyer->Firstname}} {{$order->Buyer->Name}}</span>
												<span>{{$order->Channel->Name}}</span>
												<span>{{$order->Total_Price}} {{$order->Currency_Code}}</span>
												<div>
													@if ($order->Status == 'Confirmed')
														<span class="px-2 inline-flex text-xs leading-5 font-medium rounded-full bg-green-200 text-green-700">Confirmed</span>
													@elseif ($order->Status == 'Proposal')
														<span class="px-2 inline-flex text-xs leading-5 font-medium rounded-full bg-yellow-200 text-yellow-700">Proposal</span>
													@elseif ($order->Status == 'Submitted')
														<span class="px-2 inline-flex text-xs leading-5 font-medium rounded-full bg-blue-200 text-blue-700">Submitted</span>
													@elseif ($order->Status == 'Cancelled')
														<span class="px-2 inline-flex text-xs leading-5 font-medium rounded-full bg-red-200 text-red-700">Cancelled</span>
													@endif
												</div>
											</a>
											<p class="text-sm text-gray-500">{{$order->Reference}}</p>
										</div>
									</li>
								@endforeach

								@if ($orders)
									<li class="py-2">
										<div class="ml-3">
										  <a href="Orders" class="py-2 block text-sm font-medium text-blue-500">{{_T(40, 'View more')}}</a>
										</div>
									</li>
								@else
									{{_T(41, 'No orders')}}
								@endif
							</ul>
						@else
							{{_T(41, 'No orders')}}
						@endif
					</div>
				</div>
				<!-- LATEST BOOKINGS :: END -->	

				<!-- LATEST PROPERTIES :: BEGIN -->
				<div class="flex flex-col gap-y-4">
					<h4 class="text-xl leading-6 font-medium text-gray-700 mb-2">{{$is_H2B_Channel ? _T(42, 'Latest Properties') : _T(343, 'Active Properties')}}</h4>
					<div class="flex-1 rounded-lg bg-white p-4 lg:p-8 shadow">
						@if ($active_properties)
							<ul class="divide-y divide-gray-200">
								@foreach ($active_properties as $property)
									<li class="py-2">
										<div class="ml-3">
											<a href="{{$is_H2B_Channel ? 'Property_Channel/view/' : 'Properties/edit/'}}{{$property->Id}}" class="py-2 block text-sm font-medium text-gray-900">{{$property->Name}}</a>
											<!-- <p class="text-sm text-gray-500">calvin.hawkins@example.com</p> -->
										</div>
									</li>
								@endforeach

								<li class="py-2">
									<div class="ml-3">
									  <a href="{{$is_H2B_Channel ? 'Property_Channel' : 'Properties'}}" class="py-2 block text-sm font-medium text-blue-500">{{_T(40, 'View more')}}</a>
									</div>
								</li>
							</ul>
						@else
							{{_T(344, 'No active properties')}}
						@endif
					</div>
				</div>
				<!-- LATEST PROPERTIES :: END -->

				@if ($isPropertyOwner)
					<!-- LATEST ROOMS :: BEGIN -->
					<div class="flex flex-col gap-y-4">
						<h4 class="text-xl leading-6 font-medium text-gray-700 mb-2">{{_T(2, 'Rooms')}}</h4>
						<div class="flex-1 rounded-lg bg-white p-4 lg:p-8 shadow">
							@php $rooms = \QApi::Query('Properties_Rooms', 'Name, Property.Name', ['LIMIT' => [0, 5]]);

							@if ($rooms)
								<ul class="divide-y divide-gray-200">

									@foreach ($rooms as $room)
										<li class="py-2">
											<div class="ml-3">
												<a href="Properties_Rooms/edit/{{$room->Id}}" class="py-2 block text-sm font-medium text-gray-900">{{$room->Name}}</a>
												<p class="text-sm text-gray-500">{{$room->Property->Name}}</p>
											</div>
										</li>
									@endforeach

									<li class="py-2">
										<div class="ml-3">
										  <a href="Properties_Rooms" class="py-2 block text-sm font-medium text-blue-500">{{_T(40, 'View more')}}</a>
										</div>
									</li>
								</ul>
							@else
								{{_T(43, 'No rooms')}}
							@endif
						</div>
					</div>
					<!-- LATEST ROOMS :: END -->

					<!-- LATEST RATE PLANS :: BEGIN -->
					<div class="flex flex-col gap-y-4">
						<h4 class="text-xl leading-6 font-medium text-gray-700 mb-2">{{_T(16, 'Rate Plans')}}</h4>
						<div class="flex-1 rounded-lg bg-white p-4 lg:p-8 shadow">
							@php $rate_plans = \QApi::Query('Rate_Plans', 'Name', ['LIMIT' => [0, 5]]);

							@if ($rate_plans)
								<ul class="divide-y divide-gray-200">

									@foreach ($rate_plans as $rate_plan)
										<li class="py-2">
											<div class="ml-3">
												<a href="Rate_Plans/edit/{{$rate_plan->Id}}" class="py-2 block text-sm font-medium text-gray-900">{{$rate_plan->Name}}</a>
											</div>
										</li>
									@endforeach

									<li class="py-2">
										<div class="ml-3">
										  <a href="Rate_Plans" class="py-2 block text-sm font-medium text-blue-500">{{_T(40, 'View more')}}</a>
										</div>
									</li>
								</ul>
							@else
								{{_T(44, 'No rate plans')}}
							@endif
						</div>
					</div>
					<!-- LATEST RATE PLANS :: END -->
				@endif
			</div>

			<!-- CHART :: BEGIN -->
			<!-- <div class="grid grid-cols-1 md:grid-cols-1 mb-8 gap-8 gap-y-8">
				<div class="flex flex-col gap-y-8">
					<div class="flex-1 rounded-lg bg-white p-4 lg:p-8 shadow">
						<canvas id="myChart" style="height: 30rem;"></canvas>
					</div>
				</div>
			</div> -->
			<!-- CHART :: END -->
		</div>
	@endif
</div>