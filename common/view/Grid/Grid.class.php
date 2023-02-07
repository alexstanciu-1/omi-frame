<?php

namespace Omi\View;


/**
 * @class.name Grid
 * @class.abstract true
 */
abstract class Grid_omi_view_ extends \QWebControl
{
	use Grid_Security, Grid_Methods;
	
	const stay_on_page_after_save_NO_ACTION = ["NO_ACTION"];

	public $rowsOnPage = 50;
	/**
	 * From where data should be used
	 * @var string
	 */
	public $from;
	/**
	 * @var string
	 */
	public $csvSelector;
	/**
	 * The settings of the grid
	 * @var string
	 */
	public $settings = [];
	/**
	 * The selectors to be used for a specific action/mode
	 * @var string[]|array[]
	 */
	public $selectors;
	/**
	 * @var array
	 */
	public $grid_params;
	/**
	 * @var string
	 */
	public $grid_mode = "list";
	/**
	 * @var scalar
	 */
	public $grid_id;
	/**
	 * @var array
	 */
	public $submitData;
	/**
	 * @var string
	 */
	public $isOmiControl = true;
	/**
	 * @var string
	 */
	public $processAction = "post";
	/**
	 * @var string
	 */
	public $excelExportFileName;
	/**
	 * @var string
	 */
	public $pdfExportFileName;
	/**
	 * @var boolean 
	 */
	public $inExport = false;
	/**
	 * @var the logged in user
	 */
	public static $User = null;
	/**
	 * Class short name
	 * @var string
	 */
	//public static $FromAlias = null;
	
	public $can_export_pdf = true;

	public $can_export_excel = true;

	public $can_export_csv = true;
	
	public $max_export_limit = ['all' => 10000, 'csv' => 100000];

	public $can_import_from_csv = false;

	public $caption = null;

	public $show_caption_action = true;
	
	public $jsPropsSelector = "rowsOnPage, parentPrefixUrl, from, settings.*, selectors.*, grid_params, grid_mode, grid_id, processAction";

	public static $_USE_SECURITY_FILTERS = false;
	
	public static $FromAlias;

	/**
	 * @var array 
	 * - we have config property in gen files for each grid (cannot declare it here because of trait issues)
	 */
	//public static $CONFIG;


}

