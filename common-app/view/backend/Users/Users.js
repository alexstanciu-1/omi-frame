QExtendClass("Omi\\App\\View\\Users", "Omi\\View\\Grid",
{
	init : function (dom)
	{		
		var $super = this._super(dom);
		
		// type radio
		var $radioChecked = jQuery('[xg-property^="Type"] input[type="radio"]:checked');
		
		if ($radioChecked.val() == 'H2B_Channel')
		{
			jQuery('.js-container-TFH_API_System').hide();
			jQuery('.js-container-Access_To_All_Properties').show();
			var $dd = jQuery('.js-container-TFH_API_System').find('.js-dd');

			var $valid = $dd.attr('q-valid');
			$dd.attr('qq-valid', $valid);
			$dd.removeAttr('q-valid');
		}
		else if (($radioChecked.val() == 'H2B_Property'))
			jQuery('.js-container-Access_To_All_Properties').hide();
		else if (!$radioChecked.val())
			jQuery('.js-container-Access_To_All_Properties').show();

		return $super;
	}, 
	
	onchange: function(sender, sender_id, event)
	{
		var $super = this._super(sender, sender_id, event);
		var $senderJq = jQuery(sender);
		
		if ($senderJq.closest('[xg-property^="Type"] input[type="radio"]').length > 0)
		{
			if ($senderJq.closest('input[type="radio"]').val() == 'H2B_Property')
				jQuery('.js-container-Access_To_All_Properties').hide();
			
			if ($senderJq.closest('input[type="radio"]').val() == 'H2B_Superadmin')
				jQuery('.js-container-Access_To_All_Properties').show();
			
			if ($senderJq.closest('input[type="radio"]').val() == 'H2B_Channel')
			{
				jQuery('.js-container-TFH_API_System').hide();
				var $dd = jQuery('.js-container-TFH_API_System').find('.js-dd');
				jQuery('.js-container-Access_To_All_Properties').show();
				
				var $valid = $dd.attr('q-valid');
				$dd.attr('qq-valid', $valid);
				$dd.removeAttr('q-valid');
				
				
				var $hiddens = $dd.find('input[type="hidden"]');
				
				for (var $i = 0; $i < $hiddens.length; $i++)
				{
					var $hidden = jQuery($hiddens[$i]);
					var $valid = $hidden.attr('q-valid');
					$hidden.attr('qq-valid', $valid);
					$hidden.removeAttr('q-valid');
				}
			}
			else 
			{				
				if (!jQuery('.js-container-TFH_API_System').is(':visible'))
				{
					jQuery('.js-container-TFH_API_System').show();
					
					var $dd = jQuery('.js-container-TFH_API_System').find('.js-dd');
				
					var $valid = $dd.attr('qq-valid');
					$dd.attr('q-valid', $valid);
					$dd.removeAttr('qq-valid');
					
					var $hiddens = $dd.find('input[type="hidden"]');
					for (var $i = 0; $i < $hiddens.length; $i++)
					{
						var $hidden = jQuery($hiddens[$i]);
						var $valid = $hidden.attr('qq-valid');
						$hidden.attr('q-valid', $valid);
						$hidden.removeAttr('qq-valid');
					}
				}
			}
		}
		
		return $super;
	}
});
