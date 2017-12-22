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
    public function info()
    {
        $this->checkLogin();

        $this->display();
    }

    public function save()
    {
        try {
            $pwd = I('post.pwd');
            $this->checkLogin();

            M('admin')->where(['admin_id' => $this->_admin_id])->save(['pwd2' => md5($pwd)]);

            redirect('?c=index&a=index');
        } catch (\Exception $e) {
            $this->assign('alert', true);
            $this->assign('error', "<script>alert('保存失败');</script>");
            $this->display();
        }
    }
}