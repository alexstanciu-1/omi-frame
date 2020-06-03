
QExtendClass("QDevModeApiGenCtrl", "QWebControl", 
{
	/**
	 * __ClassLoaded will be called after the CLASS is defined
	 */
	__ClassLoaded: function()
	{
		
	},
	
	proposeMethods: function()
	{
		var select = this.jQuery().find(".methodSelection");
		var classname = this.jQuery().data("showclass");
		this.call("proposeMethods", [classname, select.val()], function(resp)
		{
			alert(resp);
		});
	}
	
});