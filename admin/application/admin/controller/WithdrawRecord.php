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
 * 提现管理
 * Class Item
 * @package app\admin\controller
 */
class WithdrawRecord extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'LcUserWithdrawRecord';
    protected $sysWalletTable = 'LcSysWallet';

    /**
     * 提现记录
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
        
        $this->title = '提现记录';
        $auth = $this->app->session->get('user');
        $where = '';
        if (isset($auth['username']) and $auth['username'] != 'admin') {
            $where = "(u.system_user_id in (select uid from system_user_relation where parentid={$auth['id']}) or u.system_user_id={$auth['id']} )";
        }
        $query = $this->_query($this->table)->alias('i')->field('i.*,u.username');
        $query->where($where)->join('lc_user u','i.uid=u.id')->equal('i.status#i_status')->like('u.username#u_username,u.agent#u_agent')->dateBetween('i.act_time#i_time')->order('i.id desc')->page();
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
            $wallet = Db::name("lc_user_wallet")->find($vo['wid']);
            if($wallet){
                $vo['wallet_wname'] = $wallet['wname'];
                $vo['wallet_name'] = $wallet['name'];
                $vo['wallet_account'] = $wallet['account'];
                $vo['wallet_img'] = $wallet['img'];
                $vo['wallet_type'] = $wallet['type'];
            }
        }
    }

    /**
     * 同意提现
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function agree()
    {
        $this->applyCsrfToken();
        $id = $this->request->param('id');
        sysoplog('财务管理', '同意提现');
        $this->_save($this->table, ['status' => '1','time2' => date('Y-m-d H:i:s')]);
    }

    /**
     * 拒绝提现
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function refuse()
    {
        $this->applyCsrfToken();
        $id = $this->request->param('id');
        $withdrawRecord = Db::name($this->table)->find($id);
        $uid = $withdrawRecord['uid'];
        
        //拒绝时返还提现金额
        //流水添加
        addFunding($uid,$withdrawRecord['money'],$withdrawRecord['money2'],1,4,getLanguageByTimezone($withdrawRecord['time_zone']));
        //余额返还
        setNumber('LcUser', 'money', $withdrawRecord['money'], 1, "id = $uid");
        sysoplog('财务管理', '拒绝提现');
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
        sysoplog('财务管理', '删除提现记录');
        $this->_delete($this->table);
    }
    
    /**
     * 系统钱包列表
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function sys_wallet_list()
    {
        $rrid = $this->request->param('rrid');
        $this->assign('rrid', $rrid);
        
        $query = $this->_query($this->sysWalletTable);
        $query->order('id asc')->page();
        
    }
    /**
     * 优盾代付付
     * @auth true 
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function pay()
    {
        $this->applyCsrfToken();
        $id = $this->request->param('id');
        $withdraw = Db::name('LcUserWithdrawRecord')->find($id);
        if(empty($withdraw)) $this->error("订单不存在");
        $wallet = Db::name('LcUserWallet')->find($withdraw['wid']);
        if(empty($wallet)) $this->error("用户钱包不存在");
        
        if($withdraw['status']!=0) $this->error("订单状态异常");
        //判断地址有效性
        $json= json_decode(checkAddress($wallet['account']), true);
        if($json['code']!=200){
            $this->error($json['message']);
        }
        //发起代付
        $json= json_decode(proxypay($wallet['account'],$withdraw['money']-$withdraw['charge'],$withdraw['orderNo']), true);
        if($json['code']!=200){
            $this->error($json['message']);
        }
        //状态：审核中 3
        Db::name('LcUserWithdrawRecord')->where('id', $withdraw['id'])->update(['status' => 3]);
        sysoplog('财务管理', '发起优盾代付');
        $this->success('代付成功，请到优盾钱包APP确认');
    }
}
