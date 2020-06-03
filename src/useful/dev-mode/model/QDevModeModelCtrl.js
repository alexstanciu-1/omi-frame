

QExtendClass("QDevModeModelCtrl", "QWebControl", 
{
	/**
	 * __ClassLoaded will be called after the CLASS is defined
	 */
	__ClassLoaded: function()
	{
		var updateBackground = function()
		{
			var trs = jQuery(".QDevModeModelCtrl table tr:visible");
			trs.filter(":odd").css("background", "#f9f9f9");
			trs.filter(":even").css("background", "white");
			// jQuery(".QDevModeModelCtrl table tr:visible:even").css("background", "white");
		};
		
		updateBackground();
		
		var toggleRowsSubChildren = function (child_elements, new_state)
		{
			// child_elements, new_state
			if (new_state === "off")
				child_elements.hide();
			
			// reflect state
			for (var i = 0; i < child_elements.length; i++)
			{
				var child = child_elements[i];
				if (new_state === "on")
				{
					if (jQuery(child).data("state") === "on")
						jQuery(child).show();
				}
				var toggle_class = jQuery(child).data("toggle");
				if (toggle_class)
				{
					var sub_child_elements = jQuery(".QDevModeModelCtrl table tr." + toggle_class);
					// console.log(toggle_class + " I have found: " + sub_child_elements.length);
					toggleRowsSubChildren(sub_child_elements, new_state);
				}
			}
		};
		
		var toggleRows = function()
		{
			// this = element that triggered
			var toggle_class = jQuery(this).data("toggle");
			var child_elements = jQuery(".QDevModeModelCtrl table tr." + toggle_class);
			
			if (!child_elements.length)
				return;
			
			var visible = child_elements.first().is(":visible");
			var new_state = visible ? "off" : "on";
			
			child_elements.toggle();
			child_elements.data("state", new_state);
			
			for (var i = 0; i < child_elements.length; i++)
			{
				var child = child_elements[i];
				var child_toggle_class = jQuery(child).data("toggle");
				if (child_toggle_class)
				{
					var sub_child_elements = jQuery(".QDevModeModelCtrl table tr." + child_toggle_class);
					toggleRowsSubChildren(sub_child_elements, new_state);
				}
			}
			
			updateBackground();
		};
		
		var checkToggleRows = function()
		{
			var jq_this = jQuery(this);
			var populated = jq_this.data("populated");
			// alert(populated);
			if (populated)
			{
				toggleRows.apply(this);
			}
			else
			{
				var ctrl = $ctrl(this);
				// populate and then call toggleRows
				var $max_depth = 1;
				var $class_names = jq_this.data("classeslist").split(",");
				var $depth = parseInt(jq_this.data("depth")) + 1;
				var $visible = true;
				var $toggles = [jq_this.data("toggle")]; // jq_this.attr("class").split(/\s+/);
				var $level_classes = null;
				var $toggle_index = jq_this.closest("table").find("tr").length + 1;

				ctrl.call("getPrintData", [$max_depth, $class_names, $depth, $visible, $toggles, $level_classes, $toggle_index], function (resp)
				{
					jq_this.data("populated", true);
					var jq_resp = jQuery(resp);
					jq_resp.find("th h5.clickable").click(checkToggleRows);
					jq_this.closest("tr").after(jq_resp);
					updateBackground();
					// toggleRows.apply(this);
				});
			}
		};
		
		/*$ctrl(jQuery(".QDevModeModelCtrl .searchable_heading .QDevModeSelectCtrl")).jQuery().on("listUpdated", function()
		{
			alert("updated");
		});*/
		
		// ref_this.jQuery().trigger("listUpdated");
		jQuery(".QDevModeModelCtrl table tbody tr th h5.clickable").click(checkToggleRows);
	}
});



