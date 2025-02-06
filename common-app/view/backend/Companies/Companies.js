
QExtendClass("Omi\\App\\View\\Companies", "Omi\\View\\Grid",
{
	init : function (dom)
	{	
		var $super = this._super(dom);
		
		this.showTourismFields();
		
		return $super;
	},
	
	onclick: function(sender, sender_id, event)
	{
		var $super = this._super(sender, sender_id, event);
		
		var $senderJq = jQuery(sender).closest('input.form-checkbox');
		
		if ($senderJq.closest('[xg-item^="Contacts("]').length > 0)
		{
			$senderJq.closest('[xg-list^="Contacts("]').find('input.form-checkbox').prop("checked", false);
			$senderJq.prop('checked', true);
		}
		
		if ($senderJq.closest('[xg-item^="Contact_Phones_List("]').length > 0)
		{
			$senderJq.closest('[xg-list^="Contact_Phones_List("]').find('input.form-checkbox').prop("checked", false);
			$senderJq.prop('checked', true);
		}
		
		if ($senderJq.closest('[xg-item^="Contact_Emails_List("]').length > 0)
		{
			$senderJq.closest('[xg-list^="Contact_Emails_List("]').find('input.form-checkbox').prop("checked", false);
			$senderJq.prop('checked', true);
		}
		
		return $super;
	},
	
	onchange: function(sender, sender_id, event)
	{
		var $super = this._super(sender, sender_id, event);
		
		var $senderJq = jQuery(sender).closest('input.form-checkbox');
		var $type = $senderJq.attr('xg-property-value');
		
		return $super;
	},
	
	onevent: function(event_type, sender, args)
	{
		var $super = this._super(event_type, sender, args);
		
		if (event_type === "afterSave")
		{
			var $submit_resp = args.resp[0];
			
			if ($submit_resp.Success === false)
			{
				alert($submit_resp.Error_Message);
				return;
			}
		}
		
		return $super;
	}
});

