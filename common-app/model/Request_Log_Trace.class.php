<?php

namespace Omi;

/**
 * @author Alex
 * 
 * @storage.table Request_Logs_Traces
 *
 * @class.name Request_Log_Trace
 */
abstract class Request_Log_Trace_mods_model_ extends \QModel
{
	/**
	 * @var int
	 */
	protected $Id;
	/** 
	 * @var Request_Log
	 */
	protected $Request;
	/**
	 * @var string
	 */
	protected $Index;
	/**
	 * @storage.type DECIMAL(20,4)
	 * 
	 * @var float
	 */
	protected $Timestamp_ms;
	/**
	 * @storage.type DECIMAL(20,4)
	 * 
	 * @var float
	 */
	protected $Timestamp_ms_end;
	/**
	 * @var boolean
	 */
	protected $Is_Error;
	/**
	 * @storage.index
	 * @storage.type VARCHAR(4096)
	 * 
	 * @var string
	 */
	protected $Tags;
	/**
	 * @storage.type MEDIUMTEXT
	 * @storage.compressed
	 * 
	 * @var string
	 */
	protected $Traces;
	/**
	 * @storage.type LONGTEXT
	 * @storage.compressed
	 * 
	 * @var string
	 */
	protected $Data;
}
