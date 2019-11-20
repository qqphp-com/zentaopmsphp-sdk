<?php


namespace app\index\controller;


class Zentao
{
    //禅道部署域名
    const ztUrl = 'http://zentao.qqphp.com';//http://www.testzentao.com/index.php|http://zentao.qqphp.com|http://www.zendao2.com/index.php
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
    //session随机数，用于一些加密和验证
    public $sessionRand = 0;
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
            $this->params = [];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '?m=dept&f=manageChild&t=json');
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

    /**
     * Desc:获取用户列表
     * Date:2019/11/19/019
     */
    public function groupBrowse($optionalParams = [])
    {
        $this->params = [
            'm' => 'group',
            'f' => 'browse',
        ];
        $this->params = array_merge($this->params, $optionalParams);
        $result       = $this->getUrl(self::ztUrl);
        $resultData   = json_decode($result);
        $returnResult = $this->returnResult;
        if (strcmp($resultData->status, 'success') == 0) {
            $resultList = json_decode($resultData->data);
            $userGroup  = array();
            foreach ($resultList->groupUsers as $k => $v) {
                if (count($v)) {
                    $newUser = [];
                    foreach ($v as $j => $p) {
                        $newUser['user_id']   = $k;
                        $newUser['en_name']   = $j;
                        $newUser['zh_name']   = $p;
                        $newUser['role_name'] = $resultList->groups[$k]->name;
                        $newUser['role_sign'] = $resultList->groups[$k]->role;
                        $userGroup[]          = $newUser;
                    }
                }
            }
            $returnResult = array(
                'status' => 1,
                'msg'    => '获取成功',
                'result' => array(
                    'title'  => $resultList->title,
                    'groups' => $userGroup
                )
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:添加用户页面
     * Date:2019/11/19/019
     */
    public function userInfo($optionalParams = [])
    {
        $this->params = [
            'm' => 'user',
            'f' => 'create'
        ];
        $this->params = array_merge($this->params, $optionalParams);
        $result       = $this->getUrl(self::ztUrl);
        $resultData   = json_decode($result);
        $returnResult = $this->returnResult;
        if (strcmp($resultData->status, 'success') == 0) {
            $resultList = json_decode($resultData->data);
            unset($resultList->groupList->_empty_);
            unset($resultList->roleGroup->_empty_);
            $returnResult      = array(
                'status' => 1,
                'msg'    => '获取成功',
                'result' => array(
                    'title'     => $resultList->title,
                    'depts'     => $resultList->depts,
                    'groupList' => $resultList->groupList,
                    'roleGroup' => $resultList->roleGroup,
                )
            );
            $this->sessionRand = $resultList->rand;
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:新增用户
     * Date:2019/11/19/019
     */
    public function userCreate($optionalParams = [])
    {
        //获取加密所需随机数
        $this->userInfo();
        //模拟数据
//        $optionalParams = ['dept' => 0, 'account' => 'ly0072', 'password1' => md5('123456' . $this->sessionRand), 'password2' => md5('123456' . $this->sessionRand), 'realname' => '雷永永', 'join' => '2019-11-19', 'role' => 'dev', 'group' => 2, 'email' => 'leiyong208@gmail.com', 'commiter' => 'http://qqphp.com', 'gender' => 'm', 'verifyPassword' => md5(md5(self::ztPassword) . $this->sessionRand)];
        $returnResult = $this->returnResult;
        if (self::requestType == 'GET') {
            $this->params = [];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '?m=user&f=create&dept=0&t=json');
        } elseif (self::requestType == 'PATH_INFO') {
            $this->params = [];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '/user-create.json');
        }
        $resultData = json_decode($result);
        if (strcmp($resultData->result, 'success') == 0) {
            $returnResult = array(
                'status' => 1,
                'msg'    => '操作成功',
                'result' => $resultData->message
            );
        } else {
            $returnResult = array(
                'status' => 0,
                'msg'    => '操作失败',
                'result' => $resultData->message
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:获取产品列表
     * Date:2019/11/20/020
     */
    public function productAll($optionalParams = [])
    {
        $this->params = [
            'm' => 'product',
            'f' => 'all'
        ];
        //一开始没法确认总记录条数，当前只能获取未关闭的产品
        $this->params = array_merge($this->params, $optionalParams);
        $result       = $this->getUrl(self::ztUrl);
        $resultData   = json_decode($result);
        $returnResult = $this->returnResult;
        if (strcmp($resultData->status, 'success') == 0) {
            $resultList   = json_decode($resultData->data);
            $returnResult = array(
                'status' => 1,
                'msg'    => '获取成功',
                'result' => array(
                    'title'        => $resultList->title,
                    'products'     => $resultList->products,
                    'productStats' => $resultList->productStats
                )
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:添加产品所需信息
     * Date:2019/11/20/020
     */
    public function productInfo($optionalParams = [])
    {
        $this->params = [
            'm' => 'product',
            'f' => 'create'
        ];
        //一开始没法确认总记录条数，当前只能获取未关闭的产品
        $this->params = array_merge($this->params, $optionalParams);
        $result       = $this->getUrl(self::ztUrl);
        $resultData   = json_decode($result);
        $returnResult = $this->returnResult;
        if (strcmp($resultData->status, 'success') == 0) {
            $resultList   = json_decode($resultData->data);
            $returnResult = array(
                'status' => 1,
                'msg'    => '获取成功',
                'result' => array(
                    'title'    => $resultList->title,
                    'products' => $resultList->products,
                    'lines'    => $resultList->lines,
                    'poUsers'  => $resultList->poUsers,
                    'qdUsers'  => $resultList->qdUsers,
                    'rdUsers'  => $resultList->rdUsers,
                    'groups'   => $resultList->groups,
                )
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:添加单个产品
     * Date:2019/11/20/020
     */
    public function productCreate($optionalParams = [])
    {
        //模拟数据
//        $optionalParams = [
//            'name'         => '产品100',
//            'code'         => 'cp100',
//            'line'         => 5,
//            'PO'           => 'lisi',
//            'QD'           => 'zhapliu',
//            'RD'           => 'niuqi',
//            'type'         => 'normal',
//            'status'       => 'normal',
//            'desc'         => '<span style="font-weight:700;background-color:#FFFFFF;">产品描述</span><span style="font-weight:700;background-color:#FFFFFF;">产品描述</span><span style="font-weight:700;background-color:#FFFFFF;">产品描述</span><span style="font-weight:700;background-color:#FFFFFF;">产品描述</span><span style="font-weight:700;background-color:#FFFFFF;">产品描述</span><span style="font-weight:700;background-color:#FFFFFF;">产品描述</span>',
//            'acl'          => 'custom',
//            'whitelist[0]' => '1',
//            'whitelist[1]' => '2',
//        ];
        $returnResult = $this->returnResult;
        if (self::requestType == 'GET') {
            $this->params = [];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '?m=product&f=create&t=json');
        } elseif (self::requestType == 'PATH_INFO') {
            $this->params = [];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '/product-create.json');
        }
        $resultData = json_decode($result);
        if (strcmp($resultData->result, 'success') == 0) {
            $returnResult = array(
                'status' => 1,
                'msg'    => '操作成功',
                'result' => $resultData->message
            );
        } else {
            $returnResult = array(
                'status' => 0,
                'msg'    => '操作失败',
                'result' => $resultData->message
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:获取项目列表
     * Date:2019/11/20/020
     */
    public function projectAll($optionalParams = [])
    {
        //模拟数据
//        $optionalParams = [
//            'status' => 'wait'
//        ];
        $this->params = [
            'm' => 'project',
            'f' => 'all',
        ];
        $this->params = array_merge($this->params, $optionalParams);
        $result       = $this->getUrl(self::ztUrl);
        $resultData   = json_decode($result);
        $returnResult = $this->returnResult;
        if (strcmp($resultData->status, 'success') == 0) {
            $resultList   = json_decode($resultData->data);
            $returnResult = array(
                'status' => 1,
                'msg'    => '获取成功',
                'result' => array(
                    'title'        => $resultList->title,
                    'projects'     => $resultList->projects,
                    'projectStats' => $resultList->projectStats,
                    'teamMembers'  => $resultList->teamMembers,
                    'users'        => $resultList->users
                )
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:获取项目可选信息
     * Date:2019/11/20/020
     */
    public function projectInfo($optionalParams = [])
    {
        $this->params = [
            'm' => 'project',
            'f' => 'create'
        ];
        $this->params = array_merge($this->params, $optionalParams);
        $result       = $this->getUrl(self::ztUrl);
        $resultData   = json_decode($result);
        $returnResult = $this->returnResult;
        if (strcmp($resultData->status, 'success') == 0) {
            $resultList   = json_decode($resultData->data);
            $returnResult = array(
                'status' => 1,
                'msg'    => '获取成功',
                'result' => array(
                    'title'       => $resultList->title,
                    'projects'    => $resultList->projects,
                    'groups'      => $resultList->groups,
                    'allProducts' => $resultList->allProducts,
                )
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:添加单个项目
     * Date:2019/11/20/020
     */
    public function projectCreate($optionalParams = [])
    {
        //模拟数据
//        $optionalParams = [
//            'name'        => '支付宝项目开发',
//            'code'        => 'alipay',
//            'begin'       => '2019-11-20',
//            'end'         => '2019-11-28',
//            'days'        => '5',
//            'team'        => '支付宝开发团队',
//            'type'        => 'sprint',
//            'status'      => 'wait',
//            'products[0]' => 0,
//            'plans[0]'    => 0,
//            'desc'        => '支付宝项目开发描述支付宝项目开发描述',
//            'acl'         => 'open',
//        ];
        //选择关联产品是需要ajax请求product-ajaxGetPlans-1-0-undefined---unexpired.html，获取可以关联的计划列表，当前尚未支付返回json
        $returnResult = $this->returnResult;
        if (self::requestType == 'GET') {
            $this->params = [];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '?m=project&f=create&t=json');
        } elseif (self::requestType == 'PATH_INFO') {
            $this->params = [];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '/project-create.json');
        }
        $resultData = json_decode($result);
        if (strcmp($resultData->result, 'success') == 0) {
            $returnResult = array(
                'status' => 1,
                'msg'    => '操作成功',
                'result' => $resultData->message
            );
        } else {
            $returnResult = array(
                'status' => 0,
                'msg'    => '操作失败',
                'result' => $resultData->message
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:获取任务列表
     * Date:2019/11/20/020
     */
    public function projectTask($optionalParams = [])
    {
        //模拟数据
//        $optionalParams = [
//            'projectID' => 1,
//            'status'    => 'all',
//            'orderBy'   => 'pri_asc',
//        ];
        $this->params = [
            'm' => 'project',
            'f' => 'task',
        ];
        $this->params = array_merge($this->params, $optionalParams);
        $result       = $this->getUrl(self::ztUrl);
        $resultData   = json_decode($result);
        $returnResult = $this->returnResult;
        if (strcmp($resultData->status, 'success') == 0) {
            $resultList   = json_decode($resultData->data);
            $returnResult = array(
                'status' => 1,
                'msg'    => '获取成功',
                'result' => array(
                    'title'    => $resultList->title,
                    'projects' => $resultList->projects,
                    'project'  => $resultList->project,
                    'products' => $resultList->products,
                    'tasks'    => $resultList->tasks,
                )
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:添加任务可选信息
     * Date:2019/11/20/020
     */
    public function taskInfo($optionalParams = [])
    {
        //模拟数据
//        $optionalParams = [
//            'projectID' => 1
//        ];
        $this->params = [
            'm' => 'task',
            'f' => 'create'
        ];
        $this->params = array_merge($this->params, $optionalParams);
        $result       = $this->getUrl(self::ztUrl);
        $resultData   = json_decode($result);
        $returnResult = $this->returnResult;
        if (strcmp($resultData->status, 'success') == 0) {
            $resultList   = json_decode($resultData->data);
            $returnResult = array(
                'status' => 1,
                'msg'    => '获取成功',
                'result' => array(
                    'title'            => $resultList->title,
                    'projects'         => $resultList->projects,
                    'users'            => $resultList->users,
                    'stories'          => $resultList->stories,
                    'moduleOptionMenu' => $resultList->moduleOptionMenu,
                    'project'          => $resultList->project,
                )
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:添加单个任务
     * Date:2019/11/20/020
     */
    public function taskCreate($optionalParams = [])
    {
        //模拟数据
//        $optionalParams = [
//            'project'          => 1,
//            'type'             => 'ui',
//            'module'           => 1,
//            'assignedTo[]'     => 'lisi',
//            'testAssignedTo[]' => 'lisi',
//            'color'            => '',
//            'name'             => '测试添加任务1',
//            'pri'              => 2,
//            'estimate'         => '8',
//            'desc'             => '测试添加任务描述测试添加任务描述',
//            'estStarted'       => '2019-11-20',
//            'deadline'         => '2019-11-28',
//            'mailto[1]'        => 'lisi',
//        ];
        $returnResult = $this->returnResult;
        if (self::requestType == 'GET') {
            $this->params = [
                'status' => 'wait',
                'after'  => 'toTaskList',
            ];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '?m=task&f=create&projectID=' . $optionalParams['project'] . '&t=json');
        } elseif (self::requestType == 'PATH_INFO') {
            $this->params = [
                'status' => 'wait',
                'after'  => 'toTaskList',
            ];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '/task-create-' . $optionalParams['project'] . '.json');
        }
        $resultData = json_decode($result);
        //注意问题、提示错误信息，确依旧返回result=success
        if (strcmp($resultData->result, 'success') == 0) {
            $returnResult = array(
                'status' => 1,
                'msg'    => '操作成功',
                'result' => $resultData->message
            );
        } else {
            $returnResult = array(
                'status' => 0,
                'msg'    => '操作失败',
                'result' => $resultData->message
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:完成单个任务可选信息
     * Date:2019/11/20/020
     */
    public function taskFinishInfo($optionalParams = [])
    {
        //模拟数据
        $optionalParams = [
            'taskID' => '15'
        ];
        $this->params = [
            'm' => 'task',
            'f' => 'finish'
        ];
        $this->params = array_merge($this->params, $optionalParams);
        $result       = $this->getUrl(self::ztUrl);
        $resultData   = json_decode($result);
        $returnResult = $this->returnResult;
        if (strcmp($resultData->status, 'success') == 0) {
            $resultList   = json_decode($resultData->data);
            dump($resultList);die();
            $returnResult = array(
                'status' => 1,
                'msg'    => '获取成功',
                'result' => array(
                    'title'            => $resultList->title,
                )
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:完成单个任务
     * Date:2019/11/20/020
     */
    public function taskFinish($optionalParams = [])
    {
        //模拟数据
        $optionalParams = [

        ];
        $returnResult = $this->returnResult;
        if (self::requestType == 'GET') {
            $this->params = [
                'status' => 'wait',
                'after'  => 'toTaskList',
            ];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '?m=task&f=create&projectID=' . $optionalParams['project'] . '&t=json');
        } elseif (self::requestType == 'PATH_INFO') {
            $this->params = [
                'status' => 'wait',
                'after'  => 'toTaskList',
            ];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '/task-create-' . $optionalParams['project'] . '.json');
        }
        $resultData = json_decode($result);
        //注意问题、提示错误信息，确依旧返回result=success
        if (strcmp($resultData->result, 'success') == 0) {
            $returnResult = array(
                'status' => 1,
                'msg'    => '操作成功',
                'result' => $resultData->message
            );
        } else {
            $returnResult = array(
                'status' => 0,
                'msg'    => '操作失败',
                'result' => $resultData->message
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
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
            var_dump($params);
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