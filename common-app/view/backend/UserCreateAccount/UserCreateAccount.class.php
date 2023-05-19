<?php

namespace Omi\App\View;

/**
 * @class.name UserCreateAccount
 */
class UserCreateAccount_mods_view_ extends UserCreateAccount_backend_
{
	public $renderMethod = "renderForm";
	public $processAction = 'ajax';
	
	/**
	 * Setups the grid based on the input parameters
	 * 
	 * @param string $grid_mode
	 * @param scalar $id
	 * @param (scalar|array)[] $bind_params
	 */
	public function setupGrid($grid_mode, $id = null, $bind_params = null)
	{		
		if ($_GET['activation_code'])
		{
			$accountConfirmed = static::ConfirmAccount($_GET['activation_code']);
			
			if ($accountConfirmed)
				$this->setRenderMethod("renderActivation");

			return true;			
		}
		else
		{
			$grid_mode = 'add';

			$ret = parent::setupGrid($grid_mode, $id, $bind_params);
			$this->grid_mode = 'add';
			return $ret;
		}
	}
	
	/**
	 * @param array $data 
	 * @param string $grid_mode
	 * @param string|integer $grid_id
	 */
	public function doSubmitData($data, $grid_mode, $grid_id = null)
	{
		if (!\Omi\App::IsHuman($data['g-recaptcha-response']))
			throw new \Exception(_T(249, 'Captcha not verified'));
		
		if (!$data['Terms'])
			throw new \Exception(_T(246, 'Must accept terms and conditions'));
		
		if (!$data['Terms_Privacy'])
			throw new \Exception(_T(247, 'Must accept privacy policy'));
		
		// check if company exists
		if (!$data['Company']['VAT_No'])
			throw new \Exception(_T(248, 'Missing company vat no'));
		
		if (!$data['Company']['Owner_Type'])
			throw new \Exception('Trebuie sa alegeti daca sunteti agentie de turism sau unitate de cazare!');
		
		// if (!$data['Company']['Is_Property_Owner'])
		// 	throw new \Exception('Company must be either property owner or channel owner!');
		
		$accept_terms_only = false;
		
		if ($data['User']['Id'] && $data['Company']['Id'])
		{
			$company = \QApi::QueryById('Companies', $data['Company']['Id']);
			if (!$company)
				throw new \Exception(_T(250, 'Company not found'));
			
			$company->Terms_Accepted = true;
			$company->Terms_Accepted_IP = $_SERVER['REMOTE_ADDR'];
			$company->Terms_Accepted_Date = date('Y-m-d H:i:s');
	
			$company->save('Terms_Accepted, Terms_Accepted_IP, Terms_Accepted_Date');
			$this->redirect_to_url = \QWebRequest::GetBaseHref();
			
			$accept_terms_only = true;
		}
		
		if (!$accept_terms_only)
		{
			if ((!isset($data['Company']['Address']['Country']['Name'])) || 
				(!isset($data['Company']['Address']['City']['Name'])))
			{
				throw new \Exception(_T(297, 'Invalid address'));
			}
			
			$company = QQuery('Companies.{Name WHERE VAT_No=? LIMIT 1}', [$data['Company']['VAT_No']])->Companies[0];
			$registrationRequestCompany = QQuery('Registration_Requests.{Company.{Name WHERE VAT_No=?} LIMIT 1}', [$data['Company']['VAT_No']])->Registration_Requests[0];
			if ($company || $registrationRequestCompany)
				throw new \Exception(_T(251, 'Company already exists'));

			// if user exists
			if (!$data['User']['Person']['Email'])
				throw new \Exception(_T(252, 'Missing user email'));

			$user = QQuery('Users.{Username, Email WHERE Email=? OR Username=? LIMIT 1}', [$data['User']['Person']['Email'], $data['User']['Person']['Email']])->Users[0];
			$registrationRequestUser = QQuery('Registration_Requests.{User.{Email, Username WHERE Email=? OR Username=?} LIMIT 1}', [$data['User']['Person']['Email'], $data['User']['Person']['Email']])->Registration_Requests[0];
			if ($user || $registrationRequestUser)
				throw new \Exception(_T(253, 'User already exists'));

			// qvar_dumpk($user, $registrationRequest, $data['User']['Person']['Email']); die;
			if (isset($data['User']['Person']['Email']))
			{
				$data['User']['Email'] = $data['User']['Person']['Email'];
				$data['User']['Username'] = $data['User']['Person']['Email'];
			}

			if (isset($data['User']['Person']['Phone']))
				$data['User']['Phone'] = $data['User']['Person']['Phone'];

			# do not activate the login yet
			$data['User']['Active'] = 0;

			# do not do this here !!! I will write something to accept them 
			# $data['User']['Owner'] = $data['Company'];

			if ($data['Company']['Owner_Type'] == 'property_owner')
			{
				$data['User']['Type'] = 'H2B_Property';
				$data['Company']['Is_Property_Owner'] = true;
				$data['Company']['Is_Channel_Owner'] = false;
			}
			else if ($data['Company']['Owner_Type'] == 'channel_owner')
			{
				$data['User']['Type'] = 'H2B_Channel';
				$data['Company']['Is_Channel_Owner'] = true;
				$data['Company']['Is_Property_Owner'] = false;
			}

			if (!preg_match("/(?=.*[A-Z]{1,})(?=.*[0-9]{1,})(?=.*[a-z]{1,})(?=.*(\W){1,})/", $data['User']['Password']))
				throw new \Exception(_T(254, 'Password format is not correct'));

			$data['User']['Password'] = md5($data['User']['Password']);
			$data['User']['ActivationCode'] = uniqid("", true);
			$data['Company']['Terms_Accepted'] = true;
			
			$data['User']['Api_Key'] = static::GenerateApiKey();
					
			$data['IP'] = $data['Company']['Terms_Accepted_IP'] = $_SERVER['REMOTE_ADDR'];
			$data['Date'] = $data['Company']['Terms_Accepted_Date'] = date('Y-m-d H:i:s');
		}
		
		$ret = parent::doSubmitData($data, $grid_mode, $grid_id);
		
		if ($ret && $ret->Registration_Requests)
		{
			$registration_request = reset($ret->Registration_Requests);
			
			if ($registration_request->Company)
			{
				$properties = \QQuery('Properties.{Name, Enable_All_Channels}')->Properties;
		
				foreach ($properties as $property)
				{
					if ($property->Enable_All_Channels)
					{
						$property_contract = new \Omi\TFH\Channel_Contract();

						$property_contract->setChannel($registration_request->Company);
						$property_contract->setEnable_Channel(true);

						$property->Channel_Contracts = new \QModelArray();
						$property->Channel_Contracts[] = $property_contract;

						$property->save('Channel_Contracts.{Channel.Name, Enable_Channel}');
					}
				}
			}
		}
		
		if (!$accept_terms_only)
		{
			$mailSender = new \stdClass();
			$mailSender->Host = APP_DEFAULT_MAIL_ACCOUNT['Host'];
			$mailSender->Port = APP_DEFAULT_MAIL_ACCOUNT['Port'];
			$mailSender->Username = APP_DEFAULT_MAIL_ACCOUNT['Username'];
			$mailSender->Password = APP_DEFAULT_MAIL_ACCOUNT['Password'];
			$mailSender->Encryption = APP_DEFAULT_MAIL_ACCOUNT['Encryption'];

			$message = 'Salut,<br /><br />
				Contul t&#259;u  pe platforma H2B a fost creat cu succes. Te rug&#259;m s&#259; confirmi contul <a href="' . \QWebRequest::GetBaseUrl() . 'create-account/?activation_code=' . $data['User']['ActivationCode'] . '">aici</a>.
				<br /><br />
				Dac&#259; nu ai creat tu acest cont te rug&#259;m sa ignori acest email.
				<br /><br />
				Cu drag,<br />
				Echipa H2B';

			\Omi\Util\Email::Send($mailSender, $data['User']['Email'], "Confirmare cont nou " . (($data['User']['Type'] == 'H2B_Property') ? 'unitate de cazare' : 'agentie de turism') . " platforma H2B", $message);

			$messageSupport = 'Salut,<br /><br />'
				. 'Un cont nou ' . (($data['User']['Type'] == 'H2B_Property') ? 'unitate de cazare' : 'agen&#539;ie de turism') . ' a fost creat cu succces pe platforma H2B.'
				. '<br /><br />'
				. 'Datele contului: <br /><br />'
				. '<strong>Denumire firma: </strong>' . $data['Company']['Name'] . '<br />'
				. '<strong>Cod Unic de Inregistrare: </strong>' . $data['Company']['VAT_No'] . '<br />'
				. '<strong>Nr Registrul Comertului: </strong>' . $data['Company']['Reg_No'] . '<br />'
				. '<strong>Adresa: </strong>' . $data['Company']['Address']['Street'] . ' ' . $data['Company']['Address']['StreetNumber'] . ', ' . $data['Company']['Address']['City']['Name'] . ', ' . $data['Company']['Address']['Country']['Name'] . ', ' . $data['Company']['Address']['PostCode'] . '<br />'
				. '<strong>Nume utilizator: </strong>' . $data['User']['Person']['Firstname'] . ' ' . $data['User']['Person']['Name'] . '<br />'
				. '<strong>Email utilizator: </strong>' . $data['User']['Email']
				. '<br /><br />'
				. 'Zi faina!';

			\Omi\Util\Email::Send($mailSender, 'support@h2b.ro', "Cont nou " . (($data['User']['Type'] == 'H2B_Property') ? 'unitate de cazare' : 'agen&#539;ie de turism') . " platforma H2B", $messageSupport);		
		}
		
		return $ret;
	}
	
	/**
	 * Confirm account
	 * 
	 * @param type $activationCode
	 * 
	 * @return boolean
	 * 
	 * @throws \Exception
	 */
	public function ConfirmAccount($activationCode)
	{
		// exception on no activation code
		if (!$activationCode)
			throw new \Exception(_T(255, 'Missing activation code'));
		
		$registration = \QQuery('Registration_Requests.{User.{ActivationCode WHERE ActivationCode=? LIMIT 1}}', [$activationCode])->Registration_Requests[0];
		
		if ($registration->User && !$registration->User->Confirmed_Activation)
		{
			$registration->User->Confirmed_Activation = true;
			
			$registration->save('User.{Confirmed_Activation}');
			
			return true;
		}
		
		// not good
		return false;
	}
	
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
		if (is_string($grid->redirect_to_url))
			return $grid->redirect_to_url;
		
		return parent::stay_on_page_after_save($grid, $data, $grid_data);
	}
	
	/**
	 * @api.enable
	 */
	public static function TermsContentPopup()
	{
		ob_start();
		
		$termsClass = new \Omi\TFS\View\TermsAndConditions();
		$termsClass->render();
		
		$ret = ob_get_clean();
		
		return $ret;
	}
	
	/**
	 * @api.enable
	 */
	public static function PolicyContentPopup()
	{
		ob_start();
		
		$policyClass = new \Omi\TFS\View\PrivacyPolicy();
		$policyClass->render();
		
		$ret = ob_get_clean();
		
		return $ret;
	}
	
	/**
	 * Generate a pass key
	 * 
	 * @param type $charactersNumber
	 * @return type
	 */
	public static function GenerateApiKey($charactersNumber = 40)
	{
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache

		for ($i = 0; $i < $charactersNumber; $i++) 
		{
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		
		$passKey = implode($pass);
		
		$registrationRequestUserApiKey = QQuery('Registration_Requests.{User.{Api_Key WHERE Api_Key=?} LIMIT 1}', [$passKey])->Registration_Requests[0];
		$users = QQuery('Users.{Api_Key WHERE Api_Key=? LIMIT 1}', [$passKey])->Users[0];
		
		if ($registrationRequestUserApiKey || $users)
			return static::GenerateApiKey();
		
		return $passKey;
	}
}

