<?php

error_reporting(E_ALL & ~(E_NOTICE | E_WARNING | E_DEPRECATED));

define('Q_MYSQL_TIMEZONE', date_default_timezone_get()); # 'Europe/Bucharest'

if (!defined('Q_GENERATED_VIEW_FOLDER_TAG'))
	define('Q_GENERATED_VIEW_FOLDER_TAG', 'backend');
const Q_SHOW_LAYOUT_BOXES = false;

const Q_VIEW_RES = __DIR__ . "/../common-app/view/res/";
const Q_VIEW_IMG_RESPONSIVE = __DIR__ . "/../common-app/view/img.responsive/";

const Q_Gen_Namespace = "Omi\\App\\View";
const Q_GENERATED_VIEW_FOLDER = '~generated-ui/';
const QGEN_SaveDirBase = Q_GENERATED_VIEW_FOLDER;
const Q_DATA_CLASS = "Omi\\App";
const Q_GENERATE_GRID_BOXES_BY_DEFAULT = true;
const Q_MODEL_SUBPART_IS_DEFAULT = true;
const Q_RUN_CODE_UPGRADE_TO_TRAIT = false;
const Q_RUN_CODE_NEW_AS_TRAITS = true;
const QGEN_Templates_Path = __DIR__ . "/../common/gens/templates/";
const QGEN_ConfigDirBase = "~backend_config/";

define('Q_REMOTE_ADDR', $_SERVER['REMOTE_ADDR']);

# @TODO
# 1. some kind of setup to save some info in CODE
# 2. mysql setup @ => sudo mysql -e "CREATE USER 'alex'@'localhost' IDENTIFIED BY 'Palm25tree\!';"
if (!defined('Q_MAIN_CODE_DIR'))
	define('Q_MAIN_CODE_DIR', 'code_inst/');

\QAutoload::LoadModule(__DIR__ . "/../common/gens/", false, 'omi', "omi");
\QAutoload::LoadModule(__DIR__ . "/../common/util/", false, 'omi_util', "omi");

\QAutoload::LoadModule(__DIR__ . "/../common-app/model/", false, 'mods_model', "mods");

if (function_exists('q_autoload_layers_callback'))
{
	q_autoload_layers_callback(true);
}
else
{
	\QAutoload::LoadModule(Q_MAIN_CODE_DIR . "classes/", false, Q_SAAS_PREFIX, Q_SAAS_PREFIX);
	\QAutoload::LoadModule(Q_MAIN_CODE_DIR . "model/", false, Q_SAAS_PREFIX.'_model', Q_SAAS_PREFIX);
}

if (!is_dir(Q_GENERATED_VIEW_FOLDER))
	qmkdir(Q_GENERATED_VIEW_FOLDER);
\QAutoload::AddWatchFolder(Q_GENERATED_VIEW_FOLDER, false, Q_GENERATED_VIEW_FOLDER_TAG, false, "ui_config");

\QAutoload::LoadModule(__DIR__ . "/../common/view/", false, 'omi_view', "omi");
\QAutoload::LoadModule(__DIR__ . "/../common-app/controller/", false, 'mods_controller', "mods");
\QAutoload::LoadModule(__DIR__ . "/../common-app/view/", false, 'mods_view', "mods");

if (function_exists('q_autoload_layers_callback')) {
	q_autoload_layers_callback(false);
}
else {
	if (is_dir(Q_MAIN_CODE_DIR . "controller/"))
		\QAutoload::LoadModule(Q_MAIN_CODE_DIR . "controller/", false, Q_SAAS_PREFIX.'_controller', Q_SAAS_PREFIX);
	\QAutoload::LoadModule(Q_MAIN_CODE_DIR . "view/", false, Q_SAAS_PREFIX.'_view', Q_SAAS_PREFIX);
}

\QAutoload::AddMainFolder("code/", "code", "code");

require_once 'code/_security.php';

if ((Q_REMOTE_ADDR === dev_ip) || (($_GET['_dev_mode_key_'] ?? '') === sha1(Q_DEV_MODE_KEY)))
{
	if (($_GET['force_resync'] ?? false))
		\QAutoload::EnableDevelopmentMode(true, true, true);

	else
	{
		\QAutoload::EnableDevelopmentMode(true);
	}
	
}

{
	# $mysql = new \QMySqlStorage("sql", "127.0.0.1", MyProject_MysqlUser, MyProject_MysqlPass, MyProject_MysqlDb, 3306);
	$mysql = new \QMySqlStorage("sql", defined('MyProject_MysqlHost') ? MyProject_MysqlHost : "127.0.0.1", 
						MyProject_MysqlUser, MyProject_MysqlPass, MyProject_MysqlDb, 
						ini_get("mysqli.default_port"),
						defined('MyProject_Mysql_Socket') ? MyProject_Mysql_Socket : ini_get("mysqli.default_socket"));

	$mysql->connect();
	$mysql->connection->query('SET NAMES utf8');
	$mysql->connection->query("SET SESSION sql_mode = CONCAT(@@sql_mode, ',NO_UNSIGNED_SUBTRACTION')");

	// we set the $mysql object as the main storage for our APP
	\QApp::SetStorage($mysql);
	// setup storages adaptors here
	
	// set Mysql time_zone, with Daylight Savings Time taken into account.
	$tz = (new DateTime('now', new DateTimeZone(Q_MYSQL_TIMEZONE)))->format('P');
	\QApp::GetStorage()->connection->query("SET time_zone='{$tz}';");
}

// set default language
\QModel::SetDefaultLanguage_Dim("en");
\QModel::SetLanguage_Dim('en');

{
	if (Q_REMOTE_ADDR === dev_ip)
	{
		if ($_GET['force_dbsync'])
		{
			\QApp::SetDataClass(Q_DATA_CLASS, true, true);
			die('DB Sync ready');
		}
		\QApp::SetDataClass(Q_DATA_CLASS);
		
		include('sync_lang.php');
		// \QApp::CleanupDatabase(true);
		// die('CleanupDatabase ready');
	}
	else
		\QApp::SetDataClass(Q_DATA_CLASS);
}

\QApp::EnableLegacyErrorHandling(false);

if ($_GET['__q_run_code_resync__'] ?? null)
	# we are done
	exit;
