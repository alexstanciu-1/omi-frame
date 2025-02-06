<?php

namespace Omi\Comm;

/**
 * @author Alex
 *
 * @storage.table Content
 *
 * @model.captionProperties Name
 * 
 * @class.name Content
 */
abstract class Content_mods_model_ extends \QModel
{
	/**
	 * @var int
	 */
	protected $Id;
	/**
	 * @var string
	 */
	protected $Name;
	/**
	 * @var string
	 */
	protected $ShortDescription;
	/**
	 * @storage.type TEXT
	 * 
	 * @var string
	 */
	protected $Text_HTML;

	# +images, +video @todo when needed
}


