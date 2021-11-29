
QExtendClass("QDevModeGeneratedCtrl", "QWebControl", 
{
	/**
	 * __ClassLoaded will be called after the CLASS is defined
	 */
	__ClassLoaded: function()
	{
		jQuery(".QDevModeSelectCtrl").on("changed", function (data, class_name)
		{
			var gen_ctrl = $ctrl(jQuery(this).closest(".QDevModeGeneratedCtrl"));
			gen_ctrl.onManagementClassChanged(class_name);
		});
		
		/*var presel_class = jQuery(".QDevModeSelectCtrl input.classesSearch").val();
		if (presel_class && presel_class.length)
			jQuery(".QDevModeSelectCtrl").trigger("changed", [presel_class]);*/
	},
	
	onManagementClassChanged: function(class_name)
	{
		var ref_this = this;
		this.call("renderPreconfigProperties", [class_name], function (resp)
		{
			ref_this.jQuery(".preconfig-render-properties").html(resp);
			var ns_pos = class_name.lastIndexOf("\\");
			if (ns_pos >= 0)
			{
				var namespace = class_name.substr(0, ns_pos);
				// alert(namespace);
				ref_this.jQuery("#dev-mode-gen-add-ns").val(namespace);
			}
			else
				ref_this.jQuery("#dev-mode-gen-add-ns").val("");
		});
	},
	
	createBatch: function(from, do_delete)
	{
		var ref_this = this;
		var data = qbGet(jQuery(from).closest('.batch-add-generators'));
		
		var folder = getUrlVariable("folder");
		
		this.call("createBatch", [data, folder, do_delete], function (resp)
		{
			//ref_this.jQuery().append(resp);
			//jQuery(from).closest('.batch-add-generators').toggle();
			window.location.reload(); 
			// then update the listing panel
		});
		// qbGet(jQuery(this).closest('.batch-add-generators'))
	},
	
	syncBatch: function(from, mode)
	{
		var data = qbGet(jQuery(from).closest('.batch-add-generators'));
		var folder = getUrlVariable("folder");
		this.call("syncBatch", [data, folder, mode], function (resp)
		{
			// ref_this.jQuery().append(resp);
			//jQuery(from).closest('.batch-add-generators').toggle();
			// window.location.reload();
			alert("Sync complete\n\n" + resp);
			// then update the listing panel
		});
	},
	
	generateCode: function(from, mode)
	{
		var data = qbGet(jQuery(from).closest('.batch-add-generators'));
		var folder = getUrlVariable("folder");
		this.call("generateCode", [data, folder, mode], function (resp)
		{
			// ref_this.jQuery().append(resp);
			//jQuery(from).closest('.batch-add-generators').toggle();
			// window.location.reload();
			alert("Generate complete\n\n" + resp);
			// then update the listing panel
		});
	},
	
	deleteBatch: function(from)
	{
		this.createBatch(from, true);
	}
});