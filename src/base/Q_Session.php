<?php

/**
 * @credit https://stackoverflow.com/users/1205909/tristan-charbonnier
 * 
 * @TODO - is it better to use interface SessionHandlerInterface { ?
 * @TODO - maybe clone connection and run it async ?
 */
final class Q_Session
{
    private static $debug;
    private static $dbc;
	private static $table;
	private static $db;
	
	private static $session_name;
	private static $session_save_path;

    public static function init(string $db = null, mysqli $connection = null, string $table_name = 'Sessions_Data_', bool $debug = false)
    {
        static::$debug = $debug;
		static::$dbc = $connection ?? \QApp::GetStorage()->connection;
		if (!static::$dbc)
			throw new \Exception('Missing connection to setup session');
		static::$table = $table_name;
		static::$db = $db;

        $rc = session_set_save_handler(
            [__CLASS__, 'open'],
            [__CLASS__, 'close'],
            [__CLASS__, 'read'],
            [__CLASS__, 'write'],
            [__CLASS__, 'destroy'],
            [__CLASS__, 'gc']);
		
/*
	callable $open,
    callable $close,
    callable $read,
    callable $write,
    callable $destroy,
    callable $gc,
*/
		
		if (!$rc) {
			throw new \Exception('Unable to setup session handler. ' . json_encode(error_get_last()));
		}
		
		$rc = static::setup_table();
		if (!$rc)
			throw new \Exception('Unable to setup table');
		
		# it looks like it's need
		register_shutdown_function(function () {
			session_write_close();
		});
    }
	
	/*
2024-11-07 08:43:45 - Q_Session::open : ["\/home\/tf_h2b\/sessions","PHP_SESSID_H2B_PORTAL"]
2024-11-07 08:43:45 - Q_Session::read : ["mq0a2a1pf7fuelskam5of90hn7"]
2024-11-07 08:43:45 - Q_Session::write : ["mq0a2a1pf7fuelskam5of90hn7",""]
2024-11-07 08:43:45 - Q_Session::close : []
	 */

    public static function open(string $savePath, string $sessionName)
    {
		# file_put_contents("../session_log.txt", date("Y-m-d H:i:s") . " - " . __METHOD__ . " : " . json_encode(func_get_args()) . "\n" , FILE_APPEND);
		static::$session_name = $sessionName;
		static::$session_save_path = $savePath;
		
		return true;
    }

    public static function close()
    {
		# nothing to do, data is always saved on write
		return true;
    }

    public static function read(string $sessionId)
    {
		$esc_tab = static::get_escaped_table();
		
		$sql = "SELECT * FROM {$esc_tab} WHERE `Session_Id` = '"._mySc($sessionId)."';";
		
		$rc = static::$dbc->query($sql);
		$row = $rc ? $rc->fetch_assoc() : null;
		if ($rc) {
			if (!$row) {
				# new entry ... save it
				$sql_insert = "INSERT INTO {$esc_tab} (`Session_Id`,`IP`,`C_Time`,`M_Time`,`A_Time`,`Data`) 
							VALUES ('"._mySc($sessionId)."','"._mySc($_SERVER['REMOTE_ADDR'])."',NOW(),NOW(),NOW(),'');";

				$rc_insert = static::$dbc->query($sql_insert);
				if (!$rc_insert) {
					# should we do something ?
				}
			}
			else {
				# update access time
				$sql_update = "UPDATE {$esc_tab} SET `A_Time`=NOW() WHERE `Session_Id` = '"._mySc($sessionId)."';";
				$rc_update = static::$dbc->query($sql_update);
				if (!$rc_update) {
					# should we do something ?
				}
			}
		}
		else if (!$rc) {
			# should we do something ?
		}
		
		return $row['Data'] ?? '';
    }

	public static function write(string $sessionId, string $data)
    {
		$esc_tab = static::get_escaped_table();
		$sql_insert = "INSERT INTO {$esc_tab} (`Session_Id`,`IP`,`M_Time`,`Data`) 
						VALUES ('"._mySc($sessionId)."','"._mySc($_SERVER['REMOTE_ADDR'])."',NOW(),'"._mySc($data)."')
							ON DUPLICATE KEY UPDATE `IP`=VALUES(`IP`),`M_Time`=NOW(),`M_Time`=NOW(),`Data`=VALUES(`Data`);";
		$rc_insert = static::$dbc->query($sql_insert);
		if (!$rc_insert) {
			# should we do something ?
		}
		
		return true;
    }

    public static function destroy(string $sessionId)
    {
		$esc_tab = static::get_escaped_table();
		$sql_delete = "DELETE FROM {$esc_tab} WHERE `Session_Id` = '"._mySc($sessionId)."';";
		$rc_delete = static::$dbc->query($sql_delete);
		if (!$rc_delete) {
			# should we do something ?
		}
		return true;
    }

    public static function gc(int $lifetime)
    {
		# cleanup via cron
		register_shutdown_function(function () use ($lifetime) {
			
			/*
			$esc_tab = static::get_escaped_table();
			$lifetime = (int)$lifetime; # redundant cast for sql security
			$sql_delete = "DELETE FROM {$esc_tab} WHERE `M_Time` < DATE_SUB(NOW(), INTERVAL {$lifetime} SECOND);";
			$rc_delete = static::$dbc->query($sql_delete);
			if (!$rc_delete) {
				# should we do something ?
			}
			*/
			
		});
		
		return true;
    }
	
	# not used atm
	public static function create_sid()
	{
		# This callback is executed when a new session ID is required. No parameters are provided, and the return value should be a string that is a valid session ID for your handler. 
		return ;
	}
	# not used atm
	public static function validate_sid(string $key)
	{
		#  This callback is executed when a session is to be started, a session ID is supplied and session.use_strict_mode is enabled. The key is the session ID to validate. A session ID is valid, if a session with that ID already exists. The return value should be true for success, false for failure. 
		return ;
	}
	# not used atm
	public static function update_timestamp(string $key, string $val)
	{
		# This callback is executed when a session is updated. key is the session ID, val is the session data. The return value should be true for success, false for failure. 
		return ;
	}
	
	protected static function get_escaped_table()
	{
		return (static::$db ? "`".static::$db."`." : '')."`".static::$table."`";
	}
	
	public static function setup_table()
	{
		$sql_create_table = "
			CREATE TABLE IF NOT EXISTS ".static::get_escaped_table()." (
			  `\$id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `Session_Id` varchar(40) NOT NULL DEFAULT '',
			  `IP` varchar(46) NOT NULL DEFAULT '',
			  `C_Time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `M_Time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `A_Time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `Data` MEDIUMBLOB /*!100301 COMPRESSED*/ NOT NULL DEFAULT '',
			  PRIMARY KEY (`\$id`),
			  UNIQUE KEY `Session_Id` (`Session_Id`),
			  KEY `IP` (`IP`),
			  KEY `C_Time` (`C_Time`),
			  KEY `M_Time` (`M_Time`),
			  KEY `A_Time` (`A_Time`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
			";

		$rc = static::$dbc->query($sql_create_table);
		
		return $rc;
	}
}
