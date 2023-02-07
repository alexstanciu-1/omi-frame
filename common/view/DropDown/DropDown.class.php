<?php

namespace Omi\View;


/**
 * @class.name DropDown
 */
abstract class DropDown_mods_view_ extends \QWebControl
{
	
	
	public $noItemCaption = "Select";

	public $queryFrom = null;
	public $querySelector = null;
	public $queryBinds = null;

	public $apiCall = null;

	/**
	 * @api.enable
	 * 
	 * @param string $from
	 * @param array $selector
	 * @param array $binds
	 */
	public static function GetRenderItems($from, $selector = null, $binds = null)
	{
		$cc = get_called_class();
		$dd = new $cc();
		if ((!$from) && $cc->queryFrom)
			$from = $cc->queryFrom;
		
		$items = null;
		
		# qvar_dumpk($binds);
		# throw new \Exception('ex');
		if (isset($binds['WHR_Search']) && is_string($binds['WHR_Search']))
		{
			$binds['WHR_Search'] = preg_replace("/(\\s+)/uis", "%", $binds['WHR_Search']);
		}
		
		if ($binds["@CALL"])
		{
			list($call_class_name, $call_method_name) = explode("::", $binds["@CALL"]);
			$class_reflection = $call_class_name ? \QModel::GetTypeByName($call_class_name) : null;
			$method_reflection = $class_reflection ? $class_reflection->methods[$call_method_name] : null;
			$api_enabled = $method_reflection ? $method_reflection->api["enable"] : false;
			
			if ($api_enabled)
				$items = $call_class_name::$call_method_name($from, $selector, $binds);
			else
				throw new \Exception("Access denied to: ".$binds["@CALL"]);
		}
		else if ($from !== null)
		{
			$items = \QApi::Query($from, $selector, $binds);
		}
		else if ($cc->apiCall)
		{
			$items = \QApi::Call($cc->apiCall, $selector, $binds);
		}
		
		$dd->renderItems($items, $from, $selector, $binds);
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $from
	 * @param array $selector
	 * @param array $binds
	 */
	public static function GetDataItems($from, $selector = null, $binds = null)
	{
		$cc = get_called_class();
		$dd = new $cc();
		if ((!$from) && $cc->queryFrom)
			$from = $cc->queryFrom;

		if ($from !== null)
			$items = \QApi::Query($from, $selector, $binds);
		else if ($cc->apiCall)
			$items = \QApi::Call($cc->apiCall, $selector, $binds);
		
		return $items;
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $from
	 * @param type $selector
	 * @param array $binds
	 */
	public function getRenderItems_Inst(string $from = null, $selector = null, array $binds = null)
	{
		if ((!$from) || (!trim($from)))
			$from = $this->queryFrom;
		if (($selector === null) || (is_string($selector) && (!trim($selector))))
			$selector = $this->querySelector;
		if ($binds === null)
			$binds = $this->queryBinds;
		if ($binds === null)
			$binds = [];
		
		if ($from !== null)
			$items = \QApi::Query($from, $selector, $binds);
		else if ($this->apiCall)
			$items = \QApi::Call($this->apiCall, $selector, $binds);
		$this->renderItems($items, $from, $selector, $binds);
	}
}