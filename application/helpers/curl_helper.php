<?php
/**
 * curl后端API请求构建方法
 */
if(!function_exists('curl')){
	/**
	 * curl请求
	 * @param  string $path                请求地址
	 * @param  string $method              请求方式
	 * @param  array  $params              请求的参数
	 * @param  bool   $file_upload_support 是否支持文件上传
	 * @return boolean|string              请求是否成功，成功则返回结果
	 */
	function curl($path, $method = 'GET', $params = array(), $file_upload_support = FALSE)
	{
		$opts = array();
		$ch = curl_init();
		$opts[CURLOPT_URL] = $path;
		$opts[CURLOPT_RETURNTRANSFER] = TRUE;
		switch (strtoupper($method))
		{
			case 'GET':
			$path .= '?' . http_build_query($params, NULL, '&');
			break;
			default:
			if ($file_upload_support) {
				$opts[CURLOPT_POSTFIELDS] = $params;
			}
			else {
				$opts[CURLOPT_POSTFIELDS] = http_build_query($params, NULL, '&');
			}
			break;
		}
		curl_setopt_array($ch, $opts);
    	$result = curl_exec($ch);
		if(curl_error($ch))
		{
			curl_close($ch);
			return FALSE;
		}
		curl_close($ch);
		return $result;
	}
}