<?php

namespace Omi\App\View;

/**
 * @class.name MyAccount
 */
class MyAccount_mods_view_ extends MyAccount_backend_
{
	/**
	 * @param array $data 
	 * @param string $grid_mode
	 * @param string|integer $grid_id
	 */
	public function doSubmitData($data, $grid_mode, $grid_id = null)
	{
		// set user's name
		if ($data['Person'])
		{
			// $data['Name'] = $data['Person']['Firstname'] . ' ' . $data['Person']['Name'];
			
			if (isset($data['Person']['Email']))
				$data['Email'] = $data['Person']['Email'];
			
			if (isset($data['Person']['Phone']))
				$data['Phone'] = $data['Person']['Phone'];
		}
		
		return parent::doSubmitData($data, $grid_mode, $grid_id);
	}
	
	/**
	 * Setups the grid based on the input parameters
	 * 
	 * @param string $grid_mode
	 * @param scalar $id
	 * @param (scalar|array)[] $bind_params
	 */
	public function setupGrid($grid_mode, $id = null, $bind_params = null)
	{		
		if ($grid_mode === 'list')
		{
			$user = \QApi::Call('\Omi\User::GetCurrentUser');
			$baseUrl = \QWebRequest::GetBaseUrl();
			
			if ($user)
				header("Location: " . $baseUrl . $this->getUrlForTag() . "/edit/" . $user->Id);
			else
				header("Location: " . $baseUrl . $this->getUrlForTag() . "/add");
			
			die;
		}
		else
			return parent::setupGrid($grid_mode, $id, $bind_params);
	}
}
