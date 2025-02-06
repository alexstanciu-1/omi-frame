/*
var an_obj = { 100: 'a', 2: 'b', 7: 'c' };
console.log(Object.keys(an_obj)); // console: ['2', '7', '100']
for (var k in an_obj)
	console.log(k);
*/

QExtendClass("Omi\\View\\DropDown", "omi", {

	resultsInitiated: false,

	noItemCaption: "Select",

	withPickerDD : false,
	
	initialData : null,

	currentPickedItem : null,
	
	currentInpVal : null,

	init : function (dom)
	{
		var $super = this._super(dom);
		var dd_jq = jQuery(dom);
		this.withPickerDD = dd_jq.hasClass('qc-with-picker-dd');
		this.initialData = this.getCurrentInfo();

		this.currentPickedItem = {"id" : this.getSelectedId(), "type" : this.getSelectedType(), "caption" : this.getSelectedCaption(), "full_data" : this.getSelectedDataFull()};

		if (this.withPickerDD)
			this.currentInpVal = this.$(".qc-dd-pick").val().trim();
		
		if (dd_jq.attr("q-valid"))
		{
			this.$(".qc-dd-input-id").attr("q-valid", dd_jq.attr("q-valid"));
			this.$(".qc-dd-input-ty").attr("q-valid", dd_jq.attr("q-valid"));
		}
		
		if (dd_jq.attr("q-fix"))
		{
			this.$(".qc-dd-input-id").attr("q-fix", dd_jq.attr("q-fix"));
			this.$(".qc-dd-input-ty").attr("q-fix", dd_jq.attr("q-fix"));
		}
		
		this.update_DD_Actions_Status();
		
		if (this.$(".qc-dd-pick").length !== 0)
		{
			this.$(".qc-dd-pick").each(function( i ) {
				if (this.closest('.prop-wrapper-HeadOffice') && this.closest('.prop-wrapper-HeadOffice').length !== 0)
				{
					if(this.clientWidth < this.scrollWidth){
					var $elem = jQuery(this);
					
					var $content = $elem.html();
					
					var $scale = this.clientWidth/this.scrollWidth;
					var $content_length = Math.floor($content.length * $scale) - 2;
					
					if($content_length > 0)
						$elem.html($elem.html().substring(0, $content_length));
					
					//console.log($content_length);

					}
				}
			  });
		}

		return $super;
	},
	
	update_DD_Actions_Status: function()
	{
		var $dd_actions = jQuery(this.dom).closest('.qc-ref-ctrl').find('.qc-dropdown-actions');
		if ($dd_actions.length > 0)
		{
			// var $sel_id = 
			if (this.hasItmSelected())
			{
				$dd_actions.find('.qc-ref-edit-wr').show();
				$dd_actions.find('.qc-ref-add-wr').hide();
			}
			else
			{
				$dd_actions.find('.qc-ref-edit-wr').hide();
				$dd_actions.find('.qc-ref-add-wr').show();
			}
		}
		
	},
	
	/**
	 * Selects an element in the drop-down by id
	 * 
	 * @param {type} id
	 * @return {undefined}
	 */
	selectWithId: function(id)
	{
		if (!id)
			return false;
		
		var ddbindsjq = this.$(".qc-dd-binds");
		var ddbinds = (ddbindsjq.length > 0) ? ddbindsjq.val() : null;
		var binds = (ddbinds && (ddbinds.length > 0)) ? JSON.parse(ddbinds) : null;
		if (!binds)
			binds = {};
		if (!binds["LIMIT"])
			binds["LIMIT"] = [0, 1];

		var from = this.getFrom();
		var selector = this.$(".qc-dd-selector").val();
		// if (search_text && (search_text.trim().length > 0))
		binds["Id"] = id;
		
		this.ajax("GetDataItems", [from, selector, binds], [this, function (res)
			{
				if (res && res[0])
				{
					this.setSelected(res[0].Name, res[0].Id, res[0]._ty, JSON.stringify(res[0]));
				}
			}]);
		// setSelected: function(caption, id, type, full_data, force)
		
	},
	
	doOnPickerClick : function (sender, sender_id, event)
	{
		var dd_box = this.$(".qc-dd-box");
		var is_visible = dd_box.is(":visible");
		
		var toggle = true;
		if ((!is_visible) && (!this.resultsInitiated))
		{
			// populate results
			toggle = this.updateResults(event, sender);
		}

		// if (toggle)
		dd_box.toggle();
	},

	onclick: function(sender, sender_id, event)
	{
		if (this.hasClass("qc-dd-pick", sender))
		{
			var dd_box = this.$(".qc-dd-box");
			var is_visible = dd_box.is(":visible");

			if (!this.withPickerDD)
			{
				this.doOnPickerClick(sender, sender_id, event);
			}
			
			if (!is_visible)
				this.$(".qc-dd-search input").focus();
		}
		else if (this.hasClass("qc-dd-item", sender))
		{
			var dd_box = this.$(".qc-dd-box");

			// we may need to stop selection on certain items
			this.stopSelect = false;

			this.trigger("beforeItemSelect", {"event" : event, "control" : this, "sender" : sender});

			// if stop select was marked - just return
			if (this.stopSelect)
				return false;

			dd_box.toggle();

			this.selectItem(sender);

			// reset search
			this.$(".qc-dd-search > input").val("");
			
			this.trigger("afterItemSelect", {"event" : event, "control" : this, "sender" : sender});
		}
	},
	
	onfocus: function(sender, sender_id, event)
	{
		if (this.hasClass("qc-dd-pick", sender) && this.withPickerDD)
		{
			var search_jq = this.$(".qc-dd-search input");
			search_jq.val(jQuery(sender).val());
			this.updateResults(event, search_jq[0]);
		}
	},

	oninput: function(sender, sender_id, event)
	{
		if (this.hasClass("qc-dd-pick", sender))
		{
			var search_jq = this.$(".qc-dd-search input");
			search_jq.val(jQuery(sender).val());
			this.updateResults(event, search_jq[0]);
			
		}
		else if (sender && sender.parentNode && this.hasClass("qc-dd-search", sender.parentNode))
		{
			this.updateResults(event, sender);
		}
	},

	selectItem: function (sender, force)
	{
		var sender_jq = jQuery(sender);
		if (!sender_jq.hasClass('qc-dd-reset-item'))
		{
			//alert(sender_jq.data("full"));
			this.setSelected(sender_jq.html(), sender.getAttribute("item.id"), sender.getAttribute("item.ty"), sender_jq.data("full"));
		}
		else
			this.unsetSelected(true);
	},

	setSelected: function(caption, id, type, full_data, force, merge_full_data)
	{
		// console.log('dd::setSelected!!!', force, full_data, merge_full_data, typeof(full_data));
		if ((caption === undefined) || (caption === null))
			caption = "";
			
		var prev_data = this.getCurrentInfo();

		if (!force && (id && prev_data && prev_data.id && (prev_data.id === id)))
			return;
		
		// qc-dd-insert-full-data
		
		if ((merge_full_data || this.$().closest('.qc-dd-wr').hasClass('qc-dd-insert-full-data')) && full_data)
		{
			// JSON.parse(full_data)
			//console.log(merge_full_data, full_data, prev_data, $old_data, $new_data); 
			try
			{
				var $new_data = JSON.parse(full_data);
				var $old_data = (prev_data && prev_data.full_data && prev_data.full_data.length) ? 
									JSON.parse(prev_data.full_data) : {};
				if (is_string($old_data))
					$old_data = JSON.parse($old_data);
				if (is_string($new_data))
					$new_data = JSON.parse($new_data);
				
				var $new_object = jQuery.extend({}, $old_data, $new_data);
				
				// console.log('$new_object', $new_object);
				if ((typeof($new_object) === 'object') && (!$old_data._id) && (!$old_data.Id))
				{
					if ($new_object._id)
						delete($new_object._id);
					if ($new_object.Id)
						delete($new_object.Id);
				}
				
				if ($new_object && $new_object._ty && ($new_object._ty === 'Omi\\Address'))
				{
					var $this = $new_object;
					var $data = [];

					if ($this.Building)
						$data.push($this.Building);
					if ($this.Street)
						$data.push($this.Street);
					if ($this.StreetNumber)
						$data.push($this.StreetNumber);
					if ($this.PostCode)
						$data.push($this.PostCode);
					if ($this.City && $this.City.Name)
						$data.push($this.City.Name);
					if ($this.County && $this.County.Name)
						$data.push($this.County.Name);
					if ($this.PostCode)
						$data.push($this.PostCode);
					
					caption = implode(", ", $data);
				}
				
				// here we have $new_object
				
				full_data = JSON.stringify($new_object);
			}
			catch ($excep) {
				console.error($excep);
			}
		}
		
		if (this.withPickerDD)
		{
			var dd_pick = this.$(".qc-dd-pick");
			dd_pick.attr("name-x", dd_pick.attr("name")).removeAttr("name");
			var picker_val = $.trim(caption);
			if  (picker_val !== dd_pick.val())
			{
				dd_pick.attr("name", dd_pick.attr("name-x")).removeAttr("name-x");
			}
			dd_pick.val(picker_val);
			this.currentInpVal = picker_val;
		}
		
		this.updateData($.trim(caption), id, type, full_data);
		
		// remote the reset flag if any
		var reset_flag = this.$(".qc-dd-reset");
		if (reset_flag.length > 0)
			reset_flag.remove();

		var id_field = this.$(".qc-dd-input-id");
		if (!id_field.attr("name"))
		{
			var ty_field = this.$(".qc-dd-input-ty");
			id_field.attr("name", id_field.attr("name-x")).removeAttr("name-x");
			ty_field.attr("name", ty_field.attr("name-x")).removeAttr("name-x");
		}
		
		this.update_DD_Actions_Status();
		
		jQuery(this.dom).trigger("select.dropdown", [{control: this, id: id, type: type, caption: caption, full_data : full_data, previous: prev_data}]);
		
		//qbDebug({"action" : "trigger selected!", "full_data" : full_data}, 1);
		this.trigger("selected", {control: this, id: id, type: type, caption: caption, full_data : full_data, previous: prev_data});
	},

	updateData : function (caption, id, type, full_data)
	{
		// console.log('updateData::full_data', full_data);
		this.currentPickedItem = {"id" : id, "type" : type, "caption" : caption, "full_data" : full_data};
		this.setSelectedId(id);
		this.setSelectedType(type);
		this.setSelectedCaption(caption);
		this.setFullData(full_data);
	},

	unsetSelected: function(force)
	{
		var prev_data = this.getCurrentInfo();

		// if it does not have id then it was not selected therefore there is not need to unset
		if (!force && (!prev_data || !prev_data.id))
			return;

		this.updateData(this.withPickerDD ? this.$(".qc-dd-pick").val().trim() : this.noItemCaption, "", "", "");

		var jq = jQuery(this.dom);
		var id_field = this.$(".qc-dd-input-id");
		id_field.attr("id", uniqid());
		
		if (!id_field.attr("name-x") || force)
		{
			// we need to add the reset flag only if initial data was set
			if (this.initialData && this.initialData.id && jq.closest(".qc-ref-ctrl").attr("q-path"))
				jq.prepend("<input type='hidden' class='qc-dd-reset' name='" + jq.closest(".qc-ref-ctrl").attr("q-path") + "' value='null' />");
			var ty_field = this.$(".qc-dd-input-ty");

			id_field.attr("name-x", id_field.attr("name")).removeAttr("name");
			ty_field.attr("name-x", ty_field.attr("name")).removeAttr("name");
			//alert("reset the name for " + id_field.attr("name-x"));
		}
		
		this.update_DD_Actions_Status();

		jQuery(this.dom).trigger("select.dropdown", [{control: this, id: null, type: null, 
						caption: null, full_data : null, previous: prev_data}]);

		this.trigger("selected", {control: this, id: null, type: null, full_data : null, caption: this.noItemCaption, previous: prev_data});
	},
	
	selectPicker : function ()
	{
		var dd_pick = this.$(".qc-dd-pick");
		if (dd_pick.attr("name-x"))
			dd_pick.attr("name", dd_pick.attr("name-x")).removeAttr("name-x");
	},

	onevent: function(event_type, sender, args)
	{
		if (this.withPickerDD)
		{
			if (event_type === "afterUpdateResults")
			{
				var has_itms = (this.$(".qc-dd-item").length > 0);
				has_itms ? this.$(".qc-dd-box").show() : this.$(".qc-dd-box").hide();
			}
			else if (event_type === "onHide")
			{
				var pick = this.$(".qc-dd-pick");
				var picked_val = pick.val().trim();

				if (!this.___unqid)
					this.___unqid = uniqid();

				var itm_selected = this.$(".qc-dd-input-id").attr("name") ? true : false;

				//qbDebug(itm_selected, 1);
				//qbDebug(this.currentPickedItem, 1);

				// if we have selected item and the caption matches exactly selected item caption return or 
				// we don't have selected and the caption is not changed - return
				if ((itm_selected && this.currentPickedItem && (picked_val === this.currentPickedItem.caption)) || (!itm_selected && (this.currentInpVal === picked_val)))
					return;

				/*
				qbDebug(
					{
						"current_inp_val_length" : this.currentPickedItem ? this.currentPickedItem.caption.length : "notset", 
						"current_inp_val" : this.currentPickedItem ? this.currentPickedItem.caption : "notset",
						"new_value_length" : picked_val.length, 				
						"new_value" : picked_val,  
						"current_inp_val_type" : this.currentPickedItem ? typeof(this.currentPickedItem.caption) : "notset", 				
						"new_value_type" : typeof(picked_val),  
						"result" : (this.currentInpVal === picked_val) ? "same value" : "different value"
				}, 1);
				*/

				//qbDebug("select picker", 1);
				/*
				qbDebug(
					{
						"current_inp_val_length" : this.currentInpVal.length, 
						"current_inp_val" : this.currentInpVal, 
						"new_value_length" : picked_val.length, 				
						"new_value" : picked_val,  
						"current_inp_val_type" : typeof(this.currentInpVal), 				
						"new_value_type" : typeof(picked_val),  
						"result" : (this.currentInpVal === picked_val) ? "same value" : "different value"
				}, 1);
				*/

				// we should get here in 2 cases
				//1. the item was selected and the inp value does not match item caption
				//2. the item was not selected but the caption was changed

				if (!itm_selected)
				{
					// if the item was not selected and only caption was changed - search for item that matches the caption and select it
					var itms = this.$(".qc-dd-item");
					var match_found = false;
					if (itms.length > 0)
					{
						for (var k = 0; k < itms.length; k++)
						{
							var itmJq = jQuery(itms[k]);
							//qbDebug({"itm_html" : itmJq.html().trim(), "value" : value, "result" : (itmJq.html().trim() === value)}, 3);
							if (itmJq.html().trim() === picked_val)
							{
								this.selectItem(itms[k]);
								match_found = true;
								break;
							}
						}
					}

					if (!match_found)
						this.selectPicker();
				}
				else
				{
					this.unsetSelected();
					this.selectPicker();
				}

				this.currentInpVal = picked_val;
				//qbDebug("setup current inp :" + this.currentInpVal, 1);
			}
		}
		
		//alert(event_type);
		
		if (event_type === "closeDropdown")
		{
			args.ddjq.find(".qc-dd-box").toggle();
		}

		if (this._super)
			this._super(event_type, sender, args);
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
		return this.withPickerDD ? this.$(".qc-dd-pick").val().trim() : this.$(".qc-dd-pick").html().trim();
	},

	getSelectedData : function ()
	{
		return {"_id" : this.getSelectedId(), "Id" : this.getSelectedId(), "_ty" : this.getSelectedType()};
	},

	getSelectedDataFull : function ()
	{
		var full_data = this.$(".qc-dd-full-data").val();
		try
		{
			var dp = full_data ? JSON.parse(full_data) : null;
		}
		catch ($ex)
		{
			//console.log("ISSUE ON JSON PARSE : ", this.$(".qc-dd-input-id").attr("name"), this.$(".qc-dd-input-id").attr("name-x"));
			console.log(full_data);
		}

		//qbDebug(dp, 10);
		//var dp = full_data ? JSON.parse(full_data) : null;
		
		if ($.trim(dp) && (typeof(dp) === "string"))
			dp = JSON.parse(dp);
		return dp;
	},

	hasItmSelected : function ()
	{
		return (this.currentPickedItem && this.currentPickedItem["id"]);
	},

	getSelectedType: function()
	{
		var val = this.$(".qc-dd-input-ty").val();
		if (val)
			val = val.trim();
		return (val && (val !== "")) ? val : null;
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
		caption = $.trim(caption);
		this.withPickerDD ? this.$(".qc-dd-pick").val(caption) : this.$(".qc-dd-pick").html(caption);
	},
	
	setFullData : function (full_data)
	{
		this.$(".qc-dd-full-data").val(full_data);
	},
	
	getBinds : function()
	{
		var bindsJq = this.$(".qc-dd-binds");
		var hasBinds = (bindsJq.length > 0);		
		var bindsVal = hasBinds ? bindsJq.val() : null;
		var existingBinds = (bindsVal && (bindsVal.length > 0)) ? JSON.parse(bindsVal) : null;
		
		return existingBinds;
	},

	setBinds : function(binds, unsetBinds)
	{
		if (!binds)
			return;

		var bindsJq = this.$(".qc-dd-binds");
		var hasBinds = (bindsJq.length > 0);		
		var bindsVal = hasBinds ? bindsJq.val() : null;
		var existingBinds = (bindsVal && (bindsVal.length > 0)) ? JSON.parse(bindsVal) : null;

		if (!existingBinds)
			existingBinds = {};

		for (var i in binds)
			existingBinds[i] = binds[i];

		if (unsetBinds)
		{
			for (var i in unsetBinds)
				delete existingBinds[i];
		}

		if (!hasBinds)
		{
			bindsJq = jQuery("<input type='hidden' class='qc-dd-binds' />");
			jQuery(this.dom).prepend(bindsJq);
		}
		bindsJq.val(JSON.stringify(existingBinds));
	},
	
	getFrom : function ()
	{
		return this.$(".qc-dd-from").val();
	},

	getCurrentInfo: function()
	{
		//qbDebug(this.$(".qc-dd-from").val(), 1);
		var inp_id = this.getSelectedId();
		var inp_ty = this.getSelectedType();
		var inp_caption = this.getSelectedCaption();
		
		var $currentInfo = {id: inp_id, type: inp_ty, caption: inp_caption, full_data : this.$(".qc-dd-full-data").val()};
		//qbDebug($currentInfo, 10);
		return $currentInfo;
	},

	updateResults: debounce(function(event, sender)
	{
		// this.stopResultsUpdate = false;
		this.trigger("beforeUpdateResults", {"event" : event, "control" : this, "sender" : sender});

		closeOpenedDropdowns(this.dom);
		
		// __submitted=1&Id=2&Deploy_Server%5BId%5D=2&Deploy_Server%5B_ty%5D=Omi%5CTF%5CProvision%5CServer&Id=2&Id=2&Id=2
		var $xg_form_data = null;
		var $xg_form = jQuery(sender).closest('.xg-form');
		if ($xg_form.length)
		{
			// activate name(s) for $xg_form.serialize() then remove them
			var $name_x_hiddens = $xg_form.find('input[type=hidden][name-x]');
			for (var $x = 0; $x < $name_x_hiddens.length; $x++)
				$name_x_hiddens[$x].setAttribute('name', $name_x_hiddens[$x].getAttribute('name-x'));

			$xg_form_data = $xg_form.serialize();
			
			for (var $x = 0; $x < $name_x_hiddens.length; $x++)
				$name_x_hiddens[$x].removeAttribute('name');
		}
		
		var ddbindsjq = this.$(".qc-dd-binds");
		var ddbinds = (ddbindsjq.length > 0) ? ddbindsjq.val() : null;
		var binds = (ddbinds && (ddbinds.length > 0)) ? JSON.parse(ddbinds) : null;

		if (!binds)
			binds = {};

		if (!binds["LIMIT"])
			binds["LIMIT"] = [0, 40];

		var from = this.getFrom();
		var selector = this.$(".qc-dd-selector").val();
		var search_text = this.$(".qc-dd-search > input").val();
		if (search_text && (search_text.trim().length > 0))
			binds["WHR_Search"] = "%" + search_text.trim() + "%";
		
		var call_on_method = "GetRenderItems";
		
		var dyn_inst = this.dom.getAttribute('q-dyn-inst');
		if (dyn_inst && (dyn_inst.trim().length > 0))
			call_on_method = "getRenderItems_Inst";
		
		if ($xg_form_data && $xg_form_data.length)
			binds._xg_form_data_ = $xg_form_data;

		this.ajax(call_on_method, [from, selector, binds], [this, this.updateResultsCallback]);
		return true;
	
	}, 500),
	
	updateResultsCallback: function(response)
	{
		this.dom.querySelector(".qc-dd-items").innerHTML = response;
		// this.dom.querySelector(".qc-dd-box").style.display = 'none';
		this.trigger("afterUpdateResults", {"control": this});
	}
});

function closeOpenedDropdowns(toSkipDropdown)
{
	var dropdowns = jQuery(".qc-dd");
	if (dropdowns.length === 0)
		return;

	if (toSkipDropdown instanceof jQuery)
		toSkipDropdown = toSkipDropdown[0];

	for (var i = 0; i < dropdowns.length; i++)
	{
		if (toSkipDropdown && (dropdowns[i] === toSkipDropdown))
			continue;

		var ddjq = jQuery(dropdowns[i]);
		if (ddjq.find(".qc-dd-items").is(":visible"))
		{
			var ddctrl = $ctrl(ddjq);
			ddctrl.trigger("closeDropdown", {"control": ddctrl, "ddjq" : ddjq});
			//ddjq.find(".qc-dd-box").toggle();
		}
	}
};

jQuery(document).ready(function () {
	jQuery(document.body).on("click", function () {
		jQuery(".qc-dd.focused").removeClass("focused");
	});
});

if (!window["uniqid"])
{
	function uniqid()
	{
		var newDate = new Date;
		var partOne = newDate.getTime();
		var partTwo = 1 + Math.floor((Math.random() * 32767));
		var partThree = 1 + Math.floor((Math.random() * 32767));
		var id = partOne + '-' + partTwo + '-' + partThree;
		return id;
	}
}
