<?php

/**
 * @class.name QStorageTableColumn
 * @class.abstract true
 */
abstract class QStorageTableColumn_frame_ extends QModel
{
	
	/**
	 * The name of the column
	 *
	 * @var string
	 */
	public $name;
	/**
	 * The parent table
	 *
	 * @var QStorageTable
	 */
	public $table;
}
