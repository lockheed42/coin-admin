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
        $projectList = M('project')->order('project_id desc')->select();

        //计划名称列表
        $planNameList = [];
        $planList = M('plan')->select();
        foreach ($planList as $plan) {
            $planNameList[$plan['plan_id']] = $plan['name'];
        }
        //用户名称列表
        $userNameList = [];
        $userList = M('user')->select();
        foreach ($userList as $user) {
            $userNameList[$user['user_id']] = $user['name'];
        }

        foreach ($projectList as $key => $value) {
            $projectList[$key]['plan_name'] = $planNameList[$value['plan_id']];
            $projectList[$key]['user_name'] = $userNameList[$value['user_id']];
        }

        $this->assign('project_list', $projectList);
        $this->display();
    }

    /**
     * 审核列表
     */
    public function checkList()
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
        try {
            $this->checkLogin();

            $projectId = I('get.project_id');
            $projectInfo = M('project')->where(['project_id' => $projectId])->find();
            M('project')->where(['project_id' => $projectId])->save(['project_status' => self::STATUS_VALID]);

            //#TODO 日志
            $userInfo = M('user')->where(['user_id' => $projectInfo['user_id']])->find();
            M('business_log')->add([
                'admin_id' => $this->_admin_id,
                'content'  => "确认用户 【${userInfo['name']}】 (id: ${userInfo['name']}) 的 标的（ID: ${projectId}）款项已到位，标的开始生效",
                'cdate'    => date('Y-m-d H:i:s'),
            ]);

            redirect('?c=project&a=checkList');
        } catch (\Exception $e) {
            $this->log('error', $e);
        }

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