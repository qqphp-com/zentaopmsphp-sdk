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
            'deptID' => 47
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
            'dept'      => 47,
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
            'QD'           => 'zhangsan',
            'RD'           => 'wangwu',
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
}
