<?php

namespace Omi;

/**
 * @storage.table LoginLog
 *
 * @class.name LoginLog
 */
abstract class LoginLog_mods_model_ extends \QModel
{
	
	/**
	 * @var int
	 */
	protected $Id;
	/**
	 * @var User
	 */
	protected $User;
	/**
	 * @storage.type text
	 * @var string
	 */
	protected $UserData;
	/**
	 * @var string
	 */
	protected $SessionId;
	/**
	 * @var datetime
	 */
	protected $Date;
	/**
	 * @var string
	 */
	protected $Ip;
	
}

