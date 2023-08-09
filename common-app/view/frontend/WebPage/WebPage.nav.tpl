<virtual>
	<?php		
		$navArr = [
			'Properties' => [
				'Properties',
				'Properties_Media',
				'Properties_Facilities',
				'Properties_Rooms',
				'Properties_Rooms_Media',
				'Properties_Rooms_Occupancy_Enforcements',
				'Properties_Rooms_Beds_Enforcements',
				'Room_Occupancies',
				'Room_Occupancy_Details',
				'Room_Occupancy_Details_Rate',
				'Properties_Rooms_Facilities',
				'Properties_Rooms_Extra_Beds',
			],
			'Policies' => [
				'Restrictions',
				'Payment_Policies',
				'Cancellation_Policies',
				'Age_Intervals_App',
				'Seasons',
			],
			'Prices' => [
				'Rate_Set_Requests',
				'Rate_Plans',
				'Special_Deals',
			],
			'Partners' => [
				'Channels',
				// 'Rate_Plans_Access',
				'Property_Contracts',
				'Contracts'
			],
			'PriceProfiles' => [
				'Services',
				'Services_Discount',
				'Services_Calendar',
				'Price_Profile_Property',
				'Price_Profile_Property_Rate',
				'Price_Profile_Property_Room',
				'Price_Profile_Property_Room_Rate'
			],
			'SuperAdmin' => [
				'Users',
				'Companies',
				'Property_Owners',
				'Languages_Spoken',
				'Languages',
				'Registration_Requests',
				'Cache_Views',
				'API_Systems',
				'FailedLogins',
				// 'List_Offers'
			],
		];
		
		$user = \Omi\User::GetCurrentUser();
		
		$url = \QWebRequest::GetOriginalRequest();
		$urlObj = new \QUrl($url);
		$originalRequest = $urlObj->reset();
		
		$onProperties = in_array($originalRequest, $navArr['Properties']);
		$onPolicies = in_array($originalRequest, $navArr['Policies']);
		$onPrices = in_array($originalRequest, $navArr['Prices']);
		$onPriceProfiles = in_array($originalRequest, $navArr['PriceProfiles']);
		$onPartners = in_array($originalRequest, $navArr['Partners']);
		$onSuperAdmin = in_array($originalRequest, $navArr['SuperAdmin']);
		
		$hideSidebar = (($originalRequest == 'login') || ($originalRequest == 'recover-password'));
		
		$propertyActiveClass = (($originalRequest == 'Properties') || ($originalRequest == 'Properties_Media') || ($originalRequest == 'Properties_Facilities')) ? 'bg-main-accent-color-100' : '';
		$roomsActiveClass = (($originalRequest == 'Properties_Rooms') || ($originalRequest == 'Properties_Rooms_Media') || ($originalRequest == 'Properties_Rooms_Facilities') || ($originalRequest == 'Properties_Rooms_Occupancy_Enforcements') || ($originalRequest == 'Properties_Rooms_Extra_Beds')) ? 'bg-main-accent-color-100' : '';
		$occupancyActiveClass = (($originalRequest == 'Room_Occupancies') || ($originalRequest == 'Room_Occupancy_Details') || ($originalRequest == 'Room_Occupancy_Details_Rate')) ? 'bg-main-accent-color-100' : '';
		
		$servicesActiveClass = (($originalRequest == 'Services') || ($originalRequest == 'Services_Prices') || ($originalRequest == 'Services_Discount')) ? 'bg-main-accent-color-100' : '';
		$servicesPricesActiveClass = (($originalRequest == 'Price_Profile_Property') || ($originalRequest == 'Price_Profile_Property_Room') || ($originalRequest == 'Price_Profile_Property_Rate') || ($originalRequest == 'Price_Profile_Property_Room_Rate')) ? 'bg-main-accent-color-100' : '';
		
		// is agency
		$is_H2B_Channel = false;
		$isSuperadmin = \Omi\User::Is_Superadmin();
		$isPropertyOwner = false;
		$is_H2B_Superadmin = $isSuperadmin;
	?>
	
	<div class="flex lg:flex-shrink-0 sidemenu fixed z-20 h-full left-0 top-0">
		<div class="flex flex-col w-64">
			<div class="px-4 h-16 bg-main-accent-color flex-shrink-0 border-b border-main-accent-color-100">
				<div class="flex flex-row justify-between items-center h-full">
					@if ($logo)    
						<img class="h-12 w-auto" style="max-width: 8rem;" src='<?= $logo ?>' r-src="<?= $logo ?>" /> 
					@else
						<img class="h-12 w-auto" style="max-width: 8rem;" src="<?= Q_APP_REL . 'code/res/main/images/logo.png' ?>" /> 
						<!-- <a href="" class="text-white font-bold p-3">H2B</a> -->
					@endif

					<a href="javascript: void(0);" class="hidden lg:flex p-4 py-1 ml-2 js-control-sidemenu">
						<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="js-arrow-left h-6 w-6 text-white group-focus:text-indigo-200 transition ease-in-out duration-150">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
						</svg>
						<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="js-arrow-right hidden h-6 w-6 text-white group-focus:text-indigo-200 transition ease-in-out duration-150">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
						</svg>
					</a>
				</div>
			</div>
			
			<div class="flex flex-col flex-grow bg-main-accent-color pt-5 pb-4 overflow-y-auto">
				<div class="flex-1 flex flex-col overflow-y-auto">
					<div class="scrollable-vertical scrollable-styled-dark">
						<nav class="px-2 space-y-1">
							<ul>
								<li>
									<a href="" class="{{(!$originalRequest) ? 'bg-main-accent-color-100' : ''}} group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md text-indigo-100 focus:outline-none focus:bg-main-accent-color-100 hover:bg-main-accent-color-100 transition ease-in-out duration-150">
										<svg class="mr-4 h-6 w-6 text-indigo-200 group-hover:text-indigo-200 group-focus:text-indigo-200 transition ease-in-out duration-150" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
										</svg>
										Dashboard
									</a>
								</li>
								
								<li>
									<a href="Projects" class="{{(!$originalRequest) ? 'bg-main-accent-color-100' : ''}} group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md text-indigo-100 focus:outline-none focus:bg-main-accent-color-100 hover:bg-main-accent-color-100 transition ease-in-out duration-150">
										<svg class="mr-4 h-6 w-6 text-indigo-200 group-hover:text-indigo-200 group-focus:text-indigo-200 transition ease-in-out duration-150" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
										</svg>
										Projects
									</a>
								</li>
																
								@if (true)
									<li>
										<a href="javascript: void(0)" class="relative group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md text-indigo-100 focus:outline-none focus:bg-main-accent-color-100 hover:bg-main-accent-color-100 transition ease-in-out duration-150 js-show-dd-menu">
											<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mr-4 h-6 w-6 text-indigo-200 group-hover:text-indigo-200 group-focus:text-indigo-200 transition ease-in-out duration-150">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
											</svg>
											{{_T(24, 'Administration')}}
											<svg class="js-arrow-menu-hidden {{$onPartners ? 'hidden' : ''}} absolute right-0 top-0 mt-3 h-4 mr-2 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
											</svg>
											<svg class="js-arrow-menu-opened {{!$onPartners ? 'hidden' : ''}} absolute right-0 top-0 mt-3 mr-2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 20 20" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
											</svg>
										</a>
										<ul class="pl-10 {{!$onSuperAdmin ? 'hidden' : ''}}">
											<li>
												<a href="Users" class="{{($originalRequest == 'Users') ? 'bg-main-accent-color-100' : ''}} group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md text-indigo-100 focus:outline-none focus:bg-main-accent-color-100 hover:bg-main-accent-color-100 transition ease-in-out duration-150">
													{{_T(25, 'Users')}}
												</a>
											</li>
											<li>
												<a href="Companies" class="{{($originalRequest == 'Companies') ? 'bg-main-accent-color-100' : ''}} group flex items-center px-2 py-2 text-sm leading-6 font-medium rounded-md text-indigo-100 focus:outline-none focus:bg-main-accent-color-100 hover:bg-main-accent-color-100 transition ease-in-out duration-150">
													{{_T(25, 'Companies')}}
												</a>
											</li>
										</ul>
										
									</li>
								@endif
							</ul>
						</nav>
					</div>
				</div>
			</div>
		</div>
	</div>
	
</virtual>