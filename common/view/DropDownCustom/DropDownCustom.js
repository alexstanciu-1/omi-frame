
QExtendClass("Omi\\View\\DropDownCustom", "omi", {

	resultsInitiated: false,

	noItemCaption: "Select",
	
	Distancer : 25,

	onclick: function(sender, sender_id, event)
	{
		// alert(jQuery(sender).attr("class"));
		if (this.hasClass("qc-custom-dd-pick", sender))
		{
			var dd_box = this.$(".qc-custom-dd-box");
			var is_visible = dd_box.is(":visible");
			dd_box.toggle();
			if (!is_visible)
				this.$(".qc-dd-search input").focus();
			if ((!is_visible) && (!this.resultsInitiated))
			{
				// populate results
				// this.updateResults();
			}
		}
		else if (this.hasClass("qc-custom-dd-item", sender))
		{
			var dd_box = this.$(".qc-custom-dd-box");
			dd_box.toggle();
			
			var prev_data = this.getCurrentInfo();
			
			var item_id = sender.getAttribute("item.id");
			var item_ty = sender.getAttribute("item.ty");
			var item_val = sender.getAttribute("item.value");

			var item_caption = jQuery(sender).html();
			this.setSelected(item_caption, item_id, item_ty, item_val);

			this.trigger("selected", {control: this, id: item_id, type: item_ty, caption: item_caption, previous: prev_data});
			jQuery(this.dom).trigger("afterItemSelect", [sender]);
			// reset search
			this.$(".qc-dd-search > input").val("");
		}
	},
	
	getSelectedId: function()
	{
		var val = this.$(".qc-dd-input-id").val();
		if (val)
			val = val.trim();
		return (val && (val !== "") && (val !== "0")) ? val : null;
	},

	getSelectedCaption: function()
	{
		return this.$(".qc-dd-pick").html().trim();
	},

	getSelectedType: function()
	{
		var val = this.$(".qc-dd-input-ty").val();
		if (val)
			val = val.trim();
		return (val && (val !== "")) ? val : null;
	},
	
	getSelectedValue : function ()
	{
		var val = this.$(".qc-dd-input-val").val();
		if (val)
			val = val.trim();
		return (val && (val !== "")) ? val : null;	
	},

	setSelected: function(caption, id, type, value)
	{
		var jq_id = this.$(".qc-dd-input-id");
		if (jq_id.length && (jq_id[0]._defaultValue === undefined))
			jq_id[0]._defaultValue = jq_id.val();
		jq_id.val(id);
		this.$(".qc-dd-input-ty").val(type);
		this.$(".qc-dd-input-val").val(value);
		this.$(".qc-custom-dd-pick").html(caption);
	},

	setSelectedId: function(id)
	{
		var jq_id = this.$(".qc-dd-input-id");
		if (jq_id.length && (jq_id[0]._defaultValue === undefined))
			jq_id[0]._defaultValue = jq_id.val();
		jq_id.val(id);
	},

	setSelectedType: function(type)
	{
		this.$(".qc-dd-input-ty").val(type);
	},

	setSelectedCaption: function(caption)
	{
		this.$(".qc-custom-dd-pick").html(caption);
	},

	setSelectedValue: function(value)
	{
		this.$(".qc-dd-input-val").val(value);
	},
	
	unsetSelected: function(trigger_event)
	{
		var prev_data = this.getCurrentInfo();

		var jq_id = this.$(".qc-dd-input-id");
		if (jq_id.length && (jq_id[0]._defaultValue === undefined))
			jq_id[0]._defaultValue = jq_id.val();
		jq_id.val("");
		this.$(".qc-dd-input-ty").val("");
		this.$(".qc-dd-input-val").val("");
		this.$(".qc-dd-pick").html(this.noItemCaption);

		if (trigger_event)
			this.trigger("selected", {control: this, id: null, type: null, caption: this.noItemCaption, previous: prev_data});
	},

	getCurrentInfo: function()
	{
		var inp_id = this.getSelectedId();
		var inp_ty = this.getSelectedType();
		var inp_val = this.getSelectedValue();
		//var inp_caption = this.getSelectedCaption();
		//return {id: inp_id, type: inp_ty, value:  caption: inp_caption};
	},

	oninput: function(sender, sender_id, event)
	{
		var sender_jq = jQuery(sender);
		if (sender_jq.closest(".qc-custom-dd-search").length > 0)
		{
			this.filterResults(sender_jq.val());
		}
	},

	filterResults : function (filter)
	{
		var itms = this.$(".qc-custom-dd-item");
		itms.hide();
		filter = filter.toLowerCase();
		for (var i = 0; i < itms.length; i++)
		{
			var itm_jq = jQuery(itms[i]);
			var to_search = itm_jq.text().toLowerCase();
			if (to_search.match(filter))
			{
				itm_jq.show();
				var dd_results = itm_jq.closest(".qc-custom-dd-items");
				for (var k = 0; k < dd_results.length; k++)
				{
					var dd_results_itm_jq = jQuery(dd_results[k]);
					dd_results_itm_jq.prev(".qc-custom-dd-item").show();
				}
			}
		}
	},

	updateResults: function()
	{
		/*
		jQuery(this.dom).trigger("beforeUpdateResults");
		var ddbindsjq = this.$(".qc-dd-binds");
		var ddbinds = (ddbindsjq.length > 0) ? ddbindsjq.val() : null;
		var binds = (ddbinds && (ddbinds.length > 0)) ? JSON.parse(ddbinds) : null;

		if (!binds)
			binds = {};

		if (!binds["LIMIT"])
			binds["LIMIT"] = [0, 40];

		var from = this.$(".qc-dd-from").val();
		var selector = this.$(".qc-dd-selector").val();
		var search_text = this.$(".qc-dd-search > input").val();
		if (search_text && (search_text.trim().length > 0))
			binds["WHR_Search"] = "%" + search_text.trim() + "%";
		this.ajax("GetRenderItems", [from, selector, binds], [this, this.updateResultsCallback]);
		*/
	},

	updateResultsCallback: function(response)
	{
		/*
		this.dom.querySelector(".qc-dd-items").innerHTML = response;
		jQuery(this.dom).trigger("afterUpdateResults");
		*/
	}
	
});

jQuery(document).ready(function () {
	// init here custom dropdowns
	jQuery(".qc-custom-dd").each(function () {
		$ctrl(this);
	});
});

