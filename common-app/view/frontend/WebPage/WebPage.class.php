<?php

namespace Omi\View;

/**
 * @class.name WebPage
 */
class WebPage_mods_view_ extends \QWebPage
{
	public function canAccessMenu(string $view_tag)
	{
		# @TODO - this needs to be via security !
		return true;
	}
	
	public function findFirstGrid()
	{
		$grid_view = null;
		foreach ($this->children ?: [] as $child)
		{
			if ($child instanceof \Omi\View\Grid)
			{
				$grid_view = $child;
				break;
			}
		}
		
		return $grid_view;
	}
}

