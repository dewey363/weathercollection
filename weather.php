<?php

	header("Content-Type: text/html; charset=gb2312");

	ini_set("display_errors","0");
	ini_set("max_execution_time","7200");
	ini_set("memory_limit","1024M");

	/**
	 * �����ļ�
	 *
	 * @param string $fileName �ļ����������·����
	 * @param string $text �ļ�����
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
	 * ��������Ŀ¼
	 *
	 * @param string $dir Ŀ¼�ַ���
	 * @param int $mode Ȩ������
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
	 ������check_remote_file_exists
	 ���ܣ��ж�Զ���ļ��Ƿ����
	 ������ $url_file -Զ���ļ�URL
	 ���أ�����1 ˵������
	*/
	function check_remote_file_exists($url) {
		$curl = curl_init($url); // ��ȡ������
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); // ��������
		$result = curl_exec($curl);
		$found = false; // �������û�з���ʧ��
		if ($result !== false) {
	
			/** �ټ��http��Ӧ���Ƿ�Ϊ200 */
			$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if ($statusCode == 200) {
				$found = true;
			}
		}
		curl_close($curl);
	
		return $found;
	}

	/*
	 ������curl_file
	 ���ܣ�CURL��ץȡ��ҳ
	 ������ $url -Զ���ļ�URL
	 ���أ�����curl_exec($ch);
	*/
	function curl_file($url){
		if(check_remote_file_exists($url)=='1'){
		// ����һ����CURL��Դ
		$ch = curl_init();
		// ����URL����Ӧ��ѡ��
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10);
		// ץȡURL���������ݸ������
		return(curl_exec($ch));
		// �ر�CURL��Դ�������ͷ�ϵͳ��Դ
		curl_close($ch);
			}else{
		return false;
				}
		}

	  /*
	   ������saveFileTxt('api');
	   ���ܣ�����114la.com.cn��������Ϊ�������ļ��б� 
	   ������ $saveFilePath -����·�� Ĭ�ϣ�api
	   ���أ�����weather.txt
	  */
	  function saveFileTxt($saveFilePath='api',$fileTxtType='weather'){
	  require_once('citycode.php');	
	  if($fileTxtType=='city'){
			  foreach ($citycode as $arr => $value) {
				  $citycode[$arr] = 'http://weather.api.114la.com/'.substr($citycode[$arr], 3,4).'/'.substr($citycode[$arr], 3,4).'.txt';
			  }
			  saveFile($saveFilePath.'/city.txt',implode("\r\n",$citycode));
			  exit('city.txt ִ����ϡ�');
		  }else{
			  foreach ($citycode as $arr => $value) {
				  $citycode[$arr] = 'http://weather.api.114la.com/'.substr($citycode[$arr], 3,4).'/'.$citycode[$arr].'.txt';
			  }
			  saveFile($saveFilePath.'/weather.txt',implode("\r\n",$citycode));
			  exit('weather.txt ִ����ϡ�');
		}
	  }

	/*
	 ������makeWeather
	 ���ܣ�ץȡ�������ļ�
	 ������$saveFilePath -����·�� Ĭ�ϣ�api
	 ���أ�����curl_exec($ch);
	*/
	function makeWeather($saveFilePath='api',$openFile='/weather.txt'){
	 //��ȡ�ļ��������ظ�
	 $handle=fopen($saveFilePath.$openFile,"r");
	 if(!$handle){ exit('�ļ������ڡ�'); }
	 $temp_arr=array();
	 do{
		   $file=fgets($handle,1024);
		   $temp_arr[]=$file;
	 }
			while(!feof($handle));
			fclose($handle);
	//�������飬ȡ��ͬ�飬���������飬���õ�û���ظ������飬ɸѡ���
	 $newArr=array();
	 foreach($temp_arr as $key=>$value){
		 if(in_array($value,$arr)){
			unset($newArr[$key]);
		  }else{
	   $newArr[]=$value;
	  }
	 }
	//ȥ���ļ��п�ֵ��ͬʱȥ�����һ����ֵ��ȡ������
	$cot=count($newArr);
	unset($newArr[$cot-1]);
	//��ȡҪ�ɼ����ļ�
	foreach ($newArr as $key=>$value ) {
		flush();
		$urlFile = str_replace(PHP_EOL, '', $value); //PHP�Դ����˻���
		$urlFile = parse_url($urlFile, PHP_URL_PATH);
		$urlFile = $saveFilePath.$urlFile;
		$value = str_replace(PHP_EOL, '', $value); //PHP�Դ����˻���
	//δ�ɹ�д��������
	if(saveFile($urlFile,curl_file($value))){
		echo '<font color="#66CC00">д��ɹ�</font> '.$urlFile.'<br>';
		ob_flush();
			}else{
		continue;
			}
		}
		
	exit($openFile.'���ļ��ɼ�ִ����ϡ�');
	
		}	
		
		//saveFileTxt('api','city');
		//saveFileTxt('api','weather');
		makeWeather('api','/weather.txt');
		//makeWeather('api','/city.txt');

?>