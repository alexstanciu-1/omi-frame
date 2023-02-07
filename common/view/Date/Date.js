/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
QExtendClass("Omi\\View\\Date", "QWebControl", {

	initialize: function ()
	{
		this.buildPicker();
	},

	buildPicker : function (extraParams)
	{
		var jq = jQuery(this.dom);
		var params = jq.data("params");
		
		if (!params)
			params = {};
		
		if (extraParams)
		{
			for (var i in extraParams)
				params[i] = extraParams[i];
		}

		if (!params["dateFormat"])
			params["dateFormat"] = "d.m.Y";

		var dpJq = jq.find(".js-datepickr");
		
		dpJq.trigger("setupParams", [params]);

		var $this = this;
		params.onDateSelect = function (element, pickedDate) {
			var dpJq = jQuery(element);
			dpJq.trigger("onDateSelect", [element, pickedDate]);
	
			var dpValJq = jq.find(".js-datepickr-dbval");
			if (dpValJq.length === 0)
				return;
			dpValJq.val($this.getDateInDbFormat(pickedDate));
		};
		datepickr(dpJq[0], params);
	},

	getDateInDbFormat : function(pickedDate)
	{
		var date = new Date(pickedDate);
		var month = parseInt(date.getMonth()) + 1;
		var day = date.getDate();
		return date.getFullYear() + "-" + ((month < 10) ? "0" : "") + month + "-" + ((day < 10) ? "0" : "") + day;
	},

	getDbDate : function ()
	{
		return jQuery(this.dom).find(".js-datepickr-dbval").val();
	}
});

jQuery(document).ready(function () {
	var ctrls = jQuery(".js-datepickr-ctrl");
	if (ctrls.length === 0)
		return;

	for (var i = 0; i < ctrls.length; i++)
	{
		var ctrl = $ctrl(ctrls[i]);
		ctrl.initialize();
	}
});


