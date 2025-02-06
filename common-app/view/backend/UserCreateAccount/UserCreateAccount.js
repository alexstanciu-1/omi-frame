QExtendClass("Omi\\App\\View\\UserCreateAccount", "Omi\\View\\Grid",
{
	init : function (dom)
	{		
		var $super = this._super(dom);
		
		// alert('cool!');
		
		return $super;
	},
	
	onclick: function(sender, sender_id, event)
	{
		var $super = this._super(sender, sender_id, event);
		
		if (this.hasClass('js-terms-popup',sender))
		{
			var $senderJq = jQuery(sender);
						
			this.openTermsPopup($senderJq);
		}
		else if (this.hasClass('js-policy-popup', sender))
		{
			var $senderJq = jQuery(sender);
						
			this.openPolicyPopup($senderJq);
		}
	   
		return $super;
	},
	
	onchange: function(sender, sender_id, event)
	{
		var $super = this._super(sender, sender_id, event);
		
		var $senderJq = jQuery(sender).closest('input.form-checkbox');
		var $isPropertyOwnerJq = $senderJq.closest('.js-property-owner');
		var $isChannelOwnerJq = $senderJq.closest('.js-channel-owner');
		
		if ($isPropertyOwnerJq.length > 0)
		{			
			if ($isPropertyOwnerJq.is(':checked'))
			{	
				jQuery('.js-channel-manager').show();
				jQuery('.js-channel-manager').find('.qc-dd-input-id').addClass('q-mandatory');
				jQuery('.js-channel-manager').find('.qc-dd-input-ty').addClass('q-mandatory');
			}
		}
		else if ($isChannelOwnerJq.length > 0)
		{
			if ($isChannelOwnerJq.is(':checked'))
			{
				jQuery('.js-channel-manager').hide();
				jQuery('.js-channel-manager').find('.qc-dd-input-id').removeClass('q-mandatory');
				jQuery('.js-channel-manager').find('.qc-dd-input-ty').removeClass('q-mandatory');
			}
		}
		
		return $super;
	},
	
	doSubmit: function (form, sender, forceAjax, callbackOnSuccess, callbackOnError)
	{
		// if (!jQuery('#accept_terms').is(':checked'))
		// 	alert(jQuery('#accept_terms').data('errmsg'));
		
		this._super(form, sender, forceAjax, callbackOnSuccess, callbackOnError);
		
		grecaptcha.reset();
	},
	
	onexception : function($ex, jqXHR, textStatus, errorThrown)
	{
		grecaptcha.reset();
		console.log($ex);
		jQuery('.login-wrapper').prepend('<div class="p-4 text-red-500 border border-red-500 mb-6 js-error-message">' + $ex.__error_obj__.messsage + '</div>');
	},
	
	onevent: function(event_type, sender, args)
	{
		var $super = this._super(event_type, sender, args)
		
		if (event_type === "beforeSave")
		{
			jQuery('.js-error-message').remove();
		}
		
		return $super;
	},
	
	callbackOnSuccess : function (sender, resp)
	{
		if (resp[1] && resp[2])
		{
			window.location.href = resp[2];
		}
		else
		{
			jQuery('.xg-form').find("input, textarea").val("");
		
			jQuery('.login-wrapper').html('<div class="js-error-message">\n\
				Îți mulțumim pentru înregistrarea contului.\n\
				<br /><br />\n\
				Un email ce conține codul de confirmare a fost trimis la adresa de email introdusă. Te rugăm să accesezi adresa web menționată în email pentru confirmarea contului. După acest pas echipa H2B va verifica informațiile, va activa contul și va trimite un email referitor la activarea acestuia.\n\
				<br /><br />\n\
				Cu drag,\n\
				<br />\n\
				Echipa H2B\n\
				</div>');
			
			grecaptcha.reset();
		}
	},
	
	openTermsPopup: function ($senderJq)
	{
		var $this = this;
		omi.api("Omi\\App\\View\\UserCreateAccount::TermsContentPopup", 
	
			// params
			[],

			// success
			function(resp)
			{
				$this.setupPopup(jQuery(resp).find('.js-terms-content').html());
			}, 

			// fail
			function(jqXHR, textStatus, errorThrown)
			{

			}
		);
	},
	
	openPolicyPopup: function ($senderJq)
	{
		var $this = this;		
		
		omi.api("Omi\\App\\View\\UserCreateAccount::PolicyContentPopup", 
	
			// params
			[],

			// success
			function(resp)
			{
				$this.setupPopup(jQuery(resp).find('.js-policy-content').html());
			}, 

			// fail
			function(jqXHR, textStatus, errorThrown)
			{

			}
		);
	}
});


