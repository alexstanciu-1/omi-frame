<?php

namespace Omi\View;


/**
 * Description of Select
 *
 * @author Mihaita
 * @class.name Select
 */
abstract class Select_mods_view_ extends \QWebControl
{
	
	/**
	 * @api.enable
	 * 
	 * @param string $from
	 * @param string|array $selector
	 * @param array $binds
	 */
	public static function GetItems($from, $selector = null, $binds = null, $captionMethod = null)
	{
		if ($binds && $binds["WHR_Search"] && is_string($binds["WHR_Search"]))
			$binds["WHR_Search"] = "%".trim(str_replace(" ", "%", $binds["WHR_Search"]))."%";

		//qvardump($from, $selector, $binds);
		//die();
		$items = \QApi::Query($from, $selector, $binds);
		if (!$items || (count($items) === 0))
			return ["results" => [["id" => null, "text" => "Nici o selectie"]], false];

		$ret = [];
		$ret["results"] = [];
		$ret["results"][] = ["id" => "default", "text" => "Nici o selectie"];
		foreach ($items as $itm)
		{
			$data = [];
			$data["id"] = $itm->getId();			
			$data["text"] = ($captionMethod && method_exists($itm, $captionMethod)) ? $itm->$captionMethod() : $itm->getModelCaption();
			$ret["results"][] = $data;
		}
		$ret["more"] = false;
		return $ret;
	}
}
