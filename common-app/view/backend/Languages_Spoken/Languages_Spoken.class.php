<?php

namespace Omi\App\View;

/**
 * @class.name Languages_Spoken
 */
class Languages_Spoken_mods_view_ extends Languages_Spoken_backend_
{
	/**
	 * Redirect after save
	 * 
	 * @param \Omi\View\Grid $grid
	 * @param type $data
	 * @param type $grid_data
	 * 
	 * @return boolean
	 */
	public static function stay_on_page_after_save(\Omi\View\Grid $grid = null, $data = null, $grid_data = null, $model = null)
	{
		return BASE_HREF.'Languages_Spoken';
	}
}