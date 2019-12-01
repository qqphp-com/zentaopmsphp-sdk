<?php

namespace app\index\controller;

class Index
{
    public function index()
    {
        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ad_bd568ce7058a1091"></think>';
    }

    /**
     * Desc:使用示例-获取部门列表
     */
    public function deptBrowse()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'deptID' => 1
        );
        $result = $zentao->deptBrowse($params);
        return $result;
    }

    /**
     * Desc:使用示例-批量添加部门
     */
    public function deptManageChild()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'parentDeptID' => '1',
            'depts[0]'     => 'Department A',
            'depts[1]'     => 'Department B',
        );
        $result = $zentao->deptManageChild($params);
        return $result;
    }

    /**
     * Desc:使用示例-获取用户列表
     */
    public function companyBrowse()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'deptID' => 1
        );
        $result = $zentao->companyBrowse($params);
        return $result;
    }

    /**
     * Desc:添加用户可选信息
     */
    public function userCreateInfo()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $result = $zentao->userCreateInfo();
        return $result;
    }

    /**
     * Desc:新增用戶
     */
    public function userCreate()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'dept'      => 1,
            'account'   => 'Jack8',
            'password1' => '123456',
            'password2' => '123456',
            'realname'  => 'jack8',
            'join'      => '2019-11-11',
            'role'      => 'dev',
            'group'     => 2,
            'email'     => 'jack2019@gmail.com',
            'commiter'  => 'http://jack2019.com',
            'gender'    => 'm'
        );
        $result = $zentao->userCreate($params);
        return $result;
    }

    /**
     * Desc:获取产品列表productAll
     */
    public function productAll()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $result = $zentao->productAll();
        return $result;
    }

    /**
     * Desc:添加产品可选信息
     * Date:2019/11/25/025
     */
    public function productCreateInfo()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $result = $zentao->productCreateInfo();
        return $result;
    }

    /**
     * Desc:添加单个产品
     * Date:2019/11/25/025
     */
    public function productCreate()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'name'         => 'product-100',
            'code'         => 'p100',
            'line'         => 0,
            'PO'           => 'lisi',
            'QD'           => 'lisi',
            'RD'           => 'lisi',
            'type'         => 'normal',
            'status'       => 'normal',
            'desc'         => 'product description,product description',
            'acl'          => 'custom',
            'whitelist[0]' => '1',
            'whitelist[1]' => '2',
        );
        $result = $zentao->productCreate($params);
        return $result;
    }

    /**
     * Desc:获取项目列表
     */
    public function projectAll()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'status' => 'doing'
        );
        $result = $zentao->projectAll($params);
        return $result;
    }

    /**
     * Desc:获取项目可选信息
     */
    public function projectCreateInfo()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $result = $zentao->projectCreateInfo();
        return $result;
    }


    /**
     * Desc:添加单个项目
     */
    public function projectCreate()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'name'        => '阿里云项目开发',
            'code'        => 'alicloud',
            'begin'       => '2019-11-10',
            'end'         => '2019-11-11',
            'days'        => '1',
            'team'        => '支付宝开发团队',
            'type'        => 'sprint',
            'status'      => 'wait',
            'products[0]' => 0,
            'plans[0]'    => 0,
            'desc'        => '阿里云项目开发，挣他一个亿',
            'acl'         => 'open'
        );
        $result = $zentao->projectCreate($params);
        return $result;
    }

    /**
     * Desc:获取任务列表
     */
    public function projectTask()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'projectID' => 1,
            'status'    => 'all',
            'orderBy'   => 'pri_asc'
        );
        $result = $zentao->projectTask($params);
        return $result;
    }

    /**
     * Desc:添加任务可选信息
     * Date:2019/11/26/026
     */
    public function taskCreateInfo()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'projectID' => 1
        );
        $result = $zentao->taskCreateInfo($params);
        return $result;
    }

    /**
     * Desc:添加单个任务
     */
    public function taskCreate()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'project'          => 1,
            'type'             => 'ui',
            'module'           => 1,
            'assignedTo[]'     => 'lisi',
            'testAssignedTo[]' => 'lisi',
            'color'            => '',
            'name'             => '测试添加任务2',
            'pri'              => 2,
            'estimate'         => 1,
            'desc'             => '测试添加任务描述测试添加任务描述',
            'estStarted'       => '2019-11-11',
            'deadline'         => '2019-11-12',
            'mailto[1]'        => 'lisi'
        );
        $result = $zentao->taskCreate($params);
        return $result;
    }

    /**
     * Desc:完成单个任务可选信息
     */
    public function taskFinishInfo()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'taskID' => 10
        );
        $result = $zentao->taskFinishInfo($params);
        return $result;
    }

    /**
     * Desc:完成单个任务
     */
    public function taskFinish()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'taskID'          => 10,
            'currentConsumed' => 1,
            'consumed'        => 2,
            'assignedTo'      => 'lisi',
            'finishedDate'    => '2019-11-12',
            'comment'         => 'Complete description,I finished.'
        );
        $result = $zentao->taskFinish($params);
        return $result;
    }

    /**
     * Desc:获取BUG列表
     */
    public function bugBrowse()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'productID'  => 1,
            'branch'     => 0,
            'browseType' => 'unresolved'
        );
        $result = $zentao->bugBrowse($params);
        return $result;
    }

    /**
     * Desc:添加单个BUG可选信息
     */
    public function bugCreateInfo()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'productID' => 1
        );
        $result = $zentao->bugCreateInfo($params);
        return $result;
    }

    /**
     * Desc:添加单个BUG
     */
    public function bugCreate()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'product'        => 1,
            'module'         => 2,
            'project'        => 1,
            'openedBuild[1]' => 'trunk',
            'assignedTo'     => 'niuqi',
            'deadline'       => '2019-11-21',
            'type'           => 'codeerror',
            'os'             => 'windows',
            'browser'        => 'ie11',
            'title'          => '添加bug测试四',
            'color'          => '#2dbdb2',
            'severity'       => 2,
            'pri'            => 1,
            'steps'          => '重现步骤描述添加bug测试四',
            'story'          => 0,
            'task'           => 0,
            'mailto[1]'      => 'lisi',
            'keywords'       => 'bug4'
        );
        $result = $zentao->bugCreate($params);
        return $result;
    }

    /**
     * Desc:解决单个BUG可选信息
     */
    public function bugResolveInfo()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'bugID' => 6
        );
        $result = $zentao->bugResolveInfo($params);
        return $result;
    }

    /**
     * Desc:解决单个BUG
     */
    public function bugResolve()
    {
        include_once('../vendor/zentao/zentao.php');
        $zentao = new \zentao\zentao\zentao();
        $params = array(
            'bugID'         => 6,
            'resolution'    => 'bydesign',
            'resolvedBuild' => 'trunk',
            'resolvedDate'  => '2019-11-22',
            'assignedTo'    => 'lisi',
            'comment'       => '啊啊飒飒',
//            'buildProject'  => 1,
//            'buildName'     => '版本7.2.5',
//            'createBuild'   => 1
        );
        $result = $zentao->bugResolve($params);
        return $result;
    }
}
