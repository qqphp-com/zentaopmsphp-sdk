<?php


namespace app\index\controller;


class Zentao
{
    //禅道部署域名
    const ztUrl = 'http://zentao.qqphp.com/';//http://www.testzentao.com/index.php|http://zentao.qqphp.com/
    //禅道登录账户
    const ztAccount = 'admin';
    //禅道登录密码
    const ztPassword = 'asd401733012';
    //参数请求方式[GET/PATH_INFO]
    const requestType = 'PATH_INFO';//PATH_INFO|GET
    //身份认证[sessionName + sessionID]
    public $sessionAuth = '';
    //接口请求参数
    public $params = array();
    //返回结果
    public $returnResult = array(
        'status' => 0,
        'msg'    => '操作失败',
        'result' => array()
    );

    /**
     * 获取登录sessionId
     * Zentao constructor.
     */
    public function __construct()
    {
        $this->params      = [
            'm' => 'api',
            'f' => 'getSessionID'
        ];
        $result            = $this->getUrl(self::ztUrl);
        $resultData        = json_decode($result);
        $sessionData       = json_decode($resultData->data);
        $this->sessionAuth = $sessionData->sessionName . '=' . $sessionData->sessionID;
        $this->login();
    }

    /**
     * 用户登录验证
     * @return bool|string
     */
    public function login()
    {
        $this->params = [
            'account'  => self::ztAccount,
            'password' => self::ztPassword
        ];
        if (self::requestType == 'GET') {
            $this->params = array_merge($this->params, [
                'm' => 'user',
                'f' => 'login',
            ]);
            $result       = $this->getUrl(self::ztUrl);
        } elseif (self::requestType == 'PATH_INFO') {
            $result = $this->postUrl(self::ztUrl . '/user-login.json');
        }
        return $result;
    }

    /**
     * 组织-获取部门列表
     * @param array $optionalParams
     * @return false|string
     */
    public function deptBrowse($optionalParams = ['deptID' => 1])
    {
        $this->params = [
            'm' => 'dept',
            'f' => 'browse'
        ];
        $this->params = array_merge($this->params, $optionalParams);
        $result       = $this->getUrl(self::ztUrl);
        $resultData   = json_decode($result);
        $returnResult = $this->returnResult;
        if (strcmp($resultData->status, 'success') === 0) {
            $sessionData = json_decode($resultData->data);
            if (!empty($sessionData->tree)) {
                $returnResult = array(
                    'status' => 1,
                    'msg'    => '操作成功',
                    'result' => array(
                        'title'       => $sessionData->title,
                        'deptID'      => $sessionData->deptID,
                        'parentDepts' => $sessionData->parentDepts,
                        'sons'        => $sessionData->sons,
                        'tree'        => $sessionData->tree,
                    )
                );

            }
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 组织-添加新部门
     * @param array $optionalParams
     * @return false|string
     */
    public function deptManageChild($optionalParams = ['depts[0]' => 'A部门', 'depts[1]' => 'B部门', 'parentDeptID' => '1'])
    {
        $returnResult = $this->returnResult;
        if (self::requestType == 'GET') {
            $this->params = [
                't' => 'json'
            ];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '?m=dept&f=manageChild');
        } elseif (self::requestType == 'PATH_INFO') {
            $this->params = [];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '/dept-manageChild.json');
        }
        if (strpos($result, 'reload')) {
            $returnResult = array(
                'status' => 1,
                'msg'    => '操作成功',
                'result' => array()
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    public function uuuuser($optionalParams = [])
    {
        $this->params = [
            'm' => 'group',
            'f' => 'browse'
        ];
        $this->params = array_merge($this->params, $optionalParams);
        $result       = $this->getUrl(self::ztUrl);
        $resultData   = json_decode($result);
        $returnResult = $this->returnResult;
        dump($result);die();
        return $returnResult;
    }

    public function getUrl($url, $dump = 0)
    {
        $ch = curl_init();
        // 设置get参数
        if (self::requestType == 'GET') {
            $this->params = array_merge($this->params, ['t' => 'json']);
            if (!empty($this->params) && count($this->params)) {
                if (strpos($url, '?') !== false) {
                    $url .= http_build_query($this->params);
                } else {
                    $url .= '?' . http_build_query($this->params);
                }
            }
        } elseif (self::requestType == 'PATH_INFO') {
            $params = implode('-', $this->params);
            $url    = $url . '/' . $params . '.json';
        }
        if ($dump) {
            var_dump($url);
            die();
        }
        curl_setopt($ch, CURLOPT_COOKIE, $this->sessionAuth);
        curl_setopt($ch, CURLOPT_REFERER, self::ztUrl);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function postUrl($url, $dump = 0)
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
        if ($dump) {
            var_dump($url);
            die();
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }
}