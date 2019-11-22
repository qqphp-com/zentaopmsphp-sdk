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
//        $optionalParams = [
//            'taskID' => 15
//        ];
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
            $returnResult = array(
                'status' => 1,
                'msg'    => '获取成功',
                'result' => array(
                    'title'   => $resultList->title,
                    'users'   => $resultList->users,
                    'task'    => $resultList->task,
                    'project' => $resultList->project,
                    'actions' => $resultList->actions,
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
//        $optionalParams = [
//            'taskID'          => 200,
//            'currentConsumed' => 1,
//            'consumed'        => 8,
//            'assignedTo'      => 'productManager',
//            'finishedDate'    => '2019-12-07',
//            'comment'         => '我是李四，我完成了',
//        ];
        $returnResult = $this->returnResult;
        if (self::requestType == 'GET') {
            $this->params = [
                'status' => 'done',
            ];
            $taskID       = $optionalParams['taskID'];
            unset($optionalParams['taskID']);
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '?m=task&f=finish&taskID=' . $taskID . '&t=json');
        } elseif (self::requestType == 'PATH_INFO') {
            $this->params = [
                'status' => 'done',
            ];
            $taskID       = $optionalParams['taskID'];
            unset($optionalParams['taskID']);
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '/task-finish-' . $taskID . '.json');
        }
        if (strpos($result, 'task-view-' . $taskID . '.json') || strpos($result, 'taskID=' . $taskID)) {
            $returnResult = array(
                'status' => 1,
                'msg'    => '操作成功',
                'result' => array()
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:添加单个BUG可选信息
     * Date:2019/11/20/020
     */
    public function bugCreateInfo($optionalParams = [])
    {
        //模拟数据
        $optionalParams = [
            'productID' => 1
        ];
        $this->params   = [
            'm' => 'bug',
            'f' => 'create'
        ];
        $this->params   = array_merge($this->params, $optionalParams);
        $result         = $this->getUrl(self::ztUrl);
        $resultData     = json_decode($result);
        $returnResult   = $this->returnResult;
        if (strcmp($resultData->status, 'success') == 0) {
            $resultList   = json_decode($resultData->data);
            $returnResult = array(
                'status' => 1,
                'msg'    => '获取成功',
                'result' => array(
                    'title'            => $resultList->title,
                    'productID'        => $resultList->productID,
                    'productName'      => $resultList->productName,
                    'projects'         => $resultList->projects,
                    'moduleOptionMenu' => $resultList->moduleOptionMenu,
                    'users'            => $resultList->users,
                    'stories'          => $resultList->stories,
                    'builds'           => $resultList->builds,
                )
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:添加单个BUG
     * Date:2019/11/21/021
     */
    public function bugCreate($optionalParams = [])
    {
        //模拟数据
        /*$optionalParams = [
            'product'        => 1,//所属产品ID
            'module'         => 2,//所属模块ID
            'project'        => 1,//所属项目ID
            'openedBuild[1]' => 'trunk',//影响版本【可多个】
            'assignedTo'     => 'zhangsan',//当前指派【用户账号】
            'deadline'       => '2019-11-21',//截止日期【格式示例：2019-11-21】
            'type'           => 'codeerror',//BUG类型【codeerror代码错误|config配置相关|install安装部署|security安全相关|performance性能问题|standard标准规范|automation测试脚本|designdefect设计缺陷|others其他】
            'os'             => 'windows',//操作系统【all-全部|windows-Windows|win10-Windows 10|win8-Windows 8|win7-Windows 7|vista-Windows Vista|winxp-Windows XP|win2012-Windows 2012|win2008-Windows 2008|win2003-Windows 2003|win2000-Windows 2000|android-Android|ios-IOS|wp8-WP8|wp7-WP7|symbian-Symbian|linux-Linux|freebsd-FreeBSD|osx-OS X|unix-Unix|others-其他】
            'browser'        => 'ie11',//浏览器【all-全部|ie-IE系列|ie11-IE11|ie10-IE10|ie9-IE9|ie8-IE8|ie7-IE7|ie6-IE6|chrome-Chrome|firefox-firefox系列|firefox4-firefox4|firefox3-firefox3|firefox2-firefox2|opera-opera系列|oprea11-oprea11|oprea10-opera10|opera9-opera9|safari-safari|maxthon-傲游|us-UC|other-其他】
            'title'          => '添加bug测试三',//BUG标题
            'color'          => '#2dbdb2',//BUG颜色【示例：#2dbdb2】
            'severity'       => 2,//严重程度
            'pri'            => 1,//优先级
            'steps'          => '重现步骤描述添加bug测试三',//重现步骤描述
            'story'          => 0,//相关需求
            'task'           => 0,//相关任务
            'mailto[1]'      => 'zhangsan',//抄送给
            'keywords'       => '修改bug',//关键词
        ];*/
        $returnResult = $this->returnResult;
        if (self::requestType == 'GET') {
            $this->params = [
                'status' => 'active'
            ];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '?m=bug&f=create&productID=' . $optionalParams['product'] . '&t=json');
        } elseif (self::requestType == 'PATH_INFO') {
            $this->params = [
                'status' => 'active'
            ];
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '/bug-create-' . $optionalParams['product'] . '.json');
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
     * Desc:获取BUG列表
     * Date:2019/11/20/020
     */
    public function bugBrowse($optionalParams = [])
    {
        //模拟数据
//        $optionalParams = [
//            'productID' => 1,
//            'branch' => 0,
//        ];
        $this->params = [
            'm' => 'bug',
            'f' => 'browse',
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
                    'products'    => $resultList->products,
                    'productID'   => $resultList->productID,
                    'productName' => $resultList->productName,
                    'product'     => $resultList->product,
                    'moduleName'  => $resultList->moduleName,
                    'modules'     => $resultList->modules,
                    'browseType'  => $resultList->browseType,
                    'bugs'        => $resultList->bugs,
                )
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:解决单个BUG可选信息
     * Date:2019/11/20/020
     */
    public function bugResolveInfo($optionalParams = [])
    {
        //模拟数据
//        $optionalParams = [
//            'bugID' => 5
//        ];
        $this->params   = [
            'm' => 'bug',
            'f' => 'resolve'
        ];
        $this->params   = array_merge($this->params, $optionalParams);
        $result         = $this->getUrl(self::ztUrl);
        $resultData     = json_decode($result);
        $returnResult   = $this->returnResult;
        if (strcmp($resultData->status, 'success') == 0) {
            $resultList   = json_decode($resultData->data);
            $returnResult = array(
                'status' => 1,
                'msg'    => '获取成功',
                'result' => array(
                    'title'    => $resultList->title,
                    'products' => $resultList->products,
                    'bug'      => $resultList->bug,
                    'users'    => $resultList->users,
                    'builds'   => $resultList->builds,
                    'actions'  => $resultList->actions
                )
            );
        }
        return json_encode($returnResult, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Desc:解决单个BUG
     * Date:2019/11/20/020
     */
    public function bugResolve($optionalParams = [])
    {
        //模拟数据
        /*$optionalParams = [
            'bugID'         => 6,
            'resolution'    => 'bydesign',//解决方案【bydesign设计如此|duplicate重复BUG|external外部原因|fixed已解决|notrepro无法重现|postponed延期处理|willnotfix不予解决】
            'resolvedBuild' => 'trunk',
            'resolvedDate'  => '2019-11-22 15:46:16',
            'assignedTo'    => 'lisi',
            'comment'       => '啊啊飒飒',
//            'buildProject' => 1,
//            'buildName' => '版本7.2.4',
//            'createBuild' => 1
        ];*/
        $returnResult   = $this->returnResult;
        if (self::requestType == 'GET') {
            $this->params = [
                'status' => 'resolved',
            ];
            $bugID       = $optionalParams['bugID'];
            unset($optionalParams['bugID']);
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '?m=bug&f=resolve&bugID=' . $bugID . '&t=json');
        } elseif (self::requestType == 'PATH_INFO') {
            $this->params = [
                'status' => 'resolved',
            ];
            $bugID       = $optionalParams['bugID'];
            unset($optionalParams['bugID']);
            $this->params = array_merge($this->params, $optionalParams);
            $result       = $this->postUrl(self::ztUrl . '/bug-resolve-' . $bugID . '.json');
        }
        if (strpos($result, 'bug-view-' . $bugID . '.json') || strpos($result, 'bugID=' . $bugID)) {
            $returnResult = array(
                'status' => 1,
                'msg'    => '操作成功',
                'result' => array()
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