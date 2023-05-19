QExtendClass("Omi\\View\\WebPage", "QWebPage",
{

});

function doOnException (jqXHR, textStatus, errorThrown)
{
	var $ex = ((typeof(errorThrown) === "object") && errorThrown.__cust__) ? errorThrown : null;
	if (!$ex)
	{
		var text = (jqXHR && jqXHR.responseText) ? jqXHR.responseText : null;
		var possibJson = ((text.charAt(0) === "{") && (text.charAt(text.length - 1) === "}"));
		if (possibJson)
		{
			$ex = null;
			try
			{
				$ex = JSON.parse(text);	
			}
			catch(err)
			{

			}
		}
	}
	
	alert(($ex && $ex.Message) ? $ex.Message : "System Error");
}


jQuery(window).on("Q_Event_AjaxError", function (ajax_params, jqXHR, textStatus, errorThrown) {

});

jQuery(document).ready(function () {

	if (jQuery(".qc-in-sync").length > 0)
		checkInSync();
	
	jQuery(document.body).on("afterItemSelect", ".qb-context-change-box .qc-custom-dd", function (e, sender) {
		var ctrl = $ctrl(jQuery(this));
		omi.api('Omi\\App::SetupContext', [ctrl.getSelectedId(), ctrl.getSelectedType()], function (resp) {
			// just refresh page when ready
			//window.location.href = window.location.href;
			if (resp)
				window.location.href = jQuery("base").attr("href");
		}, function (jqXHR, textStatus, errorThrown) {
			doOnException(jqXHR, textStatus, errorThrown);
		});
	});

	jQuery(document.body).on("click", ".qb-reset-context", function () {
		omi.api('Omi\\App::ResetContext', null, function (resp) {
			// just refresh page when ready
			window.location.href = window.location.href;
		}, function (jqXHR, textStatus, errorThrown) {
			doOnException(jqXHR, textStatus, errorThrown);
		});
	});
	
	jQuery(document.body).on("click", ".qc-context-data", function () {
		
		if (jQuery(".qc-context-ddwr").length > 0)
		{
			jQuery(".qc-context-data").hide();
			jQuery(".qc-context-ddwr").show();
		}
	});
	
	jQuery(document.body).on("click", ".qc-cancel-context", function () {
		
		if (jQuery(".qc-context-ddwr").length > 0)
		{
			jQuery(".qc-context-ddwr").show();
			jQuery(".qc-context-data").show();
			// jQuery('.qc-cancel-context').hide();
		}
	});
	
	jQuery(document.body).on("click", ".qc-custom-dd", function () {
		// jQuery('.qc-cancel-context').show();
	});
});

function checkInSync()
{
	if (window.hasCheckTimeout)
		return;

	else if (window._inCheckReq || (window.lastCheckCalled && (window.lastCheckCalled > (Date.UTC() - 500))))
	{
		window.hasCheckTimeout = true;
		var timeout_func = function () {
				if (!window._inCheckReq)
				{
					window.hasCheckTimeout = false;
					__checkInSync();
				}
				else
					setTimeout(timeout_func, 2500);
			};
		setTimeout(timeout_func, 2500);
	}
	else
		__checkInSync();
}

function __checkInSync()
{
	if (window._inCheckReq)
			return;

	window.lastListCalled = Date.UTC();
	window._inCheckReq = true;

	omi.api('Omi\\User::InSync', [], function (resp) {
		if (resp)
			window.location.href = jQuery("base").attr("href");
		else
			checkInSync();
		window._inCheckReq = false;
		}, function (jqXHR, textStatus, errorThrown) {
			checkInSync();
			window._inCheckReq = false;
			doOnException(jqXHR, textStatus, errorThrown);			
	});
}

jQuery(document).ready(function () 
{
	jQuery(window).on('select.dropdown', function ($info, $selected) 
	{
		
		// WE NEED TO MAKE SURE IT's the right one !!!!
		// alert('check it\'s the right one !!!');
		if (jQuery($info.target).closest('#tfh_webpage_property_select').length > 0)
		{
			omi.api('Omi\\View\\Controller::Get_Property_URL', [($selected && $selected.id) ? $selected.id : 0, false, true], function (resp) {
				
					if (resp)
					{
						window.location.href = resp;
						// a fix for 
						throw 'ignore this error. fix for chrome to stop execution on window.location.href.';
					}
				});
			/*
			if ($selected && $selected.id)
				window.$tfh_page_property_select = "" + (($selected && $selected.id) ? $selected.id : "");
			else
				window.$tfh_page_property_select = "";
			window.sessionStorage.setItem('tfh_page_property_select', window.$tfh_page_property_select);
			tfh_setCookie('tfh_page_property_select', window.$tfh_page_property_select, 1);
			
			// window.location.reload();
			window.location.href = window.location.href.split('?')[0];
			*/
		}		
	});
	
	jQuery('body').on('click', '.js-show-terms-popup', function()
	{
		omi.api("Omi\\TFS\\View\\UserCreateAccount::TermsContentPopup", 
	
			// params
			[],

			// success
			function(resp)
			{
				var $popupJq = jQuery('<div class="qc-popup fixed z-30 inset-0 overflow-y-auto jx-modal qHideOnClickAway-remove qHideOnClickAway-parent">' +
				'<div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 jx-modal-content is-visible">' +
					'<div class="fixed inset-0 transition-opacity">' +
						'<div class="absolute inset-0 bg-gray-500 opacity-75"></div>' +
					'</div>' +
					'<!-- This element is to trick the browser into centering the modal contents. -->' +
					'<span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;'+
						'<div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-8 js-popup-container qHideOnClickAway" role="dialog" aria-modal="true" aria-labelledby="modal-headline"">' +
						jQuery(resp).find('.js-terms-content').html() +
							'<a href="javascript: void(0)" class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5 jx-close-modal">' + _L("Close") + '</a>' +
						'</div>' +
					'</div>' +
				'</div>');
				
				jQuery('body').append($popupJq.html());
			}, 

			// fail
			function(jqXHR, textStatus, errorThrown)
			{

			}
		);
	});
	
	jQuery('body').on('click', '.js-show-policy-popup', function()
	{
		omi.api("Omi\\TFS\\View\\UserCreateAccount::TermsContentPopup", 
	
			// params
			[],

			// success
			function(resp)
			{
				var $popupJq = jQuery('<div class="qc-popup fixed z-30 inset-0 overflow-y-auto jx-modal qHideOnClickAway-remove qHideOnClickAway-parent">' +
				'<div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 jx-modal-content is-visible">' +
					'<div class="fixed inset-0 transition-opacity">' +
						'<div class="absolute inset-0 bg-gray-500 opacity-75"></div>' +
					'</div>' +
					'<!-- This element is to trick the browser into centering the modal contents. -->' +
					'<span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;'+
						'<div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-8 js-popup-container qHideOnClickAway" role="dialog" aria-modal="true" aria-labelledby="modal-headline"">' +
						jQuery(resp).find('.js-terms-content').html() +
							'<a href="javascript: void(0)" class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5 jx-close-modal">' + _L("Close") + '</a>' +
						'</div>' +
					'</div>' +
				'</div>');
				
				jQuery('body').append($popupJq.html());
			}, 

			// fail
			function(jqXHR, textStatus, errorThrown)
			{
				omi.api("Omi\\TFS\\View\\UserCreateAccount::PolicyContentPopup", 

					// params
					[],

					// success
					function(resp)
					{
						var $popupJq = jQuery('<div class="qc-popup fixed z-30 inset-0 overflow-y-auto jx-modal qHideOnClickAway-remove qHideOnClickAway-parent">' +
						'<div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 jx-modal-content is-visible">' +
							'<div class="fixed inset-0 transition-opacity">' +
								'<div class="absolute inset-0 bg-gray-500 opacity-75"></div>' +
							'</div>' +
							'<!-- This element is to trick the browser into centering the modal contents. -->' +
							'<span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;'+
								'<div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-8 js-popup-container qHideOnClickAway" role="dialog" aria-modal="true" aria-labelledby="modal-headline"">' +
								jQuery(resp).find('.js-terms-content').html() +
									'<a href="javascript: void(0)" class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5 jx-close-modal">' + _L("Close") + '</a>' +
								'</div>' +
							'</div>' +
						'</div>');

						jQuery('body').append($popupJq.html());
					}, 

					// fail
					function(jqXHR, textStatus, errorThrown)
					{

					}
				);
			}
		);
	});
});

