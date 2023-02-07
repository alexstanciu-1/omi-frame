QExtendClass("Omi\\View\\DropDownTree", "Omi\\View\\DropDown", {
	
	Distancer : 25,

	initTreeData : function ($items)
	{
		var $itmsByParent = {};
		var $newItms = [];

		if (!$items)
			return [$newItms, $itmsByParent];

		for (var $i in $items)
		{
			var $itm = $items[$i];
			if ($itm.Parent)
			{
				if (!$itmsByParent[$itm.Parent.Id])
					$itmsByParent[$itm.Parent.Id] = [];
				$itmsByParent[$itm.Parent.Id].push($itm);
				
			}
			else
				$newItms.push($itm);
		}
		return [$newItms, $itmsByParent];
	}
});
