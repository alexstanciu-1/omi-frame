
QExtendClass("QDevModeBindsCtrl", "QWebControl", 
{
	/**
	 * __ClassLoaded will be called after the CLASS is defined
	 */
	__ClassLoaded: function()
	{
		var input = jQuery(".QDevModeBindsCtrl .classesSearch");
		input.off('keyup').keyup(function() {
				var filter = jQuery(this).val();
				filter = filter.toLowerCase();
				
				var all_uls = jQuery(this).closest(".QDevModeBindsCtrl").find(".classesList");
				if (filter.length === 0)
				{
					all_uls.find(">li").show();
				}
				else
				{
					all_uls.find(">li").hide();
					all_uls.find(">li[data-classname*=" + filter + "]").show();
				}
			});
		
	},
	
	generateSelector: function(classname)
	{
		var depth = this.jQuery(".bindsSelectorDepth").val();
		var ref_this = this;
		this.call("getBindsSelector", [classname, depth], function (resp)
		{
			ref_this.jQuery(".bindsSelector").val(resp);
			ref_this.generateBinds(classname);
		});
	},
	
	generateBinds: function(classname)
	{
		var selector = this.jQuery(".bindsSelector").val();
		this.call("getGeneratedBinds", [classname, selector], function (resp)
		{
			this.jQuery(".bindsGeneratedHtml").val(resp);
		});
	}
});
