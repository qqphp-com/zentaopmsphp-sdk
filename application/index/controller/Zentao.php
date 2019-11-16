<?php


namespace app\index\controller;


class Zentao
{
    //禅道部署域名
    const ztUrl = 'http://www.testzentao.com/index.php';
    //禅道登录账户
    const ztAccount = 'admin';
    //禅道登录密码
    const ztPassword = 'asd401733012';
    //身份认证[sessionName + sessionID]
    public $sessionAuth = '';
    //接口请求参数
    public $params = [];

    /**
     * 获取登录sessionId
     * Zentao constructor.
     */
    public function __construct()
    {
        $this->params      = [
            'm' => 'api',
            'f' => 'getSessionID',
            't' => 'json'
        ];
        $result            = $this->getUrl(self::ztUrl);
        $resultData        = json_decode($result);
        $sessionData       = json_decode($resultData->data);
        $this->sessionAuth = $sessionData->sessionName . '=' . $sessionData->sessionID;
        $this->login();
    }

    public function login()
    {
        $curl         = new HttpCurl();
        $this->params = [
            'm'        => 'user',
            'f'        => 'login',
            'account'  => self::ztAccount,
            'password' => self::ztPassword,
            't'        => 'json'
        ];
        $result       = $this->getUrl(self::ztUrl);
        return $result;
    }

    /**
     * 组织-获取部门列表
     * @return bool|mixed
     */
    public function deptBrowse()
    {
        $curl         = new HttpCurl();
        $this->params = [
            'm'      => 'dept',
            'f'      => 'browse',
            't'      => 'json',
            'deptID' => 1
        ];
        $result       = $this->getUrl(self::ztUrl);
        return $result;
    }

    /**
     * 组织-添加新部门
     * @return mixed
     */
    public function deptManageChild()
    {
        $curl         = new HttpCurl();
        $this->params = [
            'depts[]'      => '2ddddd',
            'parentDeptID' => '5',//上级部门ID
            't'            => 'json'
        ];
        $result       = $this->postUrl(self::ztUrl . '?m=dept&f=manageChild');
        return $result;
    }

    public function getUrl($url)
    {
        $ch = curl_init();
        // 设置get参数
        if (!empty($this->params) && count($this->params)) {
            if (strpos($url, '?') !== false) {
                $url .= http_build_query($this->params);
            } else {
                $url .= '?' . http_build_query($this->params);
            }
        }
        curl_setopt($ch, CURLOPT_COOKIE, $this->sessionAuth);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function postUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIE, $this->sessionAuth);
        curl_setopt($ch, CURLOPT_REFERER, self::ztUrl);
        curl_setopt($ch, CURLOPT_URL, $url);
        // 设置post参数
        if (!empty($this->params)) {
            if (is_array($this->params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->params));
            } else if (is_string($this->params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
            }
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }
}