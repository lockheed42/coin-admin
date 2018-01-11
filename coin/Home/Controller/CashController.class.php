<?php

namespace Home\Controller;

use Home\Common\CommonController;
use Think\Exception;

class CashController extends CommonController
{
    public function index()
    {
        $this->checkLogin();
        $oneWeek = date('Y-m-d', strtotime('-1 week'));

        //计算上一周的所有收益，汇总到每个user_id下
        $cashList = [];
        $profitList = M('profit_log')->where([
            'date'   => ['EGT', $oneWeek],
            'status' => 0,
        ])->select();
        foreach ($profitList as $profit) {
            $cashList[$profit['user_id']] += $profit['profit'];
        }

        //拼接银行信息
        $outList = [];
        foreach ($cashList as $userId => $cash) {
            $bankAccount = M('bank_account')->where(['user_id' => $userId])->find();
            $outList[$userId]['cash'] = $cash;
            $outList[$userId]['user_id'] = $userId;
            $outList[$userId]['bank_name'] = $bankAccount['bank_name'];
            $outList[$userId]['reg_bank'] = $bankAccount['reg_bank'];
            $outList[$userId]['user_name'] = $bankAccount['user_name'];
            $outList[$userId]['code'] = $bankAccount['code'];
            $outList[$userId]['date'] = $oneWeek;
        }

        $this->assign('list', $outList);
        $this->display();
    }

    /**
     * 确认打款收益
     */
    public function confirm()
    {
        $this->checkLogin();
        if (IS_POST) {
            try {
                $userId = I('post.user_id');

                //查询用户一周收益
                $oneWeek = date('Y-m-d', strtotime('-1 week'));
                //查询出总金额，并准备保存日志
                $profitList = M('profit_log')->where([
                    'date'    => ['EGT', $oneWeek],
                    'user_id' => $userId,
                    'status'  => 0
                ])->select();
                $total = 0;
                foreach ($profitList as $profit) {
                    $total += $profit['profit'];
                }

                if ($total == 0) {
                    throw new Exception('打款金额为0');
                }

                M()->startTrans();
                //把一周内该用户所有的收益都设置为已兑现
                M('profit_log')->where([
                    'date'    => ['EGT', $oneWeek],
                    'user_id' => $userId,
                    'status'  => 0
                ])->save([
                    'status' => 1
                ]);

                //保存日志
                M('cash_log')->add([
                    'admin_id' => $this->_admin_id,
                    'user_id'  => $userId,
                    'count'    => $total,
                    'cdate'    => date('Y-m-d H:i:s'),
                ]);

                M()->commit();

                //发送短信

                redirect('?c=cash&a=index');
            } catch (\Exception $e) {
                M()->rollback();
                $message = $e->getMessage();
                $this->assign('message', $message);
                $this->assign('alert', true);
                $this->assign('error', "<script>alert('{$message}');</script>");
                $this->display();
            }
        } else {
            $userId = I('get.user_id');

            //用户资料
            $user = M('user')->where(['user_id' => $userId])->find();

            //查询用户一周收益
            $oneWeek = date('Y-m-d', strtotime('-1 week'));
            $profitList = M('profit_log')->where([
                'date'    => ['EGT', $oneWeek],
                'user_id' => $userId,
                'status'  => 0,
            ])->select();
            $projectList = [];
            foreach ($profitList as $profit) {
                $projectList[$profit['project_id']] += $profit['profit'];
            }

            //拼接发行计划 和 拥有额度数据
            $outputList = [];
            foreach ($projectList as $projectId => $val) {
                $project = M('project')->where(['project_id' => $projectId])->join('t_plan on t_plan.plan_id = t_project.plan_id')->find();
                $outputList[] = [
                    'id'     => $project['project_id'],
                    'name'   => $project['name'],
                    'count'  => $project['count'],
                    'profit' => $val,
                ];
            }

            $this->assign('user_name', $user['name']);
            $this->assign('user_id', $userId);
            $this->assign('list', $outputList);
            $this->display();
        }
    }

    /**
     * 打款日志
     */
    public function cashLog()
    {
        $this->checkLogin();
        $admin = M('admin')->select();
        $adminList = [];
        foreach ($admin as $value) {
            $adminList[$value['admin_id']] = $value['name'];
        }
        $user = M('bank_account')->select();
        $userList = [];
        foreach ($user as $value) {
            $userList[$value['user_id']] = $value['user_name'];
        }

        $log = M('cash_log')->order('id desc')->select();
        foreach ($log as $k => $v) {
            $log[$k]['user_name'] = $userList[$v['user_id']];
            $log[$k]['admin_name'] = $adminList[$v['admin_id']];
        }

        $this->assign('list', $log);
        $this->display();
    }
}