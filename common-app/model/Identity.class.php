<?php

namespace Omi;

/**
 * @storage.table Identities
 *
 * @class.name Identity
 */
abstract class Identity_mods_model_ extends \QModel
{
	public static $_Identities = array();

	/**
	 * @var User
	 */
	protected $User;
	/**
	 * @var \Omi\Session
	 */
	protected $Session;


	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function commerce__GetModelEntity($view_tag = null)
	{
		return "User.Id, Session.SessionId";
	}
	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function commerce__GetListingEntity()
	{
		$class = get_called_class();
		$le = static::$ListingEntity[$class];
		if ($le !== null)
			return $le;
		return static::$ListingEntity[$class] = qParseEntity("User.Id, Session.SessionId");
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
				. "??User?<AND[User.Id=?]"
				. "??Session?<AND[Session.SessionId=?]"
				. "??NoUser?<AND[ISNULL(User.Id)]"
			. " ??LIMIT[LIMIT ?,?]";
		return $q;
	}
	/**
	 * Get Identity For current session
	 * 
	 * @api.enable
	 * 
	 * @param string $session_id
	 * 
	 * @return Identity
	 */
	public static function GetIdentity()
	{
		if (!(session_status() === PHP_SESSION_ACTIVE))
			session_start();

		$ip = Q_REMOTE_ADDR;
		if (session_status() === PHP_SESSION_ACTIVE)
			$session_id = session_id();
		
		if ((!$session_id) || (!$ip))
			return false;

		$identity = Identity::QueryFirst("Id,User.Id WHERE Session.SessionId=? AND Session.IP=?", [$session_id, $ip]);
		return $identity ?: false;
	}
	/**
	 * @api.enable
	 * 
	 * Returns the current identity
	 * If the identity is not found then it is created
	 * 
	 * @return Identity
	 */
	public static function GetCurrent()
	{
		session_start();
		$sessionId = session_id();

		$identity = static::GetIdentity();
		$user = $identity ? $identity->User : null;
		$indx = $sessionId."~".($user ? $user->getId() : "_0");
		if (isset(self::$_Identities[$indx]))
			return self::$_Identities[$indx];

		if (!$identity)
		{
			$ip = Q_REMOTE_ADDR;
			$session = Session::QueryFirst("* WHERE SessionId=? AND IP=?", [$sessionId, $ip]);
			if (!$session)
			{
				$session = new Session();
				$session->SessionId = $sessionId;
				$session->IP = $ip;
			}
			$identity = new Identity();
			$identity->Session = $session;
			$identity->merge("Id,Session.{Id,SessionId,IP}");
		}
		return self::$_Identities[$indx] = $identity;
	}

	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function GetModelEntity($view_tag = null)
	{
		return self::commerce__GetModelEntity($view_tag);
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
		return static::$ListingEntity[$class] = self::commerce__GetListingEntity();
	}
}
