
if (jQuery("#preloader").length > 0)
{
	if (localStorage.getItem('layout_collapsed') === 'true')
    {
        jQuery('body').addClass('layout-collapsed');
        jQuery('.js-page-content').removeClass("lg:ml-64");
    }
	
	jQuery(window.document).on('load', function()
	{
        jQuery("#preloader").fadeOut(400,function()
		{
            jQuery("#preloader").remove();
        });
    });
}

function initDatepicker()
{
    jQuery(".qc-popup .flatpickr").flatpickr({
        // dateFormat: 'd-m-Y'
        allowInput: true,
        static: true,               // @INFO: position the element inside a wrapper .flatpickr-wrapper and next to input
        // appendTo: jQuery('.qc-popup .js-popup-container .qc-xg-item')[0],
        // altInput: true, 
        // altFormat: 'd-m-Y',
        // onReady: function (selectedDates, dateStr, instance) {
        //     instance.calendarContainer.classList.add("qSkipHideOnClickAway");
        // }
    });
}

jQuery(document).ready(function () 
{
    jQuery(".flatpickr").flatpickr({
        // dateFormat: 'd-m-Y'
        allowInput: true,
        static: true,               // @INFO: position the element inside a wrapper .flatpickr-wrapper and next to input
    });
    
    jQuery('body').on('afterAddCollectionRow', function()
    {
        jQuery(".flatpickr").flatpickr({
            // dateFormat: 'd-m-Y'
            allowInput: true,
            static: true,           // @INFO: position the element inside a wrapper .flatpickr-wrapper and next to input
        });
    });

    tippy('[data-tippy-content]', {
		allowHTML: true
	});
    
	jQuery('body').on('keyup', '.js-adults-number', function()
	{
		var $thisJq = jQuery(this);
		if ($thisJq.val() > 9)
			$thisJq.val('9');
	});
	
	jQuery('body').on('keyup', '.js-children-number', function()
	{
		var $thisJq = jQuery(this);
		if ($thisJq.val() > 4)
			$thisJq.val('4');
		
		var $searchForm = $thisJq.closest('.js-search-form');
		
		// jQuery('.js-child-age').hide();
		var $lastOccurence = 0;
		for (var $i = 1; $i <= $thisJq.val(); $i++)
		{
			$searchForm.find('.js-child-age-' + $i).show();
			$lastOccurence = $i;
		}
		
		for (var $i = ($lastOccurence + 1); $i <= 4; $i++)
		{
			$searchForm.find('.js-child-age-' + $i).hide();
			$searchForm.find('.js-child-age-' + $i).find('input').val('');
		}
		
		var $children = $thisJq.val();
		var $adults = $thisJq.closest('.js-b2b-passengers-dd').find('.js-form-element-input[name="Search[Adults]"]').val();
		
		changePassengersText($thisJq, $adults, $children);
	});
	
	jQuery('body').on('keyup', '.js-form-element-input[name="Search[Adults]"]', function()
	{
		var $thisJq = jQuery(this);
		var $adults = $thisJq.val();
		var $children = $thisJq.closest('.js-b2b-passengers-dd').find('.js-children-number').val();
		
		changePassengersText($thisJq, $adults, $children);
	});
	
	function changePassengersText($senderJq, $adults, $children)
	{
		var $textChildren = '';
		if ($children)
		{
			if ($children == '1')
				$textChildren = ', ' + $children + ' copil';
			else
				$textChildren = ', ' + $children + ' copii';
		}
		
		var $text
		if ($adults == 1)
			$text = $adults + ' adult' + $textChildren;
		else
			$text = $adults + ' adulti' + $textChildren;
		
		$senderJq.closest('.js-b2b-passengers-dd').find('.js-b2b-open-passengers').text($text);
	}
	
	jQuery(".fancybox.use-iframe").fancybox({
		"type" : "iframe",
		beforeLoad : function (e) 
		{
			jQuery('html').addClass('no-scroll');
		},
		touch: false,
		beforeClose : function (e) 
		{
			jQuery('html').removeClass('no-scroll');
		}
	});
	
	jQuery(document).mouseup(function(e) 
	{
		var $searchTabsDD = jQuery(".js-b2b-passengers-dd");

		// if the target of the click isn't the container nor a descendant of the container
		if (!$searchTabsDD.is(e.target) && $searchTabsDD.has(e.target).length === 0) 
		{
			$searchTabsDD.find('.js-b2b-passengers-container').hide();
		}
	});
	
	jQuery('body').on('click', '.js-open-room', function()
	{
		var $thisJq = jQuery(this);
		
		if (!$thisJq.hasClass('opened'))
		{
			var $allroomsInfo = jQuery('#middle-content').find('.js-offer-room');
			
			for (var $i = 0; $i < $allroomsInfo.length; $i++)
			{
				if ($allroomsInfo.is(':visible'))
					jQuery($allroomsInfo[$i]).hide();
			}
		}
		
		$thisJq.toggleClass('opened');
		
		var $offerItem = $thisJq.closest('.js-offer-item-wrapper');
		var $roomInfo = $offerItem.find('.js-offer-room');
		
		$roomInfo.toggle();
		
		if ($roomInfo.is(':visible'))
		{
			// $roomInfo.css('outline', '3px solid red');
			
			// $roomInfo.offset().top
			// alert($('.qc-popup').length);
			
			// alert($roomInfo.offset().top);
			
			// Position of first element relative to container top
			var scrollTop = $roomInfo.offset().top - $("#middle-content").offset().top;

			// Position of selected element relative to container top
			var targetTop = $("#middle-content > *").offset().top - $("#middle-content").offset().top;

			// The offset of a scrollable container
			var scrollOffset = scrollTop - targetTop;

			// Scroll untill target element is at the top of its container
			// $("#middle-content").scrollTop(scrollOffset - 20);
			$('#middle-content').animate({
				scrollTop: scrollOffset - 20, // $roomInfo.offset().top
			}, 1000);
			/*
			var $scroll_at = $roomInfo.offset().top - $('.qc-popup').offset().top;
			
			$('.qc-popup').animate({
				scrollTop: $scroll_at, // $roomInfo.offset().top
			}, 1000);
			*/
			/*
			setTimeout(function() {
				jQuery('.qc-popup').animate({
					scrollTop: $roomInfo.offset().top
				}, 1000);
			}, 100);*/
		}
	});
	
	jQuery('body').on('click', '.js-mobile-open-room', function()
	{
		var $thisJq = jQuery(this);
		
		if (!$thisJq.hasClass('opened'))
		{
			var $allroomsInfo = jQuery('#middle-content').find('.js-offer-room');
			
			for (var $i = 0; $i < $allroomsInfo.length; $i++)
			{
				if ($allroomsInfo.is(':visible'))
					jQuery($allroomsInfo[$i]).hide();
			}
		}
		
		$thisJq.toggleClass('opened');
		
		var $offerItem = $thisJq.closest('.js-offer-item-wrapper');
		var $roomInfo = $offerItem.find('.js-offer-room');
		
		$roomInfo.toggle();
	});
	
	jQuery('body').on('click', '.js-close-offer-room', function()
	{
		jQuery('.js-offer-room').hide();
	});
	
	jQuery('.js-travel-item-description').readmore({
		speed: 400,
		collapsedHeight: 150,
		moreLink: '<button class="btn-show-description">Vezi mai mult</button>',
		lessLink: '<button class="btn-show-description">Vezi mai putin</button>'
	});
	
	/*
	jQuery('body').on('click', '.js-readmore', function()
	{
		var $thisJq = jQuery(this);
		var $travelItemDescription = $thisJq.closest('.js-travel-item-description');
		$travelItemDescription.toggleClass('expanded');
		if ($travelItemDescription.hasClass('expanded'))
			$thisJq.text('Vezi mai putin');
		else
			$thisJq.text('Vezi mai mult');
	});
	*/
   
	tippy('[data-tippy-content]');
	
	jQuery('body').on('keyup', '.prop-wrapper-Check_In_Time_Hour input', function()
	{
		var $val = jQuery(this).val();
		if ($val < 0) jQuery(this).val('0');		
		if ($val > 23) jQuery(this).val('23');
	});
	
	jQuery('body').on('keyup', '.prop-wrapper-Check_In_Time_Minutes input', function()
	{
		var $val = jQuery(this).val();
		if ($val < 0) jQuery(this).val('0');		
		if ($val > 59) jQuery(this).val('59');
	});
	
	jQuery('body').on('keyup', '.prop-wrapper-Check_Out_Time_Hour input', function()
	{
		var $val = jQuery(this).val();
		if ($val < 0) jQuery(this).val('0');		
		if ($val > 23) jQuery(this).val('23');
	});
	
	jQuery('body').on('keyup', '.prop-wrapper-Check_Out_Time_Minutes input', function()
	{
		var $val = jQuery(this).val();
		if ($val < 0) jQuery(this).val('0');		
		if ($val > 254) jQuery(this).val('59');
	});
	
	jQuery('body').on('keyup', '.prop-wrapper-Min_Length_Of_Stay_From_Arrival input', function()
	{
		var $val = jQuery(this).val();
		if ($val < 0) jQuery(this).val('0');		
		if ($val > 254) jQuery(this).val('254');
	});
	
	jQuery('body').on('keyup', '.prop-wrapper-Max_Length_Of_Stay_From_Arrival input', function()
	{
		var $val = jQuery(this).val();
		if ($val < 0) jQuery(this).val('0');		
		if ($val > 254) jQuery(this).val('254');
	});
	
	jQuery('body').on('click', '.js-open-terms-popup', function()
	{
		var $popupJq = jQuery('<div class="qc-popup fixed z-30 inset-0 overflow-y-auto jx-modal qHideOnClickAway-remove qHideOnClickAway-parent">' +
				'<div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 jx-modal-content is-visible">' +
					'<div class="fixed inset-0 transition-opacity">' +
						'<div class="absolute inset-0 bg-gray-500 opacity-75"></div>' +
					'</div>' +
					'<!-- This element is to trick the browser into centering the modal contents. -->' +
					'<span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;'+
						'<div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-8 js-popup-container qHideOnClickAway" role="dialog" aria-modal="true" aria-labelledby="modal-headline"">' +
							'<a href="javascript: void(0)" class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5 jx-close-modal">' + _L("Close") + '</a>' +
						'</div>' +
					'</div>' +
				'</div>');
		
		jQuery(document.body).append($popupJq);
	});
	
	jQuery('body').on('click', '.js-showRestrictions', function()
	{
		jQuery(this).closest('tbody').next('.js-restrictions').find('tr').toggleClass('hidden');
	});	
	
	jQuery('body').on('click', '.js-b2b-open-passengers', function()
	{
		var $thisJq = jQuery(this);
		var $passengersWrapper = $thisJq.closest('.js-b2b-passengers-dd');
		var $passengersContainer = $passengersWrapper.find('.js-b2b-passengers-container');
		
		jQuery('.js-children-number').trigger('keyup');
		
		$passengersContainer.toggle();
	});
	
	jQuery(".js-hover-toggle-contenteditable").keypress(function(e) {
		if (isNaN(String.fromCharCode(e.which))) e.preventDefault();
	});
	
	jQuery(".js-input-number-no-digit").keypress(function(e) {
		if (isNaN(String.fromCharCode(e.which))) e.preventDefault();
	});
	
	var $ratePlans = jQuery('[id^="ratePlan"]');
	
	window.document.addEventListener("qqs-end", function(event)
	{
		var ctrl_jq = $ratePlans.closest(".omi-control");
		
		var $detail = event.detail;
		var $selected = $detail.obj.get_selected();
		var $firstSelected = jQuery($selected[0]);
		var $lastSelected = jQuery($selected[$selected.length - 1]);

		if ($firstSelected.closest('.js-rate-plan-prices').length > 0)
		{
			var $dateStart = $firstSelected.data('date');
			var $dateEnd = $lastSelected.data('date');
			var $roomId = $lastSelected.data('roomid');
			var $ratePlanId = $lastSelected.data('rateplanid');
			var $propertyId = $lastSelected.data('propertyid');

			omi.api("Omi\\TFS\\View\\Rate_Set_Requests::RenderMiniPopup", [$dateStart, $dateEnd, $roomId, $ratePlanId, $propertyId], 
				// success
				function(resp)
				{
					$firstSelected.closest('.page-body-margin').append(resp);				

					jQuery('.js-popup-prices-ratePlan').css({top: ($lastSelected.position().top - 150), left: ($lastSelected.position().left)});

					initCustomDropdowns(jQuery('.js-popup-prices-ratePlan'));

					inputsHasValues();
					
					jQuery(".js-input-number-no-digit").keypress(function(e) {
						if (isNaN(String.fromCharCode(e.which))) e.preventDefault();
					});
				}, 

				// fail
				function(jqXHR, textStatus, errorThrown)
				{

				}
			);
		}
		else if ($firstSelected.closest('.js-rooms-count-wrapper').length > 0)
		{
			var $dateStart = $firstSelected.data('date');
			var $dateEnd = $lastSelected.data('date');
			var $roomId = $lastSelected.data('roomid');
			var $propertyId = $lastSelected.data('propertyid');
			
			omi.api("Omi\\TFS\\View\\Rate_Set_Requests::RenderRoomCountMiniPopup", [$dateStart, $dateEnd, $roomId, $propertyId], 
				// success
				function(resp)
				{
					$firstSelected.closest('.page-body-margin').append(resp);				

					jQuery('.js-popup-prices-roomCount').css({top: ($lastSelected.position().top - 150), left: ($lastSelected.position().left)});
					
					initCustomDropdowns(jQuery('.js-popup-prices-roomCount'));

					inputsHasValues();
					
					jQuery(".js-input-number-no-digit").keypress(function(e) {
						if (isNaN(String.fromCharCode(e.which))) e.preventDefault();
					});
				}, 

				// fail
				function(jqXHR, textStatus, errorThrown)
				{

				}
			);
		}
		else if ($firstSelected.closest('.js-rate-plan-restrictions').length > 0)
		{
			var $dateStart = $firstSelected.data('date');
			var $dateEnd = $lastSelected.data('date');
			var $roomId = $lastSelected.data('roomid');
			var $ratePlanId = $lastSelected.data('rateplanid');
			var $propertyId = $lastSelected.data('propertyid');
			var $restrictionName = $lastSelected.data('restriction');
			var $daysLoacked = $lastSelected.data('days-locked');
			
			omi.api("Omi\\TFS\\View\\Rate_Set_Requests::RenderRestictionsMiniPopup", [$dateStart, $dateEnd, $roomId, $ratePlanId, $propertyId, $restrictionName, $daysLoacked], 
				// success
				function(resp)
				{
					$firstSelected.closest('.page-body-margin').append(resp);				

					jQuery('.js-popup-prices-restrictions').css({top: ($lastSelected.position().top - 150), left: ($lastSelected.position().left)});

					initCustomDropdowns(jQuery('.js-popup-prices-restrictions'));

					inputsHasValues();
					
					jQuery(".js-input-number-no-digit").keypress(function(e) {
						if (isNaN(String.fromCharCode(e.which))) e.preventDefault();
					});
				}, 

				// fail
				function(jqXHR, textStatus, errorThrown)
				{

				}
			);
		}
		else if ($firstSelected.closest('.js-services-calendar').length > 0)
		{
			var $dateStart = $firstSelected.data('date');
			var $dateEnd = $lastSelected.data('date');
			var $serviceCalendarId = $firstSelected.closest('form').find('input[name="Id"]').val();
			
			omi.api("Omi\\TFS\\View\\Services_Calendar::RenderServiceStatusMiniPopup", [$dateStart, $dateEnd, $serviceCalendarId], 
				// success
				function(resp)
				{
					$firstSelected.closest('.page-body-margin').append(resp);				

					jQuery('.js-popup-services-status').css({top: ($lastSelected.position().top - 150), left: ($lastSelected.position().left)});
					
					initCustomDropdowns(jQuery('.js-popup-services-status'));

					inputsHasValues();
				}, 

				// fail
				function(jqXHR, textStatus, errorThrown)
				{

				}
			);
		}
	});
	
	
	
	/*
	for (var $i = 0; $i < $ratePlans.length; $i++)
	{
		var $ratePlan = jQuery($ratePlans[$i]);
		var $ratePlanId = $ratePlan.attr('id');
		
		var ctrl_jq = $ratePlan.closest(".omi-control");
		
		const selectable = new Selectable({
			filter: '.selectable',
			ignore: '.js-hover-toggle-contenteditable',
			appendTo: '#' + $ratePlanId,
			autoRefresh: true,
			// toggle: true
		});
		
		
		selectable.on("end", function(e, selected, unselected) 
		{						
			var $firstSelected = selected[0];
			var $lastSelected = selected[selected.length - 1];
			
			var $dateStart = $firstSelected.node.dataset.date;
			var $dateEnd = $lastSelected.node.dataset.date;
			var $roomId = $lastSelected.node.dataset.roomid;
			var $ratePlanId = $lastSelected.node.dataset.rateplanid;
			var $propertyId = $lastSelected.node.dataset.propertyid;
			
			var $mouseX = e.pageX;
			var $mouseY = (e.pageY + jQuery("#middle-content").scrollTop());
			
			// console.log($mouseX, $mouseY);
			
			omi.api("Omi\\TFS\\View\\Rate_Set_Requests::RenderMiniPopup", [$dateStart, $dateEnd, $roomId, $ratePlanId, $propertyId], 
				// success
				function(resp)
				{
					jQuery('.page-body-margin').append(resp);
					jQuery('.js-popup-prices-ratePlan').css({top: ($mouseY - 250), left: ($mouseX - 250)});
					
					initCustomDropdowns(jQuery('.js-popup-prices-ratePlan'));

					inputsHasValues();
					
				}, 
				
				// fail
				function(jqXHR, textStatus, errorThrown)
				{
					
				}
			);
		});
	    
		selectable.on("start", function(e, item) 
		{	
			jQuery('.js-popup-prices-ratePlan').remove();
			
			var $parentRatePlanId = jQuery(e.target).closest('tbody').attr('id');
			
			for (var $i = 0; $i < $ratePlans.length; $i++)
			{
				var $_ratePlan = jQuery($ratePlans[$i]);
				var $_ratePlanId = $_ratePlan.attr('id');
				
				if ($parentRatePlanId === $_ratePlanId)
					continue;
				
				var parent = document.getElementById($_ratePlanId);
				var instance = parent._selectable;
				instance.clear();
			}
		});
	}
	*/
   
	jQuery('body').on('click', '.js-save-prices', function(event)
	{
		var $form = jQuery(this).closest('form');
		
		$form.find(':input:disabled').removeAttr('disabled');

		omi.api("Omi\\TFS\\View\\Rate_Set_Requests::UpdatePrices", [$form.serialize()], 
			// success
			function(resp)
			{							
				if (resp.RoomId || resp.RatePlanId)
				{					
					// get selected
					var $selectedItems = window.qqs["ratePlan_" + resp.RoomId + "_" + resp.RatePlanId].get_selected();
					
					if (jQuery('.js-sidebar-prices-ratePlan').length > 0)
						jQuery('.js-sidebar-prices-ratePlan').remove();
					
					for (var $i = 0; $i < $selectedItems.length; $i++)
					{
						var $selected = jQuery($selectedItems[$i]);
						
						if (resp.Price)
							$selected.find('span').text(resp.Price);
						
						if (resp.Status === 'open')
						{
							$selected.find('div').removeClass('bg-red-100');
							$selected.find('div').addClass('bg-green-100');
						}
						else if (resp.Status === 'closed')
						{
							$selected.find('div').removeClass('bg-green-100');
							$selected.find('div').addClass('bg-red-100');
						}
						/*
						else
						{
							$selected.find('div').removeClass('bg-green-100');
							$selected.find('div').removeClass('bg-red-100');
						}
						*/
					}
					
					qq_select_reset();
					jQuery('.js-popup-prices-ratePlan').remove();
					
					/*
					var $ratePlans = jQuery('[id^="ratePlan"]');
			
					for (var $i = 0; $i < $ratePlans.length; $i++)
					{
						var $_ratePlan = jQuery($ratePlans[$i]);
						var $_ratePlanId = $_ratePlan.attr('id');

						var parent = document.getElementById($_ratePlanId);
						var instance = parent._selectable;
						instance.clear();
					}
					*/
				}
				else
					alert('Something wnet wrong!');
			}, 

			// fail
			function(jqXHR, textStatus, errorThrown)
			{

			}
		);
	});
	
	jQuery('body').on('click', '.js-save-rooms-count', function()
	{
		var $form = jQuery(this).closest('form');
		
		$form.find(':input:disabled').removeAttr('disabled');

		omi.api("Omi\\TFS\\View\\Rate_Set_Requests::UpdateRoomsCount", [$form.serialize()], 
			// success
			function(resp)
			{				
				if (resp.RoomId)
				{					
					// get selected
					var $selectedItems = window.qqs["roomCount_" + resp.RoomId].get_selected();
					
					for (var $i = 0; $i < $selectedItems.length; $i++)
					{
						var $selected = jQuery($selectedItems[$i]);
						
						if (resp.Rooms_Count)
							$selected.find('span').text(resp.Rooms_Count);
						
						if (resp.Status === 'open')
						{
							$selected.find('div').removeClass('bg-red-100');
							$selected.find('div').addClass('bg-green-100');
							
							jQuery('td[data-date="' + $selected.data('date') + '"]').find('div').removeClass('bg-red-100');
							jQuery('td[data-date="' + $selected.data('date') + '"]').find('div').addClass('bg-green-100');
						}
						else if (resp.Status === 'closed')
						{
							$selected.find('div').removeClass('bg-green-100');
							$selected.find('div').addClass('bg-red-100');
							
							jQuery('td[data-date="' + $selected.data('date') + '"]').find('div').removeClass('bg-green-100');
							jQuery('td[data-date="' + $selected.data('date') + '"]').find('div').addClass('bg-red-100');
						}
						
						/*
						else
						{
							$selected.find('div').removeClass('bg-green-100');
							$selected.find('div').removeClass('bg-red-100');
						}
						*/
					}
					
					qq_select_reset();
					
					jQuery('.js-popup-prices-roomCount').remove();
				}
				else
					alert('Something wnet wrong!');
			}, 

			// fail
			function(jqXHR, textStatus, errorThrown)
			{

			}
		);
	});
	
	jQuery('body').on('click', '.js-save-service-calendar', function(event)
	{
		var $form = jQuery(this).closest('form');
		
		$form.find(':input:disabled').removeAttr('disabled');
		
		var $status = $form.find('input[xg-property-value^="Status("]').val();
		omi.api("Omi\\TFS\\View\\Services_Calendar::UpdateServiceStatus", [$form.serialize()], 
			// success
			function(resp)
			{				
				var $items = window.qqs.service_calendar.get_selected();
				var $itemsCount = $items.length;
				
				for (var $i = 0; $i < $itemsCount; $i++)
				{
					console.log(jQuery($items[$i]));
					
					var $itemSelected = jQuery($items[$i]);
					if ($status === 'open')
					{
						$itemSelected.find('div').addClass('bg-green-100 border-white');
						$itemSelected.find('div').removeClass('bg-red-100 border-white');
					}
					else if ($status === 'closed')
					{
						$itemSelected.find('div').addClass('bg-red-100 border-white');
						$itemSelected.find('div').removeClass('bg-green-100 border-white');
					}
					
					qq_select_reset();
					
					jQuery('.js-popup-services-status').remove();					
				}
				console.log(window.qqs.service_calendar.get_selected());
			},

			// fail
			function(jqXHR, textStatus, errorThrown)
			{

			}
		);
	});
	
	jQuery('body').on('click', '.js-remove-all-favorites-offers', function()
	{
		omi.api("Omi\\TFS\\View\\Favorite_Orders::Remove_All_Favorite_Orders", 
	
			// params
			[],

			// success
			function(resp)
			{					
				if (resp.Success)
				{
					window.location.href = resp.Url;
				}
			}, 

			// fail
			function(jqXHR, textStatus, errorThrown)
			{

			}
		);
	});
	
	jQuery('body').on('click', '.js-save-restrictions', function(event)
	{
		var $form = jQuery(this).closest('form');
		var $daysLocked = jQuery(this).data('days-locked');
		
		$form.find(':input:disabled').removeAttr('disabled');

		omi.api("Omi\\TFS\\View\\Rate_Set_Requests::UpdateRestrictions", [$form.serialize(), $daysLocked], 
			// success
			function(resp)
			{				
				if (resp.RoomId && resp.RatePlanId && resp.Restriction && resp.RestrictionName)
				{										
					// get selected
					var $selectedItems = window.qqs[resp.RestrictionName + "_" + resp.RoomId + "_" + resp.RatePlanId].get_selected();
					
					if ((resp.RestrictionName === 'Min_Advance_Reservation') || (resp.RestrictionName === 'Max_Advance_Reservation'))
						resp.Restriction = Math.round(resp.Restriction / 24);
					
					for (var $i = 0; $i < $selectedItems.length; $i++)
					{
						var $selected = jQuery($selectedItems[$i]);
								
						if ($daysLocked)
						{
							if (resp.Restriction == 'none')
								$selected.find('span').text('-');
							else
								$selected.find('span').text(resp.Restriction);
														
							if (resp.Restriction === 'yes')
							{
								$selected.find('div').removeClass('bg-green-100');
								$selected.find('div').addClass('bg-orange-100');
							}
							else if (resp.Restriction === 'no')
							{							
								$selected.find('div').removeClass('bg-orange-100');
								$selected.find('div').addClass('bg-green-100');
							}
							else if (resp.Restriction === 'none')
							{							
								$selected.find('div').removeClass('bg-orange-100');
								$selected.find('div').removeClass('bg-green-100');
							}
						}
						else
						{
							if (resp.Restriction == -1)
								$selected.find('span').text('-');
							else
								$selected.find('span').text(resp.Restriction);
						}							
					}
					
					qq_select_reset();
					
					jQuery('.js-popup-prices-restrictions').remove();
				}
				else
				{
					console.log(resp);
					alert('There was an error!');
				}
			}, 

			// fail
			function(jqXHR, textStatus, errorThrown)
			{

			}
		);
	});

	// enable table plugin
	// selectable.table();
	
	var ctx = document.getElementById('myChart');
	if (ctx)
	{
		var myChart = new Chart(ctx, {
			type: 'line',
			data: {
				labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
				maintainAspectRatio: true,
				datasets: [
				{
					label: 'Visits',
					data: [12, 19, 3, 5, 2, 3, 7, 14, 20, 23, 19, 24],
					backgroundColor: [
						'rgba(163, 167, 255, 0.5)'
					]
				},
				{
					label: 'Orders',
					data: [5, 4, 0, 0, 0, 1, 2, 6, 7, 10, 11, 12],
					backgroundColor: [
						'rgba(163, 167, 255, 0.9)'
					],
					borderColor: 'rgba(0, 0, 0, 0)',
					pointBorderColor: 'rgba(0, 0, 0, 0)'
				}
				]
			},
			options: {
				title: {
					display: true,
					text: 'Visists and Orders',
					fontSize: '20'
				},
				elements: {
                    point:{
                        radius: 0
                    },
					line: {
						borderColor: 'rgba(0, 0, 0, 0)',
					}
                },
				scales: {
					yAxes: [{
						ticks: {
							beginAtZero: true,
							borderDash: 2
						},
						gridLines: {
							borderDash: [2, 5]
						}
					}],
					xAxes: [{
						display: true,
						gridLines: {
							color: 'rgba(0, 0, 0, 0)'
						}
					}]
				},
				tooltips: {
					mode: 'x-axis'
				},
				legend: {
					display: true,
					position: 'top'
				}
			}
		});
	}
	
	jQuery('body').on('click', '.js-listActionsDropDown-Trigger', function()
	{
		// get obj this which is the trigger
		var $thisJq = jQuery(this);
		
		var content = document.getElementById("middle-content");
		var top = content.scrollTop;
		
		// get dropdown obj
		var $dropdown = $thisJq.closest('.js-listActionsDropDown-Container').find('.js-listActionsDropDown');
		
		// get trigger offset
		var $triggerOffset = $thisJq.offset();
		
		// position near the trigger
		$dropdown.css({'top': + ($triggerOffset.top + top - ($dropdown.height / 2)) + 'px'});
	   
		$dropdown.slideToggle(100);
	});
	
	jQuery('body').on('click', '.js-userAccountDDTrigger', function()
	{
		jQuery('.js-userAccountDD').slideToggle();
	});
	
	jQuery('body').on('click', '.js-toggle-search', function()
	{
		var $thisJq = jQuery(this);
		var $detailsBox = $thisJq.closest('.js-details-box__main');
		var $b2bSearch = $detailsBox.find('.js-b2b-search');
		
		$b2bSearch.toggle();
		
		$thisJq.toggleClass('active');
	});
	
	jQuery('body').on('click', '.js-toggle-filters', function()
	{
		var $thisJq = jQuery(this);
		var $detailsBox = $thisJq.closest('.js-b2b-filters-wrapper');
		var $b2bFilters = $detailsBox.find('.js-b2b-filters');
		
		$b2bFilters.toggle();
		
		$thisJq.toggleClass('active');
	});
	
	jQuery('body').on('click', '.js-expand', function()
	{
		// get this
		var $thisJq = jQuery(this);
		
		// get class of elem to expand
		var $toExpand = $thisJq.data('expand');
		
		// get elem to expand
		var $elemToExpand = jQuery(this).closest('.js-itm').find('.' + $toExpand);
		
		if ($elemToExpand.hasClass('hidden'))
		{
			$elemToExpand.removeClass('hidden');
			$elemToExpand.find('.js-' + $toExpand).slideDown();
		}
		else
		{
			$elemToExpand.find('.js-' + $toExpand).slideUp('400', function(){
				$elemToExpand.addClass('hidden');
			});
		}
	});
	
	jQuery('body').on('click', '.js-trigger-state-change', function(e)
	{
		var $thisJq = jQuery(this);
		var $thisCellId = $thisJq.data('cellid');
		
		var $dd = '<div class="mx-3 origin-top-right absolute w-32 mt-1 rounded-md shadow-lg js-dd-state-change">' +
				'<div class="z-10 rounded-md bg-white shadow-xs">' +
					'<div class="py-1">' +
						'<a data-cellid="' + $thisCellId + '" href="javascript: void(0);" class="block px-4 py-2 text-sm leading-5 hover:bg-gray-100 font-medium js-state-change" data-state="priced">Priced</a>' +
						'<a data-cellid="' + $thisCellId + '" href="javascript: void(0);" class="block px-4 py-2 text-sm leading-5 hover:bg-gray-100 text-green-500 font-medium js-state-change" data-state="included">Included</a>' +
						'<a data-cellid="' + $thisCellId + '" href="javascript: void(0);" class="block px-4 py-2 text-sm leading-5 hover:bg-gray-100 text-red-500 font-medium js-state-change" data-state="not_available">Not available</a>' +
					'</div>' +
				'</div>' +
			'</div>';
	
		var $mainTable = $thisJq.closest('.js-price-profile-table');
	
		$mainTable.append($dd);
		
		var $offsetTop = $thisJq.offset().top;
		var $offsetLeft = $thisJq.offset().left;
		
		console.log($offsetLeft, $mainTable.offset().left);
		
		jQuery('.js-dd-state-change').css({top: ($offsetTop - $mainTable.offset().top + 40), left: ($offsetLeft - $mainTable.offset().left)});
	});
	
	jQuery('body').on('click', '.js-state-change', function()
	{
		var $thisJq = jQuery(this);
		var $state = $thisJq.data('state');
		var $cellId = $thisJq.data('cellid');
		var $tableCell = jQuery('#' + $cellId);
		
		$tableCell.find('.js-price-item-state').val($state);
		
		if ($state === 'priced')
		{			
			$tableCell.find('.js-price-value').removeClass('hidden');
			$tableCell.find('.js-state-text').addClass('hidden');
			
			jQuery('.js-dd-state-change').remove();
		}
		else if (($state === 'included') || ($state === 'not_available'))
		{
			$tableCell.find('.js-state-text').removeClass('hidden');
			
			$tableCell.find('.js-price-value').addClass('hidden');
			
			if ($state === 'not_available')
			{
				$tableCell.find('.js-state-text').text('Not available');
				$tableCell.find('.js-state-text').removeClass('bg-green-100 text-green-500');
				$tableCell.find('.js-state-text').addClass('bg-red-100 text-red-500');
			}
			else
			{
				$tableCell.find('.js-state-text').text('Included');
				$tableCell.find('.js-state-text').removeClass('bg-red-100 text-red-500');
				$tableCell.find('.js-state-text').addClass('bg-green-100 text-green-500');
			}
			
			jQuery('.js-dd-state-change').remove();
		}
	});
	
	jQuery('body').on('mouseout', '.js-service-cell', function()
    {
        jQuery(this).find('.js-trigger-state-change').remove();
    });
	
    jQuery('body').on('mouseover', '.js-hover-toggle-contenteditable', function()
    {
        jQuery(this).attr('contenteditable', true);
    });
    jQuery('body').on('focusin', '.js-hover-toggle-contenteditable', function()
    {
        jQuery(this).attr('contenteditable', true);
		jQuery(this).parent().addClass('border-gray-300');
    });
    
    jQuery('body').on('mouseout', '.js-hover-toggle-contenteditable', function()
    {
        if (!jQuery(this).is(':focus'))
            jQuery(this).attr('contenteditable', false);
    });
    jQuery('body').on('focusout', '.js-hover-toggle-contenteditable', function()
    {   
        if (!jQuery(this).is(':hover'))
		{
            jQuery(this).attr('contenteditable', false);
			jQuery(this).parent().removeClass('border-gray-300');
		}
    });
	
	setupStickyPageHeading();

	setupStickySearch();
    
    jQuery('body').on('click', '.js-show-dd-menu', function()
    {
		// get this
		var $thisJq = jQuery(this);
		
		// get submenu
		var $submenu = jQuery(this).next();
		
		// get menu item arrow
		var $arrowMenuHidden = $thisJq.find('.js-arrow-menu-hidden');
		var $arrowMenuOpened = $thisJq.find('.js-arrow-menu-opened');
		
		if ($submenu.hasClass('hidden'))
		{
			$submenu.slideDown('400');
			
			// rotate arrow
			$arrowMenuOpened.show();
			$arrowMenuHidden.hide();
			$submenu.removeClass('hidden');
		}
		else
		{
			$submenu.slideUp('400');
			$submenu.addClass('hidden');
			$arrowMenuOpened.hide();
			$arrowMenuHidden.show();
		}
    });
  
    
    jQuery('body').on('click', '.js-control-sidemenu', function()
    {        
        if (jQuery('body').hasClass('layout-collapsed'))
        {
            localStorage.removeItem('layout_collapsed');
            
            jQuery('body').removeClass('layout-collapsed');
            jQuery(this).find('.js-arrow-right').hide();
            jQuery(this).find('.js-arrow-left').show();
			
            jQuery('.js-page-content').addClass("lg:ml-64");
        }
        else
        {
            jQuery('body').addClass('layout-collapsed');
            jQuery('.js-page-content').removeClass("lg:ml-64");
            
            localStorage.setItem('layout_collapsed', 'true');
            
            jQuery(this).find('.js-arrow-left').hide();
            jQuery(this).find('.js-arrow-right').show();
        }
    }); 
	
	jQuery('body').on('click', '.js-mobile-control-sidemenu', function()
	{
		jQuery('.sidemenu').addClass('active');
	});
		
	jQuery(document).mouseup(function(e) 
	{
		var container = jQuery(".sidemenu");

		// if the target of the click isn't the container nor a descendant of the container
		if (!container.is(e.target) && container.has(e.target).length === 0) 
		{
			jQuery('.sidemenu').removeClass('active');
		}
		
		var container = jQuery(".js-popup-prices-ratePlan");

		// if the target of the click isn't the container nor a descendant of the container
		if (container.length && !container.is(e.target) && container.has(e.target).length === 0) 
		{
			container.remove();
			qq_select_reset();
		}
		
		var container = jQuery(".js-popup-prices-roomCount");

		// if the target of the click isn't the container nor a descendant of the container
		if (container.length && !container.is(e.target) && container.has(e.target).length === 0) 
		{
			container.remove();
			qq_select_reset();
		}
		
		var container = jQuery(".js-popup-prices-restrictions");

		// if the target of the click isn't the container nor a descendant of the container
		if (container.length && !container.is(e.target) && container.has(e.target).length === 0) 
		{
			container.remove();
			qq_select_reset();
		}
		
		
		var $container = jQuery('.js-dd-state-change');
		if ($container.length && !$container.is(e.target) && $container.has(e.target).length === 0)
		{
			$container.remove();
		}
	});
    
    jQuery(document.body).on("change", 'input[type="file"]', function () 
    {
		var jq = jQuery(this);
		var $thisJq = jq[0];
		var $countImages = $thisJq.files.length;
		var $filesNames = '';

		if ($countImages > 0)
		{
			for (var i = 0; i < $countImages; i++)
				$filesNames += ((i > 0) ? ", " : "") + $thisJq.files[i].name;
		}

		// var path_input = jq.closest(".file-field").find('input.file-path');
		// if (path_input.length > 0)
		// {
			// insert name of the file
		// 	path_input.val($filesNames);
		// 	path_input.trigger('change');
		// }

		// read url and show image preview
		readUrl(this);
	});
    
    
    
	jQuery('body').on('click', '.js-show-filters', function()
	{
		jQuery('.table-heading-search').toggleClass('hide');
	});
	
	// init custom dropdown
	initCustomDropdowns();
	
	inputsHasValues();
	
	jQuery('body').on('focus', '.js-form-element-input', function()
	{
		jQuery(this).closest('.js-form-element-group').addClass('form-input-focus');
	});
	
	jQuery('body').on('click', '.qc-xg-property-label', function()
	{
		jQuery(this).closest('.js-form-element-group').find('.js-form-element-input').focus();
	});
	
	jQuery('body').on('focusout', '.js-form-element-input', function()
	{
		inputsHasValues([jQuery(this)]);
	});
	
	jQuery('body').on('change', '.js-form-element-input', function()
	{
		inputsHasValues([jQuery(this)]);
	});
	
	jQuery('body').on('click', '.js-open-menu-btn', function() 
	{
		jQuery('.js-menu').addClass('open');
	});
	
	jQuery('body').on('click', '.js-close-menu-btn', function()
	{
		jQuery('.js-menu').removeClass('open');
	});
});

function setupStickyPageHeading()
{
	var $pageHeading = document.getElementsByClassName("js-page-heading");
	if ($pageHeading.length > 0)
	{
		// When the user scrolls the page, execute myFunction \
        document.querySelector('#middle-content').addEventListener('scroll', function() { myPageHeadingFunction(); });
		
		var headerSticky = $pageHeading[0];

		var sticky = headerSticky.offsetTop + (headerSticky.offsetHeight * 2);

		// Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
		function myPageHeadingFunction() 
		{
			if (document.querySelector('#middle-content').scrollTop > sticky) 
			{
				headerSticky.classList.add("page-heading-sticky");
				jQuery('#middle-content').css({'padding-top' : headerSticky.offsetHeight + 'px'});
			} 
			else 
			{
				headerSticky.classList.remove("page-heading-sticky");
				jQuery('#middle-content').css({'padding-top' : '0px'});
			}
		}
	}
}

function setupStickySearch()
{
	var $pageSearch = document.getElementsByClassName("js-b2b-search-main");
	if ($pageSearch.length > 0)
	{
		// When the user scrolls the page, execute myFunction \
        document.querySelector('#middle-content').addEventListener('scroll', function() { mySearchHeadingFunction(); });
		
		var searchSticky = $pageSearch[0];

		var sticky = jQuery(searchSticky).offset().top + searchSticky.offsetHeight + 50;

		// Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
		function mySearchHeadingFunction() 
		{
            if (sticky < 0)
                sticky = 0;
            
			if (document.querySelector('#middle-content').scrollTop > sticky)
			{				
				searchSticky.classList.add("search-heading-sticky");
				document.querySelector('#middle-content').style.paddingTop = (jQuery('.js-page-heading').outerHeight() + searchSticky.offsetHeight) + 'px';
				// document.querySelector('#middle-content').style.paddingTop = searchSticky.offsetHeight + 'px';
				jQuery('.page-body-margin').css({'padding-top' : '200px'});
				
				searchSticky.style.marginTop = jQuery('.page-heading-sticky').outerHeight() + 'px';
			} 
			else 
			{
				searchSticky.classList.remove("search-heading-sticky");
				
				if (jQuery('.js-page-heading').hasClass('page-heading-sticky'))
					document.querySelector('#middle-content').style.paddingTop = jQuery('.js-page-heading').outerHeight() + 'px';
				else
					document.querySelector('#middle-content').style.paddingTop = '0px';
				
				document.querySelector('#middle-content').style.marginTop = '0px';
				jQuery('.page-body-margin').css({'padding-top' : '0px'});
				searchSticky.style.marginTop = '0px';
			}
		}
	}
}

function readUrl(input)
{	
	var $thisJq = jQuery(input);
	
	if (input.files) 
	{
		var $countFiles = input.files.length;
		var $imageExtensions = ['png', 'bmp', 'jpg', 'jpeg', 'gif'];
		
		if ($countFiles > 0)
		{	
			var $previewContainer = $thisJq.siblings('.js-file-field-container');
			if (!$previewContainer.length)
				$previewContainer = $thisJq.closest('.js-file-wrapper').find('.js-file-field-container');
			$previewContainer.empty();

			for (var i = 0; i < $countFiles; i++)
			{
				// new breader object
				var $reader = new FileReader();
				
				var $fileName = input.files[i].name;
				var $fileExt = input.files[i].name.split('.').pop();
				
				// add source
				$reader.onload = function (e) 
				{
					if ($imageExtensions.includes($fileExt))
					{
						// create image element
						var $imagePreview = jQuery(document.createElement('img'));

						// add src 
						$imagePreview.attr('src', e.target.result);

						// append to preview container
						$previewContainer.append($imagePreview);
					}
					else
					{
						// create image element
						var $divPreview = jQuery(document.createElement('div'));

						// add src 
						$divPreview.text($fileName);

						// append to preview container
						$previewContainer.append($divPreview);
					}
				};
				
				// nu stiu ce face asta dar e bine :)
				$reader.readAsDataURL(input.files[i]);
			}
		}		
	}
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') 
			c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function deleteCookie(cname)
{
    document.cookie = cname + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
}

function inputsHasValues($inputs)
{
	if (($inputs === undefined) || ($inputs === null))
		$inputs = jQuery('.js-form-element-input');
	
	for (var $i =0; $i < $inputs.length; $i++)
	{
		var inputJq = jQuery($inputs[$i]);
		if (inputJq.val())
			inputJq.closest('.js-form-element-group').addClass('form-input-focus');
		else
			inputJq.closest('.js-form-element-group').removeClass('form-input-focus');
	}
}

function initCustomDropdowns(context)
{
	// CUSTOM SIMPLE DROPDOWN
	var dd = new DropDown(jQuery('.qc-radio-dropdown', context));
	
	jQuery("body").click(function() 
	{
		jQuery('.qc-radio-dropdown').find('.dropdown').addClass('hidden');
	});
}

// CUSTOM SIMPLE DROPDOWN
function DropDown(el) 
{
    this.dd = el;
    this.placeholder = this.dd.children('span');
    this.options = this.dd.find('ul.dropdown > li > label');
    this.val = '';
    this.index = -1;
    this.initEvents();
	return el;
}

DropDown.prototype = {

    initEvents : function()
	{
        var obj = this;

		// display dropdown
        obj.dd.on('click', function(event) 
		{
			var dd_jq = jQuery(this);

			dd_jq.trigger("onElementClick", [event]);
            dd_jq.find('.dropdown').toggleClass('hidden');
            return false;
        });

		// click on option
        obj.options.on('click', function(e) 
		{
            var option = jQuery(this);
			var dd_jq = option.closest(".qc-radio-dropdown");
			var ctrl_jq = obj.dd.closest(".omi-control");
			var ctrl = (ctrl_jq.length > 0) ? $ctrl(ctrl_jq) : null;
			//var input = option.closest("li").find("input[id='" + option.for + "']");
			var input = jQuery("#" + option.attr("for"));
			
			var ph_jq = dd_jq.children("span");
			ph_jq.text(option.text());

			if (ctrl)
				ctrl.trigger("click_on_dditm", {"dd_jq" : dd_jq, "input" : input});
			obj.dd.trigger("onElementClick", [e, option]);

			option.siblings("input[type='radio']").prop('checked', true);
        });
    }
}

function Confirm(title, msg, $true, $false, func, args) 
{ 
	var $content =  "" +
		"<div class='qc-popup fixed z-30 inset-0 overflow-y-auto dialog-ovelay'>" +
			"<div class='flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 is-visible'>" +
				"<div class='fixed inset-0 transition-opacity'>" +
					"<div class='absolute inset-0 bg-gray-500 opacity-75'></div>" +
				"</div>" +
				"<span class='hidden sm:inline-block sm:align-middle sm:h-screen'></span>" +

				"<div class='inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full sm:p-8'>" +
					"<header>" +
						 "<h3> " + title + " </h3>" +
						 "<i class='fa fa-close'></i>" +
					 "</header>" +

					 "<div class='dialog-msg text-center'>" +
						 "<p> " + msg + " </p>" +
					 "</div>" +

					 "<footer class='mt-8'>" +
						 "<div class='controls flex justify-center'>" +
							 "<button class='doAction inline-flex justify-center mr-4 rounded-md border border-red-300 px-4 py-2 bg-red-500 text-base leading-6 font-medium text-white shadow-sm hover:text-white focus:outline-none transition ease-in-out duration-150 sm:text-sm sm:leading-5 jx-close-modal'>" + $true + "</button>" +
							 "<button class='cancelAction inline-flex justify-center rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5'>" + $false + "</button>" +
						 "</div>" +
					 "</footer>" +
				"</div>" +
			"</div>" +
		"</div>";

	$('body').prepend($content);
		 
	$('.doAction').click(function () {
		window[func](args);
		
		$(this).parents('.dialog-ovelay').fadeOut(500, function () {
		  $(this).remove();
		});
	});
	  
	$('.cancelAction, .fa-close').click(function () {
		$(this).parents('.dialog-ovelay').fadeOut(500, function () {
		  $(this).remove();
		});
	});
}