<?php

namespace Omi;

/**
 * Description of Session
 *
 * @storage.table Sessions
 *
 * @author Mihaita
 *
 * @model.captionProperties Id,SessionId,IP
 * @class.name Session
 */
abstract class Session_mods_model_ extends \QModel
{
	
	/**
	 * @storage.index unique
	 * @var string
	 * 
	 * @fixValue trim
	 */
	protected $SessionId;
	/**
	 * @var string
	 * 
	 * @fixValue trim
	 */
	protected $IP;

	public function getModelCaption($view_tag = null)
	{
		return $this->SessionId;
	}
}