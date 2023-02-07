<?php

namespace Omi\View;


/**
 * @class.name Menu
 */
abstract class Menu_mods_view_ extends \QWebControl
{
	public function isSelected($url)
	{
		return false;
	}
}