<?php

namespace Home\Controller;

use Home\Common\CommonController;

class UserController extends CommonController
{
    public function index()
    {
        $this->checkLogin();
        $userList = M('user')->select();

        $this->assign('user_list', $userList);
        $this->display();
    }

    /**
     * 用户信息。包含银行账号
     */
    public function info()
    {
        $this->checkLogin();
        $userId = I('get.user_id');

        $user = M('user')->where(['t_user.user_id' => $userId])->join("left join t_bank_account on t_bank_account.user_id = t_user.user_id")->find();

        $this->assign('user', $user);
        $this->display();
    }
}