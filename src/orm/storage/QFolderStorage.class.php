<?php

/**
 * @class.name QFolderStorage
 * @class.abstract true
 */
abstract class QFolderStorage_frame_ extends QStorage implements QIStorageFolder 
{
	
	/**
	 * The default folder to use when loaded
	 *
	 * @var QStorageFolder
	 */
	public $default_folder;
	/**
	 * The default folder to use when loaded
	 *
	 * @var QStorageFolder
	 */
	public $current_folder;
	/**
	 * The children list
	 *
	 * @var QIStorageEntry[]
	 */
	public $children;
	
	/**
	 * Gets the default storage folder
	 *
	 * @return QStorageFolder
	 */
	public function getDefaultFolder()
	{
		return $this->default_folder;
	}
	/**
	 * Gets the default storage folder
	 *
	 * @return QStorageFolder
	 */
	public function getCurrentFolder()
	{
		return $this->current_folder;
	}
	
	/**
	 * Gets the child entries
	 *
	 * @return QIStorageEntry[]
	 */
	public function getChildEntries()
	{
		return $this->children;
	}
}

