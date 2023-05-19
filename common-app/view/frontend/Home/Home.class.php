<?php

namespace Omi\View;

/**
 * @class.name Home
 */
class Home_mods_view_ extends \QWebControl
{
	/**
	 * @api.enable
	 * 
	 * @param type $name
	 * @param type $value
	 */
	public static function Save_Account_Configuration($name, $value)
	{
		$accountConfigurations = \QApi::Query('Account_Configurations', 'Set_Company_Data, Create_Property, Set_Age_Intervals, Create_Occupancy, Create_Room, Create_Meal_Services, Set_Payment_Policy, Set_Cancellation_Policy, Create_Rate_Plan, Add_Rate_Set_Request, Active');
		if (!$accountConfigurations)
			throw new \Exception('Something went wrong!');
		
		$accountConfiguration = reset($accountConfigurations);
		
		$accountConfiguration->{$name} = $value;
		$accountConfiguration->save($name);
		
		return true;
	}
	
	/**
	 * @api.enable
	 * 
	 * @return boolean
	 * @throws \Exception
	 */
	public static function Disable_Account_Configuration()
	{
		$accountConfigurations = \QApi::Query('Account_Configurations', 'Set_Company_Data, Create_Property, Set_Age_Intervals, Create_Occupancy, Create_Room, Create_Meal_Services, Set_Payment_Policy, Set_Cancellation_Policy, Create_Rate_Plan, Add_Rate_Set_Request, Active');
		if (!$accountConfigurations)
			throw new \Exception('Something went wrong!');
		
		$accountConfiguration = reset($accountConfigurations);
		
		$accountConfiguration->Active = false;
		$accountConfiguration->save('Active');
		
		return true;
	}
}


