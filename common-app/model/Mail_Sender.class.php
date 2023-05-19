<?php

namespace Omi;

/**
 * @author Mihaita
 *
 * @storage.table Mails_Senders
 * @model.captionProperties Username
 * @model.captions {"MailsSenders" : "System Email Accounts"}
 *
 * @class.name Mail_Sender
 */
abstract class Mail_Sender_mods_model_ extends \QModel
{
	/**
	 * @var int
	 */
	protected $Id;
	/**
	 * @fixValue trim
	 * @validation mandatory
	 * @storage.captions {"MailsSenders" : "Outgoing SMTP Server"}
	 * @var string
	 */
	protected $Host;
	/**
	 * @validation mandatory
	 * @var int
	 */
	protected $Port;
	/**
	 * @fixValue trim
	 * @storage.captions {"MailsSenders" : "Email"}
	 * @storage.attrs {"autocomplete" : "off"}
	 * @validation mandatory
	 * @var string
	 */
	protected $Username;
	/**
	 * @fixValue trim
	 * @display.type password
	 * @var string
	 */
	protected $Password;
	/**
	 * @fixValue trim
	 * @validation mandatory
	 * @var string
	 */
	protected $Email;
	/**
	 * @storage.type enum('tls','ssl')
	 * @fixValue trim
	 * @validation mandatory
	 *
	 * @var string 
	 */
	protected $Encryption;
	/**
	 * @fixValue trim
	 * @var string
	 */
	protected $FromAlias;
	/**
	 * @storage.dependency subpart
	 * @fixValue trim
	 * @var string[]
	 */
	protected $ReplyTo;
	/**
	 * @var boolean
	 */
	protected $Connection_Active;
	/**
	 * @storage.type TEXT
	 * 
	 * @var string
	 */
	protected $Email_Header_Text;
	/**
	 * @storage.type TEXT
	 * 
	 * @var string
	 */
	protected $Email_Footer_Text;

	public function getModelCaption($view_tag = null)
	{
		return $this->Email;
	}

	/**
	 * Send a test email to make sure that details added can connect to the server
	 * 
	 * @param type $selector
	 * @param type $transform_state
	 * @param type $_bag
	 * @return type
	 * @throws \Exception
	 */
	public function beforeSave($selector = null, $transform_state = null, &$_bag = null, $is_starting_point = true, $appProp = null)
	{
		$ret = parent::beforeSave($selector, $transform_state, $_bag, $is_starting_point, $appProp);

		// if is starting point and not trying to delete
		if ($is_starting_point && $appProp && ($this->getTransformState() != \QModel::TransformDelete))
		{
			$toCheckMailAccount = $this;
			if (!$this->isNew(true, $appProp))
			{
				$toCheckMailAccount = $this->getClone("Id, Host, Port, Encryption, Username, Password, Email");
				$toCheckMailAccount->populate("Host, Port, Encryption, Username, Password, Email");
				$wasChanged = !static::SameCredentialsUsed($toCheckMailAccount);
			}
			else
				$wasChanged = true;

			ob_start();
			
			try
			{				
				if ($wasChanged)
				{
					\Omi\Util\Email::SMTPMail($toCheckMailAccount->Host, $toCheckMailAccount->Port, $toCheckMailAccount->Encryption, 
						$toCheckMailAccount->Username, $toCheckMailAccount->Password, $toCheckMailAccount->Email, 
						$toCheckMailAccount->Email, "Test email", "Testing email sending", null, null, null, 2);
				}
				
				$str = ob_get_clean();
			}
			catch (\Exception$ex)
			{
				$str = ob_get_clean();
				throw new \Exception("Cannot connect to mail server!");
			}
		}

		return $ret;
	}
	
	public function setUsername($value, $check = true, $null_on_fail = false)
	{
		$fail = false;
		$value = (($value !== null) ? trim($value) : null);
		$return = (($check === false) ) ? $value : (((!empty($value))) ? (string)$value : ($fail = null));
		if (($fail === null) && (!$null_on_fail))
			throw new \Exception("Failed to assign value in setUsername");
		if ($check !== 1)
		{
			$this->Username = $return;
			$this->_wst["Username"] = true;
			$this->setEmail($this->Username);
		}
		return $return;
	}
	
	protected static function SameCredentialsUsed($mailAccount)
	{
		if (!$mailAccount->getId())
			return false;

		$prevMailAccnt = $mailAccount->getClone("Id");
		$prevMailAccnt->query("Host, Port, Encryption, Username, Password");

		return (
			($prevMailAccnt->Host == $mailAccount->Host) && 
			($prevMailAccnt->Port == $mailAccount->Port) && 
			($prevMailAccnt->Encryption == $mailAccount->Encryption) && 
			($prevMailAccnt->Username == $mailAccount->Username) && 
			($prevMailAccnt->Password == $mailAccount->Password)
		);
	}

	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function GetModelEntity($view_tag = null)
	{
		return static::GetSyncProps() . ", Host, Port, Encryption, Username, Password, Email, FromAlias, ReplyTo";
	}
	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function GetListingEntity($view_tag = null)
	{
		$class = get_called_class();
		$le = static::$ListingEntity[$class];
		if ($le !== null)
			return $le;
		return static::$ListingEntity[$class] = qParseEntity("Host, Port, Encryption, Username, Password, Email, FromAlias, ReplyTo");
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
				. "??Host?<AND[Host LIKE (?)]"
				. "??Username?<AND[Username LIKE (?)]"
				. "??QINSEARCH_Host?<AND[Host LIKE (?)]"
				. "??QINSEARCH_Username?<AND[Username LIKE (?)]"
				. "??QINSEARCH_Port?<AND[Port LIKE (?)]"
				. "??QINSEARCH_Encryption?<AND[Encryption LIKE (?)]"
				. static::GetListingQueryFilters()
				. "??WHR_Search?<AND[Username LIKE (?)]"
			. " ORDER BY "
				. "??OBY_Host?<,[Host ?@]"
			. " ??LIMIT[LIMIT ?,?]";
		return $q;
	}
}
