<?php

namespace Omi;

/**
 * @author Alex
 * @storage.table $Users
 *
 * @model.captionProperties Person.{Name, Firstname}
 *
 * @patch.rename GetModelEntity before_nuvia__GetModelEntity
 *	GetListingEntity before_nuvia__GetListingEntity
 *  LoadInfo before_nuvia__LoadInfo
 *
 * @class.name User
 */
abstract class User_mods_model_ extends \QUser
{
	use \Omi\Owner_Trait;
	
	const LOGIN_INVALID_USER_OR_PASSWORD = -1;
	const LOGIN_INVALID_SESSION = -2;
	const LOGIN_DISABLED = -3;
	const LOGIN_BANNED = -4;
	const RECOVER_PASSWORD_USER_NOT_FOUND = -5;

		
	protected static $Load_Info_Entity = "User.{"
		. "*, "
		. "Person.{"
			. "Firstname, "
			. "Name, "
			. "Email, "
			. "Phone"
		. "}, "
		. "Owner.{"
			. "Name, "
			. "Terms_Accepted, "
			. "VAT_No, "
			. "Reg_No, "
			. "Address.{"
				. "City.Name, "
				. "Country.Name,"
				. "StreetNumber,"
				. "Street, "
				. "PostCode"
			. "}, "
		. "}"
	. "}";
	
	public static $RegistrationFields = "Email, Username, Password, ActivationCode, Name, Person.{Name, Firstname, Phone, Address.City.Name}";
	public static $RegistrationSubject = "Registration email";
	public static $RegistrationEmailFrom = null;
	public static $RegistrationEmailBody = null;

	public static $_FullInfo = null;
	public static $ActiveLogins = [];

	protected static $BanAfterTries_Username = 8;
	
	protected static $BanAfterTries_IP = 25;
	
	protected static $_CurrentUser = null;

	protected static $FromMailData = null;
	
	protected static $LoginExtraWhere = '';
	
	protected static $Temporary_Session = null;
	
	protected static $InInfoRequest = false;
	
	/**
	 * @storage.index
	 * @var boolean
	 */
	protected $BackendAccess;
	/**
	 * @storage.index
	 * @var boolean
	 */
	protected $IsDefault;
	/**
	 * @storage.index
	 * @var string
	 */
	protected $Phone;
	/**
	 * @var boolean
	 * @storage.index
	 */
	protected $Active;
	/**
	 * @storage.index unique
	 * @var string
	 * 
	 * @fixValue trim
	 */
	protected $ActivationCode;
	/**
	 * @var string
	 * @storage.index
	 * 
	 * @fixValue trim
	 */
	protected $PasswordRecoveryCode;
	/**
	 * @var string 
	 * @storage.index
	 */
	protected $PrevPwd;
	/**
	 * @storage.optionsPool Contacts
	 * 
	 * @var Person
	 */
	protected $Person;
	/**
	 * @storage.index
	 * @var boolean
	 */
	protected $LoggedToSystem;
	/**
	 * @var boolean
	 */
	protected $IsImportUser;
	/**
	 * @storage.index unique
	 * @validation mandatory
	 * 
	 * @var string
	 */
	protected $Username;
	/**
	 * @var boolean
	 */
	protected $IsRemoteCallUser;
	/**
	 * @var boolean
	 */
	protected $IsLegalRepresentative;
	/** 
	 * @var Comm\Company[]
	 */
	protected $Access;
	/**
	 * @storage.info The API Key must be 40 characters long
	 * 
	 * @var string
	 */
	protected $Api_Key;
	/**
	 * @storage.optionsPool Companies
	 * 
	 * @var Comm\Company
	 */
	protected $Owner;
	
	/**
	 * @storage.type enum('User','Admin','Superadmin','Sales')
	 * @var string
	 */
	protected $Type;
	
	/**
	 * @storage.type enum('H2B_Superadmin','H2B_Channel','H2B_Property')
	 * 
	 * @var string
	 */
	# protected $Type;
	
	/**
	 * The first name of the user
	 *
	 * @var string
	 */
	protected $Firstname;
	
	/**
	 * @storage.optionsPool Languages
	 * 
	 * @var \Omi\Language
	 */
	protected $Language;
	/**
	 * @storage.optionsPool Languages
	 * 
	 * @var \Omi\Language
	 */
	protected $UI_Language;
	
	
	/**
	 * @var string[]
	 */
	protected $Authorized_IPs;
	/**
	 * @var Reverse_APIs
	 */
	protected $Reverse_APIs;
	/**
	 * @var boolean
	 */
	protected $Confirmed_Activation;
	/** 
	 * @var \Omi\Mail_Sender
	 */
	protected $Mail_Sender;
		
	protected $_rights_loaded = false;
	protected $_rights = null;
	
	public function getModelCaption($view_tag = null)
	{
		return $this->Person ? $this->Person->getModelCaption() : $this->Username;
	}

	public function commerce__beforeSave($selector = null, $transform_state = null, &$_bag = null, $is_starting_point = true, $appProp = null)
	{
		$ret = parent::beforeSave($selector, $transform_state, $_bag, $is_starting_point, $appProp);
		if ($is_starting_point && $appProp)
			$this->setupPwd();
		return $ret;
	}
	/**
	 * Setup password to md5
	 * 
	 * @return null
	 */
	public function setupPwd()
	{
		if ($this->_pwdset || !$this->wasSet("Password"))
			return;		

		$this->_pwdset = true;

		if (!$this->PrevPwd && $this->getId())
			$this->query("PrevPwd");

		if ($this->Password == $this->PrevPwd)
			return;

		if (!preg_match("/(?=.*[A-Z]{1,})(?=.*[0-9]{1,})(?=.*[a-z]{1,})(?=.*(\W){1,})/", $this->Password))
			throw new \Exception("Password format is not correct '{$this->Password}'");

		$this->setPassword(md5($this->Password));
		$this->setPrevPwd($this->Password);
	}
	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function commerce__GetModelEntity_2($view_tag = null)
	{
		return "IsImportUser,"
			// . "Language.{Code, Name},"
			. "Username, "
			. "Password, "
			. "PrevPwd, "
			. "LoggedToSystem, "
			. "Name, "
			. "Email, "
			. "Phone, "
			. "ActivationCode, "
			. "Active, "
			. "BackendAccess, "
			. "PasswordRecoveryCode, "
			. "SelfGroup.Name, "
			. "Groups.Name, "
			. "IsDefault, "
			. "EchoSignClientId,"
			. "EchoSignClientSecret,"
			. "EchoSignRefreshToken,"
			. "EchoSignAccessToken,"
			. "Person.{"
				. "Name, "
				. "Email, "
				. "Phone, "
				. "Gender, "
				. "Age, "
				. "Firstname, "
				. "BirthDate, "
				. "UniqueIdentifier, "
				. "IdentityCardSeries, "
				. "IdentityCardNumber, "
				. "PassportSeries, "
				. "PassportExpireDate,"
				. "Address.{"
					. "PostCode, "
					. "Details, "
					. "Building, "
					. "SubBuilding, "
					. "Caption,"
					. "Organization, "
					. "Premise, "
					. "Country.{"
						. "Code, "
						. "Name"
					. "}, "
					. "County.{"
						. "Code, "
						. "Name"
					. "}, "
					. "City.{"
						. "Code, "
						. "Name"
					. "}, "
					. "Street, "
					. "StreetNumber,"
					. "Longitude, "
					. "Latitude"
				. "}"
			. "}";
	}
	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function commerce__GetListingEntity_2()
	{
		$class = get_called_class();
		$le = static::$ListingEntity[$class];
		if ($le !== null)
			return $le;

		return static::$ListingEntity[$class] = qParseEntity(
			"Name, "
			// . "Language.{Code, Name},"
			. "Username, "
			. "Password, "
			. "PrevPwd, "
			. "LoggedToSystem, "
			. "Email, "
			. "Phone, "
			. "ActivationCode, "
			. "Active, "
			. "BackendAccess,  "
			. "PasswordRecoveryCode, "
			. "IsDefault, "
			. "Person.{"
				. "Name, "
				. "Email, "
				. "Phone, "
				. "Gender, "
				. "Age, "
				. "Firstname, "
				. "BirthDate, "
				. "UniqueIdentifier, "
				. "IdentityCardSeries, "
				. "IdentityCardNumber, "
				. "PassportSeries, "
				. "PassportExpireDate, "
				. "Address.{"
					. "PostCode, "
					. "Details, "
					. "Building, "
					. "SubBuilding, "
					. "Caption,"
					. "Organization, "
					. "Premise, "
					. "Country.{"
						. "Code, "
						. "Name"
					. "}, "
					. "County.{"
						. "Code, "
						. "Name"
					. "}, "
					. "City.{"
						. "Code, "
						. "Name"
					. "}, "
					. "Street, "
					. "StreetNumber,"
					. "Longitude, "
					. "Latitude"
				. "}"
			. "}");
	}
	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function GetListingQuery($selector = null)
	{
		$selector = $selector ?: static::GetListingEntity();
		$q = (is_array($selector) ? qImplodeEntity($selector) : $selector)." "
				. "WHERE 1 "
				. "??Id?<AND[Id=?]"
				. "??IN?<AND[Id IN (?)]"
				. "??NOT?<AND[Id<>?]"
				. "??Type?<AND[Type=?]"
				. static::GetListingQueryFilters()
				. "??BackendAccess?<AND[BackendAccess=?]"
				// . "??Context?<AND[Context.Id=?]"
				. "??Impersonate?<AND[Impersonate.Id=?]"
				. "??Access?<AND[Access.Id=?]"
				. "??Username?<AND[Username=?]"
				// . "??QINSEARCH_Customer?<AND[Customer.Name LIKE(?)]"
				. "??QINSEARCH_Username?<AND[Username LIKE (?)]"
				. "??QINSEARCH_Email?<AND[Email LIKE (?)]"
				. "??QINSEARCH_Person?<AND[Person.Name LIKE (?)]"
				. "??QINSEARCH_Active?<AND[Active=?]"
				. "??Password?<AND[Password=?]"
				. "??IsRemoteCallUser?<AND[IsRemoteCallUser=?]"
				. "??UsedToCall?<AND[UsedToCall=?]"
				. "??IsDefault?<AND[IsDefault=?]"
				. "??Email?<AND[Email=?]"
				. "??Name?<AND[Name LIKE (?)]"
				. "??ActivationCode?<AND[ActivationCode=?]"
				. "??WHR_Search?<AND[(Username LIKE (?) OR Name LIKE (?) OR Person.{Name LIKE (?) OR Firstname})]"
				. " ??WHR_Owner_Id?<AND[ Owner.Id=? ] "
			. " ORDER BY "
				. "??OBY_Name?<,[Name ?@]"
				. "??OBY_Username?<,[Username ?@]"
				. "??OBY_Id?<,[Id ?@]"
				. "??OBY_Person?<,[Person.Name ?@]"
			. " ??LIMIT[LIMIT ?,?]";
		return $q;
	}

	
	/**
	 * Login
	 * 
	 * @api.enable
	 * 
	 * @param string $user_or_email
	 * @param string $password
	 * @param string $session_id
	 * 
	 * @return boolean
	 */
	public static function commerce__Login($user_or_email, $password, $session_id = null, $remember = false)
	{
		return static::LoginInternal($user_or_email, $password, $session_id, true, $remember);
	}
	
	public static function LoginInternal_Check(User $user)
	{
		return null;
	}

	/**
	 * Login
	 * 
	 * @param string $user_or_email
	 * @param string $password
	 * @param string $session_id
	 * 
	 * @return boolean
	 */
	public static function LoginInternal($user_or_email, $password, $session_id = null, bool $apply_md5_on_pass = true, $remember = false)
	{
		// we trim, username and passwords must not have spaces
		$user_or_email = trim($user_or_email);
		$password = trim($password);

		$ip = Q_REMOTE_ADDR;

		$data = QQuery("FailedLogins.{* WHERE (Ip='{$ip}' OR Username='{$user_or_email}') AND Ban > NOW()}");
		$isBan = ($data->FailedLogins && (count($data->FailedLogins) > 0));
		if ($isBan)
			return static::LOGIN_BANNED;

		// ensure session
		if ($session_id !== false)
			$session_id = static::SetupSession($session_id);
		// (no longer ok) make sure we logout first
		// static::Logout($user_or_email, $session_id);
		
		$passwd_check_str = $apply_md5_on_pass ? "MD5(?)" : "?";
		
		$user = QQuery("Users.{Id,Active WHERE (Username=? OR Email=?) AND Password={$passwd_check_str} ".static::$LoginExtraWhere.
					" ORDER BY Active DESC}", [$user_or_email, $user_or_email, $password])->Users;
		
		$user = $user ? $user[0] : null;
		
		$err = null;

		if (!$user)
			$err = static::LOGIN_INVALID_USER_OR_PASSWORD;
		else if (!$user->Active)
			$err = static::LOGIN_DISABLED;
		else if ((!$session_id) && ($session_id !== false))
			$err = static::LOGIN_INVALID_SESSION;
		
		if ($user)
		{
			$rc = static::LoginInternal_Check($user);
			if ($rc !== null)
				$err = $rc;
		}
		
		$failedLoginForIp = (($r = QQuery("FailedLogins.{* WHERE Ip='{$ip}'}")) && $r->FailedLogins) ? $r->FailedLogins[0] : null;
		$failedLoginForUsername = (($r = QQuery("FailedLogins.{* WHERE Username='{$user_or_email}'}")) && $r->FailedLogins) ? $r->FailedLogins[0] : null;

		if ($err !== null)
		{
			$ipFailLogin = $failedLoginForIp ? $failedLoginForIp->toArray("Id, Count") : [];
			$ipFailLogin["Ip"] = $ip;
			$ipFailLogin["LastTry"] = date("Y-m-d H:i:s");
			if (!$ipFailLogin["Count"])
				$ipFailLogin["Count"] = 0;
			$ipFailLogin["Count"] = ++$ipFailLogin["Count"];
			if ($ipFailLogin["Count"] >= static::$BanAfterTries_IP)
				$ipFailLogin["Ban"] = date("Y-m-d H:i:s", strtotime("+ 1 day"));

			$usernameFailLogin = $failedLoginForUsername ? $failedLoginForUsername->toArray("Id, Count") : [];
			$usernameFailLogin["Username"] = $user_or_email;
			$usernameFailLogin["LastTry"] = date("Y-m-d H:i:s");
			if (!$usernameFailLogin["Count"])
				$usernameFailLogin["Count"] = 0;
			$usernameFailLogin["Count"] = ++$usernameFailLogin["Count"];
			if ($usernameFailLogin["Count"] >= static::$BanAfterTries_Username)
				$usernameFailLogin["Ban"] = date("Y-m-d H:i:s", strtotime("+ 1 day"));
			\QApp::MergeFromArray("FailedLogins", [$ipFailLogin, $usernameFailLogin]);
			return $err;
		}
		else 
		{
			if ($session_id !== false)
				static::$ActiveLogins[$session_id] = self::SetupIdentity($user, $session_id, null, $remember);

			$failedLogins = [];
			if ($failedLoginForIp)
			{
				$failedLoginForIpData = $failedLoginForIp->toArray("Id");
				$failedLoginForIpData["Count"] = 0;
				$failedLogins[] = $failedLoginForIpData;
			}
			if ($failedLoginForUsername)
			{
				$failedLoginForUsernameData = $failedLoginForUsername->toArray("Id");
				$failedLoginForUsernameData["Count"] = 0;
				$failedLogins[] = $failedLoginForUsernameData;
			}

			if (count($failedLogins) > 0)
				\QApp::MergeFromArray("FailedLogins", $failedLogins);

			$data = \QApp::NewData();
			$data->setLoginsLog(new \QModelArray());

			$loginLog = new \Omi\LoginLog();
			$loginLog->setUser($user);
			$loginLog->setUserData($user->toJSON());
			$loginLog->setDate(date("Y-m-d H:i:s"));
			if ($session_id !== false)
				$loginLog->setSessionId($session_id);
			$loginLog->setIp($ip);
			
			$data->LoginsLog[] = $loginLog;
			$data->save("LoginsLog.{User, UserData, Date, SessionId, Ip}");
			
			return true;
		}
	}
	
	/** 
	 * Login the user directly
	 * 
	 * @param \Omi\User $user
	 */
	public static function LoginUser($user)
	{
		// ensure session
		$session_id = static::SetupSession($session_id);
		
		if (!$session_id)
			return static::LOGIN_INVALID_SESSION;
		if (!$user->Active)
			return static::LOGIN_DISABLED;
		
		static::$ActiveLogins[$session_id] = self::SetupIdentity($user, $session_id);
		
		return true;
	}
	
	/**
	 * 
	 * @param \Omi\User $user
	 * @param string $session_id
	 * @return \Omi\Identity
	 */		
	protected static function SetupIdentity($user, $session_id, $ip = null, $remember = false)
	{		
		if (!$ip)
			$ip = (defined('Q_REMOTE_ADDR') && Q_REMOTE_ADDR) ? Q_REMOTE_ADDR : filter_input(INPUT_SERVER, "REMOTE_ADDR");

		$session = Session::QueryFirst("* WHERE SessionId=? AND IP=?", [$session_id, $ip]);

		// now link the session id to the user, via identity
		if (!$session)
		{
			$session = new Session();
			$session->setSessionId($session_id);
			$session->setIP($ip);
			$session->merge("Id,SessionId,IP");
		}

		// first check by User
		$identity = Identity::QueryFirst("*,User.*,Session.* WHERE User.Id=?", $user->getId());

		if (!$identity)
			$identity = new Identity();
		$identity->setUser($user);
		$identity->setSession($session);
		$identity->merge("User.{Id},Session.{Id,SessionId,IP}");

		$session_identity = Identity::QueryFirst("*,User.*,Session.* WHERE Session.Id=? AND NOT User", $session->getId());
		if ($session_identity)
		{
			// copy over
			$session_identity->transferIdentity($identity);
			$session_identity->delete("Id");
		}

		return $identity;
	}

	protected static function SetupSession($session_id = null)
	{
		if (session_status() === PHP_SESSION_ACTIVE)
		{
			$current_session_id = session_id();
			if ((!$session_id) && (!$current_session_id))
			{
				session_start();
				$session_id = session_id();
			}
			else 
			{
				$session_id = $session_id ?: $current_session_id;
				if ($session_id !== $current_session_id)
					session_id($session_id);
			}
		}
		else
		{
			session_start();
			$session_id = session_id();
		}
		
		return $session_id;
	}
	/**
	 * Logout
	 * 
	 * @api.enable
	 * 
	 * @param string $user_or_email
	 * @param string $session_id
	 * 
	 * @return boolean
	 */
	public static function commerce__Logout($user_or_email = null, $session_id = null)
	{
		// \Omi\App::ResetContext();
		
		static::$_CurrentUser = null;
		if ((!$session_id) && (session_status() === PHP_SESSION_ACTIVE))
			$session_id = session_id();
		
		$user_or_email = trim($user_or_email);
		
		if ($session_id)
		{
			$identity = Identity::QueryFirst("Id WHERE Session.SessionId=?", $session_id);
			if ($identity)
			{
				$identity->setSession(null);
				$identity->update("Session");
			}
		}
		if ($session_id)
			unset(static::$ActiveLogins[$session_id]);
		
		return true;
	}
	
	public static function Quick_Check_Login(bool $quick = true)
	{
		# $t0 = microtime(true);
		$ret = static::CheckLogin_Internal(null, false, $quick);
		# $t1 = microtime(true);
		
		if ($ret)
		{
			return [$ret->User->Id, $ret->User->Owner->Id, \Omi\App::Get_Context_Id()];
		}
		else
			return [null, null];
	}
	
	/**
	 * Check Login
	 * 
	 * @api.enable
	 * 
	 * @param string $session_id
	 * 
	 * @return Identity
	 */
	public static function commerce__CheckLogin($session_id = null, $require_backend_access = false)
	{
		if (!(session_status() === PHP_SESSION_ACTIVE))
			session_start();

		$ip = \QWebRequest::IsAsyncRequest() ? $_SERVER['HTTP_IP'] : Q_REMOTE_ADDR;

		if ((!$session_id) && (session_status() === PHP_SESSION_ACTIVE))
			$session_id = session_id();
		
		if ((!$session_id) || (!$ip))
			return false;
		
		/*$identity = Identity::QueryFirst("Id WHERE (User.Username=? OR User.Email=?) AND Session.SessionId=? AND Session.IP=?", 
								[$user_or_email, $user_or_email, $session_id, $ip]);*/
		if ($require_backend_access)
			$identity = Identity::QueryFirst("Id WHERE Session.SessionId=? AND Session.IP=? AND User.Id AND User.BackendAccess", [$session_id, $ip]);
		else
			$identity = Identity::QueryFirst("Id WHERE Session.SessionId=? AND Session.IP=? AND User.Id", [$session_id, $ip]);

		if ($identity)
			return (static::$ActiveLogins[$session_id] = $identity);
		else
			return false;
	}
	
	public static function CheckLogin_Internal($session_id = null, $require_backend_access = false, bool $quick = false)
	{
		try
		{
			if (!(session_status() === PHP_SESSION_ACTIVE))
			{
				if ($quick)
					return false;
				session_start();
			}

			$ip = \QWebRequest::IsAsyncRequest() ? $_SERVER['HTTP_IP'] : Q_REMOTE_ADDR;

			if (!$session_id)
				$session_id = static::Get_Temporary_Session();
			
			if ((!$session_id) && (session_status() === PHP_SESSION_ACTIVE))
				$session_id = session_id();

			if ((!$session_id) || (!$ip))
				return false;
			
			if ($require_backend_access)
				$identity = Identity::QueryFirst("Id,User.{IsRemoteCallUser,Owner.Gid,Active},Session.* WHERE Session.SessionId=? AND Session.IP=? AND User AND User.BackendAccess", [$session_id, $ip]);
			else
				$identity = Identity::QueryFirst("Id,User.{IsRemoteCallUser,Owner.Gid,Active},Session.* WHERE Session.SessionId=? AND Session.IP=? AND User", [$session_id, $ip]);
			
			if (!isset($identity->User->Id))
				return false;
			
			if ($identity) # && \QAutoload::GetDevelopmentMode())
			{
				$last_login = QQuery("LoginsLog.{User, UserData, Date, SessionId, Ip WHERE SessionId=? ORDER BY `Date` DESC,Id DESC LIMIT 1}", [$session_id])->LoginsLog;
				
				/**
					* 1. Enforce IP (of last login)
					* 2. Time limit (session needs to expire) / no more than a day since the login
					* 3. If not active for X sec ...
					*/
				try
				{
					if (isset($last_login[0]))
					{
						if (trim($last_login[0]->Ip) !== trim($ip))
						{
							# can not use the same session under a different login, needs to login
							return ($ret = false);
						}
						if ((!isset($last_login[0]->User->Id)) || ($last_login[0]->User->Id != $identity->User->Id))
						{
							return ($ret = false);
						}
						$time_since_login = time() - ($last_login[0]->Date ? strtotime($last_login[0]->Date) : 0);
						# no more than a day since the login
						if ($time_since_login > (24 * 60 * 60))
						{
							return ($ret = false);
						}

						$last_active_date = $identity->Session->Last_Access_Date ?? $last_login[0]->Date;

						$time_since_active_session = time() - ($last_active_date ? strtotime($last_active_date) : 0);
						if (($time_since_active_session) > (60 * 60)) # more than an hour since last active
						{
							return ($ret = false);
						}
					}
					else
						return ($ret = false);
				}
				finally
				{
					if ($ret === false)
					{
						# if (\QAutoload::GetDevelopmentMode())
						# 	qvar_dumpk(["check - \$identity" => $identity, "check - \$log" => $last_login[0], "check ip" => $ip]);
						# log it !
						/*
						file_put_contents("../CheckLogin_log_LoginsLog_returns_false.txt", date("Y-m-d H:i:s") . " - " . php_sapi_name() . " - " . $_SERVER['REQUEST_URI'] . 
									($argv ? "\n\t\targs=".json_encode($argv, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).' ' : '') . 
										"\n\t\tserver=". json_encode($_SERVER, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . 
										"\n\t\tpost=". json_encode(file_get_contents("php://input"), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . 
										"\n\t\ttrace=". json_encode((new \Exception())->getTraceAsString(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
										. "\n\n=============================================================================\n\n", FILE_APPEND);
						*/
					}
				}
			}

			//qvardump("Check LOGIN IDENTITY", $identity);
			if ((!$identity->User->Owner) || ((!$identity->User->IsRemoteCallUser) && (!static::Partner_Is_Active($identity->User->Owner))))
				return false;
			
			if ($identity)
			{
				# @TEMP @TODO - de-activate non-root logins
				if (false)
				{
					if ($identity->User->Owner->Id != 1)
					{
						if ($identity->User->Id)
							$identity->User->populate('IsRemoteCallUser,UsedToCall');
						# qvar_dumpk($identity->User);
						if ((!$identity->User->IsRemoteCallUser) && (!$identity->User->UsedToCall))
							return false;
					}
				}
				
				if (!$quick)
				{
					# mark last access date
					$identity->Session->setLast_Access_Date(date('Y-m-d H:i:s'));
					$identity->Session->save('Last_Access_Date');
				}
				
				return (static::$ActiveLogins[$session_id] = $identity);
			}
			else
				return false;
		}
		finally
		{
			# \QSecurity_Check::Audit_CheckLogin($identity);
		}
	}
	
	/**
	 * Is Logged in
	 * 
	 * @api.enable
	 * 
	 * @return boolean
	 */
	public static function IsLogged($require_backend_access = false)
	{
		if (!(session_status() === PHP_SESSION_ACTIVE))
			session_start();

		if (session_status() === PHP_SESSION_ACTIVE)
		{
			$session_id = session_id();
			if (!$session_id)
				return false;
			if (!static::$ActiveLogins[$session_id])
				return static::CheckLogin($session_id, $require_backend_access) ? true : false;
			else
				return static::$ActiveLogins[$session_id] ? true : false;
		}
		else
			return false;
	}
	/**
	 * @api.enable
	 * 
	 * @param array $user_data
	 * 
	 */
	public static function Register($user_data, $confirm_url = "myaccount/register?ActivationCode=")
	{
		$user = static::FromArray($user_data, "auto", static::$RegistrationFields);
		// make sure we have the right thing
		if (!($user instanceof User))
			return false;
		
		$user->Email = filter_var(trim($user->Email), FILTER_VALIDATE_EMAIL);
		$user->Username = trim($user->Username);
		// make sure we encrypt the password
		$user->setPassword(md5(trim($user->Password)), false);

		if (!trim($user->Email))
			return false;

		if (static::UserExists($user->Email, $user->Username))
			return false;

		// set email as username if username not present
		if (!trim($user->Username))
			$user->Username = $user->Email;

		// make sure it is not active
		$user->setActive(false);
		$user->setActivationCode(uniqid("", true));
		// push it via insert

		$user->insert(static::$RegistrationFields);

		// save the user again just to update the app data field
		$data = \QApp::NewData();
		$data->Users = new \QModelArray();
		$data->Users[] = $user;
		$data->save("Users.Id");

		// now send email
		$headers = null;
		$attachments = [];
		$isHtml = true;
		$from = static::$RegistrationEmailFrom ?: "noreplay@".filter_input(INPUT_SERVER, "HTTP_HOST");
		$message = static::$RegistrationEmailBody ?: "Hi,\n\nYour registration code is: <a href='{$confirm_url}".urlencode($user->ActivationCode)."'>{$user->ActivationCode}</a>";
		$message = "Hi,\n\nYour registration code is: <a href='{$confirm_url}".urlencode($user->ActivationCode)."'>{$user->ActivationCode}</a>";
		\Omi\Util\Email::Send($user->Email, static::$RegistrationSubject, $message, $headers, $attachments, $isHtml, $from);
		return $user;
	}
	/**
	 * @api.enable
	 * @param string $activation_code
	 * 
	 * @return boolean
	 */
	public static function RegisterConfirm($activation_code)
	{
		$user = new User();
		// this will ensure cleanup
		$user->setActivationCode($activation_code);
		
		$user = User::QueryFirst("Id WHERE ActivationCode=?", $user->ActivationCode);
		if (!$user)
			return false;
		else
		{
			$user->setActive(true);
			$user->setActivationCode("");
			
			$user->update("Active, ActivationCode");
			
			return true;
		}
	}

	/**
	 * @api.enable
	 * 
	 * @param boolean $recovery_code
	 * @return boolean
	 */
	public static function ConfirmPasswordRecovery($recovery_code)
	{
		$user = User::QueryFirst("Id WHERE PasswordRecoveryCode=?", $recovery_code);
		if ($user)
		{
			//$user->setPasswordRecoveryCode("");
			//$user->update("PasswordRecoveryCode");
			return true;
		}
		return false;
		
	}

	/**
	 * @api.enable
	 * 
	 * @param string $email
	 * 
	 * @return boolean
	 */
	public static function UserExists($email = null)
	{
		$user = self::QueryFirst("Id WHERE Email=?", $email);
		return $user ? true : false;
	}
	/**
	 * @param \Omi\User $usr
	 * @return array
	 * @throws \Exception
	 */
    protected static function common_GetUserMailData($usr)
    {
		return ["noreplay@".filter_input(INPUT_SERVER, "HTTP_HOST"), \QWebRequest::GetBaseUrl()];
	}
	/**
	 * @api.enable
	 * 
	 * @param string $email
	 * @param string $email
	 * @param string $tpl
	 * @return int|boolean
	 */
	public static function RecoverPassword($email, $confirm_url = "myaccount/recover-password?RecoverCode=", $username = null, $tpl = null)
	{		
		// get user
		$user = static::QueryFirst("Id, Email WHERE Email=? OR Username=?", [$email, $username]);

		if (!$user)
			return static::RECOVER_PASSWORD_USER_NOT_FOUND;
		
		if ($confirm_url)
		{
			$user->setPasswordRecoveryCode(uniqid("", true));
			$user->save("PasswordRecoveryCode");
			
			$message = static::$RegistrationEmailBody ?: "Salut,\nAcceseaza link-ul pentru resetarea parolei: <a href='{$confirm_url}".urlencode($user->PasswordRecoveryCode)."'>Aici</a>";
		}
		else
		{
			$pwd = self::GetRandomPassowrd();
			$user->setPassword(md5($pwd));
			$user->setPrevPwd($user->Password);

			$user->save("PrevPwd, Password");
			
			if (!$tpl)
				$tpl = Omi_Mods_Path . "res/mail_templates/new_password.tpl";

			if (!file_exists($tpl))
				throw new \Exception("Recover password template not found!");
			
			ob_start();
			include($tpl);
			$message = ob_get_clean();
		}
		
		if (!$from)
		{
			$mailSender = new \stdClass();
			$mailSender->Host = APP_DEFAULT_MAIL_ACCOUNT['Host'];
			$mailSender->Port = APP_DEFAULT_MAIL_ACCOUNT['Port'];
			$mailSender->Username = APP_DEFAULT_MAIL_ACCOUNT['Username'];
			$mailSender->Password = APP_DEFAULT_MAIL_ACCOUNT['Password'];
			$mailSender->Encryption = APP_DEFAULT_MAIL_ACCOUNT['Encryption'];
			
			$from = $mailSender;
		}
		
		return \Omi\Util\Email::Send($from, $user->Email, "New password", $message);
	}
	
	/**
	 * @api.enable
	 * 
	 * @param string $pwdRecoveryCode
	 * @return \Omi\User
	 */
	public static function GetUserByRecoverPasswordCode($pwdRecoveryCode)
	{
		$user = User::QueryFirst("Id, Email, Username, PasswordRecoveryCode WHERE Active AND PasswordRecoveryCode=?", $pwdRecoveryCode);
		#$user2 = User::QueryFirst("Id WHERE PasswordRecoveryCode=?", $pwdRecoveryCode);
		#qvardump("\$user, \$pwdRecoveryCode", $user, $user2, $pwdRecoveryCode);
		return $user;
	}

	/**
	 * @api.enable
	 * 
	 * To be used instead of get full info
	 * In controller on initController we need to check if the user is logged in using check login method
	 * and after if is logged in then call this method to get the current user
	 * 
	 * @return User
	 */
	public static function GetCurrentUser($force = false, $load_if_null = true)
	{
		return ((static::$_CurrentUser || (!$load_if_null)) && !$force) ? static::$_CurrentUser : 
					(static::$_CurrentUser = static::GetFullInfo());
	}
	
	/**
	 * NEVER API ENABLE THIS !!!
	 * 
	 * @param \Omi\User $user
	 */
	public static function Setup_CurrentUser(int $user_id = null)
	{
		if ($user_id === null)
			static::$_CurrentUser = null;
		else
		{
			if (!$user_id)
				throw new \Exception('Missing user ID');
			$set_usr = \QQuery('Users.{*,Owner.Name WHERE Id=?}', [$user_id])->Users;
			$set_usr = $set_usr ? $set_usr[0] : null;
			
			return static::$_CurrentUser = $set_usr;
		}
	}
	
	/**
	 * @api.enable
	 * 
	 * @return User
	 * 
	 */
	public static function GetSecurityCurrentUser()
	{
		return \Omi\App::GetSecurityUser();
	}

	/**
	 * @api.enable
	 * @return User
	 */
	public static function GetFullInfo()
	{
		static::$InInfoRequest = true;
		$identity = static::CheckLogin();
		static::$InInfoRequest = false;
		return static::LoadInfo($identity);
	}
	
	/**
	 * Generates a password with 8 chars.
	 * 
	 * @return string
	 */
	public static function GetRandomPassowrd()
	{
		$randomLc = substr(str_shuffle("abcdefghjkmnpqrstuvwxyzabcdefghjkmnpqrstuvwxyz"), 0, 5);
		$randomUc = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZABCDEFGHJKLMNPQRSTUVWXYZ"), 0, 2);
		$randomNc = substr(str_shuffle("2345678923456789"), 0, 2);
		//$randomSc = substr(str_shuffle("!?@#$%^&*()+-{}[];:><,.~"), 0, 2);
		$randomSc = substr(str_shuffle("@#$%&*+"), 0, 2);
		return str_shuffle("{$randomLc}{$randomUc}{$randomNc}{$randomSc}");
	}
	/**
	 * @api.enable
	 * 
	 * @param type $data
	 */
	public static function EditProfile($data)
	{
		$newPwd = $data["_NewPassword"];
		$pwdRecoveryCode = $data["PasswordRecoveryCode"];
		
		$pwdChanged = false;
		if ($newPwd || $pwdRecoveryCode)
		{
			// we need to make sure that if we change the password then we reset the recovery code
			$data["PasswordRecoveryCode"] = "";
			// $pwd = $data["_Password"];
			$newPwdConfirm = $data["_NewPasswordConfirm"];

			// if (!$pwd)
			// 	throw new \Exception("Current password is needed in order to change the password!!");
			if (!$newPwdConfirm)
				throw new \Exception("New password must be confirmed!");
			else if ($newPwd !== $newPwdConfirm)
				throw new \Exception("In confirm password field you need to re-type the new password!");

			// this is also used in recover password change pass and it doesn't work
			// $usr = \QApi::QueryById("Users", $data["Id"], "Password");

			$usr = \QQuery('Users.{Id, Email, Password WHERE Id=?}', $data['Id'])->Users[0];
			
			if (!$usr)
				throw new \Exception("User not found!");
			// else if ($usr->Password !== md5($pwd))
			//	throw new \Exception("Current password does not match!");
			// else if ($usr->Password === md5($newPwd))
			//	throw new \Exception("New password needs to be different then current password!");

			$usr->Password = $data["Password"] = $newPwd;
			$usr->setupPwd();
			$pwdChanged = true;
		}

		unset($data["_NewPassword"]);
		unset($data["_Password"]);
		unset($data["_NewPasswordConfirm"]);

		//if (!$data["Person"]["Name"])
		//	throw new \Exception("Numele este obligatoriu!");

		unset($data["Person"]["Company"]);

		// error :: Argument 1 passed to q_TFH_props_security_func_set_owner() must be an instance of Omi\User, null given
		// \QApi::Merge("Users", $data);
		
		$usr->save('Password');
		
		if ($pwdChanged)
			static::Logout();
	}
	
	public static function ResetStaticContext()
	{
		$ret = [
				'_FullInfo' => static::$_FullInfo,
				'ActiveLogins' => static::$ActiveLogins,
				'_CurrentUser' => static::$_CurrentUser,
				'FromMailData' => static::$FromMailData,
			];
		
		static::$_FullInfo = null;
		static::$ActiveLogins = [];
		static::$_CurrentUser = null;
		static::$FromMailData = null;
		
		return $ret;
	}
	
	public static function RestoreStaticContext(array $data)
	{
		foreach ($data as $k => $v)
			static::$$k = $v;
	}
	
	public static function Set_Temporary_Session(string $new_session_id = null)
	{
		static::$Temporary_Session = $new_session_id;
	}
	
	public static function Get_Temporary_Session()
	{
		return static::$Temporary_Session;
	}

	/**
	 * 
	 * @param string $from
	 * @param array $parameters
	 * @param array $id
	 * @param string $fromAlias
	 */
	public function setupSecurityFilters($from, &$parameters = null, &$id = null, $fromAlias = null)
	{
		return;
		
		$dataCls = \QApp::GetDataClass();
		if (!$dataCls::$_USE_SECURITY_FILTERS || !$this->isUser() || !in_array($from, ["Customers", "Orders", "Offers", "Services", "Products"]))
			return;

		if (!$parameters)
			$parameters = [];
		$parameters["CreatedBy"] = $this->getId();

		if ($id && is_array($id))
			$id["CreatedBy"] = $this->getId();
	}

	/**
	 * Returns true if the user can perform the action, false otherwise
	 * We need the model
	 * We may need extra params in order to decide what the user can or cannot do
	 * The action is something like: add/edit/view/delete/export/import
	 * The module is the identifier: quotes/orders/offers, ...
	 * 
	 * 
	 * @param string $action
	 * @param string $module
	 * @param QIModel $model
	 * @param array $params
	 */
	public function can($action, $module = null, $model = null, $params = null)
	{
		return true;
		
		$dataCls = \QApp::GetDataClass();

		if (!$dataCls::$_USE_SECURITY_FILTERS)
			return true;

		$user = static::GetSecurityCurrentUser();

		$_rights = $user->getRights();
		if (!$_rights)
			return true;
		
		//qvardump($_rights, $action, $module, $model, $params);
		//die();

		// by default the user can do anything - we only need to setup what the user can't do!

		$ai = "@";
		$ndi = "#";

		$module_indx = $module ?: $ndi;
		$model_indx = $model ? get_class($model)."|".($model->getId() ?: $model->getTemporaryId()) : $ndi;
		$params_indx = $params ? implode("~", $params) : $ndi;
	
		/*
		if ($action == "export_excel")
		{
			qvardump((!($_rights[$user->Type] && $_rights[$user->Type][$action] && 
			($_rights[$user->Type][$action][$ai] || 
				($_rights[$user->Type][$action][$module_indx] && 
					($_rights[$user->Type][$action][$module_indx][$ai] || 
						($_rights[$user->Type][$action][$module_indx][$model_indx] && 
							($_rights[$user->Type][$action][$module_indx][$model_indx][$ai] || $_rights[$user->Type][$action][$module_indx][$model_indx][$params_indx])
						)
					)
				)
			))));
			die();
		}
		*/

		
		$apply_type = $user->getSecurityType();
		# $is_admin = (substr($apply_type, -6, 6) === '/admin');
		
		# if (\QAutoload::GetDevelopmentMode())
		#	qvar_dumpk($apply_type, $action);

		# customer/admin
		
		if ((/*($action === 'add') || ($action === 'edit') || */($action === 'delete')) && (/*(!$is_admin) || */(substr($apply_type, 0, strlen('customer/')) === 'customer/')))
		{
			return false;
		}
	
		return (!($_rights[$apply_type] && $_rights[$apply_type][$action] && 
			($_rights[$apply_type][$action][$ai] || 
				($_rights[$apply_type][$action][$module_indx] && 
					($_rights[$apply_type][$action][$module_indx][$ai] || 
						($_rights[$apply_type][$action][$module_indx][$model_indx] && 
							($_rights[$apply_type][$action][$module_indx][$model_indx][$ai] || $_rights[$apply_type][$action][$module_indx][$model_indx][$params_indx])
						)
					)
				)
			)));
	}

	/**
	 * Check Login
	 * 
	 * @api.enable
	 * 
	 * @param string $session_id
	 * 
	 * @return Identity
	 */
	public static function CheckLogin($session_id = null, $require_backend_access = false)
	{
		return static::commerce__CheckLogin($session_id, $require_backend_access);
	}

	/**
	 * Login
	 * 
	 * @api.enable
	 * 
	 * @param string $user_or_email
	 * @param string $password
	 * @param string $session_id
	 * 
	 * @return boolean
	 */
	public static function Login($user_or_email, $password, $session_id = null, $remember = false)
	{		
		return static::commerce__Login($user_or_email, $password, $session_id, $remember);
	}
	
	/**
	 * Logout
	 * 
	 * @api.enable
	 * 
	 * @param string $user_or_email
	 * @param string $session_id
	 * 
	 * @return boolean
	 */
	public static function Logout($user_or_email = null, $session_id = null, bool $reset_context = true)
	{
		return static::commerce__Logout($user_or_email, $session_id);
	}

	/**
	 * @api.enable
	 * @param string $username
	 */
	public static function GetByUsername($username)
	{
		return QQuery("Users.{Username WHERE Username=? AND Owner.Id=?}", [$username, \Omi\App::GetCurrentOwner()->getId()])->Users[0];
	}

	protected static function CheckUsername($user)
	{
		// skip remote call users
		if ($user->IsRemoteCallUser || !$user->wasSet("Username"))
			return;

		$username = $prevUsername = $user->Username;
		if ($user->getId())
		{
			// load the prev username
			$user->query("Username");
			$prevUsername = $user->Username;
			// set the username as what we have provided
			$user->Username = $username;
		}

		// check if username was changed and throw exception - not allowed
		if ($prevUsername != $user->Username)
			throw new \Exception("Username cannot be changed!");

		// check if the username already exists
		$owner = $user->Owner ?: \Omi\App::GetCurrentOwner();
		$eusrs = QQuery("Users.{Username WHERE Username=? "
			. "AND Owner.Id=? AND Id<>?}", [$user->Username, $owner->getId(), $user->getId() ?: 0])->Users;
		
		if ($eusrs && (count($eusrs) > 0))
			throw new \Exception("Username {$user->Username} already exists!");
	}

	protected static function CheckUniqueInContext($user, $appProp)
	{
		// if the user is remote call user or is a default user we don't care
		if ($user->IsRemoteCallUser || $user->IsDefault)
			return;

		if ((!$user->Username || !$user->Password) && !$user->getId())
			throw new \Exception("Both username and password are mandatory for new user!");

		// if we don't have password for sure the user is not new and the password was not changed
		if (!$user->Password)
			return;

		// if the user is new we need to check for credentials to be unique
		$checkUnique = $user->isNew(true, $appProp);

		// store the username
		$username = $user->Username;
		if ($user->getId())
		{
			// load username and password from database
			$userClone = $user->getClone("Id");
			$userClone->populate("Username, Password");
			
			// if the user does not have the username set - use the username from database
			if (!$username)
				$username = $userClone->Username;

			// password was changed - password that comes on user is different than what we have in the database
			// we need to check unique if the password was changed
			if ($user->Password && ($user->Password != $userClone->Password))
				$checkUnique = true;
		}
		
		// if the user is not new and the password did not change then do nothing
		if (!$checkUnique)
			return;
		
		$binds = [
			"Username" => $username,
			"Password" => $user->Password
		];
		
		if ($user->getId())
			$binds["Not"] = $user->getId();

		$existingUser = ($eUsers = \QQuery("Users.{Username, Owner WHERE 1 "
			. "??Username?<AND[Username=?]"
			. "??Password?<AND[Password=?]"
			. "??Not?<AND[Id<>?]"
		. "}", $binds)->Users) ? $eUsers[0] : null;

		// throw exception if there is other user with same credentials combination
		if ($existingUser)
			throw new \Exception("There is another user with same login credentials(username and password)!\n");
	}

	/**
	 * 
	 * @param type $selector
	 * @param type $transform_state
	 * @param type $_bag
	 * @param type $is_starting_point
	 * @return type
	 * @throws \Exception
	 */
	public function vpfuse__beforeSave($selector = null, $transform_state = null, &$_bag = null, $is_starting_point = false, $appProp = null)
	{
		$pwd = $this->Password;
		$ret = $this->commerce__beforeSave($selector, $transform_state, $_bag, $is_starting_point, $appProp);
		if ($is_starting_point && $appProp)
		{
			// check for username to be unique on owner
			// static::CheckUsername($this);

			if ($this->isNew(true, $appProp))
			{
				if (!$this->wasSet("Password"))
				{
					$this->setPassword(static::GetRandomPassowrd());
					$this->_clear_pwd = $this->Password;
				}
				else
				{
					$this->_clear_pwd = $pwd;
				}

				if (!$this->wasSet("IsRemoteCallUser"))
					$this->setIsRemoteCallUser(false);

				if (!$this->wasSet("IsDefault"))
					$this->setIsDefault(false);
			}
				

			if ((($transform_state === \QIModel::TransformDelete) || ($this->getTransformState() === \QIModel::TransformDelete)) && ($this->IsDefault || $this->IsRemoteCallUser))
				throw new \Exception("User cannot be removed!");

			// setup password
			$this->setupPwd();

			// check for username to be unique on owner
			static::CheckUniqueInContext($this, $appProp);
		}
		return $ret;
	}

	public function afterSave($selector = null, $transform_state = null, &$_bag = null, $is_starting_point = true, $appProp = null)
	{
		$ret = parent::afterSave($selector, $transform_state, $_bag, $is_starting_point, $appProp);
		if ($is_starting_point && $appProp && $this->isNew() && !$this->_found_on_merge)
		{
			# we will not send a email atm ! @TODO
			# static::SendCreatedUserEmail($this->getId(), $this->_clear_pwd);
		}
		return $ret;
	}

	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function vpfuse__GetModelEntity($view_tag = null)
	{
		return self::commerce__GetModelEntity_2($view_tag);
	}

	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function vpfuse__GetListingEntity()
	{
		$class = get_called_class();
		$le = static::$ListingEntity[$class];
		if ($le !== null)
			return $le;
		return static::$ListingEntity[$class] = static::commerce__GetListingEntity_2();
	}

	public static function before_nuvia__LoadInfo($identity)
	{
		if (!$identity)
			return null;
			
		if (self::$_FullInfo && self::$_FullInfo[$identity->getId()])
			return self::$_FullInfo[$identity->getId()];

		if (!self::$_FullInfo)
			self::$_FullInfo = [];

		$identity->populate("User.{*, Groups.{Id,Name}, "
			. " "
			. "Impersonate.{*, "
				. "Name,"
				. "Code,"
				. "Phone,"
				. "Email,"
				. "LegalRepresentative.{"
					. "Name,"
					. "Firstname,"
					. "Email,"
					. "Phone"
				. "},"
				. "WhiteLabel.Domain, "
				. "HeadOffice.{"
					. "Country.{Code, Name},"
					. "County.{"
						. "Name,"
						. "Country"
					. "},"
					. "City.{"
						. "Name,"
						. "County,"
						. "Country"
					. "},"
					. "PostCode,"
					. "Street,"
					. "StreetNumber,"
					. "Caption,"
					. "Building,"
					. "SubBuilding,"
					. "Organization,"
					. "Premise"
				. "}"
			. "}, "
			. "Owner.{*, "
				. "Name,"
				. "Code,"
				. "Phone,"
				. "Email,"
				. "LegalRepresentative.{"
					. "Name,"
					. "Firstname,"
					. "Email,"
					. "Phone"
				. "},"
				. "WhiteLabel.Domain, "
				. "HeadOffice.{"
					. "Country.{Code, Name},"
					. "County.{"
						. "Name,"
						. "Country"
					. "},"
					. "City.{"
						. "Name,"
						. "County,"
						. "Country"
					. "},"
					. "PostCode,"
					. "Street,"
					. "StreetNumber,"
					. "Caption,"
					. "Building,"
					. "SubBuilding,"
					. "Organization,"
					. "Premise"
				. "}"
			. "}, "
			. "Person.{"
				. "Name, "
				. "Firstname,"
				. "Email, "
				. "Phone, "
				. "Address.{"
					. "Country.{Code, Name},"
					. "County.{"
						. "Name,"
						. "Country"
					. "},"
					. "City.{"
						. "Name,"
						. "County,"
						. "Country"
					. "},"
					. "PostCode,"
					. "Street,"
					. "StreetNumber,"
					. "Caption,"
					. "Building,"
					. "SubBuilding,"
					. "Organization,"
					. "Premise"
				. "}"
			. "}"
		. "}");
		return self::$_FullInfo[$identity->getId()] = $identity->User;
	}

	/**
	 * @api.enable
	 * Authenticate user using data from login header
	 * 
	 */
	public static function Authenticate($session_id = null)
	{
		$session_id = static::SetupSession($session_id);
		list($username, $password) = static::GetAuthCredentials();
	
		// make a login
		if (!$username || !$password)
		{
			header('HTTP/1.0 401 Unauthorized');
			throw new \Exception("Credentials are mandatory");
		}

		$user = static::QueryFirst("Username, Active, IsRemoteCallUser WHERE "
			. "(Username=? OR Email=?) AND "
			. "Password=? AND "
			. "Active=?", 
			[$username, $username, $password, true]);

		if (!$user)
		{
			header('HTTP/1.0 401 Unauthorized');
			throw new \Exception("Access Restricted");
		}

		static::$ActiveLogins[$session_id] = self::SetupIdentity($user, $session_id, \QWebRequest::IsAsyncRequest() ? $_SERVER['HTTP_IP'] : null);
		return $user;
	}

	/**
	 * @api.enable
	 * 
	 * @return array
	 */
	public static function GetAuthCredentials()
	{
		// here we only need to deal with login and check user details!
		if (isset($_SERVER['HTTP_AUTHORIZATION']) && (!(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))))
			list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
		return [isset($_SERVER["PHP_AUTH_USER"]) ? $_SERVER["PHP_AUTH_USER"] : null, isset($_SERVER["PHP_AUTH_PW"]) ? $_SERVER["PHP_AUTH_PW"] : null];

	}

	/**
	 * @api.enable
	 * 
	 * Authenticate user using data from login header
	 * 
	 */
	public static function AuthenticateRemoteUser($session_id = null)
	{
		$user = static::Authenticate($session_id);

		if (!$user->IsRemoteCallUser)
		{
			static::Logout();
			header('HTTP/1.0 401 Unauthorized');
			throw new \Exception("Access Restricted");
		}
	}

	/**
	 * @api.enable
	 * 
	 * @param \Omi\Comm\TradeCompany $owner
	 */
	public static function vpfuse__SetupDefaultUser(\Omi\Comm\TradeCompany $owner = null)
	{
		$user = [
			"Username" => "admin",
			"Password" => self::GetRandomPassowrd(),
			"Active" => true,
			"BackendAccess" => true,
			"IsDefault" => true,
			"IsRemoteCallUser" => false,
			"UsedToCall" => false
		];

		if ($owner)
			$user["Owner"] = $owner->toArray("Id");

		return (($app = \QApi::Merge("Users", $user)) && $app->Users) ? $app->Users[0] : null;
	}

	/**
	 * @api.enable
	 * 
	 * @param \Omi\Comm\TradeCompany $owner
	 */
	public static function vpfuse__SetupAdminUser(\Omi\Comm\TradeCompany $owner = null, $email = null, $name = null, $firstname = null)
	{
		$eUser = $owner ? \QQuery("Users.{Id, Password, Person WHERE IsLegalRepresentative=? AND Owner.Id=?}", [1, $owner->getId()])->Users[0] : null;

		$user = $eUser ? $eUser->toArray("Id, Password, Person.Id") : [];

		$user["Username"] = $email;

		if (!$user["Password"])
			$user["Password"] = self::GetRandomPassowrd();

		$user["Active"] = true;
		$user["Email"] = $email;
		$user["BackendAccess"] = true;
		$user["IsDefault"] = false;
		$user["IsRemoteCallUser"] = false;
		$user["UsedToCall"] = false;
		$user["IsLegalRepresentative"] = true;

		if (!$user["Person"])
			$user["Person"] = [];

		$user["Person"]["Name"] = $name;
		$user["Person"]["Firstname"] = $firstname;

		if ($owner)
			$user["Owner"] = $owner->toArray("Id");

		return (($app = \QApi::Merge("Users", $user)) && $app->Users) ? $app->Users[0] : null;

		/*
		$f = fopen("generated_users.txt", "a+");
		fwrite($f, $owner->Name."({$owner->getId()}) => {$user["Username"]}::{$user["Password"]}\n");
		fclose($f);
		*/
	}

	/**
	 * @api.enable
	 * Setup remote call user
	 * 
	 * @param \Omi\Comm\Reseller $context
	 * @param \Omi\Comm\Reseller $owner
	 * @param boolean $usedToCall
	 */
	public static function vpfuse__SetupRemoteCallUser(\Omi\Comm\Reseller $context, \Omi\Comm\Reseller $owner = null, $usedToCall = false)
	{
		$user = [
			"Username" => "remote_call_".uniqid(),
			"Password" => self::GetRandomPassowrd(),
			"BackendAccess" => true,
			"Active" => !$usedToCall
		];

		
		//if ($owner)
		//	$user["Owner"] = $owner->toArray("Id");
			
		// if we setup a user that will be used for calls then we need to setup the partner/customer that will be accesed by this user
		// else if we setup a user that will be used to login when a remote call is received then we need to setup the context
		$user[$usedToCall ? "Access" : "Context"] = $context->toArray("Id");
		
		$ret_data = (($app = \QApi::Merge("Users", $user)) && $app->Users) ? $app->Users[0] : null;
		
		return $ret_data;
	}

	/**
	 * @api.enable
	 * 
	 * @param \Omi\User $usr
	 */
	public static function CheckIfRemote(User $usr)
	{
		return $usr->IsRemoteCallUser;
	}

	/**
	 * @param \Omi\User $usr
	 * @return array
	 * @throws \Exception
	 */
    protected static function GetUserMailData($usr)
    {
		$mail = new \stdClass();
		$mail->Host = DEFAULT_EMAIL_ACCNT_SMTP_HOST;
		$mail->Port = DEFAULT_EMAIL_ACCNT_SMTP_PORT;
		$mail->Username = DEFAULT_EMAIL_ACCNT_SMTP_USER;
		$mail->Password = DEFAULT_EMAIL_ACCNT_SMTP_PASS;
		$mail->Encryption = DEFAULT_EMAIL_ACCNT_SMTP_ENCR;

		$ret = static::$FromMailData = [$mail, \QWebRequest::GetBaseUrl()];
		
		return $ret;
	}

	/**
	 * @api.enable
	 * 
	 * @param int $userId
	 * @param string $password
	 */
	public static function SendCreatedUserEmail($userId, $password, $username_tpl = null, $pwd_tpl = null)
	{
		$usr = \QApi::QueryById("Users", $userId, "Username, Email, Active, IsRemoteCallUser, IsDefault, Owner.Name");
		if (!$usr || !$usr->Email || $usr->IsRemoteCallUser || $usr->IsDefault || !$usr->Active)
			return;

		$username = $usr->Username;

		list($from, $app_url) = static::GetUserMailData($usr);
		
		if (!$from && (defined('APP_DEFAULT_MAIL_ACCOUNT')) && APP_DEFAULT_MAIL_ACCOUNT)
		{
			$mailSender = new \stdClass();
			$mailSender->Host = APP_DEFAULT_MAIL_ACCOUNT['Host'];
			$mailSender->Port = APP_DEFAULT_MAIL_ACCOUNT['Port'];
			$mailSender->Username = APP_DEFAULT_MAIL_ACCOUNT['Username'];
			$mailSender->Password = APP_DEFAULT_MAIL_ACCOUNT['Password'];
			$mailSender->Encryption = APP_DEFAULT_MAIL_ACCOUNT['Encryption'];
			
			$from = $mailSender;
		}
		
		if (!$from)
		{
			return [false, false];
		}

		# $owner = $usr->Owner ? $usr->Owner->Name : "Our";

		if (!$username_tpl)
			$username_tpl = Omi_Mods_Path."res/mail_templates/create_user_username.tpl";

		if (!file_exists($username_tpl))
			throw new \Exception("Created user template not found!");

		if (!$pwd_tpl)
			$pwd_tpl = Omi_Mods_Path."res/mail_templates/create_user_password.tpl";

		if (!file_exists($pwd_tpl))
			throw new \Exception("Created user template not found!");

		ob_start();
		include($username_tpl);
		$message = ob_get_clean();
		$susrm = \Omi\Util\Email::Send($from, $usr->Email, "New User - Username", $message);

		if ((!defined('OMI_Send_Password_On_Create_Account')) || OMI_Send_Password_On_Create_Account)
		{
			ob_start();
			include($pwd_tpl);
			$message = ob_get_clean();
			$spwdm = \Omi\Util\Email::Send($from, $usr->Email, "New User - Password", $message);
		}
		
		return [$susrm, $spwdm];
	}
		
	public function isUser()
	{
		return ($this->Type && ($this->Type === "User"));
	}

	public function isAdmin()
	{
		return ($this->Type && ($this->Type === "Admin"));
	}

	public function isSuperadmin()
	{
		return ($this->Type && ($this->Type === "Superadmin"));
	}
	
	public static function Is_Superadmin()
	{
		return (($cu = static::GetCurrentUser()) && ($cu->Type == 'Superadmin'));
	}
	
	public static function Is_Property_Owner()
	{
		$cu = static::GetCurrentUser();
		
		if (!$cu)
			return false;
		
		$cu->populate('Owner.Is_Property_Owner');
		
		return ((($cu->Type == 'Superadmin') || ($cu->Type == 'Admin')) && ($cu->Owner && $cu->Owner->Is_Property_Owner));
	}

	/**
	 * @param type $selector
	 * @param type $transform_state
	 * @param type $_bag
	 * @return type
	 */
	public function beforeSave($selector = null, $transform_state = null, &$_bag = null, $is_starting_point = true, $appProp = null)
	{
		$ret = $this->vpfuse__beforeSave($selector, $transform_state, $_bag, $is_starting_point, $appProp);
		if ($is_starting_point && $appProp && ($this->isNew(true, $appProp) && !$this->wasSet("Type")))
			$this->setType("User");
		if ($is_starting_point && $appProp)
			$this->initUser($appProp);
		return $ret;
	}
	
	/**
	 * 
	 * @param type $selector
	 * @param type $transform_state
	 * @param type $_bag
	 * @return type
	 */
	public function afterBeginTransaction($selector = null, $transform_state = null, &$_bag = null, $is_starting_point = true, $appProp = null)
	{
		# unique api key
		if ($is_starting_point && ($appProp === 'Users') && isset($this->Api_Key) && trim($this->Api_Key))
		{
			$trimmed_Api_Key = trim($this->Api_Key);
			$usr = \QQuery('Users.{Id,Api_Key WHERE TRIM(Api_Key)=? LIMIT 1}', [$trimmed_Api_Key])->Users;
			if (isset($usr[0]->Id) && ($this->isNew() || ($usr[0]->Id != $this->Id)))
				throw new \Exception('Duplicated API Key.');
		}
		
		# unique username
		if ($is_starting_point && ($appProp === 'Users') && isset($this->Username) && trim($this->Username))
		{
			$trimmed_Username = trim($this->Username);
			$usr = \QQuery('Users.{Id,Username WHERE TRIM(Username)=? LIMIT 1}', [$trimmed_Username])->Users;
			if (isset($usr[0]->Id) && ($this->isNew() || ($usr[0]->Id != $this->Id)))
				throw new \Exception('Duplicated Username.');
		}
		
		return parent::afterBeginTransaction($selector, $transform_state, $_bag, $is_starting_point, $appProp);
	}

	/**
	 * Init the user
	 */
	private function initUser($appProp)
	{
		if (!$this->isNew(true, $appProp))
			return;

		$this->setBackendAccess(true);
		if (!$this->wasSet("IsDefault"))
			$this->setIsDefault(false);
		if (!$this->wasSet("IsRemoteCallUser"))
			$this->setIsRemoteCallUser(false);
	}

	/**
	 * @api.enable
	 * 
	 * @return null
	 */
	public static function GetNew()
	{
		$cc = get_called_class();
		$usr = new $cc();
		$usr->setType("User");
		return $usr;
	}

	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function before_nuvia__GetModelEntity($view_tag = null)
	{
		return self::vpfuse__GetModelEntity($view_tag) . ", Type";
	}

	public static function before_nuvia__GetListingEntity()
	{
		$class = get_called_class();
		$le = static::$ListingEntity[$class];
		if ($le !== null)
			return $le;
		return static::$ListingEntity[$class] = array_merge(qParseEntity("Type"), self::vpfuse__GetListingEntity());
	}

	/**
	 * Returns true if can provision
	 * @return boolean
	 */
	public function canDoProvisioning()
	{
		return true;
	}

	protected function getRights()
	{
		if ($this->_rights_loaded)
			return $this->_rights;
		$this->_rights_loaded = true;
		$cfg_file = rtrim(\QAutoload::GetRuntimeFolder(), "\\/")."/_users_security_cfg.php";
		//$_USER_RIGHTS - the variable for user rights
		if (file_exists($cfg_file))
			require_once($cfg_file);
		return $this->_rights = $_USER_RIGHTS;
	}

	public function getSecurityType()
	{
		// return $this->Type;
		$groups = $this->getGroupsDynaminc();
		$main_type = $groups['toplevel'] ? 'top' : ($groups['partner'] ? 'partner' : 'customer');
		$second_type = $groups['admin'] ? 'admin' : ($groups['sales'] ? 'sales' : (($main_type === 'customer') ? 'user' : 'support'));
		return $main_type.'/'.$second_type;
	}

	/**
	 * @api.enable
	 * 
	 * @param \Omi\Comm\TradeCompany $owner
	 */
	public static function SetupDefaultUser(\Omi\Comm\TradeCompany $owner = null, $email = null, $name = null, $firstname = null)
	{
		if (($usr = static::vpfuse__SetupDefaultUser($owner, $email, $name, $firstname)))
		{
			$usr->setType("Superadmin");
			$usr->save("Type");
		}
		return $usr;
	}

	/**
	 * @api.enable
	 * 
	 * @param \Omi\Comm\TradeCompany $owner
	 */
	public static function SetupAdminUser(\Omi\Comm\TradeCompany $owner = null, $email = null, $name = null, $firstname = null)
	{
		if (($usr = static::vpfuse__SetupAdminUser($owner, $email, $name, $firstname)))
		{
			$usr->setType("Superadmin");
			//$usr->save("Type");
			$usr->query("UPDATE Type=?", $usr->Type);
		}
		return $usr;
	}

	/**
	 * @api.enable
	 * Setup remote call user
	 * 
	 * @param \Omi\Comm\Reseller $context
	 * @param \Omi\Comm\Reseller $owner
	 * @param boolean $usedToCall
	 */
	public static function SetupRemoteCallUser(\Omi\Comm\Reseller $context, \Omi\Comm\Reseller $owner = null, $usedToCall = false)
	{
		if (($usr = static::vpfuse__SetupRemoteCallUser($context, $owner, $usedToCall)))
		{
			$usr->setType("Superadmin");
			//$usr->save("Type");
			$usr->query("UPDATE Type=?", $usr->Type);
		}
		
		return $usr;
	}

	public function is(string $group)
	{
		$type = $this->Type;
		switch ($group)
		{
			case "admin":
			case "Admin":
			{
				return ($type === 'Admin') || ($type === 'Superadmin');
			}
			case 'superadmin':
			case 'Superadmin':
			{
				return ($type === 'Superadmin');
			}
			case 'top_admin':
			case 'toplevel_admin':
			{
				$is_root = \Omi\App::IsHolder();
				return $is_root && (($type === 'Admin') || ($type === 'Superadmin'));
			}
			case 'toplevel_superadmin':
			{
				$is_root = \Omi\App::IsHolder();
				return $is_root && ($type === 'Superadmin');
			}
			case 'user':
			case 'User':
			{
				return ($type === 'User');
			}
			case 'sales':
			case 'Sales':
			{
				return ($type === 'Sales');
			}
			default:
			{
				return $this->Groups ? $this->Groups->has($group, 'Name', true) : false;
			}
		}
	}

	public static function GetGroupsList(bool $real_user = false)
	{
		$user = $real_user ?  static::GetCurrentUser() : static::GetSecurityCurrentUser();
		if (!$user)
			return [];
	
		return $user->getGroupsDynaminc();
	}

	public static function Get_Security_Type()
	{
		return ($c_user = static::GetSecurityCurrentUser()) ? $c_user->getSecurityType() : null;
	}

	public static function Get_RealUser_Security_Type()
	{
		return ($c_user = static::GetCurrentUser()) ? $c_user->getSecurityType() : null;
	}
	
	public static function Get_All_Possible_Groups()
	{
		return ['superadmin', 'admin', 'user', 'H2B_Superadmin', 'H2B_Channel'];
	}

	public function getGroupsDynaminc()
	{
		if ($this->_cached_grps !== null)
			return $this->_cached_grps;
		
		$grps = [];
		$type = $this->Type;
		
		// is partner, is top
		if ($this->Customer)
		{
			// it's a customer !
			$grps['customer'] = 1;
		}
		else
		{
			$grps['notcustomer'] = 1;
			$grps['partner'] = 1;
			$grps['owner'] = 1;
		}
	
		if (($type === 'Admin') || ($type === 'Superadmin'))
		{
			if ($grps['toplevel'])
				$grps['toplevel_admin'] = 1;
			$grps['admin'] = 1;
		}
		if ($type === 'Superadmin')
		{
			if ($grps['toplevel'])
				$grps['toplevel_superadmin'] = 1;
			$grps['superadmin'] = 1;
		}
		else if ($type === 'H2B_Superadmin')
			$grps['H2B_Superadmin'] = 1;
		else if ($type === 'H2B_Channel')
			$grps['H2B_Channel'] = 1;
		//if ($type === 'User')
		$grps['user'] = 1;
		if ($type === 'Sales')
			$grps['sales'] = 1;
		if ($this->Groups)
		{
			throw new \Exception('@todo user:Groups');
		}
		
		return ($this->_cached_grps = $grps);
	}

	public static function createLoginUrl()
	{
		# if ($restriction === filter_input(INPUT_SERVER, array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) ? "HTTP_X_FORWARDED_FOR" : "REMOTE_ADDR", FILTER_VALIDATE_IP))
		$request_ip = Q_REMOTE_ADDR;
		$auth_code = $_POST['auth_code'] ?: $_GET['auth_code'];
		$partner_code = $_POST['partner_code'] ?: $_GET['partner_code'];
		$login_identifier = $_POST['login_identifier'] ?: $_GET['login_identifier'];
		$login_email = $_POST['login_email'] ?: $_GET['login_email'];

		# option 2
		$login_access_code = $_GET['login_access_code'];

		/*
		var_dump([
					'request_ip' => $request_ip,
					'auth_code' => $auth_code,
					'partner_code' => $partner_code,
					'login_identifier' => $login_identifier,
					'login_email' => $login_email,
					'login_access_code' => $login_access_code
				]);
		*/

		{
			if (!in_array($request_ip, ___AUTH_IP))
			{
				#  case 403: $text = 'Forbidden'; break;
				http_response_code(403);
				throw new \Exception('Forbidden');
				die;
			}
			else if ($auth_code !== ___AUTH_CODE)
			{
				#  case 403: $text = 'Forbidden'; break;
				http_response_code(403);
				throw new \Exception('Forbidden');
				die;
			}
			else if (!$partner_code)
			{
				http_response_code(500);
				throw new \Exception('Missing partner or holder key');
				die;
			}
			
			if (!$login_identifier)
			{
				http_response_code(500);
				throw new \Exception('Missing login identifier');
				die;
			}

			

			# 1 ... create the user if not exists

			# qvar_dump($holder);

			# Test if we have the user or we need to create it
			$partner_obj = \QQuery('Companies.{* WHERE (Code=? OR Id=?) AND Owner.Id=Id', [$partner_code, $partner_code])->Companies;
			$partner_obj = $partner_obj ? $partner_obj[0] : null;
			
			if (!$partner_obj)
			{
				# is it the holder
				$holder = \Omi\App::GetHolder();
				if ($holder && (($holder->Code === $partner_code) || ($holder->Id === $partner_code)))
					$partner_obj = $holder;
			}
			if (!$partner_obj)
			{
				http_response_code(500);
				throw new \Exception('Invalid partner or holder');
				die;
			}

			# qvar_dumpk($partner_obj);

			# now try to find the login
			$login_user = \QQuery('Users.{* WHERE Username=? AND Owner.Id=? }', [$login_identifier, $partner_obj->getId()])->Users;
			$login_user = $login_user ? $login_user[0] : null;

			if (!$login_user)
			{
				# create it
				# \QApi::Insert();
				# Username : $login_identifier
				# Type : Superadmin
				# Password : generate it
				# Email : dummy atm 
				# Active : 1

				# switch context to ensure proper owner
				$saved_context = null;

				try
				{
					if (($saved_context = \Omi\App::GetCurrentOwner()->Id) != $partner_obj->getId())
						\Omi\App::SetupContext($partner_obj->getId());

					$data = [
						'Id' => '',
						'_ty' => 'Omi\User',
						'Username' => trim($login_identifier),
						'Password' => "GeneratedPass_".uniqid('', true),
						'Type' => 'Superadmin',
						'Email' => $login_email ?: (filter_var($login_identifier, FILTER_VALIDATE_EMAIL) ? $login_identifier : null),
						'Active' => 1,
					];

					$ret_data = \QApi::Insert('Users', $data);
					$login_user = $ret_data ? ($ret_data->Users ? $ret_data->Users[0] : null) : null;
				}
				finally 
				{
					if ($saved_context != \Omi\App::GetCurrentOwner()->Id)
						\Omi\App::SetupContext($saved_context);
				}
			}

			if (!$login_user)
			{
				http_response_code(500);
				throw new \Exception('Unable to prepare the user\'s login');
				die;
			}

			# now we need to setup an access valid for ... X hours
			$acc_code_data = [
							'Code' => sha1(uniqid("", true).json_encode([$_SERVER, time(), microtime(true)])),
							'ExpireDate' => date("Y-m-d H:i:s", time() + 3600),
							'Login' => ['Id' => $login_user->getId(), '_ty' => get_class($login_user)]
							];

			# qvar_dumpk($acc_code_data);

			$ret_data = \QApi::Insert('LoginAccessCodes', $acc_code_data);
			$login_access = $ret_data ? ($ret_data->LoginAccessCodes ? $ret_data->LoginAccessCodes[0] : null) : null;

			# qvar_dumpk($login_access);
			if (!$login_access)
			{
				http_response_code(500);
				throw new \Exception('Unable create access code');
				die;
			}

			return $login_access->Code;

		}
		
	}

	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function GetModelEntity($view_tag = null)
	{
		return self::before_nuvia__GetModelEntity($view_tag);
	}

	public static function GetListingEntity($view_tag = null)
	{
		$class = get_called_class();
		$le = static::$ListingEntity[$class];
		if ($le !== null)
			return $le;
		return static::$ListingEntity[$class] = self::before_nuvia__GetListingEntity();
	}

	public static function LoadInfo($identity)
	{		
		if (!$identity)
			return null;

		if (self::$_FullInfo && self::$_FullInfo[$identity->getId()])
			return self::$_FullInfo[$identity->getId()];

		if (!self::$_FullInfo)
			self::$_FullInfo = [];

		$str_query = static::$Load_Info_Entity;
		
		# qvar_dump(qParseEntity($str_query));
		
		$identity->query($str_query);
		
		return self::$_FullInfo[$identity->getId()] = $identity->User;
	}
}