<?php

namespace Omi\View;


/**
 * @class.name DropDownCustom
 */
abstract class DropDownCustom_omi_view_ extends \QWebControl
{
	
	
	public $Distancer = 25;
	
	public $noItemCaption = "Select";

	public function initTreeData($items, $sp = null, $useModels = false, $parentProp = "Parent")
	{
		$itmsByParent = [];
		$newItms = [];

		if (!$items || (count($items) === 0))
			return [$newItms, $itmsByParent];

		$itemsByValue = [];
		foreach ($items as $key => $itm)
		{
			$parent = $useModels ? $itm->{$parentProp} : $itm[$parentProp];
			if ($parent)
			{
				if (!isset($itmsByParent[$useModels ? $parent->getId() : $parent]))
					$itmsByParent[$useModels ? $parent->getId() : $parent] = [];
				$itmsByParent[$useModels ? $parent->getId() : $parent][] = $itm;
			}
			else
				$newItms[] = $itm;
			$itemsByValue[$useModels ? $itm->getId() : $key] = $itm;
		}

		if ($sp)
			$newItms = [$itemsByValue[$sp]];
		return [$newItms, $itmsByParent];
	}
}