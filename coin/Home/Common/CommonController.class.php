<?php

namespace Home\Common;

use Think\Controller;

class CommonController extends Controller
{
    /**
     * @var string 登录的用户id
     */
    protected $_admin_id;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 检查登录
     *
     * @throws Exception
     */
    protected function checkLogin()
    {
        $info = $this->getLoginInfo();
        if (empty($info)) {
            throw new \Exception('用户未登录');
        }

        $this->_admin_id = $info['admin_id'];
        $this->assign('admin_id', $this->_admin_id);
        $this->assign('admin_name', $info['name']);
    }

    /**
     * 根据key 和 security 获取用户信息
     */
    public function getLoginInfo()
    {
        return [
            'admin_id' => 1,
            'name'     => '超级管理员',
        ];
        $apiKey = I('get.api_key');
        $apiSecurity = I('get.api_security');

        return M('admin')->where(['api_key' => $apiKey, 'api_security' => $apiSecurity])->find();
    }
}