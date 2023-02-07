

/** Begin :: Generated function: Omi\View\DropDown::renderItem **/
QExtendClass("Omi\\View\\DropDown", "QWebControl", {
renderItem: function($items)
{
	var $_QOUT = "";

$_QOUT += "<virtual\
	jsFunc=\"renderItem($items = null)\" \
	q-args=\"$item = null, $binds = null\">\
	" + ( $item.getModelCaption($binds ? $binds["__caption_tag"] : null) ) + "</virtual>";
		return $_QOUT;
}});

/** End :: Generated function: Omi\View\DropDown::renderItem **/



/** Begin :: Generated function: Omi\View\DropDown::renderItems **/
QExtendClass("Omi\\View\\DropDown", "QWebControl", {
renderItems: function($items)
{
	var $_QOUT = "";

$_QOUT += "<div jsFunc=\"renderItems($items = null)\" q-args=\"$items = null, $from = null, $selector = null, $binds = null\">\
	";	var $_expr_v = $binds['_props_'] || [] ;
var $_isArr_v = Array.isArray($_expr_v);
if (($_expr_v._ty === 'QModelArray') && ($_expr_v.__len__ === undefined))
	$_expr_v = $_expr_v._items;
for (var $k in $_expr_v)
{

		if (($_isArr_v && (!(($k >=0) && ($k < $_expr_v.length)))) || ((!$_isArr_v) && ($k.charAt(0) === '_')))
			continue;
		$v = $_expr_v[$k];
this.$k = $v;
}

	$_QOUT += "	";if(!$binds || !$binds['hideNoneOption']) { $_QOUT += "		<div class=\"qc-dd-item qc-dd-reset-item\">" + ( htmlspecialchars($binds['noneOptionCaption'] || _L('None'), ENT_QUOTES | ENT_HTML5, 'UTF-8') ) + "</div>\
	";} $_QOUT += "	";if($items) { $_QOUT += "		";var $_expr_item = $items ;
var $_isArr_item = Array.isArray($_expr_item);
if (($_expr_item._ty === 'QModelArray') && ($_expr_item.__len__ === undefined))
	$_expr_item = $_expr_item._items;
for (var $_key_item in $_expr_item)
{

		if (($_isArr_item && (!(($_key_item >=0) && ($_key_item < $_expr_item.length)))) || ((!$_isArr_item) && ($_key_item.charAt(0) === '_')))
			continue;
		$item = $_expr_item[$_key_item];
 $_QOUT += "			";if(this._call_renderItem) { $_QOUT += "				";call_user_func(this._call_renderItem, this, $item, $binds); $_QOUT += "			";} else { $_QOUT += "				";this.RenderS(((($this !== undefined) && ($this !== null))) ? this : null, "item", $item, $binds); $_QOUT += "			";} $_QOUT += "		";}
 ; $_QOUT += "	";} $_QOUT += "</div>";
		return $_QOUT;
}});

/** End :: Generated function: Omi\View\DropDown::renderItems **/



/** Begin :: Generated function: Omi\View\DropDown::render **/
QExtendClass("Omi\\View\\DropDown", "QWebControl", {
render: function($from, $selector, $binds, $caption, $full_data, $input_id_name, $input_id_default, $input_type_name, $input_type_default, $input_name_name, $inputs_extra_class, $input_data_name, $input_data_default, $picker_prop)
{
	var $_QOUT = "";

$_QOUT += "<div class=\"qc-dd omi-control js-dd q-hide-on-click-away" + ( ($picker_name ? ' qc-with-picker-dd' : '') + "" + ($cssClass ? " "+ "" +trim($cssClass) : "") ) + " QWebControl\" \
	 jsFunc=\"render($from, $selector, $binds, $caption, $full_data, $input_id_name, $input_id_default, $input_type_name, \
		$input_type_default, $input_name_name, $inputs_extra_class, $input_data_name, $input_data_default, $picker_prop)\" \
	 q-args=\"$from = null, $selector = null, $binds = null, $caption = null, $full_data = null, $input_id_name = null, $input_id_default = null, \
		$input_type_name = null, $input_type_default = null, $input_name_name = \'name\', $inputs_extra_class = null, $attrs = null, $picker_name = null, $cssClass = null, $picker_placeholder = null\"\
	" + ( ($attrs ? " " + "" + $attrs : "") ) + " qCtrl=\"" + ( this.getQCtrl_Attr() ) + "\" q-dyn-parent=\"" + ( get_class(this.parent) ) + "\" q-dyn-inst=\"" + ( this.dynamic_name ) + "\">\
	\
	<input class=\"qc-dd-from\" type=\"hidden\" value=\"" + ( ((($from !== undefined) && ($from !== null))) ? htmlspecialchars($from, ENT_QUOTES | ENT_HTML5, 'UTF-8') : "" ) + "\" />\
	<input class=\"qc-dd-selector\" type=\"hidden\" value=\"" + ( ((($selector !== undefined) && ($selector !== null))) ? htmlspecialchars($selector, ENT_QUOTES | ENT_HTML5, 'UTF-8') : "" ) + "\" />\
	\
	";		if (is_array($binds['_props_']))
		{
			var $_expr_v = $binds['_props_'] ;
var $_isArr_v = Array.isArray($_expr_v);
if (($_expr_v._ty === 'QModelArray') && ($_expr_v.__len__ === undefined))
	$_expr_v = $_expr_v._items;
for (var $k in $_expr_v)
{

		if (($_isArr_v && (!(($k >=0) && ($k < $_expr_v.length)))) || ((!$_isArr_v) && ($k.charAt(0) === '_')))
			continue;
		$v = $_expr_v[$k];
this.$k = $v;
}

		}
		
		$noItemCaption = $binds['noItemCaption'];
	$_QOUT += "\
	";if($binds) { $_QOUT += "		<input class=\"qc-dd-binds\" type=\"hidden\" value=\"" + ( htmlspecialchars(json_encode($binds), ENT_QUOTES | ENT_HTML5, 'UTF-8') ) + "\" />\
	";} $_QOUT += "\
	";$input_name_name = $input_name_name || "name"; $_QOUT += "	";if($input_id_name) { $_QOUT += "		<input type=\"hidden\" class=\"qc-dd-input-id" + ( $inputs_extra_class ? " " + "" + $inputs_extra_class : "" ) + "\" " + ( $input_name_name ) + "=\"" + ( ((($input_id_name !== undefined) && ($input_id_name !== null))) ? htmlspecialchars($input_id_name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : "" ) + "\" value=\"" + ( ((($input_id_default !== undefined) && ($input_id_default !== null))) ? htmlspecialchars($input_id_default, ENT_QUOTES | ENT_HTML5, 'UTF-8') : "" ) + "\" />\
	";} $_QOUT += "\
	";if($input_type_name) { $_QOUT += "		<input type=\"hidden\" class=\"qc-dd-input-ty" + ( $inputs_extra_class ? " " + "" + $inputs_extra_class : "" ) + "\" " + ( $input_name_name ) + "=\"" + ( ((($input_type_name !== undefined) && ($input_type_name !== null))) ? htmlspecialchars($input_type_name, ENT_QUOTES | ENT_HTML5, 'UTF-8') : "" ) + "\" value=\"" + ( ((($input_type_default !== undefined) && ($input_type_default !== null))) ? htmlspecialchars($input_type_default, ENT_QUOTES | ENT_HTML5, 'UTF-8') : "" ) + "\" />\
	";} $_QOUT += "\
	<input class=\"qc-dd-full-data\" type=\"hidden\" value=\"" + ( ((($full_data !== undefined) && ($full_data !== null))) ? htmlspecialchars($full_data, ENT_QUOTES | ENT_HTML5, 'UTF-8') : '' ) + "\" />\
\
	";		if ($picker_name)
		{
			$_QOUT += "<input type=\"text\" name-x=\"" + ( $picker_name ) + "\" class=\"qc-dd-pick" + ( $inputs_extra_class ? " " + "" + $inputs_extra_class : "" ) + "\" \
				   value=\"" + ( ($caption && ($caption != this.noItemCaption)) ? $caption : '' ) + "\" placeholder=\"" + ( $picker_placeholder || '' ) + "\" />";		}
		else
		{
			$_QOUT += "<div class=\"qc-dd-pick\">" + ( _L($caption ? (($caption === 'Select') ? $noItemCaption : $caption) : "Select") ) + "</div>";		}
	$_QOUT += "	\
	<div class=\"qc-dd-box q-hide-on-click-away-container\">\
		<div class=\"qc-dd-search\"><input type=\"text\" value=\"\" placeholder=\"search\" /></div>\
		<div class=\"qc-dd-items\">\
\
		</div>\
	</div>\
</div>";
		return $_QOUT;
}});

/** End :: Generated function: Omi\View\DropDown::render **/

