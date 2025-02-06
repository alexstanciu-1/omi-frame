QExtendClass("Omi\\App\\View\\Registration_Requests", "Omi\\View\\Grid",
{
	onclick: function(sender, sender_id, event)
	{
		var $super = this._super(sender, sender_id, event);
		
		if (this.hasClass("js-activate-account", sender))
		{
			var $registrationId = jQuery(sender).data('registration-id');
			
			omi.api("Omi\\App\\View\\Registration_Requests::ActivateAccount", [$registrationId], 
				// success
				function(resp)
				{					
					window.location.reload();
				}, 

				// fail
				function(jqXHR, textStatus, errorThrown)
				{

				}
			);
		}
		else if (this.hasClass('js-send-confirmation-email', sender))
		{
			var $registrationId = jQuery(sender).data('registration-id');
			
			omi.api("Omi\\App\\View\\Registration_Requests::SendConfirmationEmail", [$registrationId], 
				// success
				function(resp)
				{					
					window.location.reload();
				}, 

				// fail
				function(jqXHR, textStatus, errorThrown)
				{

				}
			);
		}
		
		return $super;
	}
});
