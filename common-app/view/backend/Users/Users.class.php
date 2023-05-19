<?php 

namespace Omi\App\View; 

/**
 * @class.name Users
 */
class Users_mods_view_ extends Users_backend_
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
		return BASE_HREF.'Users';
	}
	
	/**
	 * @param array $data 
	 * @param string $grid_mode
	 * @param string|integer $grid_id
	 */
	public function doSubmitData($data, $grid_mode, $grid_id = null)
	{
		// set user's name		
        // $data['Name'] = $data['Person']['Firstname'] . ' ' . $data['Person']['Name'];
		
		if (isset($data['Person']['Email']))
			$data['Email'] = $data['Person']['Email'];
		if (isset($data['Person']['Phone']))
			$data['Phone'] = $data['Person']['Phone'];
		
		if (isset($data['Api_Key']) && (strlen(trim((string)$data['Api_Key'])) < 40) && (strlen(trim((string)$data['Api_Key'])) > 60))
			throw new \Exception('The Api_Key must be at least 40 characters long and no more than 60!');
		
		$ret = parent::doSubmitData($data, $grid_mode, $grid_id);
		
		$user = \Omi\User::GetCurrentUser();
		
		if (($user->Type != "H2B_Superadmin") && ($grid_mode == 'add'))
		{
			$mailSender = new \stdClass();
			$mailSender->Host = APP_DEFAULT_MAIL_ACCOUNT['Host'];
			$mailSender->Port = APP_DEFAULT_MAIL_ACCOUNT['Port'];
			$mailSender->Username = APP_DEFAULT_MAIL_ACCOUNT['Username'];
			$mailSender->Password = APP_DEFAULT_MAIL_ACCOUNT['Password'];
			$mailSender->Encryption = APP_DEFAULT_MAIL_ACCOUNT['Encryption'];

			$message = '
				<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
						<title>A fost creat un nou utilizator</title>
						<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
					</head>
					<body style="margin: 0; padding: 0;">
						<div style="padding: 20px 20px 20px 20px; font-family:Arial, \'Helvetica Neue\', Helvetica, sans-serif; line-height: 1.2;">
							Salut,<br /><br />
							A fost creat un nou utilizator pe compania ' . $user->Owner->Name . '. Utilizatorul cu numele ' . $data['Person']['Name'] . ' ' . $data['Person']['Firstname'] . ' si adresa de email ' . $data['Email'] . '
							<br /><br />
							Cu drag,<br />
							Echipa H2B <br /><br />
							<img style="width: 150px;" src="' . \QWebRequest::GetBaseUrl() . 'code/res/main/images/logo.png" />
						<div>
					</body>
				</html>';

			\Omi\Util\Email::Send($mailSender, 'support@h2b.ro', 'A fost creat un nou utilizator', $message);
		}
		
		return $ret;
	}
}