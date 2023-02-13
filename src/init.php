<?php

if (!defined('Q_IS_TFUSE'))
	define('Q_IS_TFUSE', false);

if (PHP_VERSION_ID < 80000)
{
	define("T_NAME_QUALIFIED", 314);
	define("T_NAME_FULLY_QUALIFIED", 312);
	define("T_NAME_RELATIVE", 313);
}

if (Q_IS_TFUSE)
{
	/**
	 * First include QFirewall
	 */
	if (file_exists(__DIR__ . "/useful/QFirewall.php"))
	{
		require_once(__DIR__ . "/useful/QFirewall.php");
		#\QFirewall::BlockIPByCountry();
		\QFirewall::BlockIP();
		\QFirewall::UpdateRequestsCount(defined('Q_FIREWALL_LOG_PATH') ? Q_FIREWALL_LOG_PATH : null);
	}
	if (!defined('Q_SECURE_TPLS'))
		define('Q_SECURE_TPLS', false);
}
else
{
	if (!defined('Q_SECURE_TPLS'))
		define('Q_SECURE_TPLS', true);
}

if (!defined("JSON_UNESCAPED_SLASHES"))
	define("JSON_UNESCAPED_SLASHES", 64);

if (!defined("QORM_IDCOL"))
	define("QORM_IDCOL", "\$id");
if (!defined("QORM_TYCOL"))
	define("QORM_TYCOL", "\$_type");
if (!defined("QORM_TYCOLPREFIX"))
	define("QORM_TYCOLPREFIX", "\$");
if (!defined("QORM_TYCOLSUFIX"))
	define("QORM_TYCOLSUFIX", "\$_type");
if (!defined("QORM_FKPREFIX"))
	define("QORM_FKPREFIX", "\$");
if (!defined("PHP_INT_MIN"))
	define("PHP_INT_MIN", ~PHP_INT_MAX);

if (!defined("Q_Thousands_Separator"))
	define("Q_Thousands_Separator", ",");

// CONSTANTS 
const __IN_PHP__ = true;

/**
 * This is a great way to ensure global unique IDs for types declarations
 */
if (!defined("Q_FRAME_GET_ID_TYPE"))
	define('Q_FRAME_GET_ID_TYPE', "https://www.omibit.com/API/types/");
define('Q_FRAME_MIN_ID_TYPE', 4096);

// establish frame path
define("Q_FRAME_PATH", __DIR__."/");
define("Q_FRAME_BPATH", dirname(__DIR__)."/");

// fix the bug of having double slashes in SCRIPT_FILENAME or SCRIPT_NAME
$_filenameDir = realpath(getcwd())."/";
# $_scriptNameDir = preg_replace("#(^|[^:])//+#", "\\1/", rtrim(dirname($_SERVER["SCRIPT_NAME"]), "/")."/");

if (!defined("Q_RUNNING_PATH"))
	define("Q_RUNNING_PATH", $_filenameDir);

if (!defined("BASE_HREF"))
{
	$_web_dir = realpath(substr($_SERVER["SCRIPT_FILENAME"], 0, -strlen($_SERVER["SCRIPT_NAME"])));
	if (!$_web_dir)
		throw new \Exception('The running dir is outside the current dir. Not implemented atm.');

	$_scriptNameDir = substr($_filenameDir, strlen($_web_dir));
	define("BASE_HREF", $_scriptNameDir);
}

if (defined('Q_CODE_DIR'))
{
	define("Q_FRAME_REL", substr(Q_FRAME_PATH, strlen(Q_CODE_DIR)));

	define("Q_FRAME_BREL", substr(Q_FRAME_BPATH, strlen(Q_CODE_DIR)));
	
	if (Q_CODE_DIR !== Q_RUNNING_PATH)
	{
		define("Q_APP_REL", BASE_HREF . substr(Q_RUNNING_PATH, strlen(Q_CODE_DIR)));
	}
	else
		define("Q_APP_REL", BASE_HREF);
	
	define("Q_REQ_REL", substr($_SERVER["REQUEST_URI"], strlen(Q_APP_REL)));
}
else
{
	define("Q_FRAME_REL", substr(Q_FRAME_PATH, strlen(Q_RUNNING_PATH) - strlen(BASE_HREF)));

	define("Q_FRAME_BREL", substr(Q_FRAME_BPATH, strlen(Q_RUNNING_PATH) - strlen(BASE_HREF)));
	define("Q_APP_REL", substr(Q_RUNNING_PATH, strlen(Q_RUNNING_PATH) - strlen(BASE_HREF)));
	define("Q_REQ_REL", substr($_SERVER["REQUEST_URI"], strlen(Q_APP_REL)));
}

/**
 * First include QObject because QAutoload needs it
 */
require_once(__DIR__."/base/QObject.php");
/**
 * Next we register the error handler
 */
require_once(__DIR__."/base/QErrorHandler.php");
/**
 * Include Autoload, it's basic usage
 */
require_once(__DIR__."/base/QAutoload.php");
/**
 * Include base functions
 */
require_once(__DIR__."/functions/base.php");

spl_autoload_register(["QAutoload", "AutoloadClass"]);

mysqli_report(MYSQLI_REPORT_OFF);

/**
 * Initialize
 */
QAutoload::AddWatchFolder(Q_FRAME_PATH, false, "frame", false, "frame");

set_error_handler(array("QErrorHandler", "HandleError"), E_ALL & ~(E_NOTICE | E_USER_NOTICE | E_STRICT | E_DEPRECATED | E_WARNING | E_COMPILE_WARNING));
set_exception_handler(array("QErrorHandler", "UncaughtExceptionHandler"));
register_shutdown_function(array("QErrorHandler", "OnShutdown"));
register_shutdown_function(function () {
	# yes, we need to make sure it runs last!
	register_shutdown_function(['QErrorHandler', 'Cleanup_On_End']);
});

