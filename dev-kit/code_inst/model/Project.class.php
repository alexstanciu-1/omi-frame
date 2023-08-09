<?php

namespace Omi\DK;

/**
 * @author Alex
 * 
 * @storage.table Projects
 *
 * @model.captionProperties Id,Name
 * @class.name Project
 */
abstract class Project_dk_model_ extends \QModel
{
	/**
	 * @var string
	 */
	protected $Name;
	/**
	 * @var string
	 */
	protected $Path;
	
	/**
	 * @var string
	 */
	protected $Exec_Path;
	
	/**
	 * @var string
	 */
	protected $Dev_URL;
	
}

