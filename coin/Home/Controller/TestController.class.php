<?php
/**
 * Created by PhpStorm.
 * User: Lockheed Hong
 * Date: 17/11/28
 * Time: 下午9:32
 */

namespace Home\Controller;

use Think\Controller;

class TestController extends Controller
{
    public function abc($id)
    {
        echo 'id:' . $id;
    }

    public function index()
    {
        echo 'index';
    }
}