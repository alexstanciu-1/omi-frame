/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
QExtendClass("Omi\\View\\RegistrationNumber", "QWebControl", {

	initialize: function ()
	{
		this.setup();
	},

	setup : function ()
	{
		var bodyJq = jQuery(document.body);

		jQuery(".select-2-no-search").select2({minimumResultsForSearch: Infinity});
		jQuery(".select-2").select2();

		var $this = this;
		bodyJq.on("change", ".js-regno-sel", function () {
			$this.setRegistrationNumber();
		});

		bodyJq.on("keyup", ".js-regno-text", function () {
			$this.setRegistrationNumber();
		});
	},

	setRegistrationNumber : function ()
	{
		var regNo = "";
		var pos = 0;
		var jq = jQuery(this.dom);
		jq.find(".js-regno-part").each(function () {
			var registerPartJq = jQuery(this);
			regNo += ((pos > 0) ? "/" : "") + registerPartJq.val();
			pos++;
		});
		jq.find(".js-registration-number").val(regNo);
	},

	isValid : function ()
	{
		var valid = true;
		jQuery(this.dom).find(".js-regno-part").each(function () {
			if (!valid)
				return;

			if (!jQuery(this).val())
				valid = false;
		});
		return valid;	
	},

	getRegistrationNumber : function ()
	{
		return jQuery(this.dom).find(".js-registration-number").val();
	}
});

jQuery(document).ready(function () {
	var ctrls = jQuery(".js-registration-number-ctrl");
	if (ctrls.length === 0)
		return;

	for (var i = 0; i < ctrls.length; i++)
	{
		var ctrl = $ctrl(ctrls[i]);
		ctrl.initialize();
	}
});