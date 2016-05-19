<?php
/**
 * QcloudApi_Module_Vod
 * 视频云模块类
 */

namespace Qcloud\Module;

use Qcloud\Common\Requpload;

class Vod extends Base {
	
    /**
     * $_serverHost
     * 接口域名
     * @var string
     */
    protected $_serverHost = 'vod.api.qcloud.com';
    
    public function setServerHost($host) {
    	
    	$this->_serverHost = $host;
    }
    
    /**
     * MultipartUploadVodFile
     * 上传视频文件
     * @param  array $input 
	 * @param  array $options
     * @return
     */
    public function MultipartUploadVodFile($input = []) {
    	
    	$max_retry = 3;
    	$action    = 'MultipartUploadVodFile';
    	    	
    	if (empty($input['file']) || !is_file($input['file'])) {
    		throw new \Exception('找不到要上传的文件');
    	}
    	$fileSha  = hash_file('sha1', $input['file']);
    	$fileSize = filesize($input['file']);    
    	$fileName = pathinfo($input['file'], PATHINFO_FILENAME);
    	$fileType = pathinfo($input['file'], PATHINFO_EXTENSION);
    	$fileName = rawurlencode($fileName);
    	
    	$sliceSize = empty($input['dataSize']) ? 1024*1024*5 : intval($input['dataSize']);
    	    	
    	$rsp = ['code'=>0, 'offset'=>0, 'flag'=>0];
    	
    	while (!empty($rsp) && $rsp['code'] == 0 && $rsp['flag'] == 0) {
    		
    		$offset = $rsp['offset'];    		
    		if ($fileSize - $offset < $sliceSize) {
    			$sliceSize = $fileSize - $offset;
    		}
			//API参数
			$args = array(
    				'Action'      => $action,
    				'Nonce'       => rand(1, 65535),
    				'Region'      => $this->_defaultRegion,
    				'SecretId'    => $this->_secretId,
    				'Timestamp'   => time(),
    				'dataSize'    => $sliceSize,
    				'fileName'    => $fileName,
    				'fileSha'     => $fileSha,
    				'fileSize'    => $fileSize,
    				'fileType'    => $fileType,
					'offset'      => $offset,
					'name'        => $fileName.'.'.$fileType,
    				'isTranscode' => isset($input['isTranscode'])  ? $input['isTranscode']  : 0,
    				'isScreenshot'=> isset($input['isScreenshot']) ? $input['isScreenshot'] : 0,
    				'isWatermark' => isset($input['isWatermark'])  ? $input['isWatermark']  : 0,    				
    				'notifyUrl'   => isset($input['notifyUrl'])    ? $input['notifyUrl']    : ""
    		);
    
    		$fp = fopen($input['file'], "rb");
    		if (!$fp) {
    			throw new \Exception('打开文件失败');
    		}
    		fseek($fp, $offset);
    		$data = fread($fp, $sliceSize);
    		fclose($fp);
    		
    		$retry = 0;
    		
    		do {
				$rsp = $this->dispatchRequest($action, array($args), $data);
				if (!empty($rsp) && $rsp['code'] == 0) {					
					break;
				}
				$this->setError("", 'request falied, retry:'.$retry);
    		} while ($retry++ < $max_retry);
				
    		if (empty($rsp) || $rsp['code'] != 0) {
    			throw new \Exception('Qcloud upload failed:>'.json_encode($rsp));
    			return false;
    		}
    	}    	
    	if ($rsp['flag'] == 1) {
    		return $rsp;
    	}    	
    	return false;		
    }
    
    /**
     * dispatchRequest
     * 发起接口请求
     * @param  string $name      接口名
     * @param  array $arguments 接口参数
     * @param  string $data     发送的数据
     * @return
     */
    protected function dispatchRequest($name, $arguments, $data) {
    
    	$action = ucfirst($name);
    
    	$params = array();
    	if (is_array($arguments) && !empty($arguments)) {
    		$params = (array) $arguments[0];
    	}
    	$params['Action'] = $action;
    
    	if (!isset($params['Region'])) {
    		$params['Region']  = $this->_defaultRegion;    
    	}
    	$request['method'] = $this->_requestMethod;
    	$request['uri']    = $this->_serverUri;
    	$request['host']   = $this->_serverHost;
    	$request['port']   = $this->_serverPort;
    	$request['query']  = http_build_query($params);
    
    	$response = Requpload::send($params, $this->_secretId, $this->_secretKey, $request,$data);
    	
    	//echo '<p>';var_dump($response);echo '</p>';
    
    	return $response;
    }    
}