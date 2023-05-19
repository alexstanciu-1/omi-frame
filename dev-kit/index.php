<?php

const Q_IS_TFUSE = true;

require_once("../src/init.php");

error_reporting(E_ALL & ~(E_NOTICE | E_WARNING | E_DEPRECATED));

const dev_ip = '127.0.0.1';
const dev_email = 'ealexs@gmail.com';

define('Q_MYSQL_TIMEZONE', date_default_timezone_get()); # 'Europe/Bucharest'

if (!defined('Q_GENERATED_VIEW_FOLDER_TAG'))
	define('Q_GENERATED_VIEW_FOLDER_TAG', 'backend');
const Q_SHOW_LAYOUT_BOXES = false;

const Q_VIEW_RES = "../common-app/view/res/";
const Q_VIEW_IMG_RESPONSIVE = "../common-app/view/img.responsive/";

const Q_Gen_Namespace = "Omi\\App\\View";
const Q_GENERATED_VIEW_FOLDER = '~includes/generated-ui/';
const QGEN_SaveDirBase = Q_GENERATED_VIEW_FOLDER;
const Q_DATA_CLASS = "Omi\\App";
const Q_GENERATE_GRID_BOXES_BY_DEFAULT = true;
const Q_MODEL_SUBPART_IS_DEFAULT = true;
const Q_RUN_CODE_UPGRADE_TO_TRAIT = false;
const Q_RUN_CODE_NEW_AS_TRAITS = true;
const QGEN_Templates_Path = "../common/gens/templates/";
const QGEN_ConfigDirBase = "~backend_config/";
const Q_DEFAULT_ENCRYPT_KEY = 'kLhZG2Ngwq+0wisB';

const MyProject_MysqlUser = "alex";
const MyProject_MysqlPass = 'Palm25tree!';
if (!defined('MyProject_MysqlDb'))
	define('MyProject_MysqlDb', "omi_kit_alex");

define('Q_REMOTE_ADDR', $_SERVER['REMOTE_ADDR']);

const Default_Logo = "uploads/branding/logos/nfon_logo_rsz.png";

# @TODO
# 1. some kind of setup to save some info in CODE
# 2. mysql setup @ => sudo mysql -e "CREATE USER 'alex'@'localhost' IDENTIFIED BY 'Palm25tree\!';"

\QAutoload::LoadModule("../common/gens/", false, 'omi', "omi");
\QAutoload::LoadModule("../common/util/", false, 'omi_util', "omi");

\QAutoload::LoadModule("../common-app/model/", false, 'mods_model', "mods");

\QAutoload::LoadModule("code_inst/model/", false, 'dk_model', "dk");

\QAutoload::AddWatchFolder(Q_GENERATED_VIEW_FOLDER, false, Q_GENERATED_VIEW_FOLDER_TAG, false, "ui_config");

\QAutoload::LoadModule("../common/view/", false, 'omi_view', "omi");
\QAutoload::LoadModule("../common-app/controller/", false, 'mods_controller', "mods");
\QAutoload::LoadModule("../common-app/view/", false, 'mods_view', "mods");

\QAutoload::LoadModule("code_inst/view/", false, 'dk_view', "dk");

\QAutoload::AddMainFolder("code/", "code", "code");

require_once 'code/_security.php';

\QAutoload::EnableDevelopmentMode(true);
# \QAutoload::EnableDevelopmentMode(true, true, true);

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

if ($_SERVER['REMOTE_ADDR'] === '127.0.0.1')
{
	$username = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : $_SERVER['USER'];
	if (!$username)
		exit("UNABLE TO DETERMINE USER.");
	
	# verify connection
	$conn = \QApp::GetStorage()->connection;
	
	if (! (isset($conn->connect_errno) && ($conn->connect_errno === 0)))
		exit("MYSQL CONNECTION TEST FAILED.");
	
	$rc = $conn->query('SELECT `$id` FROM `$App` WHERE `$id`=1 LIMIT 1');
	if (! ($rc && ($r = $rc->fetch_array())))
	{
		$ins = $conn->query('INSERT INTO `$App` (`$id`) VALUES (1)');
		if (!$ins)
			exit("UNABLE TO INIT App.Id.");
	}
	
	$db_user = \QQuery("Users.{Id,Username,Active WHERE Username=? ORDER BY Id LIMIT 1}", [$username])->Users;
	
	$db_user = $db_user[0] ?? null;
	
	if (!$db_user)
	{
		$ret = \QApi::Merge("Users", \Omi\User::FromArray(["Username" => $username, "Password" => "AA12##".uniqid(), "Email" => dev_email ?? null, "Active" => 1]) );
		$db_user = $ret->Users[0] ?? null;
	}
	
	# @TODO - also setup owner for user
	
	if (!$db_user)
		exit("UNABLE TO SETUP USER.");
	
	# login
	$rc = \Omi\User::LoginUser($db_user);
	if ($rc !== true)
		exit("UNABLE TO LOGIN USER.");
}
else
	exit("NOT ALLOWED OUTSIDE LOCALHOST.");
\QApp::Run(new \Omi\View\Controller());
