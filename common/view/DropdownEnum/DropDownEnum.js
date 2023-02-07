/*
var an_obj = { 100: 'a', 2: 'b', 7: 'c' };
console.log(Object.keys(an_obj)); // console: ['2', '7', '100']
for (var k in an_obj)
	console.log(k);
*/

QExtendClass("Omi\\View\\DropDownEnum", "omi", {

	isMulti : false,

	init : function (dom)
	{
		var $super = this._super(dom);
		this.isMulti = jQuery(this.dom).hasClass("qc-enum-dd-ctrl-multi");
		//alert(this.isMulti);
		this.setupTooltip();
		return $super;
	},

	onclick: function(sender, sender_id, event)
	{
		var sender_jq = jQuery(sender);

		// click on option
		if (sender_jq.hasClass("qc-enum-opt-chk"))
		{
			if (!this.isMulti)
				this.doOnSimpleCheck(sender_jq);
			else
			{
				this.doOnMultiCheck(sender_jq, sender_jq[0].checked);
				this.trigger("clickOnEnumDropdownItem", {"event" : event, "control" : this, "sender" : sender});
			}
			
		}
		else if (sender_jq.hasClass("qc-multi-chk"))
		{
			
			this.doOnMultiCheck(sender_jq, sender_jq[0].checked);
			this.trigger("clickOnEnumDropdownItem", {"event" : event, "control" : this, "sender" : sender});
		}

		// click on dropdown
		else if (sender_jq.hasClass("qc-enum-dd") || sender_jq.hasClass("qc-enum-dd-caption-wr") || sender_jq.hasClass("qc-enum-dd-caption"))
			this.toggleDropdown();
	},

	toggleDropdown : function()
	{
		this.getEnumDropdown().toggleClass('active');
	},

	getParentCtrl : function ()
	{
		var parentCtrl = jQuery(this.dom).parent().closest(".omi-control");
		return (parentCtrl.length > 0) ? $ctrl(parentCtrl) : null;
	},

	getEnumDropdown : function ()
	{
		return jQuery(this.dom).find(".qc-enum-dd");
	},

	setCaption : function (caption)
	{
		var ph_jq = this.getEnumDropdown().find(".qc-enum-dd-caption");
		ph_jq.text($.trim(caption));
		this.setupTooltip();
	},

	doOnSimpleCheck : function (sender_jq)
	{
		var dom_jq = jQuery(this.dom);
		var label_jq = dom_jq.find(".qc-enum-dd-opt[for='" + sender_jq.attr("id") + "']");
		
		var $keep_title = dom_jq.find(".dropdown").data('keep-title');
		if (($keep_title !== '1') && ($keep_title !== 1))
			this.setCaption(label_jq.text());
		this.toggleDropdown();
	},

	setupTooltip : function ()
	{
		var dom_jq = jQuery(this.dom);
		var caption_wr_jq = dom_jq.find(".qc-enum-dd-caption-wr");
		var caption_jq = caption_wr_jq.find(".qc-enum-dd-caption");
		//alert(caption_jq.height() + "|" + caption_wr_jq.height());
		
		// if the height of the caption is bigger then the height of the wrapper - setup the tooltip
		// else destroy the tooltip
		
		var enum_tooltip_jq = dom_jq.find(".qc-enum-dd-tooltip");
		if (caption_jq.height() > caption_wr_jq.height())
		{
			enum_tooltip_jq.addClass("qc-tooltip");
			var tooltip_jq = enum_tooltip_jq.find(".qc-tooltip-val");
			if (tooltip_jq.length === 0)
			{
				tooltip_jq = jQuery("<span class='qc-tooltip-val tooltip'></span>");
				enum_tooltip_jq.append(tooltip_jq);
			}
			tooltip_jq.text($.trim(caption_jq.text()));
		}
		else
		{
			enum_tooltip_jq.removeClass("qc-tooltip");
			enum_tooltip_jq.find(".qc-tooltip-val").remove();
		}
	},

	doOnMultiCheck : function (sender_jq, check)
	{
		var row_jq = sender_jq.closest(".qc-multi-check-row");

		row_jq.find(".qc-multi-chk")[0].checked = check;
		row_jq.find(".qc-enum-opt-chk")[0].checked = check;
		
		// trigger change on multi chk
		if (sender_jq.hasClass("qc-multi-chk"))
		{
			var parent_ctrl = this.getParentCtrl();
			if (parent_ctrl && parent_ctrl.isGrid)
				parent_ctrl.checkFormFields(row_jq.find(".qc-enum-opt-chk")[0]);
		}

		/*
		var selected_list = this.getEnumDropdown().find(".qc-enum-opt-chk:checked");
		if (selected_list.length > 0)
		{
			var caption = "";
			for (var i = 0; i < selected_list.length; i++)
			{
				var sel_jq = jQuery(selected_list[i]);
				caption += ((caption.length > 0) ? ", " : "") + $.trim(sel_jq.closest(".qc-multi-check-row").find(".qc-enum-dd-opt").text());
			}
		}
		else
			caption = "Select";
		*/

		//this.setCaption(caption);
	},

	oninput: function(sender, sender_id, event)
	{
		
	}
});

jQuery(document).ready(function () {
	jQuery(document.body).on("click", function(e) {
		var current_enum = jQuery(e.target).closest(".qc-enum-dd");
		jQuery('.qc-enum-dd').not(current_enum).removeClass('active');
	});
});