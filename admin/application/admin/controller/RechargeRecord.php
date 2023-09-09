<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2019 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://demo.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkAdmin
// | github 代码仓库：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller;

use library\Controller;
use think\Db;

/**
 * 充值管理
 * Class Item
 * @package app\admin\controller
 */
class RechargeRecord extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'LcUserRechargeRecord';

    /**
     * 充值记录
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '充值记录';
        $auth = $this->app->session->get('user');
        $params = $this->request->param();
        $where = ' 1 ';
        if (isset($auth['username']) and $auth['username'] != 'admin') {
            $where .= " and (u.system_user_id in (select uid from system_user_relation where parentid={$auth['id']}) or u.system_user_id={$auth['id']} )";
        }
        if (isset($params['superior_username'])) {
            // 上级邀请人
            if ($params['superior_username']) {
                $user_id = Db::table('lc_user')->alias('u')->join('lc_user_relation ur', 'ur.uid=u.id')->join('lc_user lu', 'lu.id=ur.parentid')->where("lu.username like '%{$params['superior_username']}%' and (u.system_user_id in (select uid from system_user_relation where parentid={$auth['id']}) or u.system_user_id={$auth['id']} )")->column('lu.id');
                $where .= " and i.uid in (".($user_id ? implode(',', $user_id) : 0).") ";
            }
            // 充值金额
            if ($params['recharge_amount'] && $params['recharge_amount_1']) {
                $where .= " and i.money BETWEEN {$params['recharge_amount']} and {$params['recharge_amount_1']} ";
            }
        }
        $this->methods = Db::table('lc_user_recharge_method')->where('delete',0)->select();
        $query = $this->_query($this->table)->alias('i')->field('i.*,u.username');
        $query->where($where)->join('lc_user u','i.uid=u.id')->equal('i.status#i_status,i.rid#rid,i.orderNo#order_no')->like('u.username#u_username,u.agent#u_agent')->dateBetween('i.act_time#i_time')->order('i.id desc')->page();
    }

    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _index_page_filter(&$data)
    {
        foreach($data as &$vo){
            $method = Db::name("lc_user_recharge_method")->find($vo['rid']);
            if($method){
                $vo['rname'] = $method['name'];
                $vo['rtype'] = $method['type'];
            }
        }
    }

    /**
     * 同意充值
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function agree()
    {
        $this->applyCsrfToken();
        $id = $this->request->param('id');
        $ids = explode(',',$id);
        foreach($ids as $id) {
            $rechargeRecord = Db::name($this->table)->find($id);
            $method_type = Db::table('lc_user_recharge_method')->where("id={$rechargeRecord['rid']}")->value('type');
            if ($method_type == 3) {
                continue;
            }
            $uid = $rechargeRecord['uid'];
            
            if ($rechargeRecord['status'] != 0) {
                continue;
            }
            
            //流水添加
            addFunding($uid,$rechargeRecord['money'],$rechargeRecord['money2'],1,2,getLanguageByTimezone($rechargeRecord['time_zone']));
            //添加余额
            setNumber('LcUser', 'money', $rechargeRecord['money'], 1, "id = $uid");
            Db::name($this->table)->where("id=$id")->update(['status' => '1','time2' => date('Y-m-d H:i:s')]);
            //添加积分
            // setNumber('LcUser', 'value', $rechargeRecord['money'], 1, "id = $uid");
            //更新会员等级
            // $user_1 = Db::name("LcUser")->find($uid);
            // setUserMember($uid,$user_1['value']);
            
            //添加冻结金额
            // $info = Db::name('LcInfo')->find(1);
            // if($info['recharge_need_flow']){
            //     setNumber('LcUser', 'frozen_money', $rechargeRecord['money'], 1, "id = $uid");
            // }
            //团队充值奖励
            // setTemRechargeReward($uid,$rechargeRecord['money']);
        }
        
        sysoplog('财务管理', '同意充值');
        
        // $this->_save($this->table, ['status' => '1','time2' => date('Y-m-d H:i:s')]);
        $this->success('success');
    }

    /**
     * 拒绝充值
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function refuse()
    {
        $this->applyCsrfToken();
        $id = $this->request->param('id');
        
        sysoplog('财务管理', '拒绝充值');
        
        $this->_save($this->table, ['status' => '2', 'time2' => date('Y-m-d H:i:s')]);
    }
    
    /**
     * 删除记录
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        $this->applyCsrfToken();
        
        sysoplog('财务管理', '删除充值记录');
        
        $this->_delete($this->table);
    }
}
