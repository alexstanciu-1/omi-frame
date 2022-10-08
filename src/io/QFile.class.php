<?php

/**
 * QFile
 *
 * @author Alex
 * @storage.table Files
 * @class.name QFile
 */
abstract class QFile_frame_ extends QModel
{
	
	// hidden property: _fstorage
	
	/**
	 * @var file
	 */
	public $Path;
	
	/**
	 * DEPRECATED !!!
	 * 
	 * Called when an upload was made and you need to do something with the file
	 * As an option the file could be handled on upload
	 * 
	 * @param string $property
	 * @param string[] $upload_info
	 */
	public function handleUpload($property, $upload_info)
	{
		
	}
}

