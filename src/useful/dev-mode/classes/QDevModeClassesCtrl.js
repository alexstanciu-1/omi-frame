
QExtendClass("QDevModeClassesCtrl", "QWebControl", 
{
	/**
	 * __ClassLoaded will be called after the CLASS is defined
	 */
	__ClassLoaded: function()
	{
		var input = jQuery(".QDevModeClassesCtrl .searchClassElements");
		input.off('keyup').keyup(function() {
				var filter = jQuery(this).val();
				filter = filter.toLowerCase();
				
				var all_uls = jQuery(this).closest(".QDevModeClassesCtrl").find("table.classElements");
				if (filter.length === 0)
				{
					all_uls.find("tr.data-idf").show();
				}
				else
				{
					all_uls.find("tr.data-idf").hide();
					all_uls.find("tr.data-idf[data-idf*=" + filter + "]").show();
				}
			});
			
		jQuery(".QDevModeClassesCtrl .methParamsExpander").click(function ()
		{
			jQuery(this).next().toggle();
		});
			
		// searchClassElements
		// classElements
		// .data-idf
		// data-idf
			
		jQuery(".QDevModeClassesCtrl .showFile").click(function ()
		{
			var mode = jQuery(this).data("src");
			var tag = jQuery(this).data("tag");
			var class_name = jQuery(this).data("class");
			if (!class_name)
				class_name = jQuery(this).closest("h1").data("src");
			
			$ctrl(this).call("getSourceFor", [class_name, mode, tag], function (resp)
			{
				// now put the response in a very simple modal
				window.scrollTo(0, 0);
				jQuery(document.body).append(
					"<div class='q_devpanelover qHideOnClickAway' onclick='jQuery(\".q_devpanelover\").remove();' style='z-index: 100000; position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; background-color: white; opacity: 0.6;'></div>" + 
					"<div class='q_devpanelover qHideOnClickAway' style='z-index: 200000; position: absolute; top: 10%; width: 1100px; left: 50%; margin-left: -550px; background-color: white;'><div class='highlight'>" + 
						resp + "</div></div>");
			});
		});
	},
	
	popupProperty: function(class_name, prop)
	{
		this.call("getSourceForProperty", [class_name, prop], function (resp)
		{
			window.scrollTo(0, 0);
			jQuery(document.body).append(
					"<div class='q_devpanelover qHideOnClickAway' onclick='jQuery(\".q_devpanelover\").remove();' style='z-index: 100000; position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; background-color: white; opacity: 0.6;'></div>" + 
					"<div class='q_devpanelover qHideOnClickAway' style='z-index: 200000; position: absolute; top: 10%; width: 1100px; left: 50%; margin-left: -550px; background-color: white;'><div class='highlight'>" + 
						resp + "</div></div>");
		});
	},
	
	popupMethod: function(class_name, method)
	{
		this.call("getSourceForMethod", [class_name, method], function (resp)
		{
			window.scrollTo(0, 0);
			jQuery(document.body).append(
					"<div class='q_devpanelover qHideOnClickAway' onclick='jQuery(\".q_devpanelover\").remove();' style='z-index: 100000; position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; background-color: white; opacity: 0.6;'></div>" + 
					"<div class='q_devpanelover qHideOnClickAway' style='z-index: 200000; position: absolute; top: 10%; width: 1100px; left: 50%; margin-left: -550px; background-color: white;'><div class='highlight'>" + 
						resp + "</div></div>");
		});
	}

});
