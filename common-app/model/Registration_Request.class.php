<?php

namespace Omi\Comm;

/**
 * model.captionProperties 
 * 
 * @storage.table Registration_Requests
 * 
 * @class.name Registration_Request
 */
abstract class Registration_Request_mods_model_ extends \QModel
{
	/**
	 * @var int
	 */
	protected $Id;
	
	/**
	 * @var Company
	 */
	protected $Company;
	/**
	 * @var \Omi\User
	 */
	protected $User;
	/**
	 * @storage.type VARCHAR(32)
	 * 
	 * @var string
	 */
	protected $IP;
	/**
	 * @var datetime
	 */
	protected $Date;
	
	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function GetListingQuery($selector = null)
	{
		$selector = $selector ?: static::GetListingEntity();
        
		# Channel_Contracts.{Channel
		
		$q = (is_array($selector) ? qImplodeEntity($selector) : $selector)." "
				# . " SELECT {} "
				. " WHERE 1 "
				. "??Id?<AND[Id=?]"
				. "??Id_IN?<AND[Id IN (?)]"				
			. " GROUP BY Id "
			. " ORDER BY "
				. "??OBY_Id?<,[Id ?@]"
			. " ??LIMIT[LIMIT ?,?]";
        
		return $q;
	}
}
