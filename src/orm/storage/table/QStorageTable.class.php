<?php


/**
 * @class.name QStorageTable
 * @class.abstract true
 */
abstract class QStorageTable_frame_ extends QStorageEntry implements QIStorageContainer 
{
	
	// a storage table is a table with headings (columns) and data inside it
	// later on this can be used to interact with CSV(s),XLS and more table like structures
	// we may also consider XML like models in the future
	
	/**
	 * The list of columns
	 *
	 * @var QStorageTableColumn[]
	 */
	public $columns;
	/**
	 * The list of references
	 *
	 * @var QStorageTableReference[]
	 */
	public $references;
}
