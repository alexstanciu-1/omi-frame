<?php

namespace Omi\View;


/**
 * @class.name DropDownTree
 */
abstract class DropDownTree_omi_view_ extends DropDown
{
	

	public $Distancer = 25;

	public function initTreeData($items, $sp = null, $parentProp = "Parent", $binds = [])
	{
		$itmsByParent = [];
		$newItms = [];
		$newItmsById = [];

		if (!$items || (count($items) === 0))
			return [$newItms, $itmsByParent];

		$itemsById = [];
		$parents = [];

		if ($binds["_reset_owner_parent_"])
		{
			$owner = \Omi\App::GetCurrentOwner();
			if ($owner)
			{
				foreach ($items ?: [] as $itm)
				{
					if (($itm->getId() === $owner->getId()) && (get_class($owner) === get_class($itm)) && $itm->{$parentProp})
						$itm->$parentProp = null;
				}
			}
		}

		foreach ($items ?: [] as $itm)
		{
			if ($itm->{$parentProp})
			{
				$parents[$itm->{$parentProp}->getId()] = $itm->{$parentProp};
				if (!isset($itmsByParent[$itm->{$parentProp}->getId()]))
					$itmsByParent[$itm->{$parentProp}->getId()] = [];
				$itmsByParent[$itm->{$parentProp}->getId()][] = $itm;
			}
			else
			{
				$newItms[] = $itm;
				$newItmsById[$itm->getId()] = $itm;
			}

			$itemsById[$itm->getId()] = $itm;
		}

		// adding here rest of parents
		foreach ($parents ?: [] as $pid => $parent)
		{
			if (!$newItmsById[$pid] && !$itemsById[$pid])
				$newItms[] = $parent;
		}

		return [$newItms, $itmsByParent];
	}
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
		
		if (!trim($binds["WHR_Search"]))
			unset($binds["WHR_Search"]);
		else
			$binds["WHR_Search"] = "%".trim(preg_replace("/(\\s+)/uis", "%", $binds["WHR_Search"]))."%";
		
		$items = \QApi::Query($from, $selector, $binds);
		
		$dd = new $cc();
		if (!$binds)
			$binds = [];
		$dd->renderItems($items, $from, $selector, $binds);
	}
}