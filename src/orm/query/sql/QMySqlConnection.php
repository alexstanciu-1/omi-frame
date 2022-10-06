<?php

class QMySqlConnection extends \mysqli
{
	public static $Audit = false;
	
	public static $TotalQTime = null;
	
	protected $mysqlData = [];

	public function __construct(string $host, string $username, string $passwd, string $dbname, int $port, string $socket = null)
	{
		$this->mysqlData = [
			'dbname' => $dbname,
		];
		return parent::__construct($host, $username, $passwd, $dbname, $port, $socket);
	}

	public function query($query, $resultmode = MYSQLI_STORE_RESULT)
	{
		
		$ret = parent::query($query, $resultmode);
		if (Q_IS_TFUSE && ($ret === false))
		{
			if (defined('LOGS_DIR') && is_dir(LOGS_DIR))
			{
				$dir = LOGS_DIR . 'mysql_errors/' . date('Y-m') . "/";
				if (!is_dir($dir))
				{
					qmkdir($dir);
					chgrp($dir, 'omi');
					chmod($dir, 0775);
				}
				$processUser = ($p_id = posix_geteuid()) ? (($tmp_zzz = posix_getpwuid($p_id)) ? $tmp_zzz['name'] : null) : null;
				$log_str = date('H:i:s.u') . " [{$this->errno}] " . $this->error . "\n" . json_encode($_SERVER) . "\nTrace:\n" . (new \Exception())->getTraceAsString() . "\n" . trim($query) . "\n-----------------------------------------------------------------------------------------------------------------------------\n" ;
				file_put_contents($dir . $processUser . " - " . date('Y-m-d') . ".log", $log_str, FILE_APPEND);
			}
		}
		return $ret;
	}

	public function real_query($query)
	{
		throw new \Exception("not implemented");
	}
}

