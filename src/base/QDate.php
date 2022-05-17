<?php

class QDate
{
	public static function Get_Date_Intervals_From_Date_Tag(string $tag)
	{
		switch (strtolower(str_replace(" ", "_", $tag)))
		{
			case 'today':
			{
				return [date("Y-m-d 00:00:00"), date("Y-m-d 00:00:00", strtotime("+1 days"))];
			}
			case 'yesterday':
			{
				return [date('Y-m-d 00:00:00', strtotime("-1 days")), date('Y-m-d 00:00:00')];
			}
			case 'this_week':
			{
				return [date("Y-m-d 00:00:00", strtotime('monday this week')), date("Y-m-d 00:00:00", strtotime('monday next week'))];
			}
			case 'last_week':
			{
				return [date("Y-m-d 00:00:00", strtotime('monday this week -7 days')), date("Y-m-d 00:00:00", strtotime('monday this week')),];
			}
			case 'this_month':
			{
				return [date("Y-m-01 00:00:00"), date("Y-m-d 00:00:00", strtotime('+1 month'))];
			}
			case 'last_month':
			{
				return [date("Y-m-01 00:00:00", strtotime('-1 month')), date("Y-m-01 00:00:00")];
			}
			case 'this_year':
			{
				return [date("Y-01-01 00:00:00"), date("Y-01-01 00:00:00", strtotime('+1 year'))];
			}
			case 'last_year':
			{
				return [date("Y-01-01 00:00:00", strtotime('-1 year')), date("Y-01-01 00:00:00")];
			}
			default:
				return [null, null];	
		}
	}
}
