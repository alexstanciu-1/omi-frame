<?php

namespace Omi;

class Currency_Conversion
{
	public static $BNR_RATES = [];
	
	public static $BNRRatesUrl = null;
	
	/**
	 * @api.enable
	 * 
	 * @param boolean $force
	 * @return type
	 * @throws \Exception
	 */
	public static function GetBNRRates($force = false)
	{
		if (!empty(static::$BNR_RATES))
			return static::$BNR_RATES;

		$bnrRatesFile = "temp/bnr_rates.php";
		$fmtime = file_exists($bnrRatesFile) ? filemtime($bnrRatesFile) : null;
		if ($force || (!$fmtime || ($fmtime < strtotime("-1 hour"))))
		{
			self::$BNRRatesUrl = self::$BNRRatesUrl ?: (defined('TF_BNRRatesUrl') ? TF_BNRRatesUrl : 'http://www.bnr.ro/nbrfxrates.xml');
			// we are using ron
			static::$BNR_RATES = ["RON" => 1];
			$ratesC = file_get_contents(self::$BNRRatesUrl);
			
			$doc = new \DOMDocument();
			$doc->loadXML($ratesC);
			
			$ratesElements = $doc->getElementsByTagName("Rate");
			foreach ($ratesElements ?: [] as $rateElement)
			{
				if (!($currencyCode = $rateElement->getAttribute("currency")))
					continue;
				static::$BNR_RATES[$currencyCode] = (float)$rateElement->nodeValue;
			}

			// put file contents if changed
			filePutContentsIfChanged($bnrRatesFile, qArrayToCode(static::$BNR_RATES, "_rates"), true);
			touch($bnrRatesFile);
		}
		else
		{
			require_once($bnrRatesFile);
			static::$BNR_RATES = $_rates;
		}
		
		if (empty(static::$BNR_RATES))
			throw new \Exception("Currency conversion rates couldn't be loaded!");

		return static::$BNR_RATES;
	}
}
