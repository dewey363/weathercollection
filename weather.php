<?php

	header("Content-Type: text/html; charset=gb2312");

	ini_set("display_errors","0");
	ini_set("max_execution_time","7200");
	ini_set("memory_limit","1024M");

	/**
	 * 保存文件
	 *
	 * @param string $fileName 文件名（含相对路径）
	 * @param string $text 文件内容
	 * @return boolean
	 */
	function saveFile($fileName, $text) {
		if (!$fileName || !$text)
			return false;
		if (makeDir(dirname($fileName))) {
			if ($fp = fopen($fileName, "w")) {
				if (@fwrite($fp, $text)) {
					fclose($fp);
					return true;
				} else {
					fclose($fp);
					return false;
				}
			}
		}
		return false;
	}
	
	/**
	 * 连续创建目录
	 *
	 * @param string $dir 目录字符串
	 * @param int $mode 权限数字
	 * @return boolean
	 */
	function makeDir($dir, $mode=0755) {
		if (!dir) return false;
		if(!file_exists($dir)) {
			return mkdir($dir,$mode,true);
		} else {
			return true;
		}
	}

	/*
	 函数：check_remote_file_exists
	 功能：判断远程文件是否存在
	 参数： $url_file -远程文件URL
	 返回：返回1 说明存在
	*/
	function check_remote_file_exists($url) {
		$curl = curl_init($url); // 不取回数据
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); // 发送请求
		$result = curl_exec($curl);
		$found = false; // 如果请求没有发送失败
		if ($result !== false) {
	
			/** 再检查http响应码是否为200 */
			$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if ($statusCode == 200) {
				$found = true;
			}
		}
		curl_close($curl);
	
		return $found;
	}

	/*
	 函数：curl_file
	 功能：CURL简单抓取网页
	 参数： $url -远程文件URL
	 返回：返回curl_exec($ch);
	*/
	function curl_file($url){
		if(check_remote_file_exists($url)=='1'){
		// 创建一个新CURL资源
		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10);
		// 抓取URL并把它传递给浏览器
		return(curl_exec($ch));
		// 关闭CURL资源，并且释放系统资源
		curl_close($ch);
			}else{
		return false;
				}
		}

	  /*
	   函数：saveFileTxt('api');
	   功能：保存114la.com.cn天气链接为单独的文件列表 
	   参数： $saveFilePath -保存路径 默认：api
	   返回：返回weather.txt
	  */
	  function saveFileTxt($saveFilePath='api',$fileTxtType='weather'){
	  require_once('citycode.php');	
	  if($fileTxtType=='city'){
			  foreach ($citycode as $arr => $value) {
				  $citycode[$arr] = 'http://weather.api.114la.com/'.substr($citycode[$arr], 3,4).'/'.substr($citycode[$arr], 3,4).'.txt';
			  }
			  saveFile($saveFilePath.'/city.txt',implode("\r\n",$citycode));
			  exit('city.txt 执行完毕。');
		  }else{
			  foreach ($citycode as $arr => $value) {
				  $citycode[$arr] = 'http://weather.api.114la.com/'.substr($citycode[$arr], 3,4).'/'.$citycode[$arr].'.txt';
			  }
			  saveFile($saveFilePath.'/weather.txt',implode("\r\n",$citycode));
			  exit('weather.txt 执行完毕。');
		}
	  }

	/*
	 函数：makeWeather
	 功能：抓取并保存文件
	 参数：$saveFilePath -保存路径 默认：api
	 返回：返回curl_exec($ch);
	*/
	function makeWeather($saveFilePath='api',$openFile='/weather.txt'){
	 //读取文件并过滤重复
	 $handle=fopen($saveFilePath.$openFile,"r");
	 if(!$handle){ exit('文件不存在。'); }
	 $temp_arr=array();
	 do{
		   $file=fgets($handle,1024);
		   $temp_arr[]=$file;
	 }
			while(!feof($handle));
			fclose($handle);
	//遍历数组，取相同组，创建新数组，最后得到没有重复的数组，筛选完成
	 $newArr=array();
	 foreach($temp_arr as $key=>$value){
		 if(in_array($value,$arr)){
			unset($newArr[$key]);
		  }else{
	   $newArr[]=$value;
	  }
	 }
	//去掉文件中空值，同时去掉最后一个空值被取到数组
	$cot=count($newArr);
	unset($newArr[$cot-1]);
	//获取要采集的文件
	foreach ($newArr as $key=>$value ) {
		flush();
		$urlFile = str_replace(PHP_EOL, '', $value); //PHP自带过滤换行
		$urlFile = parse_url($urlFile, PHP_URL_PATH);
		$urlFile = $saveFilePath.$urlFile;
		$value = str_replace(PHP_EOL, '', $value); //PHP自带过滤换行
	//未成功写入则跳过
	if(saveFile($urlFile,curl_file($value))){
		echo '<font color="#66CC00">写入成功</font> '.$urlFile.'<br>';
		ob_flush();
			}else{
		continue;
			}
		}
		
	exit($openFile.'中文件采集执行完毕。');
	
		}	
		
		//saveFileTxt('api','city');
		//saveFileTxt('api','weather');
		makeWeather('api','/weather.txt');
		//makeWeather('api','/city.txt');

?>