<?php

namespace Omi\App\View;

/**
 * @class.name Companies
 */
class Companies_mods_view_ extends Companies_backend_
{
	public $rowsOnPage = 200;
	
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
		return BASE_HREF.'Companies';
	}
	
	/**
	 * @param array $data 
	 * @param string $grid_mode
	 * @param string|integer $grid_id
	 */
	public function doSubmitData($data, $grid_mode, $grid_id = null)
	{		
		if (($grid_mode === 'add') || ($grid_mode === 'edit'))
		{
			if (!$data['Bank_Accounts'] || (count($data['Bank_Accounts']) < 1))
				throw new \Exception('Company must have a bank account!');
			
			if (!$data['Contact_Emails_List'] || (count($data['Contact_Emails_List']) < 1))
				throw new \Exception('Company must have an email!');
			
			if (!$data['Contact_Phones_List'] || (count($data['Contact_Phones_List']) < 1))
				throw new \Exception('Company must have a phone!');
			
			if (!$data['Contacts'] || (count($data['Contacts']) < 1))
				throw new \Exception('Company must have a contact!');
		}
		
		if ($data['Logo'] && $data['Logo']['_uploads'])
		{			
			if ($data['Logo']['_uploads']['Path'])
			{
				$ext = strtolower(pathinfo($data['Logo']['_uploads']['Path']['name'], PATHINFO_EXTENSION));

				if (defined('Q_IMAGE_EXTENSION_ALLOWED') && Q_IMAGE_EXTENSION_ALLOWED)
				{
					if (!in_array($ext, Q_IMAGE_EXTENSION_ALLOWED))
					{
						return (object)[
							$this->from => [[
								'Success' => false,
								'Error_Message' => 'Imaginea trebuie sa aiba una din extensiile: ' . implode(',', Q_IMAGE_EXTENSION_ALLOWED)
							]]
						];
					}
				}
				
				$imageSize = $data['Logo']['_uploads']['Path']['size'];
				
				if (defined('Q_UPLOAD_MAX_FILE_SIZE') && Q_UPLOAD_MAX_FILE_SIZE)
				{
					if ($imageSize > Q_UPLOAD_MAX_FILE_SIZE)
					{
						return (object)[
							$this->from => [[
								'Success' => false,
								'Error_Message' => 'Dimensiunea logo-ului trebuie sa fie de maxim ' . round(Q_UPLOAD_MAX_FILE_SIZE / 1024 / 1024, 1) . ' MB'
							]]
						];
					}
				}
			}
		}
		
		$ret = parent::doSubmitData($data, $grid_mode, $grid_id);
				
		return $ret;
	}
}
