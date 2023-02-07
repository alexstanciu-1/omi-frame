/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
QExtendClass("Omi\\View\\Birthdate", "QWebControl", {

	initialize: function ()
	{
		this.setup();
	},

	setup : function ()
	{
		var bodyJq = jQuery(document.body);

		jQuery(".select-2-no-search").select2({minimumResultsForSearch: Infinity});

		var $this = this;
		bodyJq.on("change", ".js-birthdate-day", function () {
			$this.setBirthDate();
		});

		bodyJq.on("change", ".js-birthdate-month", function () {
			$this.setBirthDate();
		});
		
		bodyJq.on("change", ".js-birthdate-year", function () {
			$this.setBirthDate();
		});
	},
	
	setBirthDate : function ()
	{
		var jq = jQuery(this.dom);
		var day = jq.find(".js-birthdate-day").val();
		var month = jq.find(".js-birthdate-month").val();
		var year = jq.find(".js-birthdate-year").val();
		jq.find(".js-birthdate").val((year ? year : "0000") + "-" + (month ? month : "00") + "-" + (day ? day : "00"));
	},

	isValid : function ()
	{
		var jq = jQuery(this.dom);
		return (jq.find(".js-birthdate-day").val() && jq.find(".js-birthdate-month").val() && jq.find(".js-birthdate-year").val());
	},

	getBirthdate : function ()
	{
		return jQuery(this.dom).find(".js-birthdate").val();
	}
});

jQuery(document).ready(function () {
	var ctrls = jQuery(".js-birthdate-ctrl");
	if (ctrls.length === 0)
		return;

	for (var i = 0; i < ctrls.length; i++)
	{
		var ctrl = $ctrl(ctrls[i]);
		ctrl.initialize();
	}
});