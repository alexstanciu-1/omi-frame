<?php


/**
 * @class.name QStorageFolder
 */
abstract class QStorageFolder_frame_ extends QStorageEntry implements QIStorageFolder 
{
	
	/**
	 * The children list
	 *
	 * @var QIStorageEntry[]
	 */
	public $children;
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
