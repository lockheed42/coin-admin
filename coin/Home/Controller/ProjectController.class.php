<?php
/**
 * Created by PhpStorm.
 * User: Lockheed Hong
 * Date: 17/11/28
 * Time: 下午9:32
 */

namespace Home\Controller;

use Home\Common\CommonController;

class ProjectController extends CommonController
{
    /**
     * 标的状态
     */
    //已作废
    const STATUS_DEL = 1;
    //未生效
    const STATUS_NOT_VALID = 2;
    //已生效
    const STATUS_VALID = 3;

    public function index()
    {
        $this->checkLogin();
        $projectList = M('project')->where(['project_status' => self::STATUS_NOT_VALID])->select();
        foreach ($projectList as $proKey => $project) {
            $bankAccountInfo = M('bank_account')->where(['user_id' => $project['user_id']])->find();
            $projectList[$proKey]['user'] = $bankAccountInfo['user_name'];
            $projectList[$proKey]['bank_code'] = $bankAccountInfo['code'];

            $planInfo = M('plan')->where(['plan_id' => $project['plan_id']])->find();
            $projectList[$proKey]['money'] = $planInfo['price'] * $project['count'];
            $projectList[$proKey]['plan_name'] = $planInfo['name'];
        }

        $this->assign('project_list', $projectList);
        $this->display();
    }

    /**
     * 确认标的已付款，可以生效
     */
    public function confirm()
    {

    }

    /**
     * 自动过期所有超过24小时，还未付款的标的，并归还额度给plan
     */
    public function autoOverdue()
    {
        try {
            $projectList = M('project')->where(['project_status' => ProjectController::STATUS_NOT_VALID])->select();
            foreach ($projectList as $project) {
                if ($project['cdate'] < time() - 86400) {
                    M('project')->where(['project_id' => $project['project_id']])->save(['project_status' => self::STATUS_DEL]);

                    //归还额度
                    M('plan')->where(['plan_id' => $project['plan_id']])->setInc('sell', $project['count']);
                    $this->log('auto_project', "标的id: ${project['project_id']}，属于用户：${project['user_id']} 自动过期");
                }
            }
        } catch (\Exception $e) {
            $this->log('error', $e);
        }
    }
}