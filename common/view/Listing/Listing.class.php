<?php

namespace Omi\View;


/**
 * @class.name Listing
 */
abstract class Listing_omi_view_ extends \QWebControl
{
	
	
	public $rowsOnPage = 20;

	public static function InitPaginator($countResults, $rowsOnPage, $selectedPage, $inSidePages = 4)
	{
		if (!$inSidePages)
			$inSidePages = 4;
		// get number of pages
		$pages = ($countResults && $rowsOnPage) ? (($countResults % $rowsOnPage == 0) ? $countResults / $rowsOnPage : intval($countResults / $rowsOnPage) + 1) : null;

		$lwPage = $selectedPage - $inSidePages;
		$lwPage--;
		$upPage = $selectedPage + $inSidePages;
		$setted = false;
		while(!$setted)
		{
			if ($lwPage < 0)
			{
				$lwPage++;
				$upPage++;
				if ($upPage > $pages)
					$upPage--;
			}
			else if ($upPage > $pages)
			{
				$upPage--;
				$lwPage--;
				if ($lwPage < 0)
					$lwPage++;
			}
			else
				$setted = true;
		}
		return [$pages, $lwPage, $upPage];
	}
}