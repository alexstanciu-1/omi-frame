<?php

final class QCurl
{
	protected static $Curl_Cache = [];
	
	public static function HTTP(string $url, array $get = null, array $post = null, array $curl_opts = null, string $method = 'auto')
	{
		$curl = static::Setup_Curl($url, $get, $post, $curl_opts, $method);
		
		$ret = curl_exec($curl);
		$info = curl_getinfo($curl);
		
		if (is_string($ret))
		{
			$header_size = $info['header_size'];
			$header = substr($ret, 0, $header_size);
			$body = substr($ret, $header_size);
		}
		
		return [$body, $info, $header];
	}
	
	public static function ASYNC_HTTP(string $url, callable $call_on_done, array $get = null, array $post = null, array $curl_opts = null, 
				array $files = null, string $method = 'auto')
	{
		# @TODO ... we need to make sure we don't re-use a running handle !!!!
	}
	
	protected static function Setup_Curl(string $url, array $get = null, array $post = null, array $curl_opts = null, string $method = 'auto')
	{
		$url_parts = parse_url($url);
		
		# depending on domain & protocol, get from cache
		$curl = curl_init();
		
		$http_headers = [];
		
		$opts = $curl_opts ? $curl_opts : [];
		if (!isset($opts[CURLOPT_URL]))
			$opts[CURLOPT_URL] = $url . ($get ? "?" . http_build_query ($get) : '');
		
		$opts[CURLOPT_RETURNTRANSFER] = 1;
		$opts[CURLOPT_HEADER] = 1;
		
		$post_data = $post ?? [];
		
		if ($post_data)
		{
			if ($method === 'auto')
			{
				$method = 'POST';
				$http_headers[] = 'Content-Type: multipart/form-data';
			}
			$opts[CURLOPT_POSTFIELDS] = $post_data;
		}
		
		curl_setopt_array($curl, $opts);
		
		return $curl;
	}
	
	public static function Flush_Cache()
	{
		# release cache data & $Curl_Cache
		
	}
}


