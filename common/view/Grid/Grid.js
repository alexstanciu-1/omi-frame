
QExtendClass("Omi\\View\\Grid", "omi", {
	
	isGrid : true,
	callbackOnSuccess : null,
	callbackOnError : null,
	_images_uploader_index_ : null,
	last_checked_element : null,

	init : function (dom)
	{		
		var $super = this._super(dom);
		// do stuff here on init
		this.setupDefaults();
		this.initValues();
		this.setupDatepickers();
		this.initCheckboxCollection();

		this.initWysiwyg();
		
		this.changeTab();
		
		var $scroll_on = document.querySelector('.js-omi-add-scroll-event');
		
		if ($scroll_on) {
			const $debounced_func = debounce(this.scroll, 500, null, this);
			// document.addEventListener('wheel', $debounced_func);
			// $scroll_on.addEventListener('wheel', $debounced_func);
			$scroll_on.addEventListener('scroll', $debounced_func);
		}

		return $super;
	},
	
	changeTab : function ()
	{
		var $hash = location.hash.substring(2);
		var $vars = this.parseQueryString($hash);
		var $tab;
		if (($tab = $vars['tab']))
		{
			var $label = jQuery(this.dom).find('label[qc-tab-property="' + $tab + '"]');
			
			if ($label.length)
				$label[0].click();
		}
		else
		{
			var $vars = this.parseQueryString(location.search);
			var $selected_tab = $vars ? ($vars['?_s_tab_'] ? $vars['?_s_tab_'] : ($vars['_s_tab_'] ? $vars['_s_tab_'] : null) ) : null;
			
			if ($selected_tab)
			{
				var $label = this.$('.jx-tab[data-controls="' + $selected_tab + '"]');
				if ($label.length > 0)
				{
					// $label.addClass('active');
					setTimeout(function () {
						$label.click();
					}, 100);
				}
			}
		}
	},
	
	parseQueryString: function (str)
	{
		var $vars = str.split("&");
		var $ret = {};
		
		for (var $i = 0; $i < $vars.length; $i++)
		{
			var $pair = $vars[$i].split("=");
			if ($pair[1] !== undefined)
				$ret[$pair[0]] = decodeURIComponent($pair[1]);
			else
				$ret[$pair[0]] = "";
		}
		
		return $ret;
	},

	initWysiwyg : function ()
	{
		var trumbowyg_txt_area = this.$('.qc-trumbowyg');
		var wysiwyg = this.$(".qc-wysiwyg");
		if (wysiwyg.bwTextarea)
			wysiwyg.bwTextarea();
		
		var trumbowyg = trumbowyg_txt_area.trumbowyg({removeformatPasted: true});

		for (var i = 0; i < wysiwyg.length; i++)
		{
			var wysiwyg_itm = jQuery(wysiwyg[i]);
			var $this = this;
			wysiwyg_itm[0]._wysiwyg_onchange = function (event, wysiwyg, textarea)
			{
				jQuery(textarea).val(jQuery(wysiwyg).find("body").html());
				$this.onchange(textarea);
			};
		}
				
		for (var i = 0; i < trumbowyg.length; i++)
		{
			var trumbowyg_itm = jQuery(trumbowyg[i]);
			var $this = this;
			var $iii = i; // we need this line, otherwise i will be the last increment always !!!
			
			trumbowyg_itm.on('tbwchange', function()
			{
				var textarea = trumbowyg_txt_area[$iii];
				$this.onchange(textarea);
			});
		}
	},

	initCheckboxCollection : function (context)
	{
		var chk_colls = this.$(".qc-checkboxes-coll .qc-chk-cell", context);
		for (var i = 0; i < chk_colls.length; i++)
		{
			var chk_coll_jq = jQuery(chk_colls[i]);
			var chk = chk_coll_jq.find(".qc-checkbox input");
			if (!chk[0].checked)
			{
				var hidden_id_jq = chk_coll_jq.find(".qc-hidden-id");
				if (hidden_id_jq.length > 0)
					hidden_id_jq.attr("name-x", hidden_id_jq.attr("name")).removeAttr("name");
				var hidden_type_jq = chk_coll_jq.find(".qc-hidden-ty");
				if (hidden_type_jq.length > 0)
					hidden_type_jq.attr("name-x", hidden_type_jq.attr("name")).removeAttr("name");
			}
		}
	},

	setupDatepickers : function (datepickers)
	{
		var $this = this;
		datepickers = datepickers ? datepickers : this.$(".datepickr");
		for (var i = 0; i < datepickers.length; i++)
		{
			var datepickr_jq = jQuery(datepickers[i]);
			$this.bindDatepickr(datepickr_jq);
		}
		
		var flatpickers = this.$(".flatpickr");
		if (flatpickers)
		{
			for (var i = 0; i < flatpickers.length; i++)
			{
				var flatpicker_jq = jQuery(flatpickers[i]);
				
				var $date_format = flatpicker_jq.data('format');
				
				var $date = flatpickers[i].getAttribute('value');
				
				var $flatpickr_options = {
					// dateFormat: 'd-m-Y'
					allowInput: true,
					static: true,               // @INFO: position the element inside a wrapper .flatpickr-wrapper and next to input
					// enableTime: true,
					// appendTo: jQuery('.qc-popup .js-popup-container .qc-xg-item')[0],
					// altInput: true, 
					// altFormat: 'd-m-Y',
					// onReady: function (selectedDates, dateStr, instance) {
					//     instance.calendarContainer.classList.add("qSkipHideOnClickAway");
					// }
				}
				
				if ($date_format)
					$flatpickr_options.dateFormat = $date_format;
				
				if ($date)
					$flatpickr_options.defaultDate = new Date($date);

				flatpicker_jq.flatpickr($flatpickr_options);
			}
		}
	},

	getReferenceFull : function (property, context)
	{
		var cust_ddjq = this.$(".qc-xg-property[xg-property^='" + property + "'] .qc-dd", context);

		if (cust_ddjq.length > 0)
		{
			var cust_ctrl = $ctrl(cust_ddjq);
			return cust_ctrl ? cust_ctrl.getSelectedDataFull() : null;
		}
		
		var cust_dd_rep = this.$(".qc-xg-property[xg-property^='" + property + "'] .qc-dd-rep", context);
		if (cust_dd_rep.length > 0)
		{
			var full_data = cust_dd_rep.find(".qc-dd-full-data").val();
			var dp = full_data ? JSON.parse(full_data) : null;
			//if ($.trim(dp) && (typeof(dp) === "string"))
			//	dp = JSON.parse(dp);
			return dp;
		}

		return null;
	},

	bindDatepickr : function (datepickr_jq, extra_binds)
	{
		var $dp_binds = this.getDatepickrBinds(datepickr_jq, extra_binds);
		
		var $this = this;
		if (datepickr_jq[0])
		{
			var $datepickr_jq_0 = datepickr_jq[0];
			datepickr_jq[0].addEventListener(
				'change',
				function() {
				
					var $ts = 0;
					try
					{
						var $ts = Date.parse(this.value) / 1000;
					}
					catch ($ex)
					{
						
					}
					
					if ($ts > 0)
						$this.trigger("onDateSelect", {"element" : $datepickr_jq_0, "timestamp" : $ts});
					
					var form_element_jq = jQuery($datepickr_jq_0).parent().siblings(".qc-form-element");
					
					if (form_element_jq.length > 0)
					{	
						form_element_jq.val(this.value);
						$this.onchange(form_element_jq[0]);
					}
				},
				false
			 );
		}
		// console.log('this.getDatepickrBinds(datepickr_jq, extra_binds)', $dp_binds);
		datepickr(datepickr_jq[0], $dp_binds);
	},

	getDatepickrBinds : function (datepickr_jq, extra_binds)
	{
		var binds = {};
		if (datepickr_jq.data("format"))
			binds["dateFormat"] = datepickr_jq.data("format");
		
		var $this = this;
		binds["onDateSelect"] = function (element, timestamp) {
		
			var picked_date = new Date(timestamp);
			var month = parseInt(picked_date.getMonth()) + 1;
			var day = picked_date.getDate();
			
			var element_jq = jQuery(element);
			
			var dbDate = picked_date.getFullYear() + "-" + ((month < 10) ? "0" : "") + month + "-" + ((day < 10) ? "0" : "") + day;
			$this.trigger("onDateSelect", {"element" : element, "timestamp" : timestamp});
			var form_element_jq = element_jq.parent().siblings(".qc-form-element");
			if (form_element_jq.length > 0)
			{	
				form_element_jq.val(dbDate);
				$this.onchange(form_element_jq[0]);
			}
			else
				$this.onchange(element);
		};

		if (extra_binds)
		{
			for (var k in extra_binds)
				binds[k] = extra_binds[k];
		}

		if (binds["minDate"])
		{
			var minDate = new Date(binds["minDate"]);
			if (!binds["selectedMonth"])
				binds["selectedMonth"] = minDate.getMonth();
			if (!binds["selectedYear"])
				binds["selectedYear"] = minDate.getFullYear();
		}

		return binds;
	},

	setupDefaults : function ()
	{
		if (!this.inAddMode())
			return;

		var form_elements = this.$(".qc-form-element");
		if (form_elements.length === 0)
			return;

		for (var i = 0; i < form_elements.length; i++)
		{
			var fel_jq = jQuery(form_elements[i]);
			var value = fel_jq.val();

			if (value)
			{
				if (fel_jq.attr("name-x"))
				{
					fel_jq.attr("name", fel_jq.attr("name-x")).removeAttr("name-x");
					fel_jq[0]._hasDefault = true;
				}
			}
		}
	},

	initValues : function ()
	{
		var form_elements = this.$(".qc-form-element");

		if (form_elements.length === 0)
			return;

		for (var i = 0; i < form_elements.length; i++)
		{
			var fel_jq = jQuery(form_elements[i]);
			if (!fel_jq[0]._hasDefault)
			{
				var value = fel_jq.val();
				//qc-not-bool
				if ((fel_jq[0].type === "checkbox") || (fel_jq[0].type === "radio"))
					value = fel_jq[0].checked;

				fel_jq[0]._intial_value = value;
			}
		}
	},

	getMode : function ()
	{
		var props = this.getProperties();
		return props ? props.grid_mode  : null;
	},

	inAddMode : function ()
	{
		return (this.getMode() === "add");
	},

	inEditMode : function ()
	{
		return (this.getMode() === "edit");
	},

	inViewMode : function ()
	{
		return (this.getMode() === "view");
	},
	
	inDeleteMode : function ()
	{
		return (this.getMode() === "delete");
	},

	inListMode : function ()
	{
		return (this.getMode() === "list");
	},

	inBulkMode : function ()
	{
		return (this.getMode() === "bulk");
	},

	getProperties : function ()
	{
		if (this._gridProps)
			return this._gridProps;
		var props = this.$('.qc-grid-properties').data("properties");
		this._gridProps = {};
		for (var i in props)
		{
			if (i === "_ty")
				continue;
			this._gridProps[i] = props[i];
		}
		return this._gridProps;
	},
	
	collect_object : function (expected_type, form, collectFiles)
	{
		var $serialized = this.collectData();
		if (!$serialized)
			return null;
		
		var $data = {};
		
		for (var $key in $serialized)
		{
			var $i_data = $serialized[$key];
			
			if (($i_data === undefined) || ($i_data === null) || ($i_data === ''))
				continue;
			
			var $chunks = $key.replaceAll(']', '').split('[');
			// alert($key + " : " +$chunks);
			if ($chunks && ($chunks.length > 1))
			{
				var $ref = $data;
				for (var $i = 0; $i < ($chunks.length - 1); $i++)
				{
					if ($ref[$chunks[$i]] === undefined)
						$ref[$chunks[$i]] = {};
					$ref = $ref[$chunks[$i]];
				}
				$ref[$chunks[$i]] = $i_data;
			}
			else
				$data[$key] = $i_data;
		}
		
		if (expected_type && $data && (!$data._ty))
			$data._ty = expected_type;
		return $data;
	},

	collectData : function (form, collectFiles)
	{
		if (form === undefined)
		{
			form = this.$('.xg-form');
			if (!form.length)
			{
				console.error('Missing form.');
				// alert('Missing form.');
			}
		}
		if (collectFiles === undefined)
			collectFiles = false;
		
		var $drop_downs_data = form.find('.qc-dd-wr.qc-dd-insert-full-data .qc-dd-input-id.qc-form-element[name]');
		
		for (var $i = 0; $i < $drop_downs_data.length; $i++)
		{
			var $dd_id_jq = jQuery($drop_downs_data[$i]);
			/*var $val = $dd_id_jq.val();
			if ($val)
				$val = $val.trim();
			if (($val === "") || ($val == 0))*/
			{
				var $cust_ddjq = $dd_id_jq.closest(".qc-dd");
				var $qc_dd_wr = $dd_id_jq.closest('.qc-dd-wr');
				var $q_path = $qc_dd_wr.attr('q-path');
				
				if ($q_path && ($q_path.length > 0) && $cust_ddjq.length > 0)
				{
					var $cust_ctrl = $ctrl($cust_ddjq);
					if ($cust_ctrl)
					{
						var $c_full_data = $cust_ctrl.getSelectedDataFull();
						if ($c_full_data)
						{
							var $inp_qc_dd_full_data = $cust_ddjq.find("input.qc-dd-full-data");
							$inp_qc_dd_full_data.attr("name", $q_path + "[_json_]");
						}
					}
				}
			}
		}
		
		var $data = {};
		var $fdata = form.serializeArray();
		
		if ($fdata && ($fdata.length > 0))
		{
			for (var k = 0; k < $fdata.length; k++)
			{
				var fd = $fdata[k];
				if (!fd.name || (fd.name.substr(0, 2) === "__") || (fd.name.indexOf("_rdrpl_") > -1))
					continue;
				$data[fd.name] = fd.value;
			}
		}

		//qbDebug({"WHEN" : "BEFORE", "data" : $data}, 10);

		var toSendCheckboxes = form.find(".qc-form-element.qc-changed[type='checkbox']");

		for (var j = 0; j < toSendCheckboxes.length; j++)
		{
			var ts_jq = jQuery(toSendCheckboxes[j]);
			if (!ts_jq[0].checked && ts_jq.attr("name"))
				$data[ts_jq.attr("name")] = 0;
		}

		if (!collectFiles)
			return $data;

		var files = form.find("input[type='file']");
		if (files.length > 0)
		{
			for (var i = 0; i < files.length; i++)
			{
				var file = files[i];
				var name = (file.attributes && file.attributes.name) ? file.attributes.name.value : null;
				if (!name || (!(file && file.files && file.files.length)))
					continue;

				$data[name] = {_ty: "QFile", _ftype: "file", _dom: file, _file: name};
				$data["__have_files__"] = true;
			}
		}
		return $data;
	},

	doOnException : function (jqXHR, textStatus, errorThrown, hide_exception)
	{
		var $ex = ((typeof(errorThrown) === "object") && errorThrown.__cust__) ? errorThrown : null;
		if (!$ex)
		{
			var text = (jqXHR && jqXHR.responseText) ? jqXHR.responseText : null;
			$ex = null;
			try
			{
				$ex = JSON.parse(text);	
				
				if ((typeof($ex) === "object") && $ex.EXCEPTION)
					$ex = $ex.EXCEPTION;
			}
			catch(err)
			{
			}
		}
		
		if (!hide_exception)
			this.onexception($ex, jqXHR, textStatus, errorThrown);
	},
	
	onexception : function($ex, jqXHR, textStatus, errorThrown)
	{
		alert(($ex && $ex.Message) ? $ex.Message : "System Error");
	},

	redirectToList : function ($item_id, $selected_tab_id)
	{
		var back_btn = this.$(".qc-back-btn");
		var list_btn = this.$(".qc-list-btn");
		var redirect_after_save = this.$('.qc-redirect-to').attr('href');
		
		var redirect_to = (back_btn.length > 0) ? back_btn.attr("href") : ((list_btn.length > 0) ? list_btn.attr("href") : null);
		
		if (!redirect_to)
			alert("Data was saved!");
		else if ($item_id)
			redirect_to += '/view/' + $item_id;
		
		if (redirect_after_save)
			redirect_to = redirect_after_save;

		window.location.href = (redirect_to ? redirect_to : window.location.href) + ($selected_tab_id ? "?_s_tab_="+$selected_tab_id : "");
	},
	
	get_current_tab_id : function()
	{
		return this.$('.qc-main-tabs-panel .qc-tab-itm>.jx-tab.active').data('controls');
	},

	doSubmit : function (form, sender, forceAjax, callbackOnSuccess, callbackOnError, hide_exception, callback_before_send, show_loader)
	{
		if (show_loader === undefined)
			show_loader = true;
		var grid_props = this.getProperties();
		var callAjax = (forceAjax || (grid_props && grid_props.processAction && (grid_props.processAction === 'ajax')));
		
		if ((!callbackOnSuccess) && this.callbackOnSuccess)
			callbackOnSuccess = this.callbackOnSuccess;
		if ((!callbackOnError) && this.callbackOnError)
			callbackOnError = this.callbackOnError;
		
		// trigger before grid submit on children
		var children = this.$(".omi-control");
		if (children.length > 0)
		{
			for (var _k = 0; _k < children.length; _k++)
			{
				var child_ctrl = $ctrl(children[_k]);
				child_ctrl.trigger("beforeGridSubmit", {"form" : form, "sender" : sender});
			}
		}
		
		if (!this.validateForm(form, sender))
		{
			console.log('form not valid!');
			// @TODO ... show this for NFON only !
			// alert('Bitte befüllen Sie alle Pflichtfelder bevor Sie versuchen zu Speichern.');
			return;
		}
		
		if (!callAjax)
		{
			form[0].submit();
			return;
		}

		if (children.length > 0)
		{
			for (var _k = 0; _k < children.length; _k++)
			{
				var child_ctrl = $ctrl(children[_k]);
				child_ctrl.trigger("beforeCollectSubmitData", {"form" : form, "sender" : sender});
			}
		}

		var $submitData = this.collectData(form, true);
		
		if (!this.checkSubmitData($submitData))
		{
			console.log('empty data');
			// alert('No data');
			return;
		}

		var $this = this;

		if (this._insubmitprocess)
		{
			if (show_loader) {
				alert("submit process already initiated! [1]");
			}
			return;
		}

		this._insubmitprocess = true;
		
		this.trigger("beforeSave", []);
		
		var $current_tab_id = this.get_current_tab_id();
		var $saved_current_item = $submitData && $submitData['Id'] ? $submitData['Id'] : null;
		
		var $saveOnTab = false;
		if (this.hasClass('qc-save-on-tab', sender))
		{
			$saveOnTab = true;
			var $redirectTo = sender.href;
		}
		
		var $onAdd = false;
		if (this.hasClass('qc-on-add', sender))
			$onAdd = true;
		
		if (show_loader)
			this.setupLoader();
		
		var $call_obj = {method: this._ty + "::FormSubmit", args: [$submitData, grid_props], cancel: false};
		if (callback_before_send) {
			callback_before_send($call_obj);
		}
		
		if (!$call_obj.cancel)
		{
			omi.api($call_obj.method, $call_obj.args, 
				// the callback
				function (resp) {
					$this._insubmitprocess = false;
					if (show_loader)
						$this.unsetLoader();
					$this.trigger("afterSave", {'resp' : resp, method: $call_obj.method, args: $call_obj.args});

					// resp[0] - the actual model
					// resp[1] - boolean - do redirect
					// resp[2] - the url for redirect
					// resp[3] - reload
					// resp[4] - do nothing

					if ($saveOnTab)
					{
						var $item_id = (resp && resp[0]) ? resp[0]['Id'] : null;
						if ($onAdd)
							window.location.href = $redirectTo + $item_id;
						else
							window.location.href = $redirectTo;
					}
					else if (!callbackOnSuccess)
					{
						if (resp[4])
						{
							// do nothing
						}
						else if (resp[3]) // $stay_on_page
						{
							window.location.reload();
						}
						else if (resp[1] && resp[2])
						{
							window.location.href = resp[2];
						}
						else
						{
							var $item_id = (resp && resp[0]) ? resp[0]['Id'] : null;
							if ((!$item_id) && $saved_current_item)
								$item_id = $saved_current_item;
							// console.log(resp);
							// return;
							if ($item_id)
								$this.redirectToList($item_id, $current_tab_id);
							else
								//alert("Data succesfully saved!");
								$this.redirectToList(undefined, $current_tab_id);
							//window.location.href = window.location.href;
						}
					}
					else
					{
						callbackOnSuccess(sender, resp);
					}
				},
				// callback when error
				function (jqXHR, textStatus, errorThrown) 
				{
					$this._insubmitprocess = false;
					if (show_loader)
						$this.unsetLoader();
					if (callbackOnError)
					{
						callbackOnError.apply(this, [sender, jqXHR, textStatus, errorThrown]);
					}
					$this.doOnException(jqXHR, textStatus, errorThrown, hide_exception);
				},
				null,
				null,
				null,
				$submitData["__have_files__"]
			);
		}
	},

	quickCall : function (method, params, onSuccessCallback, onSuccessCallbackParams, onErrorCallback, force, skipLoader, hide_exception)
	{
		var $this = this;
		if (this._in_quick_call && !force)
			return;
		this._in_quick_call = true;

		if (!skipLoader)
			this.setupLoader();
		omi.api(method, params, function (resp) {
			$this._in_quick_call = false;

			if (!skipLoader)
				$this.unsetLoader();

			if (onSuccessCallback)
				onSuccessCallback(resp, onSuccessCallbackParams);
		}, function (jqXHR, textStatus, errorThrown) {
			$this._in_quick_call = false;

			if (!skipLoader)
				$this.unsetLoader();

			$this.doOnException(jqXHR, textStatus, errorThrown, hide_exception);
			
			if (onErrorCallback)
				onErrorCallback(jqXHR, textStatus, errorThrown);
		});
	},

	// do quick quote
	doQuickSearch : function ()
	{
		if (this.$_schedFor)
		{
			// it's already sched
		}
		else
		{
			var $this = this;
			var $sched_func = function () {
				
				if ($this._inQSReq)
				{
					var $sched_in = $this.$_schedFor - Date.UTC();
					if ($sched_in < 50)
					{
						$sched_in = 50;
						$this.$_schedFor = Date.UTC() + 50;
					}
					setTimeout($sched_func, $sched_in);
				}
				else
				{
					$this.$_schedFor = false;
					// alert('__doQuickSearch');
					$this.__doQuickSearch();
				}
			};
			
			$this.$_schedFor = Date.UTC() + 300;
			setTimeout($sched_func, 300);
		}
	},

	__doQuickSearch : function ()
	{
		// alert('__doQuickSearch');
		
		if (this._inQSReq)
			return;

		// this.lastQSCalled = Date.UTC();
		this._inQSReq = true;

		var $this = this;

		// pull search data here
		var $searchData = this.collectQSData();
		
		this.quickCall(this._ty + "::GetQSSearchData", [this.getProperties(), $searchData], function (resp) {
			$this._inQSReq = false;
            
			if (resp && resp[0])
			{
				var table_jq = $this.$(".js-itms-table");
				table_jq.find(".js-itm").remove();
				table_jq.find(".js-no-results").remove();
				table_jq.find(".js-prepend-search-results").before(resp[0]);
				history.pushState({"url" : resp[1]}, null, resp[1]);

				var collectionMoreBtnJq = $this.$(".qc-collection-more");
				!resp[2] ? collectionMoreBtnJq.hide() : collectionMoreBtnJq.show();
			}
		}, function (jqXHR, textStatus, errorThrown) {
			$this._inQSReq = false;
		});
	},

	collectQSData : function ()
	{
		var searchFieldsWrJs = this.$(".js-search-fields-row");

		// search fields
		var searchFieldsJs = searchFieldsWrJs.find(".js-search-field, .js-oby-field");

		var $searchData = {};
		for (var i = 0; i < searchFieldsJs.length; i++)
		{
			var fieldJq = jQuery(searchFieldsJs[i]);
			
			var type = fieldJq[0].attributes.type ? fieldJq[0].attributes.type.value.toLowerCase() : null;

			if (!type)
				continue;

			// if is enum dd
			if (fieldJq.hasClass("js-enum-dd-ctrl"))
			{
				
				var elementsJqs = fieldJq.find('.qc-form-element');

				for (var k = 0; k < elementsJqs.length; k++)
				{
					var elementJq = jQuery(elementsJqs[k]);
					var fname = elementJq.attr("name");

					if (!fname)
						continue;

					var $res_collect = this.collectQSItmData(elementJq[0]);
					if (($res_collect !== undefined) && ($res_collect !== null) && ($res_collect !== ''))
					{
						$searchData[fname] = $res_collect;
					}
				}
			}
			else if (fieldJq.hasClass("js-dd"))
			{
				
			}
			else
			{

				var fname = fieldJq.attr("name");

				if (!fname)
					continue;

				if ((type === "radio") && !fieldJq[0].checked)
					continue;

				var $res_collect = this.collectQSItmData(fieldJq[0]);
				if (($res_collect !== undefined) && ($res_collect !== null) && ($res_collect !== ''))
				{
					$searchData[fname] = $res_collect;
				}
			}
		}

		return $searchData;
	},
	
	collectQSItmData : function (dom)
	{
		var tag = dom.tagName.toLowerCase();
		if (tag === "input")
			tag = "input/" + (dom.attributes.type ? dom.attributes.type.value.toLowerCase() : "text");

		switch (tag)
		{
			case "input/text":
			case "input/hidden":
			case "input/password":
			case "input/number":
			case "input/datetime":
			case "input/date":
			case "input/time":
			{
				var val = dom.value;
				if ((typeof val) === 'string')
					val = val.trim();
				break;
			}
			case "input/checkbox":
			{
				var val = dom.checked ? (((dom.value !== undefined) && (dom.value !== null) && (dom.value !== "")) ? dom.value : 1)
									// we add the feature of setting a value for unchecked
									: ((dom.attributes && dom.attributes.valueUnchecked) ? dom.attributes.valueUnchecked.value : null);
				return val;
			}
			case "input/radio":
			{
				// if it's not the one selected send undefined
				var val = (dom.checked && dom.value) ? dom.value : undefined;
				break;
			}
			case "select":
			{
				var val = (dom.selectedIndex >= 0) ? (dom.options[dom.selectedIndex].value || dom.options[dom.selectedIndex].text) : null;
				break;
			}
			case "textarea":
			{
				var val = dom.value;
				if ((typeof val) === 'string')
					val = val.trim();
				break;
			}
			default:
			{
				var val = dom.innerHTML;
				break;
			}
		}
		
		return val;
	},

	doSearch : function (search_btn_jq)
	{
		var formJq = search_btn_jq.closest("form");
		var $this = this;

		var $searchData = this.getSearchBinds(formJq);
		
		if (this._insearchprocess)
		{
			alert("search process already initiated!");
			return;
		}

		this._insearchprocess = true;
		
		// do a quick call on search
		this.quickCall(this._ty + "::GetSearchData", [this.getProperties(), $searchData], function (resp) {
			$this._insearchprocess = false;
			if (resp && resp[0])
			{
				jQuery(".qc-inner").replaceWith(jQuery(resp[0]));
				history.pushState({"url" : resp[1]}, null, resp[1]);
			}
		}, function (jqXHR, textStatus, errorThrown) {
			$this._insearchprocess = false;
		});
	},

	checkSubmitData : function ($submitData)
	{
		var hasData = false;
		if ($submitData)
		{
			for (var k in $submitData)
			{
				if ($submitData[k])
				{
					hasData = true;
					break;
				}
			}
		}

		if (!hasData)
		{
			//alert("You must fill in some data in form!");
			return false;
		}
		return true;
	},

	markUnusedPopupProps : function (context)
	{
		// remove popup data if no id's
		var popup_prop_ids = context.find(".qc-popup-prop-id");
		if (popup_prop_ids.length === 0)
			return;
		
		for (var k = 0; k < popup_prop_ids.length; k++)
		{
			var prop_id_jq = jQuery(popup_prop_ids[k]);
			if (!prop_id_jq.val())
			{
				prop_id_jq.attr("name-x", prop_id_jq.attr("name")).removeAttr("name");
				var ty_jq = prop_id_jq.closest(".qc-popup-prop-wr").find(".qc-popup-prop-ty");
				if (ty_jq.length > 0)
					ty_jq.attr("name-x", ty_jq.attr("name")).removeAttr("name");
			}
		}
	},

	validateForm : function (form, sender)
	{
		this.markUnusedPopupProps(form);

		// Need to clear other errors before showing new one.
		jQuery(".qc-validation-alert").addClass("hidden");

		// value=\"2\" => means delete
		var $hidden_inputs = form.find(".js-itm .js-rm-flag input[type=hidden][name][value=\"2\"]").closest('.js-itm').find(".q-mandatory");
		// check first mandatory data and after check the rest
		var mandatory_itms_list = form.find(".q-mandatory").not($hidden_inputs);
		
		var valid_data = true;
		
		if (mandatory_itms_list.length > 0)
		{
			for (var i = 0; i < mandatory_itms_list.length; i++)
			{
				var mandatory_jq = jQuery(mandatory_itms_list[i]);
				
				var $with_qc_dd_insert_full_data = (mandatory_jq.parent().hasClass('qc-dd') && mandatory_jq.closest('.qc-dd-wr').hasClass('qc-dd-insert-full-data'));
				
				if ($with_qc_dd_insert_full_data)
				{
					var $qc_dd_insert_full_data = mandatory_jq.parent().find('input.qc-dd-full-data');
					$qc_dd_insert_full_data.addClass('q-mandatory');
					
					if (!$qc_dd_insert_full_data.attr('q-valid'))
						$qc_dd_insert_full_data.attr('q-valid', mandatory_jq.attr('q-valid'));
					
					$qc_dd_insert_full_data.removeClass('q-mandatory');
				}
				
				//qbDebug({"name" : mandatory_jq.attr("name"), "name-x" : mandatory_jq.attr("name-x")}, 1);
				if ((!this.isValid(mandatory_jq)) && ((!$with_qc_dd_insert_full_data) || !this.isValid(mandatory_jq.parent().find('input.qc-dd-full-data'))))
				{
					console.log('not valid! A', mandatory_jq.attr("name"), mandatory_jq.attr("name-x")) // please do not remove this line, it is good for debug
					/*
					var str = "";
					for (var _k in mandatory_jq[0].attributes)
					{
						str += mandatory_jq[0].attributes[_k].name + " => " + mandatory_jq[0].attributes[_k].value + "\n";
					}
					alert(str);
					*/

					this.showErrorBlock(mandatory_jq, true);
					valid_data = false;
					break;
				}
			}
		}

		// then check the rest of the fields
		if (valid_data)
		{
			var to_check_itms = form.find(".qc-changed").not(".q-mandatory");
			if (to_check_itms && to_check_itms.length > 0)
			{
				for (var i = 0; i < to_check_itms.length; i++)
				{
					var to_check_itm_jq = jQuery(to_check_itms[i]);
					if (!this.isValid(to_check_itm_jq))
					{
						/*
						var str = "";
						for (var _k in to_check_itm_jq[0].attributes)
						{
							str += to_check_itm_jq[0].attributes[_k].name + " => " + to_check_itm_jq[0].attributes[_k].value + "\n";
						}
						alert(str);
						*/

						this.showErrorBlock(to_check_itm_jq, true);
						valid_data = false;
						break;
					}
				}
			}
		}

		//if (valid_data)
		//	form.find(".qc-radio input[type='radio']").removeAttr("name");
    
		return valid_data;
	},

	setupLoader : function ()
	{
		jQuery(document.body).append('<div class="qc-preloader">' +
			'<span class="loader"></span>' +
		'</div>');
	},

	unsetLoader : function ()
	{
		jQuery(".qc-preloader").remove();
	},

	doDropdownPopupSubmit : function (form, sender, dropdown, $page_refresh)
	{
		var sender_jq = jQuery(sender);
		var grid_props = this.getProperties();

		// trigger before grid submit on children
		var children = this.$(".omi-control");
		if (children.length > 0)
		{
			
			for (var _k = 0; _k < children.length; _k++)
			{
				var child_ctrl = $ctrl(children[_k]);
				child_ctrl.trigger("beforeGridSubmit", {"form" : form, "sender" : sender});
			}
		}

		if (!this.validateForm(form, sender))
			return;

		var $submitData = this.collectData(form, true);

		if (!this.checkSubmitData($submitData))
			return;

		var $this = this;

		if (this._insubmitprocess)
		{
			alert("submit process already initiated! [2]");
			return;
		}

		this._insubmitprocess = true;
		this.setupLoader();
		omi.api(this._ty + "::DropdownPopupFormSubmit", [$submitData, grid_props], 
			// the callback
			function (resp) {
				//qbDebug(resp, 10);
				$this._insubmitprocess = false;
				$this.unsetLoader();
				sender_jq.closest(".qc-popup").remove();
				
				// we need to make sure that data changes always
				if (dropdown)
				{
					var dd_ctrl = $ctrl(dropdown);
					var dd_inp_id = dd_ctrl.$(".qc-dd-input-id");
					if (dd_inp_id.length > 0)
						dd_inp_id[0]._intial_value = null;

					dd_ctrl.setSelected(resp[0], resp[1], resp[2], resp[3]);
				}
				
				if ($page_refresh)
					window.location.reload();
			},
			// callback when error
			function (jqXHR, textStatus, errorThrown) {
				$this._insubmitprocess = false;
				$this.unsetLoader();
				$this.doOnException(jqXHR, textStatus, errorThrown);
			},
			null,
			null,
			null,
			$submitData["__have_files__"]
		);
	},

	setupPopup : function (resp, params, $isSidebar, $widthClass = null)
	{
		var new_jq = '';
		
		if ($isSidebar)
		{
			var new_jq = jQuery(''+
				'<div class="qc-popup fixed z-30 inset-0 overflow-y-auto jx-modal qHideOnClickAway-remove qHideOnClickAway-parent">' +
					'<div class="flex items-end justify-center min-h-screen pt-4 px-4 text-right sm:block sm:p-0 jx-modal-content popup-wrapper">' +
						'<div class="fixed inset-0 transition-opacity">' +
							'<div class="absolute inset-0 bg-gray-500 opacity-75"></div>' +
						'</div>' +
						'<div class="inline-block align-top bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all w-1/2 min-h-screen sm:p-8 relative js-popup-container qHideOnClickAway" role="dialog" aria-modal="true" aria-labelledby="modal-headline"">' +
							resp +
							'<a href="javascript: void(0)" class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5 jx-close-modal">' + _L("Close") + '</a>' +
						'</div>' +
					'</div>' +
				'</div>');
		}
		else
		{
      if (true)
      {
  			var new_jq = jQuery('<div class="qc-popup fixed z-30 inset-0 overflow-y-auto jx-modal qHideOnClickAway-remove qHideOnClickAway-parent">' +
  				'<div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 jx-modal-content is-visible">' +
  					'<div class="fixed inset-0 transition-opacity">' +
  						'<div class="absolute inset-0 bg-gray-500 opacity-75"></div>' +
  					'</div>' +
  					'<!-- This element is to trick the browser into centering the modal contents. -->' +
  					'<span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;'+
  						'<div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle ' + $widthClass +  ' sm:w-full sm:p-8 relative js-popup-container qHideOnClickAway" role="dialog" aria-modal="true" aria-labelledby="modal-headline"">' +
  							resp +
  							'<a href="javascript: void(0)" class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5 jx-close-modal">' + _L("Close") + '</a>' +
  						'</div>' +
  					'</div>' +
  				'</div>');
         }
         else
         {
            var new_jq = jQuery('<div class="qc-popup qc-data-expand-popup jx-modal qHideOnClickAway-remove qHideOnClickAway-parent">' +
      			'<div class="popup-wrapper jx-modal-content">' +
      					'<div class="popup-container js-popup-container qHideOnClickAway">' +
      						resp +
      						'<a href="javascript: void(0)" class="close-trigger popup-close jx-close-modal">' + _L("Close") + '</a>' +
      					'</div>' +
      				'</div>' +
      			'</div>');
         }
		}
	
		var resp_jq = new_jq.find(".js-popup-container").children();
		var fchild = jQuery(resp_jq[0]);

		var $custom_popup_width = new_jq.find('.qc-inner[data-q-popup_width]').first();
		if ($custom_popup_width.length && $custom_popup_width.data('q-popup_width'))
		{
			new_jq.find('.js-popup-container').css("width", $custom_popup_width.data('q-popup_width'));
		}

		if (fchild.length > 0 && fchild.data("popup-cls"))
			new_jq.addClass(fchild.data("popup-cls"));

		new_jq[0]._masterCtrl = this;
		new_jq.find(".sidebar").remove();
		new_jq.find(".qc-card").removeClass("shadow-1");
		new_jq.find(".qc-top-actions").remove();
		var submit_btn = new_jq.find(".qc-submit-btn");
		submit_btn.removeClass('qc-submit-btn').addClass('qc-popup-submit-btn');
		jQuery(document.body).append(new_jq);
		new_jq.find('.popup-wrapper').addClass('is-visible');
		
		// trigger event
		this.trigger("onPopupSetup", {"resp" : resp, "popup" : new_jq, "params" : params});
		
		if (initCustomDropdowns)
			initCustomDropdowns(new_jq);
		
		inputsHasValues();
        
        if (initDatepicker)
            initDatepicker();
		
		return new_jq;
	},

	setupItmName : function ($elem, value, no_reset, force_reset)
	{
		var $elem_jq = jQuery($elem);
		//alert(value + " | " + no_reset + " | " + force_reset);

		// check if the value had changed - there is no initial value or the new value is different than the initial one
		//var value_had_changed = (!$elem._intial_value || ($elem._intial_value !== value));
		var value_had_changed = ($elem._intial_value != value);
		
		//alert(value_had_changed + " | " + $elem._intial_value + "::" + typeof($elem._intial_value) + " | " + value + "::" + typeof(value));

		// if the value had changed or no_reset - just setup the name
		// we may want to force the reset in some cases so we have the force reset flag ready
	
		// no_reset = false
		
		// if ((value_had_changed || no_reset) && (!force_reset))
		
		// if (no_reset)
		{
			if ((value_had_changed || no_reset) && (!force_reset))
			{
				//if (!$elem._intial_value)
				//	$elem._intial_value = value;

				var namex = $elem_jq.attr("name-x");
				if (namex)
					$elem_jq.attr("name", namex).removeAttr("name-x");
				$elem_jq.addClass("qc-changed");
			}
			else
			{
				var name = $elem_jq.attr("name");
				if (name)
				{
					// 
					$elem_jq.attr("name-x", name).removeAttr("name");
				}
				$elem_jq.removeClass("qc-changed");
			}
		}
		/*
		else
		{
			if (!force_reset)
			{
				//if (!$elem._intial_value)
				//	$elem._intial_value = value;

				var namex = $elem_jq.attr("name-x");
				if (namex)
					$elem_jq.attr("name", namex).removeAttr("name-x");
				$elem_jq.addClass("qc-changed");
			}
			else
			{
				var name = $elem_jq.attr("name");
				if (name)
				{
					// 
					$elem_jq.attr("name-x", name).removeAttr("name");
				}
				$elem_jq.removeClass("qc-changed");
			}
		}
		*/
	   
		//alert(value + " | " + (this.isValid($elem_jq) ? " valid" : " not valid"));
		// first dothe fix then do the validation
		this.isValid($elem_jq) ? this.showSuccessBlock($elem_jq) : this.showErrorBlock($elem_jq);
	},

	hideErrorBlock : function (sender_jq)
	{
		if (!sender_jq.attr("q-valid"))
			return;

		var form_grp_jq = sender_jq.closest(".js-form-grp");
		form_grp_jq.removeClass("error");
		form_grp_jq.find('.qc-validation-alert').hide();
	},

	showSuccessBlock : function (sender_jq)
	{
		if (!sender_jq.attr("q-valid"))
			return;

		this.hideErrorBlock(sender_jq);
		var form_grp_jq = sender_jq.closest(".js-form-grp");
		form_grp_jq.addClass("success").removeClass("error");
	},
	
	showErrorBlock: function (sender_jq, focus)
	{
		if (!sender_jq.attr("q-valid"))
			return;

		var value = sender_jq.val();
		
		var form_grp_jq = sender_jq.closest(".js-form-grp");
		form_grp_jq.addClass("error").removeClass("success");
		
		var is_date = sender_jq.hasClass("qc-date");

		var dd_jq = sender_jq.closest(".qc-dd");
		var in_dropdown = (dd_jq.length > 0);

		this.filterErrorBlocks(sender_jq, value, form_grp_jq, true);

		// we need to display the tab with the invalid data
		var tabContent = sender_jq.closest(".tab-content");

		if (tabContent.length > 0)
		{
			var tabLink = this.$(".jx-tab[data-controls='" + tabContent.attr('id') + "']");
			if (!tabLink.hasClass('active'))
			{
				tabLink.trigger("click");
				// this.$(".jx-tab").removeClass("active");
				// tabLink.addClass("active");
			}
		}

		jQuery('html, body').animate({
			scrollTop: sender_jq.offset().top - jQuery("#header").outerHeight() - jQuery(".sticky").outerHeight()
		}, 'fast');

		if (focus)
		{
			if (in_dropdown)
				dd_jq.addClass("focused");
			else if (is_date)
				sender_jq.siblings(".datepickr-wrapper").find(".datepickr")[0].focus();
			else
				sender_jq[0].focus();
		}
	},
	
	filterErrorBlocks : function (sender_jq, value, err_block_jq, $force_show)
	{
		var has_value = ($.trim(value).length > 0);
		var validation_alert_blocks = err_block_jq.find(".qc-validation-alert");
		
		for (var i = 0; i < validation_alert_blocks.length; i++)
		{
			var alert_block_jq = jQuery(validation_alert_blocks[i]);
			var alert_html = alert_block_jq.html();
			alert_html = alert_html.replace("$value", value);
			
			alert_block_jq.html(alert_html);
			if (alert_block_jq.data("tag") === "mandatory")
				(has_value && (!$force_show)) ? alert_block_jq.hide() : alert_block_jq.css("display", "block");
		}
	},

	
	selectTabById : function (id)
	{
		var tab = this.$("#" + id);
		
		if (tab.length === 0)
			return;
		
		// we are on tab
		var input = tab.prev();

		if (input.length === 0)
			return;

		var label_jq = this.$(".qc-tab-label[for='" + input.attr("id") + "']");
		if (!label_jq.hasClass('active'))
		{
			input.trigger("click");
			this.$(".qc-tab-label").removeClass("active");
			label_jq.addClass("active");
		}
	},

	isValid : function (sender_jq)
	{
		var $value = (sender_jq[0].type === "file") ? (sender_jq[0].value ? sender_jq[0].value : sender_jq.attr("value")) : sender_jq[0].value;
		
		this.fixValue(sender_jq, $value);
		
		var $ret = this.validate(sender_jq, $value);
		return $ret;
	},

	fixValue : function (sender_jq, $value)
	{
		if (!sender_jq.attr("q-fix"))
			return;

		if (!$value)
			$value = $value = sender_jq.val();

		var fixFunction = new Function('$value', "return " + sender_jq.attr("q-fix") + ";");
		var $retVal = fixFunction.apply(this, [$value]);
		if ($retVal !== $value)
		{
			sender_jq.val($retVal);
			$value = $retVal;
		}
	},

	validate : function (sender_jq, $value)
	{
		if (!sender_jq.attr("q-valid"))
		{
			//console.log('Validation OK - no rule [no q-valid]: ', sender_jq.attr('name') ? sender_jq.attr('name') : sender_jq.attr('name-x'), $value);
			return true;
		}
		
		if (!$value)
			$value = sender_jq.val();

		var validateFunction = new Function('$value', "return " + sender_jq.attr("q-valid") + ";");
		var $validation_result = validateFunction.apply(this, [$value]);
		// console.log($value, $validation_result);
		if (!$validation_result)
			console.log('Validation ' + ($validation_result ? "OK" : "NO") + ' - q-valid [' + sender_jq.attr("q-valid") + '] ', sender_jq.attr('name') ? sender_jq.attr('name') : sender_jq.attr('name-x'), $value);
		return $validation_result;
	},

	getImgs : function () 
	{
		return [
			"data:image/jpeg",
			"data:image/jpg",
			"data:image/png",
			"data:image/gif",
			"data:image/bmp"
		];
	},
	
	readUrl : function (input) 
	{
		var $thisJq = jQuery(input);

		if (!input.files)
			return;
		
		var $countFiles = input.files.length;

		if ($countFiles === 0)
			return;

		var $previewContainer = $thisJq.closest('.file-field').find('.preview-container');
		$previewContainer.empty();
		
		var imgs = this.getImgs();

		for (var i = 0; i < $countFiles; i++)
		{
			// new breader object
			var $reader = new FileReader();

			// add source
			$reader.onload = function (e) 
			{
				// the source of the file
				var src = e.target.result;

				var is_image = false;
				for (var k = 0; k < imgs.length; k++)
				{
					var img = imgs[k].toLowerCase();
					if (src.toLowerCase().indexOf(img) > -1)
					{
						is_image = true;
						break;
					}
				}

				if (!is_image)
					return;

				// create image element
				var $imagePreview = jQuery(document.createElement('img'));

				// add src
				$imagePreview.attr('src', src);

				// add classes
				$imagePreview.addClass('img-responsive').addClass('m-top-1');

				// append to preview container
				$previewContainer.append($imagePreview);
			};

			// nu stiu ce face asta dar e bine :)
			$reader.readAsDataURL(input.files[i]);
		}
	},

	onchange : function (sender, sender_id, event)
	{
		var sender_jq = jQuery(sender);
		this.checkFormFields(sender);

		if (sender_jq.hasClass("qc-enum-opt-chk"))
		{
			// trigger search separate for DropDownEnum.js with single value
			var enumCtrl = $ctrl(sender_jq.closest(".js-enum-dd-ctrl"));

			if (enumCtrl && !enumCtrl.isMulti && jQuery(enumCtrl.dom).hasClass("js-search-field"))
			{
				// keep in sync with advanced search
				this.syncWithAdvancedSearch(sender_jq);
				
				// do quick search
				this.doQuickSearch();
			}
		}
		else if (sender_jq.hasClass('js-srch-handle'))
		{
			var $hidden_name = sender_jq.data('srch-handle');
			var $srch_hidden = this.$('.js-srch-hidden[name="' + $hidden_name + '"]');
			// alert(sender_jq.val());
			$srch_hidden.val(sender_jq.val());
			// $srch_hidden.change();
			// keep in sync with advanced search
			this.syncWithAdvancedSearch($srch_hidden);
			// do quick search
			this.doQuickSearch();
		}
		else if (sender_jq.hasClass("js-search-field") && ((sender_jq.hasClass("datepickr") || 
				((sender_jq.prop('tagName') === 'INPUT') && ((sender_jq.attr('type') === 'hidden') || 
				(sender_jq.attr('type') === 'datetime-local') || (sender_jq.attr('type') === 'datetime') || 
				(sender_jq.attr('type') === 'time') || (sender_jq.attr('type') === 'date'))))))
		{
			// keep in sync with advanced search
			this.syncWithAdvancedSearch(sender_jq);

			// do quick search
			this.doQuickSearch();
		}
		// start image uploader
		else if (sender_jq.hasClass('q-multiple-images-uploader'))
		{
			var $add_button_jq = sender_jq.closest('.qc-list').find('.qc-collection-add');
			this._images_uploader_index_ = (sender.files.length > 0) ? 0 : null;
			
			var $files_too_big = [];
			var $files_too_big_str = "";
			
			for (var $i = 0; $i < sender.files.length; $i++)
			{
				if (window._q_maximum_upload_size_ && (window._q_maximum_upload_size_ > 0) && 
						(sender.files[$i].size > window._q_maximum_upload_size_))
				{
					// console.log(sender.files[$i].name);
					// alert(sender.files[$i].name + " / " + sender.files[$i].size + " / " + window._q_maximum_upload_size_);
					// alert("File too big: " + sender.files[$i].name);
					$files_too_big.push(sender.files[$i]);
					$files_too_big_str += sender.files[$i].name + "\n";
				}
				
				$add_button_jq[0].click();
			}
			
			if ($files_too_big.length > 0)
			{
				alert("These files are too big:\n\n" + $files_too_big_str);
			}
		}
		// end image uploader
		
		if (sender === undefined)
		{
			// console.trace();
			// console.log('onchange', sender);
		}
		
		if (sender_jq.closest('.qc-collection').find('.qc-collection-chk-toggle').is(':checked') && 
				sender_jq.hasClass('qc-form-element') && sender_jq.closest('.qc-xg-property').closest('.qc-coll-itm').find('.qc-collection-chk input[type="checkbox"]').is(':checked'))
		{
			this.replicate_checked_inputs(sender, event, sender_jq);
		}
	},

	oninput: function(sender, sender_id, event)
	{
		var sender_jq = jQuery(sender);

		if (sender_jq.hasClass("js-search-field"))
		{
			// keep in sync with advanced search
			this.syncWithAdvancedSearch(sender_jq);

			// do quick search
			this.doQuickSearch();
		}
		else if (sender_jq.hasClass('js-srch-handle'))
		{
			var $hidden_name = sender_jq.data('srch-handle');
			var $srch_hidden = this.$('.js-srch-hidden[name="' + $hidden_name + '"]');
			// alert(sender_jq.val());
			$srch_hidden.val(sender_jq.val());
			// $srch_hidden.change();
			// keep in sync with advanced search
			this.syncWithAdvancedSearch($srch_hidden);
			// do quick search
			this.doQuickSearch();
		}
	},
	
	syncWithAdvancedSearch : function (sender_jq, adv_search)
	{
		if (!adv_search)
			adv_search = this.$(".js-advanced-search");
		
		// if is enum dd
		if (sender_jq.hasClass("js-enum-dd-ctrl"))
		{

		}
		else if (sender_jq.hasClass("js-dd"))
		{

		}
		else
		{
			// console.log('syncWithAdvancedSearch', sender_jq.attr("sync-identifier"), sender_jq);
			adv_search.find(".js-keepin-sync[sync-identifier='" + sender_jq.attr("sync-identifier") + "']").val(sender_jq.val());
		}
	},

	checkFormFields : function (sender)
	{
		var sender_jq = jQuery(sender);

		var $elem = sender;
		var value = sender_jq.val();

		var in_radio_grp = (sender_jq.closest(".qc-radio").length > 0);
		var in_custom_dropdown = ((sender_jq.closest(".qc-radio-dropdown").length > 0) || (sender_jq.closest(".qc-enum-dd").length > 0));

		if (in_radio_grp || in_custom_dropdown)
		{
			var data_jq = this.$("#" + sender_jq.data("id"));
			data_jq.val(value);
			$elem = data_jq[0];
		}

		var $elem_jq = jQuery($elem);

		var force_reset = false;
		if ($elem_jq.hasClass("qc-form-element"))
		{		
			if ((sender.type === "checkbox"))
			{
				if (!sender_jq.hasClass("qc-not-bool"))
				{
					value = sender.checked;
					sender_jq.val(value ? 1 : 0);
				}

				if (sender_jq.hasClass("qc-keep-sync") && !sender_jq[0].checked)
					force_reset = true;
			}

			var no_reset = (
					((sender_jq.closest(".qc-advanced-search").length > 0) || 
					(sender_jq.closest(".js-search-fields-row").length > 0)) && !sender_jq.hasClass("qc-force-reset"));

			// setup itm name - control reset
			
			this.setupItmName($elem, value, no_reset, force_reset);
		}

		if (sender_jq.closest(".qc-file-field").length > 0)
		{
			var file_field_jq = sender_jq.closest(".qc-file-field");
			var path_input = file_field_jq.find('input.file-path');

			var $countImages = sender.files.length;
			var $filesNames = '';

			if ($countImages > 0)
			{
				for (var i = 0; i < $countImages; i++)
					$filesNames += sender.files[i].name + ' ';
			}

			// insert name of the file
			path_input.val($filesNames);
			path_input.trigger('change');

			// read url and show image preview
			this.readUrl(sender);
		}
	},

	onfocus : function (sender, sender_id, event)
	{
		var sender_jq = jQuery(sender);
		if (sender_jq.is(".qc-text-with-dd"))
		{
			sender_jq.closest(".qc-xg-property").find(".qc-dd .qc-dd-box").show();
		}
	},

	onclick: function(sender, sender_id, event)
	{
		// alert(arguments.callee.caller);
		// do here the actions for the right click
		if (event.which === 3)
		{	
			return;
		}

		var $this = this;
		var sender_jq = jQuery(sender);

		var $js_click = sender.classList.contains("js-click") ? sender : (sender.closest(".js-click") || null);
		// if the sender has the class js order by
		if ($js_click)
		{
			// determine action
			var $jq = jQuery($js_click);
			var $action = $jq.data('js-action');
			var $args = $jq.attr('data-js-action-args');
			
			$args = $args ? (Array.isArray($args) ? $args : JSON.parse($args)) : [];
			// extract meta values
			this.js_action_prepare_args($args);
			
			$args.unshift($js_click);
						
			if ((!$action) || (typeof($action) !== 'string'))
				console.error('missing action');
			else
			{
				$action = "js_" + $action.replaceAll('-', '_');
				console.log('about to take action: ' . $action);
				this[$action](...$args);
			}
		}
		else if (sender_jq.closest(".js-order-by").length > 0)
		{
			var $sendeJq = sender_jq.closest(".js-order-by");
			var $orderData = $sendeJq.data("order");
			var correspFieldJq = this.$(".js-oby-field[oby-indx='" + $orderData + "']");

			if ($sendeJq.hasClass("asc"))
			{
				$sendeJq.removeClass("asc");
				$sendeJq.addClass("desc");
				
				correspFieldJq.val('DESC');
			}
			else if ($sendeJq.hasClass("desc"))
			{
				$sendeJq.removeClass("desc");
				correspFieldJq.val('ASC');
			}
			else
			{
				$sendeJq.addClass("asc");
				correspFieldJq.val('ASC');
			}

			// sync with advanced search
			this.syncWithAdvancedSearch(correspFieldJq);

			// do quick search
			this.doQuickSearch();
		}
		else if (sender_jq.hasClass('qc-export-binds') || sender_jq.parent().hasClass('qc-export-binds'))
		{
			var $trigger = sender_jq.hasClass('qc-export-binds') ? sender_jq : sender_jq.parent();
			$trigger.attr("href", $trigger[0].href + "?" + window.location.search);
		}
		else if (sender_jq.hasClass('js-panel-reset') || sender_jq.parent().hasClass('js-panel-reset'))
		{
			var $trigger = sender_jq.hasClass('js-panel-reset') ? sender_jq : sender_jq.parent();
			// $trigger.attr("href", $trigger[0].href + "?" + window.location.search);
			window.location.href = $trigger.data('href');
		}
		else if (sender_jq.closest(".qc-provisioning-sync").length > 0)
		{
			var sync_jq = sender_jq.closest(".qc-provisioning-sync");
			var props = $this.getProperties();
			var c = confirm("Are you sure you want to perform this action?");
			if (c)
			{
				this.quickCall(this._ty + "::ProvisioningSync", [props, sync_jq.data("type"), sync_jq.data("record-id")], function (resp) {
					
				});
			}
			return;
		}
		else if (sender_jq.closest(".qc-chkcollitm-pick-bulk").length > 0)
		{
			var pickers = this.$(".qc-chkcollitm-pick");
			for (var i = 0; i < pickers.length; i++)
			{
				pickers[i].checked = sender.checked;	
			}
			return;
		}
		else if (sender_jq.closest(".qc-import").length > 0)
		{
			var props = $this.getProperties();
			this.quickCall(this._ty + "::GetImportForm", [props], function (resp) {
				var popup_jq = $this.setupPopup(resp);
				var $importCtrl = $ctrl(popup_jq.find(".QWebControl"));
				$importCtrl.MainGrid = $this;
			});
			return;
		}
		else if (this.hasClass('qc-submit-btn', sender))
		{
			event.preventDefault();
			
			this.doSubmit(jQuery(this.$('.xg-form')[0]), sender);
			return;
		}
		else if (this.hasClass('qc-edit-btn', sender))
		{
			var $current_tab_id = this.get_current_tab_id();
			// alert(sender.href);
			if (sender.href && $current_tab_id)
				sender.setAttribute('href', sender.getAttribute('href') + "?_s_tab_="+$current_tab_id);
			// event.preventDefault();
			// sender_jq.attr("href", sender.href + "?_s_tab_="+$current_tab_id);
			// window.location.href = sender.href + "?_s_tab_=" + $current_tab_id;
		}
		else if (sender_jq.closest(".qc-search-btn").length > 0)
		{
			this.doSearch(sender_jq);
			return;
		}
		else if (sender_jq.closest(".qc-popup-prop-setup").length > 0)
		{
			this.setupPopupForProp(sender_jq);
			
		}
		else if (this.hasClass('qc-popup-submit-btn', sender))
		{
			var popup_jq = sender_jq.closest(".qc-popup");
			var master_ctrl = (popup_jq[0] && popup_jq[0]._masterCtrl) ? popup_jq[0]._masterCtrl : null;
			if (master_ctrl)
				master_ctrl.trigger("beforePopupSave", {"sender" : sender});
			
			this.doSubmit(jQuery(this.$('.xg-form')[0]), sender, true, function (sender, resp) {
				
				if (master_ctrl)
					master_ctrl.trigger("afterPopupSave", {"sender" : sender_jq, "resp" : resp});
				
				sender_jq.closest(".qc-popup").remove();
			});

			return;
		}
		else if (this.hasClass("qc-collection-chk-inp", sender))
		{
			//						$event, $sender, $element_selector, $jq_container, $row_selector
			this.handle_shift_click(event, sender, ".qc-collection-chk-inp", sender_jq.closest('.coll-table'), ".js-itm");
		}
		else if (this.hasClass("qc-chkcollitm-pick", sender))
		{
			this.handle_shift_click(event, sender, ".qc-chkcollitm-pick", sender_jq.closest('.js-itms-table'), ".js-itm");
			
			var chk_cell_jq = sender_jq.closest(".qc-chk-cell");
			var rowi_jq = chk_cell_jq.find(".qc-rowi");

			var itm_in_collection = (rowi_jq.length > 0);
			var hidden_id_jq = chk_cell_jq.find(".qc-hidden-id");
			var hidden_ty_jq = chk_cell_jq.find(".qc-hidden-ty");

			if (sender.checked)
			{
				if (!itm_in_collection)
				{
					hidden_id_jq.attr("name", hidden_id_jq.attr("name-x")).removeAttr("name-x");
					hidden_ty_jq.attr("name", hidden_ty_jq.attr("name-x")).removeAttr("name-x");
				}
				else
					chk_cell_jq.find(".js-rm-flag").empty();
			}
			else
			{
				if (!itm_in_collection)
				{
					hidden_id_jq.attr("name-x", hidden_id_jq.attr("name")).removeAttr("name");
					hidden_ty_jq.attr("name-x", hidden_ty_jq.attr("name")).removeAttr("name");
				}
				else
					chk_cell_jq.find(".js-rm-flag").append(jQuery("<input type=\"hidden\" value=\"" + window.TransformDelete + "\" name=" + sender_jq.data("vars-path") + " />"));
			}
			return;
		}
		else if (this.hasClass("qc-ref-ctrl-list-full", sender) || this.hasClass("qc-ref-ctrl-list-full", sender_jq.parent()[0]))
		{
			var $elem = sender_jq.hasClass('qc-ref-ctrl-list-full') ? sender_jq : sender_jq.parent();

			if ($elem[0]._in_process)
				return;
			
			this.stopPopupAdd = false;
			
			this.trigger("beforePopupAdd", {"control" : this, "sender" : $elem[0]});

			if (this.stopPopupAdd)
				return;

			var _ctrl = $elem.data("ctrl");
			if (_ctrl)
			{
				$elem[0]._in_process = true;
				omi.api(_ctrl + "::RenderPopupForm", ["list"], function (resp) {
					$elem[0]._in_process = false;
					var new_jq = $this.setupPopup(resp);
					new_jq.find(".qc-popup-submit-btn").removeClass("qc-popup-submit-btn").addClass("qc-dropdown-popup-submit-btn").data("dropdown-id", dd_id);
					$this.trigger("afterPopupAdd", {"control" : this, "sender" : $elem[0], "popup" : new_jq});
				});
			}
			return;
		}
		else if (this.hasClass("qc-ref-ctrl-new-full", sender) || this.hasClass("qc-ref-ctrl-new-full", sender_jq.parent()[0]))
		{
			var $elem = sender_jq.hasClass('qc-ref-ctrl-new-full') ? sender_jq : sender_jq.parent();
			
			var $isSidebar = false;
			if ($elem.hasClass('is-sidebar'))
				$isSidebar = true;
			
			var $widthClass = null;
			if ($elem.data('width-class'))
				$widthClass = $elem.data('width-class');
			
			var $saveOnDropdown = true;
			if ($elem.hasClass('js-save-no-dropdown'))
				$saveOnDropdown = false;
			
			var $pageRefresh = false;
			if ($elem.hasClass('jsPageRefresh'))
				$pageRefresh = true;

			if ($elem[0]._in_process)
				return;
			
			this.stopPopupAdd = false;
			
			this.trigger("beforePopupAdd", {"control" : this, "sender" : $elem[0]});

			if (this.stopPopupAdd)
				return;

			var _ctrl	= $elem.data("ctrl");
			var _args	= $elem.data("args");
			var _params	= $elem.data("params");
						
			var params = {};
			
			if (_ctrl)
			{
				$elem[0]._in_process = true;
				omi.api(_ctrl + "::RenderPopupForm", ["add", _args, _params], function (resp) 
				{
					$elem[0]._in_process = false;
					var dd_jq = $elem.closest(".qc-ref-ctrl").find(".qc-dd");
					if (dd_jq && dd_jq.length)
					{
						var dd_id = dd_jq.attr("id");
						
						if (!dd_id)
						{
							dd_id = uniqid();
							dd_jq.attr("id", dd_id);
						}
						
						if (!dd_id)
							console.error('Unable to find or setup a id for the dropdown.');
					}
					
					// setup popup | build popup
					var new_jq = $this.setupPopup(resp, null, $isSidebar, $widthClass);
					
					var $save_btn = new_jq.find(".qc-popup-submit-btn");
					$save_btn.removeClass("qc-popup-submit-btn");
					
					$save_btn.addClass("qc-dropdown-popup-submit-btn");
					if (!$saveOnDropdown)
						$save_btn.addClass('jsSaveNoDropdown');
					
					if ($pageRefresh)
						$save_btn.addClass('jsPageRefresh');
					
					if (dd_id)
						$save_btn.data("dropdown-id", dd_id);
					
					$this.trigger("afterPopupAdd", {"control" : this, "sender" : $elem[0], "popup" : new_jq});
				});
			}
			return;
		}
		else if (this.hasClass("qc-ref-ctrl-edit-full", sender) || this.hasClass("qc-ref-ctrl-edit-full", sender_jq.parent()[0]))
		{
			var $elem = sender_jq.hasClass('qc-ref-ctrl-edit-full') ? sender_jq : sender_jq.parent();

			this.openPopup($elem);
			
			return;
		}
		else if (this.hasClass("qc-ref-ctrl-view-full", sender) || this.hasClass("qc-ref-ctrl-view-full", sender_jq.parent()[0]))
		{
			var $elem = sender_jq.hasClass('qc-ref-ctrl-view-full') ? sender_jq : sender_jq.parent();

			this.openPopup($elem, null, 'view');
			
			return;
		}

		else if (this.hasClass('qc-dropdown-popup-submit-btn', sender))
		{			
			var $saveOnDropdown = true;
			if (sender_jq.hasClass('jsSaveNoDropdown'))
				$saveOnDropdown = false;
			
			var $pageRefresh = false;
			if (sender_jq.hasClass('jsPageRefresh'))
				$pageRefresh = true;
			
			var dd_jq = null;
			if ($saveOnDropdown)
			{
				var dd_id = sender_jq.data("dropdown-id");
				if (!dd_id)
				{
					alert("dropdown id not found!");
					return;
				}
				var dd_jq = jQuery("#" + dd_id);
				if (dd_jq.length === 0)
				{
					alert("dropdown not found!");
					return;
				}
			}
			
			var $button_form = sender_jq.closest("form");
			if (!$button_form.length)
			{
				$button_form = sender_jq.closest(".js-popup-container").find('form');
			}
			
			this.doDropdownPopupSubmit($button_form, sender, dd_jq, $pageRefresh);
			
			return;
		}

		else if (this.hasClass("dd-tab-panel", sender))
		{
			var data_show = sender.getAttribute("data-show");
			var pb = jQuery(sender).closest(".item-wrap");
			var panels = pb.find(".dd-toggle-panel");
			var toggle_panel = panels.filter(".data-show-" + data_show);
			
			jQuery(sender).toggleClass("active");
			toggle_panel.toggle();
			toggle_panel.trigger("onToogle");
			
			panels.not(toggle_panel).hide();
			jQuery(sender).closest(".item").find(".dd-tab-panel").not(sender).removeClass("active");
			
			// jQuery(sender).closest(".item-wrap").find(".dd-toggle-panel.data-show-" + data_show).toggle();
			return;
		}
		
		/*
		else if (this.hasClass("qc-ref-ctrl-delete-full", sender) || this.hasClass("qc-ref-ctrl-delete-full", jQuery(sender).parent()[0]))
		{
			var c = confirm('Are you sure you want to delete this item?');
			if (!c)
				return;

			var rm_triggerer = this.hasClass("qc-ref-ctrl-delete-full", sender) ? jQuery(sender) : jQuery(sender).parent();
			var row_jq = rm_triggerer.closest(".item-wrap");

			if (!rm_triggerer.hasClass("qc-bulk-delete"))
			{
				// do list remove
				var grid_data = this.getProperties();
				var is_ajax = (grid_data && grid_data["processAction"] && (grid_data["processAction"] === "ajax"));
				if (is_ajax)
				{
					

					var form_jq = row_jq.find(".qc-list-form");
					if (form_jq.length === 0)
						return;

					var $submitData = this.collectData(form_jq);
					omi.api(this._ty + "::DeleteItem", [$submitData, grid_props], 
						// the callback
						function (resp) {
							alert("Data succesfully removed!");
							//refresh page
							window.location.href = window.location.href;
						}, 
						// callback when error
						function (jqXHR, textStatus, errorThrown) {
							$this.doOnException(jqXHR, textStatus, errorThrown);
						},
						null,
						null,
						null
					);
				}
				else
				{
					var rm_url = rm_triggerer.data("rm-url");
					if (rm_url)
						window.location.href = rm_url;
				}
			}
			else
			{
				var rm_flag_jq = row_jq.find(".js-rm-flag");
				if (rm_flag_jq.length > 0)
				{
					rm_flag_jq.append("<input type='hidden' name='" + rm_triggerer.data("vars-path") + "' value='" + QModel.TransformDelete + "' />");
					row_jq.hide();
				}
				else
					row_jq.remove();
			}
			return;
		}
		*/
		else if (this.hasClass("qc-ref-ctrl-delete", sender) || this.hasClass("qc-ref-ctrl-delete", jQuery(sender).parent()[0]))
		{
			this.removeRow(sender);
		}
		else if (this.hasClass("qc-collection-add", sender))
		{
			this.addCollectionRow(sender);
		}
		//else if (this.hasClass("qc-collection-more", sender))
		else if (sender_jq.closest(".qc-collection-more").length > 0)
		{
			// avoid a submit
			event.preventDefault();
			this.showMore(sender);
		}
		else if (sender_jq.hasClass("qc-export-excel") || sender_jq.hasClass("qc-export-pdf") || 
					sender_jq.hasClass("qc-export-pdf-full") || sender_jq.hasClass("qc-export-excel-full"))
		{
			// append URL to the href
			var sjq_href = sender_jq.attr('href');
			if ((sjq_href !== undefined) && (sjq_href.indexOf('?') < 0))
			{
				// alert(sender_jq.attr('href'));
				// alert(window.location.search);
				sender_jq.attr('href', sjq_href + window.location.search);
			}
		}
		else if (sender_jq.hasClass("qc-ref-ctrl-delete-flag") || sender_jq.closest(".qc-ref-ctrl-delete-flag").length)
		{
			// alert('yep!');
			var $row = sender_jq.closest('.qc-xg-item');
			if ($row.length)
			{
				var $tsp_val = $row.find('.js-bulk-tsp').val();
				if ($tsp_val == QModel.TransformDelete)
				{
					$row.find('.js-bulk-tsp').val('');
					$row[0].style.backgroundColor = "";
				}
				else
				{
					$row.find('.js-bulk-tsp').val('2');
					$row[0].style.backgroundColor = "#ffcccb"; // light red
				}
				// alert($tsp_val + " | " + QModel.TransformDelete);
			}
		}
		else if (event.shiftKey && (sender.tagName === 'LABEL') && sender.hasAttribute('for') && (navigator.userAgent.indexOf("Firefox") != -1))
		{
			// This is a ugly FFox bug ... if you press shift+click on a LABEL, the checkbox input will not get the event
			// @TODO - how can we do this safer and avoid any possible double clicking event in the future
			var $elem = document.getElementById(sender.getAttribute('for'));
			var $event_tmp = new Event('click', {'bubbles' : true});
			$event_tmp.shiftKey = true;
			$elem.dispatchEvent($event_tmp);
		}
	},
	
	scroll: function (event) {
		// trigger showMore when close to the bottom
		
		var $show_more_dom = this.$('.qc-collection-more');
		if ($show_more_dom.data('hasMoreItems') === false) {
			console.log('hasMoreItems: ', $show_more_dom.data('hasMoreItems'));
			return;
		}
		
		var $scroll_top_max = event.target.scrollHeight - event.target.clientHeight;	   
		// console.log('$scroll_top_max: ' + $scroll_top_max + " / " + event.target.scrollTopMax);
		
		const $space_left_to_scroll = $scroll_top_max - event.target.scrollTop;
		const $viewport_height = event.target.clientHeight;
		
		const $elements = this.$('.qc-main-list .qc-xg-item');
		var $max_elem_height = 0;
		for (var $i = 0; $i < $elements.length; $i++) {
			$max_elem_height = Math.max($max_elem_height, jQuery($elements[0]).outerHeight(true));
		}
		
		if ($max_elem_height < 20)
			// ensure a minimum value
			$max_elem_height = 20;
		
		// we want elements preparted as much as a viewport can handle plus one
		var $desired_available_elements = Math.ceil($viewport_height / $max_elem_height) + 1;
		
		
		// I want to have X elements ready on the scroll
		var $estimated_elements_hidden = Math.floor($space_left_to_scroll / $max_elem_height);
		var $more_is_needed = $estimated_elements_hidden < $desired_available_elements;

		/*
		console.log('$space_left_to_scroll: ' + $space_left_to_scroll);
		console.log('$viewport_height: ' + $viewport_height);
		console.log('$max_elem_height: ' + $max_elem_height);
		console.log('$desired_available_elements: ' + $desired_available_elements);
		console.log('$estimated_elements_hidden: ' + $estimated_elements_hidden);
		console.log('$more_is_needed: ' + $more_is_needed);

		console.log('$max_elem_height: ' + $max_elem_height);
		console.log('event.target: ', event.target);
		console.log('window.scrollY: ' + window.scrollY);
		console.log('scrollHeight: ' + event.target.scrollHeight);
		// console.log('scrollLeft: ' + event.target.scrollLeft);
		console.log('scrollTop: ' + event.target.scrollTop);
		console.log('$scroll_top_max: ' + $scroll_top_max);
		// console.log('scrollWidth: ' + event.target.scrollWidth);
		console.log('---------------------------------------------------------');
		*/
	   
		if ($more_is_needed && ($show_more_dom.length > 0) && ($show_more_dom.data('hasMoreItems') !== false)) {
			this.showMore($show_more_dom[0]);
		}
	},
	
	get_checked_data : function ($jq_context)
	{
		if (!$jq_context)
			$jq_context = this.$('.xg-form');

		var pickers = $jq_context.find(".js-itms-table td.qc-chk-cell");
		
		var $ret = null;
		
		for (var i = 0; i < pickers.length; i++)
		{
			var $jq = jQuery(pickers[i]);
			var $checkbox = $jq.find('.qc-chkcollitm-pick');
			
			if ($checkbox.length && $checkbox[0].checked)
			{
				// qc-hidden-id
				// qc-rowi
				if ($ret === null)
					$ret = {};
				
				this.get_value_with_path($ret, $jq.find('.qc-hidden-id'));
				this.get_value_with_path($ret, $jq.find('.qc-hidden-ty'));
				var $rowi = $jq.find('.qc-rowi');
				if ($rowi.length)
					this.get_value_with_path($ret, $rowi);
			}
		}
		
		return $ret;
	},
	
	get_value_with_path : function ($object, $input_name, $input_value)
	{
		if ($input_name instanceof jQuery)
		{
			if ($input_value === undefined)
				$input_value = $input_name.val();
			$input_name = $input_name.attr('name') ? $input_name.attr('name') : $input_name.attr('name-x');
		}
		else if ($input_name instanceof HTMLElement)
		{
			if ($input_value === undefined)
				$input_value = $input_name.value;
			$input_name = $input_name.hasAttribute('name') ? $input_name.getAttribute('name') : $input_name.getAttribute('name-x');
		}
		
		if (!(typeof($input_name) === 'string'))
		{
			// console.log($object, $input_name, $input_value);
			console.error('invalid input name');
			return false; // error
		}
		var $chunks = $input_name.replaceAll(']', '').split('[');
		
		if (!$chunks)
		{
			console.error('invalid input name #2');
			return false; // error
		}
		if ($object)
		{
			var $ref = $object;
			for (var $i = 0; $i < ($chunks.length - 1); $i++)
			{
				if ($ref[$chunks[$i]] === undefined)
					$ref[$chunks[$i]] = {};
				$ref = $ref[$chunks[$i]];
			}
			$ref[$chunks[$i]] = $input_value;
			return true;
		}
		else
			return $input_value;
	},
	
	removeRow : function (sender)
	{
		var _toRmItm = jQuery(sender).closest(".qc-ref-ctrl");

		this.canRemove = true;
		this.trigger('beforeRemove', {"sender" : sender, "removedItm" : _toRmItm});

		if (!this.canRemove)
			return;

		var sender_jq = this.hasClass("qc-ref-ctrl-delete", sender) ? jQuery(sender) : jQuery(sender).parent();

		var vars_path = sender_jq.data('vars-path');
		if (typeof vars_path === 'undefined')
			_toRmItm.remove();

		var new_jq = jQuery("<input type=\"hidden\" value=\"" + window.TransformDelete + "\" name=\"" + vars_path + "\" />");

		_toRmItm.find(".js-rm-flag").append(new_jq);
		_toRmItm.hide();
		
		this.trigger('afterRemove', {"sender" : sender, "removedItm" : _toRmItm});

		// we may have reference collection and we need to mark as changed the dropdown - so it will carry through
		var _toRmItm_inner = _toRmItm.find(".qc-xg-item.qc-coll-itm");
		
		if (_toRmItm_inner.length > 0)
		{
			if (_toRmItm_inner.hasClass("qc-reference-itm"))
			{
				var dd_jq = _toRmItm_inner.find(".qc-dd");
				if (dd_jq.length > 0)
				{
					var dd_ctrl = $ctrl(dd_jq);
					var current_info = dd_ctrl.getCurrentInfo();
					if (current_info && current_info.id)
					{
						// dd_ctrl.setSelected(current_info.caption, current_info.id, current_info.type, current_info.full_data, true);
						var id_field = dd_ctrl.$(".qc-dd-input-id");
						if (!id_field.attr("name"))
						{
							var ty_field = dd_ctrl.$(".qc-dd-input-ty");
							id_field.attr("name", id_field.attr("name-x")).removeAttr("name-x");
							ty_field.attr("name", ty_field.attr("name-x")).removeAttr("name-x");
						}
					}
				}
				else
				{
					// possible readonly cell
					if (sender_jq.hasClass('qc-ref-ctrl-delete') && sender_jq.data('obj-id') && sender_jq.data('obj-ty'))
					{
						// alert(sender_jq.data('obj-id') + " | " + sender_jq.data('obj-ty'));
						var $place_missing_id = jQuery("<input type=\"hidden\" value=\"" + sender_jq.data('obj-id') + "\" name=\"" + sender_jq.data('obj-path') + "[Id]\" />");
						var $place_missing_ty = jQuery("<input type=\"hidden\" value=\"" + sender_jq.data('obj-ty') + "\" name=\"" + sender_jq.data('obj-path') + "[_ty]\" />");
						_toRmItm.find(".js-rm-flag").append($place_missing_id);
						_toRmItm.find(".js-rm-flag").append($place_missing_ty);
					}
				}
			}
			else if (_toRmItm_inner.hasClass("qc-coll-scalar-itm"))
			{
				var inp_jq = _toRmItm_inner.find(".qc-ref-cell .qc-form-element");
				if ((inp_jq.length > 0) && inp_jq.attr("name-x"))
					inp_jq.attr("name", inp_jq.attr("name-x")).removeAttr("name-x");
				else
				{
					// possible readonly cell
					
				}
			}
			else
			{
				// possible readonly cell
				
			}
		}
	},

	showMore : function (sender, callback)
	{
		// data-offset='<?= \$data ? count(\$data) : 0 ?>' 
		// data-length='20' 
		// data-from='{{\$this->from}}' 
		// data-selector='{{\$this->getSelectorForMode(\$this->grid_mode)}}
		
		var data_offset = parseInt(sender.getAttribute("data-offset"));
		var data_length = parseInt(sender.getAttribute("data-length"));
		var data_from = sender.getAttribute("data-from");
		var data_selector = sender.getAttribute("data-selector");
		if (data_selector === "")
			data_selector = null;
		var data_grid_mode = sender.getAttribute("data-grid-mode");
		var data_form_vars_path = sender.getAttribute("data-form-vars-path");

		var moreWr = jQuery(sender).closest(".qc-collection-more-wrapper");

		var obj_form_vars_path_index = moreWr.prev(".qc-list-vars-path-index-container").find(".qc-list-vars-path-index");
		
		var form_vars_path_index = obj_form_vars_path_index.attr("data-form-vars-path-index");
		data_form_vars_path += "[" + form_vars_path_index + "]";
		obj_form_vars_path_index.attr("data-form-vars-path-index", form_vars_path_index++);

		var data_form_render = sender.getAttribute("data-form-render");

		// var $bind_param = this.getSearchBinds();
		var $bind_param = this.collectQSData();
		
		if (!$bind_param)
			$bind_param = {};

		jQuery(sender).trigger("setupExtraBinds", [$bind_param]);

		$bind_param["LIMIT"] = [data_offset, data_length];

		//qbDebug($bind_param, 10);
		//return;

		// $grid_mode, $render_method, $vars_path, $from, $id = null, $bind_param = null, $selector
		//alert(data_form_render);

		this.setupLoader();
		
		var $this = this;

		this.ajax("RenderListData", [data_grid_mode, data_form_render, data_form_vars_path, data_from, null, $bind_param, 
			data_selector, this.getProperties(), obj_form_vars_path_index.data("next-crt-no")], [this, function (response)
		{
			$this.unsetLoader();

			//alert(obj_form_vars_path_index.closest(".qc-list-vars-path-index-row").length);
			obj_form_vars_path_index.closest(".qc-list-vars-path-index-row").before(response[0]);
			sender.setAttribute("data-offset", data_offset + data_length);
			obj_form_vars_path_index.data("next-crt-no", response[1]);

			if (!response[2]) {
				jQuery(sender).hide();
				jQuery(sender).data('hasMoreItems', false);
			}
		}]);
	},

	addCollectionRow : function (sender, callback, callbackParams, $picked_data)
	{
		this.stopAddingCollectionRow = false;
		this.trigger("beforeAddCollectionRow", {"control" : this, "sender" : sender});
		if (this.stopAddingCollectionRow)
			return;

		var render_form_meth = sender.getAttribute("data-form-render");
		var form_vars_path = sender.getAttribute("data-form-vars-path");

		// qc-list-vars-path-index-container
		// qc-list-vars-path-index
		var $list_vars_path_index_container = jQuery(sender).closest(".qc-list-vars-path-index-container");
		var obj_form_vars_path_index = $list_vars_path_index_container.find(".qc-list-vars-path-index");
		
		for (var $ii = 0; $ii < obj_form_vars_path_index.length; $ii++)
		{
			var $closest_tmp = jQuery(obj_form_vars_path_index[$ii]).closest('.qc-list-vars-path-index-container');
			if ($closest_tmp.length && ($closest_tmp[0] === $list_vars_path_index_container[0]))
			{
				obj_form_vars_path_index = jQuery(obj_form_vars_path_index[$ii]);
				break;
			}
		}
		
		form_vars_path_index = obj_form_vars_path_index.attr("data-form-vars-path-index");
		++form_vars_path_index;
		
		//alert(form_vars_path_index);
		//alert(++form_vars_path_index);
		form_vars_path += "[" + form_vars_path_index + "]";
		obj_form_vars_path_index.attr("data-form-vars-path-index", form_vars_path_index);

		var pb = jQuery(sender).closest(".item-wrap");

		var grid_props = this.getProperties();

		var _toaddrows = jQuery(sender).closest('.qc-coll-add-cell').find(".qc-add-rows");
		
		var $this = this;
		
		var $rows_to_render = $picked_data ? $picked_data.length : (_toaddrows.length > 0) ? _toaddrows.val() : 1;
		
		if ($rows_to_render > 1)
		{
			obj_form_vars_path_index.attr("data-form-vars-path-index", form_vars_path_index + ($rows_to_render - 1));
		}
		
		this.ajax("renderAddRow", [grid_props, render_form_meth, form_vars_path, $rows_to_render, $picked_data], [this, function (response) {
			var new_jq = jQuery(response);
			new_jq.insertBefore(pb);
			// init custom dropdowns
			initCustomDropdowns(new_jq);

			// datepickr(".datepickr", {dateFormat: 'Y-m-d'});
			$this.setupDatepickers(new_jq.find('.datepickr'));
			
			// flag inputs as changed from 'name-x' to 'name'
			{
				var $elements_to_flag_as_changed = new_jq.find("input[name-x].qc-form-element,select[name-x].qc-form-element");

				for (var $tmpi = 0; $tmpi < $elements_to_flag_as_changed.length; $tmpi++)
				{
					var $tmp_e = $elements_to_flag_as_changed[$tmpi];
					if ($tmp_e.hasAttribute('name-x') && (!$tmp_e.hasAttribute('name')))
					{
						$tmp_e.setAttribute('name', $tmp_e.getAttribute('name-x'));
						$tmp_e.removeAttribute('name-x');
					}
				}
			}
			
			var $tmp_collection_chk_toggle = jQuery(sender).closest('.qc-collection').find('.qc-collection-chk-toggle');
			if ($tmp_collection_chk_toggle.length)
				$this.js_collection_chk_toggle($tmp_collection_chk_toggle[0]);
			
			var $s2_no_search = new_jq.find('.select-2-no-search');
			if ($s2_no_search.length && $s2_no_search.select2) {
				$s2_no_search.select2({minimumResultsForSearch: Infinity});
			}
			jQuery(sender).trigger("afterAddCollectionRow", {"new_jq" : new_jq});

			if (callback)
				callback(sender, callbackParams ? {"new_jq" : new_jq, "params" : callbackParams} : new_jq);
			
			$this.trigger("afterAddCollectionRow", {"control" : $this, "new_jq" : new_jq});
		}]);
	},
	
	getSearchBinds : function (adv_search)
	{
		if (!adv_search)
			adv_search = this.$(".js-advanced-search");

		return (adv_search.length > 0) ? this.collectSearchBinds(adv_search) : {};
	},

	collectSearchBinds : function (formJq)
	{
		var $fd = this.collectData(formJq);
		
		if (!$fd)
			$fd = {};

		for (var i in $fd)
		{
			//var is_oby = (i.indexOf("OBY_") > -1);
			if (!$fd[i])
			{
				//qbDebug("DELETE " + i  + "|", 1);
				delete $fd[i];
			}
		}
		return $fd;
	},

	setupPopupForProp : function(sender_jq)
	{
		var $this = this;
		var popup_prop = sender_jq.closest(".qc-popup-prop-setup");
		var wr_jq = popup_prop.closest(".qc-popup-prop-wr");
		var id_itm = wr_jq.find(".qc-popup-prop-id");
		var id = (id_itm.length > 0) ? id_itm.val() : null;
		var view = popup_prop.data("view");
		if (!view)
			return;
		
		var $params = popup_prop.data("params");
		var $mode = popup_prop.data("mode");
		
		this.quickCall(this._ty + "::RenderViewPopup", [view, id, $params, $mode], function (resp) {
			
			var fp_unq = wr_jq.data("popup-prop-unq");
			if (!fp_unq)
			{
				fp_unq = uniqid();
				wr_jq.attr("popup-prop-unq", fp_unq);
			}

			var popup_jq = $this.setupPopup(resp);
			popup_jq.attr("for-prop-unq", fp_unq);
			
			popup_jq.addClass("qc-up-prop");
			
		});
	},
	
	openPopup : function ($elem, params, $grid_mode)
	{
		return this.open_popup(undefined, params, $grid_mode, null, $elem);
	},

	open_popup : function ($class_name, params, $grid_mode, $on_popup_save, $elem)
	{
		if ($elem && $elem[0]._in_process)
			return;
		
		var $pick_mode = false;
		if ($grid_mode === 'list/pick')
		{
			$grid_mode = 'list';
			$pick_mode = true;
		}

		this.stopPopupEdit = false;

		if (!$on_popup_save)
			this.trigger("beforePopupEdit", {"control" : this, "sender" : $elem ? $elem[0] : null});

		if (this.stopPopupEdit)
			return;

		var _ctrl = $class_name || $elem.data("ctrl");
		if (_ctrl)
		{
			var $this = this;
			var dd_jq = $elem ? $elem.closest(".qc-ref-ctrl").find(".qc-dd") : null;
			if ($elem)
				$elem[0]._in_process = true;
			if (!params)
				params = {};
			
			var $obj_id = dd_jq && dd_jq.length ? $ctrl(dd_jq).getSelectedId() : ($elem ? $elem.data("id") : null);
			if (dd_jq && dd_jq.length)
			{
				params["full_data"] = $ctrl(dd_jq).getSelectedDataFull();
				params["from"] = $ctrl(dd_jq).getFrom();
				
				var ddbindsjq = dd_jq ? dd_jq.find(".qc-dd-binds") : null;
				var ddbinds = (ddbindsjq && (ddbindsjq.length > 0)) ? ddbindsjq.val() : null;
				var binds = (ddbinds && (ddbinds.length > 0)) ? JSON.parse(ddbinds) : null;
				if (binds)
					params["dd_binds"] = binds;
			}
			
			// console.log($ctrl(dd_jq).getSelectedId(), params);
			omi.api(_ctrl + "::RenderPopupForm", [$grid_mode ? $grid_mode : ($obj_id ? "edit" : "add"), $obj_id, params], function (resp) {
				if ($elem)
					$elem[0]._in_process = false;
				
				if (dd_jq && dd_jq.length)
				{
					var dd_id = dd_jq.attr("id");
					if (!dd_id)
					{
						dd_id = uniqid();
						dd_jq.attr("id", dd_id);
					}
				}
				var new_jq = $this.setupPopup(resp);
				var $popup_ctrl = $ctrl(new_jq.find('.omi-control'));
				
				var $submit_btn = new_jq.find(".qc-popup-submit-btn");
				
				if ($pick_mode && (!$submit_btn.length))
				{
					$submit_btn = jQuery('<button type="button" style="right: 60px;" class="btn btn-info btn-border">' + _L('Select') + '</button>');
					// page-header
					new_jq.find('.qc-inner .page-header').append($submit_btn);
				}
				
				$submit_btn.removeClass("qc-popup-submit-btn");
				
				if (!$on_popup_save)
				{
					$submit_btn.addClass("qc-dropdown-popup-submit-btn").data("dropdown-id", dd_id);
					$this.trigger("afterPopupEdit", {"control" : this, "sender" : $elem ? $elem[0] : null, "popup" : new_jq, "params" : params});
				}
				else
				{
					// alert('Checked items');
					$submit_btn.click(function ()
					{
						// collect form data and send it as an argument
						var $popup_data = $popup_ctrl.get_form_data();
						var $checked_data = $popup_ctrl.get_checked_data($pick_mode ? $popup_ctrl.$('.qc-main-list').first() : undefined);
						if ($checked_data)
						{
							var $popup_ctrl_from = $popup_ctrl.getProperties().from;
							if ($checked_data[$popup_ctrl_from])
								$checked_data = $checked_data[$popup_ctrl_from];
						}
						
						var $close_popup = $on_popup_save($popup_data, $popup_ctrl, $checked_data);
						
						if ($close_popup === true)
						{
							// @todo - dirty remove
							new_jq.remove();
						}
						
					});
				}

			});
		}
	},
	
	get_form_data : function ()
	{
		var $inputs_list = this.$('.xg-form').find('input[name],select[name]');
		var $obj = {};
		for (var $i = 0; $i < $inputs_list.length; $i++)
			this.get_value_with_path($obj, $inputs_list[$i]);
	
		return $obj;
	},
	
	get_input_value : function ($input_name)
	{
		var $inputs_list = this.$('.xg-form').find('input[name="' + $input_name + '"],input[name-x="' + $input_name + '"],select[name="' + $input_name + '"],select[name-x="' + $input_name + '"]');
		return $inputs_list.val();
	},

	onevent: function(event_type, sender, args)
	{
		//qbDebug({"event_type" : event_type, "sender" : sender, "args" : args}, 10);
		//console.log({"event_type" : event_type, "sender" : sender, "args" : args});
		// to be changed
		if (event_type === "selected")
		{
			var drop_down = args.control;
			var dd_jq = jQuery(drop_down.dom);

			if (args && args["id"])
			{
				if (drop_down.withPickerDD)
				{
					// alert('withPickerDD');
					this.onchange(dd_jq.find(".qc-input.qc-text-with-dd")[0]);
				}
				else
					this.onchange(dd_jq.find(".qc-dd-input-id")[0]);
			}
			
			if (drop_down && drop_down.update_DD_Actions_Status)
			{
				drop_down.update_DD_Actions_Status();
				/*
				var ref_ctrl = dd_jq.closest(".qc-ref-ctrl");
				if (ref_ctrl.length > 0)
				{
					var dd_actions = ref_ctrl.find(".qc-dropdown-actions");
					if (dd_actions.length > 0)
					{
						// args.id ? dd_actions.find(".qc-ref-edit-wr").show() : dd_actions.find(".qc-ref-edit-wr").hide();
						drop_down
					}
				}
				*/
			}
		}
		else if (event_type === "beforeItemSelect")
		{
			var drop_down_ctrl = args.control;
			var dd_jq = jQuery(drop_down_ctrl.dom);
			var ref_ctrl_jq = dd_jq.closest(".qc-ref-ctrl");

			if ((ref_ctrl_jq.length === 0) || (!ref_ctrl_jq.hasClass("qc-avd-on-select")))
				return;

			var coll_itm = dd_jq.closest(".qc-coll-itm");
			if (coll_itm.length === 0)
				return;

			var property_markup = dd_jq.closest(".qc-xg-property").attr("xg-property");
			var propval = property_markup.match(/.*?(?=\()/);
			var collection_jq = coll_itm.closest(".qc-collection");
			var dropdowns = collection_jq.find(".qc-coll-itm .qc-xg-property[xg-property^='" + propval + "'] .qc-dd");

			var sender_jq = jQuery(args.sender);
			var selected_id = sender_jq.attr("item.id");

			var picked = false;
			if (dropdowns.length > 0)
			{
				for (var i = 0; i < dropdowns.length; i++)
				{
					if (dropdowns[i] === dd_jq[0])
						continue;

					var id = $ctrl(dropdowns[i]).getSelectedId();
					if (id === selected_id)
						picked = true;
				}
			}
			if (picked)
			{
				args.control.stopSelect = true;
				alert("Item already picked in collection!");
			}
		}
		else if (event_type === "beforeUpdateResults")
		{
			var drop_down_ctrl = args.control;
			var dd_jq = jQuery(drop_down_ctrl.dom);

			var ref_ctrl_jq = dd_jq.closest(".qc-ref-ctrl");

			if ((ref_ctrl_jq.length === 0) || (!ref_ctrl_jq.hasClass("qc-avd-filter")))
				return;

			var coll_itm = dd_jq.closest(".qc-coll-itm");
			if (coll_itm.length === 0)
				return;

			var property_markup = dd_jq.closest(".qc-xg-property").attr("xg-property");
			var propval = property_markup.match(/.*?(?=\()/);
			var collection_jq = coll_itm.closest(".qc-collection");
			var dropdowns = collection_jq.find(".qc-coll-itm .qc-xg-property[xg-property^='" + propval + "'] .qc-dd");

			//alert(dropdowns.length + "  ||  " + property_markup);
			var ids = [];
			if (dropdowns.length > 0)
			{
				for (var i = 0; i < dropdowns.length; i++)
				{
					if (dropdowns[i] === dd_jq[0])
						continue;

					var id = $ctrl(dropdowns[i]).getSelectedId();
					if (id)
						ids.push(id);
				}
			}

			if (ids.length > 0)
			{
				var not = [];
				not.push(ids);
				$ctrl(dd_jq).setBinds({"NOT" : not});
			}
		}
		else if (event_type === "afterPopupSave")
		{
			var popup_save_sender = args.sender;
			var popup_jq = popup_save_sender.closest(".qc-popup");
			
			if (popup_jq.hasClass("qc-up-prop") && args.resp && args.resp[0])
			{
				//qbDebug(args.resp, 10);
				
				var itm = args.resp[0];
				var wr_jq = this.$(".qc-popup-prop-wr[popup-prop-unq='" + popup_jq.attr("for-prop-unq") + "']");

				wr_jq.find(".qc-popup-prop-id").val(itm._id);
				wr_jq.find(".qc-popup-prop-ty").val(itm._ty);

				//alert(wr_jq.find(".qc-popup-prop-id").length + " | " + itm._id);
				//alert(wr_jq.find(".qc-popup-prop-ty").length + " | " + itm._ty);
			}
		}
		else if (event_type === "click_on_dditm")
		{
			var inp = args.input.closest(".qc-radio-dropdown").siblings(".qc-form-element");
			inp.val(args.input.val());
			if (inp.length > 0)
				this.onchange(inp[0]);
		}
		else if (event_type === "clickOnEnumDropdownItem")
		{
			var enumCtrlJq = jQuery(args.control.dom);
			if (enumCtrlJq.hasClass("js-search-field"))
			{
				// keep in sync with advanced search
				this.syncWithAdvancedSearch(jQuery(args.sender));

				// do quick search
				this.doQuickSearch();
			}
		}
		else if ((event_type === 'afterAddCollectionRow') && args.new_jq)
		{
			// handle the image uploader here
			{
				// consume from the input/file if present
				var $images_uploader_jq = args.new_jq.closest('.qc-list').find('.q-multiple-images-uploader');
				if ($images_uploader_jq.length > 0)
				{
					var $grab_pos = this._images_uploader_index_;
					var $images_uploader = $images_uploader_jq[0];
					if (($grab_pos !== undefined) && ($grab_pos !== null) && ($grab_pos < $images_uploader.files.length))
					{
						var dataTransfer = new DataTransfer();
						// Add the items
						dataTransfer.items.add($images_uploader.files[$grab_pos]);
						var $row_input_file = args.new_jq.find('.qc-form-element.qc-file');
						$row_input_file[0].files = dataTransfer.files;
						$row_input_file.trigger('change'); // we need this for some reason
						this.onchange($row_input_file[0]);

						this._images_uploader_index_++;
						if (this._images_uploader_index_ === $images_uploader.files.length)
						{
							this._images_uploader_index_ = null;
							// reset the uploader
							$images_uploader.value = "";
						}
					}
					else
					{
						this._images_uploader_index_ = null; // safety reset
						$images_uploader.value = "";
					}
				}
			}
		}
	},
	
	js_with_selected_delete : function($sender)
	{
		var $this = this;
		var $selected_elements = this.get_checked_data();
		if (!$selected_elements)
		{
			alert('No items were selected.');
		}
		else
		{
			// @TODO confirm -> execute -> refresh page
			// console.log($ret);
			// alert('@TODO :: js_with_selected_delete');
			if (confirm('Are you sure you wish to delete ?'))
			{
				$this.quickCall($this._ty + '::Multi_Delete', [$selected_elements, $this.getProperties()], function ($call_resp)
					{
						// if done
						window.location.reload();
					});
			}
		}
	},
	
	js_action_prepare_args : function($object, $depth, $initial_object)
	{
		if (!(typeof($object) === 'object'))
			return $object;
		
		if (Array.isArray($object))
		{
			for (var $i = 0; $i < $object.length; $i++)
			{
				if ((typeof($object[$i]) === 'string') && ($object[$i].substr(0, "@form:".length) === "@form:"))
				{
					$object[$i] = this.get_input_value($object[$i].substr("@form:".length));
				}
				else if (typeof($object[$i]) === 'object')
					this.js_action_prepare_args($object[$i], $depth + 1, $initial_object || $object);
			}
		}
		else
		{
			for (var $i in $object)
			{
				if ((typeof($object[$i]) === 'string') && ($object[$i].substr(0, "@form:".length) === "@form:"))
				{
					$object[$i] = this.get_input_value($object[$i].substr("@form:".length));
				}
				else if (typeof($object[$i]) === 'object')
					this.js_action_prepare_args($object[$i], $depth + 1, $initial_object || $object);
			}
		}
		
		return $object;
	},
	
	js_with_selected_set_values : function ($sender)
	{
		var $selected_elements = this.get_checked_data();
		if (!$selected_elements)
		{
			alert('No items were selected.');
		}
		else
		{
			// $class_name, params, $grid_mode, $elem
			var $class_name = this._ty + "_Bulk_Set_Vals";
			var $params = {};
			var $this = this;
			
			this.open_popup($class_name, $params, 'add', function ($popup_data, $popup_ctrl)
				{
					// ajax with loader to set those  :: method, params, onSuccessCallback, onSuccessCallbackParams, onErrorCallback, force, skipLoader
					$this.quickCall($this._ty + '::Bulk_Set_Values', [$selected_elements, $popup_data, $this.getProperties()], function ($call_resp)
					{
						// if done
						window.location.reload();
					});
					
					return true; // close popup
				});
		}
	},
	
	js_pick_elements : function ($sender, $popup_view_name, $popup_query_filter, $return_elements_class, $return_elements_in)
	{
		// "PickNumbers", {"Customer_Id": "Customer[Id]", "Free": true}, "Omi\\VF\\Telecom\\Number", "VOIP_Numbers.Number"
		var $this = this;
		$params = $popup_query_filter ? $popup_query_filter : {};
		var $add_collection_sender = jQuery($sender).closest('.qc-coll-add-cell').find('.qc-collection-add');
		
		this.open_popup($popup_view_name, $params, 'list/pick', function ($popup_data, $popup_ctrl, $checked_data)
				{
					// ajax with loader to set those  :: method, params, onSuccessCallback, onSuccessCallbackParams, onErrorCallback, force, skipLoader
					// now we need to setup the data and trigger a add row
					
					if ($checked_data)
					{
						var $rows_data = [];
						for (var $k in $checked_data)
						{
							var $obj = null;
							if ($return_elements_in && $return_elements_in.length)
							{
								$obj = {'_ty': $return_elements_class};
								$obj[$return_elements_in] = {'Id': $checked_data[$k].Id, '_ty': $checked_data[$k]._ty};
							}
							else
								$obj = {'_ty': $return_elements_class, 'Id': $checked_data[$k].Id};
							$rows_data.push($obj);
						}
						
						// console.log('$rows_data', $rows_data);
						
						$this.addCollectionRow($add_collection_sender[0], undefined, undefined, $rows_data);
					}
					
					return true; // close popup
				});
	},
	
	js_collection_chk_toggle : function ($sender)
	{
		if ($sender.checked)
			jQuery($sender).closest('.qc-collection').find('.qc-collection-chk').show();
		else
			jQuery($sender).closest('.qc-collection').find('.qc-collection-chk').hide();
	},
	
	handle_shift_click : function($event, $sender, $element_selector, $jq_container, $row_selector)
	{
		var $sender_jq = jQuery($sender);
		if (!this.last_checked_element)
		{
			this.last_checked_element = $sender_jq.closest($row_selector);
		}
		else
		{
			if ($event.shiftKey)
			{
				var $all_checkboxes = $jq_container.find($row_selector);
				
				// from the first to this one ... check them all
				// var start = $chkboxes.index(this);
				var start = $all_checkboxes.index(this.last_checked_element);
				var end = $all_checkboxes.index($sender_jq.closest($row_selector)[0]);
				$all_checkboxes.slice(Math.min(start, end), Math.max(start, end) + 1)
							.find($element_selector).prop('checked', this.last_checked_element.find($element_selector)[0].checked);

				this.last_checked_element = null;
			}
			else
			{
				this.last_checked_element = $sender_jq.closest($row_selector);
			}
		}
	},
	
	replicate_checked_inputs : function ($sender, $event, $jq_sender)
	{
		if (!$jq_sender)
			$jq_sender = jQuery($sender);
		
		var $jq_collection = jQuery($sender).closest('.qc-collection');
		var $checkboxes = $jq_collection.find('.qc-collection-chk').not('.qc-collection-chk-heading');
		
		var $xg_prop = $jq_sender.closest('.qc-xg-property').attr('xg-property');
		
		for (var $i = 0; $i < $checkboxes.length; $i++)
		{
			var $chk = jQuery($checkboxes[$i]);
			// 1. find all checked elements
			if (!$chk.find('input[type="checkbox"]').is(':checked'))
				continue;
			// 2. find the correct xg-property
			var $inp_box = $chk.closest('.qc-coll-itm').find('.qc-xg-property[xg-property="' + addslashes($xg_prop) + '"]');
			// alert('.qc-xg-property[xg-property="' + $xg_prop + '"]' + " - " + $inp_box.length);
			
			var $element = null;
			if ($jq_sender.closest('.qc-dd').length)
			{
				var $sender_ctrl = $ctrl($sender);
				var $local_ctrl = $ctrl($inp_box.find('.omi-control'));
				
				if ($sender_ctrl === $local_ctrl)
					continue;
				
				if ($sender_ctrl.hasItmSelected())
				{
					// setSelected: function(caption, id, type, full_data, force, merge_full_data)
					/*$local_ctrl.setSelected($sender_ctrl.getSelectedCaption(), $sender_ctrl.getSelectedId(), $sender_ctrl.getSelectedType(),
												$sender_ctrl.getSelectedDataFull(), true);*/
					$local_ctrl.updateData($sender_ctrl.getSelectedCaption(), $sender_ctrl.getSelectedId(), $sender_ctrl.getSelectedType(),
												$sender_ctrl.getSelectedDataFull());
				}
				else
				{
					// $local_ctrl.unsetSelected(); // unset without events
					$local_ctrl.updateData($sender_ctrl.withPickerDD ? $sender_ctrl.$(".qc-dd-pick").val().trim() : $sender_ctrl.noItemCaption, "", "", "");
				}
			}
			else
			{
				var $element = $inp_box.find('.qc-form-element[xg-property-value="' + addslashes($xg_prop) + '"]');

				if ($element[0] === $sender) // 3. update (avoid itself)
					continue;

				if ($element.hasClass('qc-input'))
				{
					$element.attr("value", $jq_sender.val());
				}
				else if ($element.hasClass('qc-checkbox-inp'))
				{
					$element[0].checked = $jq_sender[0].checked;
				}
				else if ($element.hasClass('qc-dropdown-hidden'))
				{
					var $span_caption = $jq_sender.closest(".js-form-grp").find('.qc-radio-dropdown span').text();
					if ($element.attr("value") !== $jq_sender.val())
					{
						var $radio_dd = $element.closest('.js-form-grp').find('.qc-radio-dropdown');
						$radio_dd.find('span').text($span_caption);
						var $azzadasda = $radio_dd.find('input[type=radio][value="' + addslashes($jq_sender.val()) + '"]');
						$azzadasda.attr('checked', true);
						$element.attr('value', $jq_sender.val());
						
						if (!$element.attr('name'))
						{
							$element.attr('name', $element.attr('name-x'));
							$element.attr('name-x', null);
							$element.addClass('qc-changed');
						}
					}
				}
			}
		}
		
	}
});

jQuery(document).ready(function () {

	var body_jq = jQuery(document.body);
	body_jq.on('afterClose', '.qc-popup.qc-data-expand-popup', function(e, mainEvent) {
		jQuery(this).remove();
	});

	body_jq.on("click", ".js-change-page", function () {
		var jq = jQuery(this);
		jq.closest(".js-paginator").find(".js-start-limit").val(jq.data("start"));
		jq.trigger("afterSetupLimit");
	});
	
	jQuery('body').on('click', '.js-delete-item', function()
	{
		if (confirm('Are you sure you want to delete this item!'))
		{
			var $id = jQuery(this).data('id');
			var $model = jQuery(this).data('model');
			var ctrl_jq = jQuery(this).closest(".omi-control");
			
			omi.api(ctrl_jq.attr('q-ctrl') + '::DeleteItemFromList', [$id], 
				// success
				function(resp)
				{
					console.log(resp);
					// reload page
					window.location.reload();
				}, 

				// fail
				function(jqXHR, textStatus, errorThrown)
				{
				}
			);
		}
	});

	var nav_dd = $('#-nav-dd').not(".navigation-blocked");
	if (nav_dd.length > 0)
	{
		var dd = new DropDown(nav_dd);
		dd.on("onElementClick", function (e, mainEvent) {
			var jq = jQuery(mainEvent.target);
			if (jq.hasClass("qc-rdr"))
				window.location.href = jq.attr("href");
		});
	}

	jQuery(".js-change-page").on("afterSetupLimit", function () {
		var formJq = jQuery(this).closest("form");
		if (formJq.length === 0)
			return;
		formJq[0].submit();
	});
});

function gridSearch()
{
	var jq = jQuery(this);
	var formJq = jq.closest("form");
	if (formJq.length === 0)
		return;

	// reset paginator here
	formJq.find(".js-paginator .js-start-limit").val(0);
	formJq[0].submit();
}

function uniqid()
{
	var newDate = new Date;
	var partOne = newDate.getTime();
	var partTwo = 1 + Math.floor((Math.random() * 32767));
	var partThree = 1 + Math.floor((Math.random() * 32767));
	var id = partOne + '-' + partTwo + '-' + partThree;
	return id;
}


window.FILTER_VALIDATE_EMAIL = 274;
window.FILTER_VALIDATE_URL = 273;
window.FILTER_VALIDATE_INT = 275;
window.FILTER_VALIDATE_FLOAT = 276;
window.FILTER_VALIDATE_IP = 279;

function filter_var($value, $type)
{
	if ($type === FILTER_VALIDATE_EMAIL)
	{
		const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(String($value).toLowerCase());
		
		// return /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i.test($value);
		// return ("" + $value).replace(/[^a-zA-Z\d!#$%&'*+\-\/=?\^_`{|}~@.\[\]]/g, '');
	}
	else if ($type === FILTER_VALIDATE_URL)
	{
		// return /^(http(s)?:\/\/)?(www\.)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/.test($value);
		return ("" + $value).replace(/[^a-zA-Z\d$\-_.+!*'(),{}|\\\^~\[\]`<>#%";\/?:@&=]/g, '');
	}
	else if ($type === FILTER_VALIDATE_INT)
	{
		return /[0-9]/.test($value);
	}
	else if ($type === FILTER_VALIDATE_FLOAT)
	{
		return /([0-9]|\.)/.test($value);
	}
	else if ($type === FILTER_VALIDATE_IP)
	{
		return /^(?!0)(?!.*\.$)((1?\d?\d|25[0-5]|2[0-4]\d)(\.|$)){4}$/.test($value);
	}
}

function preg_match($test, $value)
{
	var regex = new RegExp($test);
	return regex.test($.trim($value));
}

function preg_replace($pattern, $replacement, $value)
{
	var $re = new RegExp($pattern);		
	return $value.replace($re, $replacement);
}

var initial_url = window.location.href;

function getBrowserName() {
	var name = "Unknown";
	if(navigator.userAgent.indexOf("MSIE") !== -1)
		name = "MSIE";
	else if(navigator.userAgent.indexOf("Firefox") !== -1)
		name = "Firefox";
	else if(navigator.userAgent.indexOf("Opera") !== -1)
		name = "Opera";
	else if(navigator.userAgent.indexOf("Chrome") !== -1)
		name = "Chrome";
	else if(navigator.userAgent.indexOf("Safari")!== -1)
		name = "Safari";
	return name;
}

if (getBrowserName() !== "Safari")
{
	window.onpopstate = function(event) {
		window.location.href = (event && event.state && event.state.url) ? event.state.url : initial_url;
	}
}

