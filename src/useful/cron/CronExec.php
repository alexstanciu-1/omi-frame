<?php

namespace Omi;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cron
 */
class CronExec extends \QModel
{
	/**
	 * @api.enable
	 */
	public static function DailyRun()
	{
		
	}
	/**
	 * @api.enable
	 */
	public static function HourlyRun()
	{
		
	}
	/**
	 * @api.enable
	 */
	public static function EveryFiveMinsRun()
	{
		
	}
	/**
	 * @api.enable
	 */
	public static function EveryMinRun()
	{
		
	}
	/**
	 * @api.enable
	 * 
	 * @param string $domain
	 */
	public static function SetupCrons($domain)
	{
		$crontabContent = \Omi\Linux\Crontab::ReadAll();
		$mainCron = new \Omi\Linux\Crontab($crontabContent);

		$domain = rtrim($domain, "\\/")."/";

		static::SetupCron($mainCron, $domain, "0 0 * * *", "DailyRun", "every_day");
		static::SetupCron($mainCron, $domain, "0 * * * *", "HourlyRun", "every_hour");
		static::SetupCron($mainCron, $domain, "*/5 * * * *", "EveryFiveMinsRun", "every_five_mins");
		static::SetupCron($mainCron, $domain, "*/1 * * * *", "EveryMinRun", "every_five_mins");

		$mainCron->save();
	}

	public static function SetupCron($mainCron, $domain, $schedule, $method, $filter)
	{
		$crontabEntry = \Omi\Linux\Crontab::EncodeEntry($schedule, "\Omi\CronExec", $method, [], $filter, null, null, null, null, $domain . "crons/cronjob.php");
		$crontab = new \Omi\Linux\Crontab($crontabEntry);
		$mainCron->replaceFrom($crontab, $domain . "crons/cronjob.php - {$filter}");
	}
}