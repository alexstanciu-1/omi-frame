<?php

namespace Omi;

/**
 * @storage.table Documents
 * 
 * @model.captionProperties File
 * 
 * @class.name Document
 */
abstract class Document_mods_model_ extends \QModel
{
	/**
	 * @var string
	 */
	protected $Id;
	/**
	 * @storage.filePath uploads/documents/
	 * 
	 * @var file
	 */
	protected $File;
	/**
	 * @var string
	 * 
	 * @fixValue trim
	 */
	protected $IP;
	/** 
	 * @var \Omi\User
	 */
	protected $User;
	/**
	 * @var datetime
	 */
	protected $Date;
}

