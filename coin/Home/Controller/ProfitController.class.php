<?php
/**
 * Created by PhpStorm.
 * User: Lockheed Hong
 * Date: 17/11/28
 * Time: 下午9:32
 */

namespace Home\Controller;

use Home\Common\CommonController;

class ProfitController extends CommonController
{
    /**
     * 没有手动设定当天的每T算力，短信报警
     */
    public function autoAlertNoProfitPrice()
    {

    }

    /**
     * 每天自动结算当日收益
     */
    public function autoCalc()
    {
        try {
            $planList = M('plan')->where(['status' => PlanController::STATUS_RUNNING])->select();
            foreach ($planList as $plan) {
                $projectList = M('project')->where(['plan_id' => $plan['plan_id'], 'project_status' => ProjectController::STATUS_VALID])->select();
                foreach ($projectList as $project) {
                    //保存收益记录
                    M('profit_log')->add([
                        'plan_id'    => $plan['plan_id'],
                        'project_id' => $project['project_id'],
                        'user_id'    => $project['user_id'],
                        'profit'     => $project['count'] * $plan['profit_price'],
                        'date'       => date('Y-m-d'),
                    ]);
                }

                //结算完毕的计划，计入发行计划收益日志，并手工输入收益清零
                M('plan_profit_log')->add([
                    'plan_id' => $plan['plan_id'],
                    'price'   => $plan['profit_price'],
                    'cdate'   => date('Y-m-d H:i:s'),
                ]);
                M('plan')->where(['plan_id' => $plan['plan_id']])->save(['profit_price' => 0]);
            }
        } catch (\Exception $e) {
            $this->log('error', $e);
        }
    }
}