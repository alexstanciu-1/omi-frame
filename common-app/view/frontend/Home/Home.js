QExtendClass("Omi\\View\\Home", "QWebControl",
{

});

jQuery(document).ready(function () 
{
	jQuery('body').on('click', '.js-activate-property', function()
	{
		var $thisJq = jQuery(this);
		var $propertyId = $thisJq.data('property');

		omi.api("Omi\\App\\View\\Properties::RequiresVerification", [$propertyId], 
			// success
			function(resp)
			{
				resyncProperty($propertyId);
				
				alert('A fost trimisa solicitarea pe email');
				window.location.reload();
			}, 

			// fail
			function(jqXHR, textStatus, errorThrown)
			{

			}
		);
	});
	
	jQuery('body').on('click', '.js-account-configuration-check', function()
	{
		var $thisJq = jQuery(this);
		var $name = $thisJq.attr('name');
		var $isChecked = $thisJq.is(':checked');
		omi.api("Omi\\View\\Home::Save_Account_Configuration", [$name, $isChecked], 
			// success
			function(resp)
			{
				
			}, 

			// fail
			function(jqXHR, textStatus, errorThrown)
			{

			}
		);
	});
	
	jQuery('body').on('click', '.js-close-account-configuration', function()
	{
		var $thisJq = jQuery(this);
		
		omi.api("Omi\\View\\Home::Disable_Account_Configuration", [], 
		// success
		function(resp)
		{
			location.reload();
		}, 

		// fail
		function(jqXHR, textStatus, errorThrown)
		{

		}
	);
	});
});

function resyncProperty($propertyId)
{
	if (!$propertyId)
	{
		console.warn('resyncProperty called without a valid argument');
		return false;
	}

	omi.api("Omi\\TFH\\Rate_Set_Request::Full_Sync_Property", [$propertyId]);
}