<?php 

namespace Omi\App\View;

/**
 * @class.name User_Mail_Sender
 */
class User_Mail_Sender_mods_view_ extends User_Mail_Sender_backend_
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
		return true;
	}
	
	/**
	 * @param array $data 
	 * @param string $grid_mode
	 * @param string|integer $grid_id
	 */
	public function doSubmitData($data, $grid_mode, $grid_id = null)
	{
		$ret =  parent::doSubmitData($data, $grid_mode, $grid_id);
		
		if ($ret->Users && (($grid_mode == 'add') || ($grid_mode == 'edit')))
		{
			$user = reset($ret->Users);
			
			if (!$user)
				throw new \Exception('Missing user!');
			
			$mail_sender = $user->Mail_Sender;
			
			if (!$mail_sender)
				throw new \Exception('Missing mail sender!');
			
			$mail_sender->populate('Port, Encryption, FromAlias, Username, Password, Host, Connection_Active');
			
			ob_start();
			try
			{
				$smtp_email = \Omi\Util\Email::SMTPMail($mail_sender->Host, $mail_sender->Port, $mail_sender->Encryption, 
					$mail_sender->Username, $mail_sender->Password, $mail_sender->Username, 
					$mail_sender->Username, "Test email", "Testing email sending", null, null, null, 'UTF-8', 2);
				
				$str = ob_get_clean();
				
				if ($smtp_email)
				{
					$mail_sender->Connection_Active = true;
					$mail_sender->db_save('Connection_Active');
				}
				else
				{
					$mail_sender->Connection_Active = false;
					$mail_sender->db_save('Connection_Active');	
				}
			}
			catch (\Exception $ex)
			{
				$str = ob_get_clean();
			}

			if ($ex)
			{
				$mail_sender->Connection_Active = false;
				$mail_sender->db_save('Connection_Active');
				
				throw new \Exception($str . " | " . $ex->getMessage());
			}
		}
		
		return $ret;
	}
}
