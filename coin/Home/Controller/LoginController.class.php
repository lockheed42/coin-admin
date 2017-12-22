<?php
/**
 * Created by PhpStorm.
 * User: lockheed
 * Date: 2017/12/21
 * Time: 17:43
 */

namespace Home\Controller;

use Home\Common\CommonController;

class LoginController extends CommonController
{
    /**
     * 登录
     */
    public function login()
    {
        try {
            if (IS_POST) {
                $name = I('post.username');
                $pwd = I('post.password');

                $admin = M('admin')->where(['name' => $name, 'pwd' => md5($pwd)])->find();
                if (empty($admin)) {
                    throw new \Exception('用户未登录');
                }

                $this->setPassport($admin);
                redirect('?c=index&a=index');
            } else {
                $this->display();
            }
        } catch (\Exception $e) {
            $this->assign('alert', true);
            $this->assign('error', "<script>alert('账号密码错误');</script>");
            $this->display();
        }
    }

    /**
     * 登出
     */
    public function logout()
    {
        $this->delPassport();
        redirect('?c=login&a=login');
    }

    public function reg()
    {
        $name = I('post.name');
        $pwd = I('post.pwd');
        $code = I('post.code');

        if ($code != 'Hongjie') {
            return;
        }

        M('admin')->add([
            'name'  => $name,
            'pwd'   => md5($pwd),
            'cdate' => date('Y-m-d H:i:s')
        ]);
    }
}