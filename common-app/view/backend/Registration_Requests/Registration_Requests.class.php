<?php 

namespace Omi\App\View; 

/**
 * @class.name Registration_Requests
 */
class Registration_Requests_mods_view_ extends Registration_Requests_backend_
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
		if ($data['User'] && $data['User']['Username'])
		{
			$users = \QQuery('Users.{Email WHERE Username=?}', [$data['User']['Username']])->Users;
			
			if ($users)
			{
				$user = reset($users);
				
				if ($data['User']['Id'])
				{					
					if ($user->Id != $data['User']['Id'])
						throw new \Exception('Username already exists!');
				}
				else if ($user->Id)
					throw new \Exception('Username already exists!');
					
			}
		}
		
		$ret = parent::doSubmitData($data, $grid_mode, $grid_id);
		
		if ($ret->Registration_Requests && (($grid_mode == 'add') || ($grid_mode == 'edit')))
		{
			$registration_request = reset($ret->Registration_Requests);
			
			if ($registration_request->User)
			{
				$user = $registration_request->User;
				
				$user->Email = $user->Username;
				$user->populate('Email, Phone, Person.{Email, Phone}');
				$user->Person->Email = $user->Email;
				$user->Person->Phone = $user->Phone;
				
				$user->db_save('Email, Person.{Email, Phone}');
			}
		}
		
		return $ret;
	}
	
	/**
	 * @api.enable
	 * 
	 * @param type $registrationId
	 */
	public static function ActivateAccount($registrationId)
	{
		if (!$registrationId)
			throw new \Exception('Missing registration id!');
		
		$registrationRequest = \QApi::QueryById('Registration_Requests', $registrationId, 'Company.*, User.*');
		
		$registrationRequest->User->Active = true;
		$registrationRequest->User->Owner = $registrationRequest->Company;
		
		$app = \QApp::NewData();
		$app->Companies = new \QModelArray();
		$app->Companies[] = $registrationRequest->Company;
		
		$app->Users = new \QModelArray();
		$app->Users[] = $registrationRequest->User;
		
		$app->save('Companies, Users.{Active, Owner}', null, null, false, false, false, false);
		
		$mailSender = new \stdClass();
		$mailSender->Host = APP_DEFAULT_MAIL_ACCOUNT['Host'];
		$mailSender->Port = APP_DEFAULT_MAIL_ACCOUNT['Port'];
		$mailSender->Username = APP_DEFAULT_MAIL_ACCOUNT['Username'];
		$mailSender->Password = APP_DEFAULT_MAIL_ACCOUNT['Password'];
		$mailSender->Encryption = APP_DEFAULT_MAIL_ACCOUNT['Encryption'];
		
		$message = 'Salut,<br /><br />
			Contul t&#259;u  pe platforma H2B a fost activat cu succes.
			<br /><br />
			Pentru autentificare poti accesul link-ul <a href="https://portal.h2b.ro">portal.h2b.ro</a>
			Pentru orice informa&#539;ii referitoare la set&#259;rile contului ne po&#539;i contacta la adresa <a href="mailto: support@h2b.ro">support@h2b.ro</a> sau la numerele de telefon de pe pagina noastr&#259; de contact: <a href="www.h2b.ro/contact">www.h2b.ro/contact</a>.
			<br /><br />
			Cu drag,<br />
			Echipa H2B';
		
		\Omi\Util\Email::Send($mailSender, $registrationRequest->User->Email, "Activare cont " . (($registrationRequest->User->Type == 'H2B_Property') ? 'unitate de cazare' : 'agen&#539;ie de turism') . " platforma H2B", $message);
		
		return true;
	}
	
	/**
	 * @api.enable
	 * 
	 * @param type $registrationId
	 */
	public static function SendConfirmationEmail($registrationId)
	{
		if (!$registrationId)
			throw new \Exception('Missing registration id!');
		
		$registrationRequest = \QApi::QueryById('Registration_Requests', $registrationId, 'Company.*, User.*');
		
		if (!$registrationRequest->User->Email)
			throw new \Exception('Adresa de email este obligatorie!');
		
		$registrationRequest->User->ActivationCode = uniqid("", true);
		
		$app = \QApp::NewData();
		$app->Users = new \QModelArray();
		$app->Users[] = $registrationRequest->User;
		
		$app->save('Users.{ActivationCode}', null, null, false, false, false, false);
		
		$mailSender = new \stdClass();
		$mailSender->Host = APP_DEFAULT_MAIL_ACCOUNT['Host'];
		$mailSender->Port = APP_DEFAULT_MAIL_ACCOUNT['Port'];
		$mailSender->Username = APP_DEFAULT_MAIL_ACCOUNT['Username'];
		$mailSender->Password = APP_DEFAULT_MAIL_ACCOUNT['Password'];
		$mailSender->Encryption = APP_DEFAULT_MAIL_ACCOUNT['Encryption'];
		
		$message = 'Salut,<br /><br />
			Contul t&#259;u  pe platforma H2B a fost creat cu succes. Te rug&#259;m s&#259; confirmi contul <a href="' . \QWebRequest::GetBaseUrl() . 'create-account/?activation_code=' . $registrationRequest->User->ActivationCode . '">aici</a>.
			<br /><br />
			Dac&#259; nu ai creat tu acest cont te rug&#259;m sa ignori acest email.
			<br /><br />
			Cu drag,<br />
			Echipa H2B';
		
		\Omi\Util\Email::Send($mailSender, $registrationRequest->User->Email, "Confirmare cont nou " . (($registrationRequest->User->Type == 'H2B_Property') ? 'unitate de cazare' : 'agentie de turism') . " platforma H2B", $message);
		
		return true;
	}
}
