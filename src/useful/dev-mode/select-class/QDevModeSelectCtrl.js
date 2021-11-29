
QExtendClass("QDevModeSelectCtrl", "QWebControl", 
{
	/**
	 * __ClassLoaded will be called after the CLASS is defined
	 */
	__ClassLoaded: function()
	{
		var input = jQuery(".QDevModeSelectCtrl .classesSearch");
		input.off('keyup').keyup(function() {
				
				var ctrl = $ctrl(this);
				if (ctrl.hasListTimeout)
					return;
				else if (ctrl.inListRequest || (ctrl.lastListCalled && (ctrl.lastListCalled > (Date.UTC() - 500 /* ms */))))
				{
					ctrl.hasListTimeout = true;
					var ref_this = this;
					var timeout_func = function () {
							if (!ctrl.inListRequest)
							{
								ctrl.hasListTimeout = false;
								ctrl.updateByFilter(ref_this);
							}
							else
								setTimeout(timeout_func, 50);
						};
					setTimeout(timeout_func, 50);
				}
				else 
				{
					ctrl.updateByFilter(this);
				}
			});
			
		jQuery(".QDevModeSelectCtrl input.classesSearch").focus(function ()
		{
			jQuery(this).closest(".QDevModeSelectCtrl").find("ul.classesList").css("display", "block");
		});
		
		jQuery(".QDevModeSelectCtrl ul.classesList li a").click(function ()
		{
			var class_name = jQuery(this).closest("li").data("oclassname");
			var ctrl = jQuery(this).closest('.QDevModeSelectCtrl');
			ctrl.find('input.classesSearch').val(class_name);
			ctrl.find('ul.classesList').hide();
			ctrl.trigger('changed', [class_name]);
		});
	},
	
	updateByFilter: function(filter_input)
	{
		if (this.inListRequest)
			return;
		
		var filter = jQuery(filter_input).val();
		filter = filter.toLowerCase();
		
		this.lastListCalled = Date.UTC();
		this.inListRequest = true;
		
		var ref_this = this;
		var url_tag = this.jQuery().data("url_tag");
		// make sure we are not calling this more than N th times per X sec
		// $filter = null, $limit = 200, $classes = null, $url_tag
		this.call("renderList", [filter, 200, null, url_tag], function (resp){
			ref_this.jQuery(".classesList").replaceWith(resp);
			ref_this.jQuery().trigger("listUpdated");
			
			ref_this.jQuery().find("ul.classesList").css("display", "block");
			ref_this.jQuery("ul.classesList li a").click(function ()
			{
				var class_name = jQuery(this).closest("li").data("oclassname");
				var ctrl = jQuery(this).closest('.QDevModeSelectCtrl');
				ctrl.find('input.classesSearch').val(class_name);
				ctrl.find('ul.classesList').hide();
				ctrl.trigger('changed', [class_name]);
			});
			
			ref_this.inListRequest = false;
		});
	}

});
	