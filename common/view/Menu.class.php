<?php

namespace Omi\View;


/**
 * @class.name Menu
 */
abstract class Menu_omi_view_ extends \QWebControl
{
	public function isSelected($url)
	{
		return false;
	}
}