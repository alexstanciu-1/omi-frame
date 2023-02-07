/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
QExtendClass("Omi\\View\\Select", "QWebControl", {

	initialize: function ()
	{
		this.setup();
	},

	setup : function ()
	{
		var jq = jQuery(this.dom);
		var url = jq.data("url") ? jq.data("url") : jQuery("base").attr("href");
		var from = jq.data("from");
		var selector = jq.data("selector");
		var bindParams = jq.data("params");
		var method = jq.data("method");
		var captionMethod = jq.data("caption-method");

		if (!from)
			return;

		if (!bindParams)
			bindParams = {"__s__" : true};

		var $this = this;

		jq.select2({
			ajax: {
				method: "POST",
				url: url,
				dataType: 'json',
				delay: 250,
				data: function (params) {
					if (params.term)
						bindParams["WHR_Search"] = params.term;
					jq.trigger("collectBinds", [bindParams]);
					return $this.getRequestParams([from, selector, bindParams, captionMethod], method);
				},
				processResults: function (data, page) {
					data = data ? data[0] : null;
					return data;
				},
				cache: true
			}
		});
	},

	getRequestParams: function(params, method)
	{
		var reqParams = {"__customExtract__" : true, "__qFastAjax__": true};
		var qb = {};
		if (params.length > 0)
		{
			for (var i = 0; i < params.length; i++)
				qb[i] = params[i];
		}

		if (!method)
			method = "Omi\\View\\Select.GetItems";

		qb["_q_"] = method;
		reqParams["_qb0"] = qb;
		//qbDebug(reqParams, 10);
		return reqParams;
	}
});

jQuery(document).ready(function () {
	var ctrls = jQuery(".js-select-ctrl");
	if (ctrls.length === 0)
		return;
	
	for (var i = 0; i < ctrls.length; i++)
	{
		var ctrl = $ctrl(ctrls[i]);
		ctrl.initialize();
	}
});