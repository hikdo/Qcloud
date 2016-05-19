<?php

/**
 * qcloud
 * @author hook
 */

class QcloudController extends Home_BaseController {
	
		
	public function indexAction() {
				
		$config  = \Yaf\Registry::get('config')->qcloud;
		$service = new Qcloud\Module\Vod();
		$service->setServerHost('vod.qcloud.com');
		$service->setConfig([
			'SecretId'      => $config->SecretId,
			'SecretKey'     => $config->SecretKey,
			'DefaultRegion' => $config->DefaultRegion,
			'RequestMethod' => 'POST'
		]);		
		
		$input = [
			'file' => '/data/upload/test.wmv',
			'isTranscode'  => 1, 
			'isScreenshot' => 1, 
			'isWatermark'  => 0, 			
		];
		
		//$ret = $service->MultipartUploadVodFile($input);
		
		//var_dump($ret);
		if (!empty($ret['fileId'])) {		
			$service->setServerHost('vod.api.qcloud.com');
			$ret = $service->DescribeVodPlayUrls(['fileId'=>$ret['fileId']]);
			if (false === $ret) {
				$error = $service->getError();
				throw new \Exception('code:'.$error->getCode().',msg:'.$error->getMessage());
			}
			echo '<pre>';
			print_r($ret);
			echo '</pre>';
		}
		return false;
	}
	
	
	
}
