<?php

namespace Home\Controller;

use Home\Common\CommonController;
use Think\Exception;

class PlanController extends CommonController
{
    /**
     * 发行计划状态。
     */
    //预售中
    const STATUS_FOR_SELL = 1;
    //进行中
    const STATUS_RUNNING = 2;
    //已关闭
    const STATUS_CLOSED = 3;

    public function index()
    {
        $this->checkLogin();
        $planList = M('plan')->select();

        $this->assign('plan_list', $planList);
        $this->display();
    }

    public function info()
    {
        $this->checkLogin();
        $planId = I('get.plan_id');

        $plan = M('plan')->where(['plan_id' => $planId])->find();
        $supplierList = M('plan_supplier')->where(['plan_id' => $planId])->select();

        $this->assign('plan', $plan);
        $this->assign('suppliers', $supplierList);
        $this->display();
    }

    /**
     * 设置发行计划的当日收益。
     */
    public function setProfit()
    {
        $this->checkLogin();
        if (IS_POST) {
            try {
                $planId = I('post.plan_id', 0);
                $profit = I('post.profit');

                $plan = M('plan')->where(['plan_id' => $planId])->find();
                if (empty($plan) || $plan['status'] != self::STATUS_RUNNING) {
                    throw new Exception('发行计划状态不符合条件');
                }

                M('plan')->where(['plan_id' => $planId])->save(['profit_price' => $profit]);
                M('plan_profit_log')->add([
                    'plan_id' => $planId,
                    'price'   => $profit,
                    'cdate'   => date('Y-m-d H:i:s'),
                ]);

                redirect('?c=plan&a=index');
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $this->assign('plan_id', $planId);
                $this->assign('message', $message);
                $this->assign('alert', true);
                $this->assign('error', "<script>alert('{$message}');</script>");
                $this->display();
            }
        } else {
            $planId = I('get.plan_id');

            $this->assign('plan_id', $planId);
            $this->display();
        }
    }

    /**
     * 自动开始计划
     */
    public function autoBegin()
    {
        try {
            $time = time();

            $planList = M('plan')->where(['status' => self::STATUS_FOR_SELL])->select();
            foreach ($planList as $plan) {
                if (strtotime($plan['begin']) <= $time) {
                    M('plan')->where(['plan_id' => $plan['plan_id']])->save(['status' => self::STATUS_RUNNING]);
                    $this->log('auto_plan', "计划id: ${plan['plan_id']} 设置为进行中");
                }
            }
        } catch (\Exception $e) {
            $this->log('error', $e);
        }
    }

    /**
     * 自动结束计划
     */
    public function autoEnd()
    {
        try {
            $time = time();

            $planList = M('plan')->where(['status' => self::STATUS_RUNNING])->select();
            foreach ($planList as $plan) {
                if (strtotime($plan['end']) <= $time) {
                    M('plan')->where(['plan_id' => $plan['plan_id']])->save(['status' => self::STATUS_CLOSED]);
                    $this->log('auto_plan', "计划id: ${plan['plan_id']} 设置为关闭");
                }
            }
        } catch (\Exception $e) {
            $this->log('error', $e);
        }
    }
}