<?php
namespace Home\Controller;

use Home\Common\CommonController;

class IndexController extends CommonController
{
    public function index()
    {
        $this->checkLogin();

        $this->display();
    }
}