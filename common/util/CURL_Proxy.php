<?php

namespace Omi;

/**
 * Example usage: 
 * 
 * I. As a listener
 *		\Omi\CURL_Proxy::Process(['192.168.25.11']);
 *   Forward
 *		\Omi\CURL_Proxy::Process(['192.168.25.11'], true, 'http://deeper-proxy/proxy.php');
 * 
 * II. As a sender
 *			\Omi\CURL_Proxy::Send(...)
 */
class CURL_Proxy
{
	protected static $ProxyUrl;
	protected static $Curl;
	
	public static function Send(string $url, array $options = null, string $proxy_url = null, array $proxy_options = null)
	{
		if (!$proxy_url)
			$proxy_url = static::$ProxyUrl;
		
		$proxy_url = $proxy_url.((strpos($proxy_url, "?") !== false) ? "&" : "?" ).
						"__proxy_url__=".urlencode($url);
		
		if (static::$Curl)
		{
			$curl = static::$Curl;
			curl_reset($curl);
		}
		else
			$curl = static::$Curl = curl_init($proxy_url);
		
		if ($proxy_options)
		{
			if ($proxy_options[CURLOPT_POST] || $proxy_options[CURLOPT_POSTFIELDS])
				throw new \Exception('POST is not allowed');
			curl_setopt_array($curl, $proxy_options);
		}
		
		if ($options || $proxy_options)
		{
			curl_setopt($curl, CURLOPT_POST,			true);
			curl_setopt($curl, CURLOPT_POSTFIELDS,		http_build_query(["__opts__" => $options, "__proxy_opts__" => $proxy_options]));
		}
		
		// overwrite these
		curl_setopt($curl, CURLOPT_URL, $proxy_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		// CURLOPT_POSTFIELDS
		$curl_ret = curl_exec($curl);
		$curl_error = curl_error($curl);
		
		return [$curl_ret, $curl, $curl_error];
	}
	
	public static function Process($AllowedHosts = null, $Forward = false, $ProxyUrl = null)
	{
		$remote = Q_REMOTE_ADDR;
		if ($AllowedHosts && (!($AllowedHosts[$remote] || in_array($remote, $AllowedHosts))))
		{
			http_response_code(401);
			echo 'Unauthorized';
			return;
		}
		
		$url = $_GET["__proxy_url__"];
		
		if (!$url)
		{
			http_response_code(400);
			echo 'Bad Request. Missing __proxy_url__ parameter';
			return;
		}
		$options = ($_POST && isset($_POST["__opts__"])) ? $_POST["__opts__"] : null;
		$proxy_options = ($_POST && isset($_POST["__proxy_opts__"])) ? $_POST["__proxy_opts__"] : null;
		
		if ($Forward)
		{
			if (!$ProxyUrl)
			{
				http_response_code(400);
				echo 'Bad Request. Missing $ProxyUrl';
				return;
			}
			else
			{
				list($curl_ret, $curl, $curl_error) = static::Send($url, $options, $ProxyUrl, $proxy_options);
				static::ReturnResponse($curl_ret, $curl, $curl_error);
			}
		}
		else
		{
			$curl = curl_init($url);
			
			// force return transfer
			if ($options)
				curl_setopt_array($curl, $options);

			// overwrite these 2
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$curl_ret = curl_exec($curl);
			$curl_error = curl_error($curl);

			static::ReturnResponse($curl_ret, $curl, $curl_error);
		}
	}
	
	public static function ReturnResponse($curl_ret, $curl, $curl_error)
	{
		if ($curl_error)
		{
			// @todo
			http_response_code(400);
			var_dump($curl_ret, $curl, $curl_error);
		}
		else
		{
			echo $curl_ret;
		}
	}
}
