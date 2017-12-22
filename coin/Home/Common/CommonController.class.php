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
        $info = $this->getPassport();
        if (empty($info)) {
            redirect('?c=login&a=login');
        }

        $this->_admin_id = $info['admin_id'];
        $this->assign('admin_id', $info['admin_id']);
        $this->assign('admin_name', $info['name']);
    }

    /**
     * 清除passport
     */
    public function delPassport()
    {
        cookie('passport', null);
    }

    /**
     * 获取passport。失败跳转到登录界面，成功返回admin信息
     *
     * @return mixed
     * @throws \Exception
     */
    public function getPassport()
    {
        $passport = cookie('passport');
        return M('admin')->where(['passport' => $passport])->find();
    }

    /**
     * 设置passport，并保存在cookie内
     *
     * @param $admin
     */
    public function setPassport($admin)
    {
        $passport = md5($admin['admin_id'] . $admin['pwd']);
        cookie('passport', $passport, 86400);
        M('admin')->where(['admin_id' => $admin['admin_id']])->save(['passport' => $passport]);
    }
}