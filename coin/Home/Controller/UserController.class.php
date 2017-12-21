<?php
namespace Home\Controller;

use Home\Common\CommonController;

class UserController extends CommonController
{
    public function index()
    {
        $this->checkLogin();

        $this->display();
    }
}