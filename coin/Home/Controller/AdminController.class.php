<?php
/**
 * Created by PhpStorm.
 * User: lockheed
 * Date: 2017/12/22
 * Time: 17:22
 */

namespace Home\Controller;

use Home\Common\CommonController;

class AdminController extends CommonController
{
    /**
     * 管理员个人资料
     */
    public function info()
    {
        $this->checkLogin();

        $this->display();
    }

    /**
     * 保存信息
     */
    public function save()
    {
        try {
            $this->checkLogin();
            $pwd = I('post.pwd');

            M('admin')->where(['admin_id' => $this->_admin_id])->save(['pwd' => md5($pwd)]);

            redirect('?c=index&a=index');
        } catch (\Exception $e) {
            $this->assign('alert', true);
            $this->assign('error', "<script>alert('保存失败');</script>");
            $this->display();
        }
    }
}