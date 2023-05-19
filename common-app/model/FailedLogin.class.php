<?php

namespace Omi;

/**
 * Description of FailedLogin
 * @storage.table FailedLogins
 *
 * @author Omi-Mihai
 * @model.captions {"FailedLogins" : "Banned Logins"}
 * @class.name FailedLogin
 */
abstract class FailedLogin_mods_model_ extends \QModel
{	
	/**
	 * @var int 
	 */
	protected $Id;
	/**
	 * @var datetime
	 */
	protected $LastTry;
	/**
	 * @var string
	 */
	protected $Ip;
	/**
	 * @var string
	 */
	protected $Username;
	/**
	 * @var datetime
	 * @storage.captions {"FailedLogins" : "Banned Until"}
	 */
	protected $Ban;
	/**
	 * @var int
	 */
	protected $Count;
	
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
			. "??Ip?<AND[Ip LIKE (?)]"
			. "??Ban?<AND[Ban LIKE (?)]"
			. "??NO_IP?<AND[ISNULL(Ip)]"
			. "??Username?<AND[Username LIKE (?)]"
			. "??QINSEARCH_Username?<AND[Username LIKE (?)]"
			. "??QINSEARCH_Ip?<AND[Ip LIKE (?)]"
			. "??QINSEARCH_Ban?<AND[Ban LIKE (?)]"
			. "??UsernameIn?<AND[Username IN (?)]"
			. "??NoUsername?<AND[ISNULL(Username)]"
			. "??Banned?<AND[!ISNULL(Ban)]"
		
			. " ORDER BY "
				. "??OBY_Count?<,[Count ?@]"

		. "??LIMIT[LIMIT ?,?]";
		return $q;
	}
}