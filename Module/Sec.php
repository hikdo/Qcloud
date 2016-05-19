<?php
/**
 * QcloudApi_Module_Sec
 * 云安全模块类
 */

namespace Qcloud\Module;

class Sec extends Base {
	
    /**
     * $_serverHost
     * 接口域名
     * @var string
     */
    protected $_serverHost = 'csec.api.qcloud.com';

    /**
     * captchaQuery
     * 获取验证码
     * @param  array $params 请求参数
     * @return
     */
    public function captchaQuery($params) {

        $name = 'captchaQuery';
        $response = $this->_dispatchRequest($name, array($params));

        if (!$response) {
            $this->setError("", 'request falied!');
            return false;
        }

        if (is_array($response) && $response['code']) {
            $this->setError($response['code'], $response['message']);
            return false;
        }

        if ($params['script'] == 1) {
            return $response;
        }

        unset($response['code'], $response['message']);

        return $response;
    }
}