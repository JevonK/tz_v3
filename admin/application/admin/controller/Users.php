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
 * 用户管理
 * Class Item
 * @package app\admin\controller
 */
class Users extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'LcUser';
    protected $table_member = 'LcUserMember';
    protected $table_relation = 'LcUserRelation';

    /**
     * 用户列表
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
        $auth = $this->app->session->get('user');
        $this->title = '用户列表';
        
        $query = $this->_query($this->table)->equal('auth_email#u_auth_email,auth_google#u_auth_google,clock#u_clock')->like('username#u_username,ip#u_ip')->dateBetween('time#u_time')->order('id desc')->page();
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
        $ip = new \Ip2Region();
        foreach($data as &$vo){
            $vo['online'] = strtotime($vo['logintime'])>(time()-300)?1:0;
            $vo['withdraw_sum']  = Db::name("lc_user_withdraw_record")->where("uid = {$vo['id']} AND status = '1'")->sum('money');
            $vo['recharge_sum']  = Db::name("lc_user_recharge_record")->where("uid = {$vo['id']} AND status = '1'")->sum('money');
            $vo['invest_sum']  = Db::name('lc_invest')->where("uid = {$vo['id']}")->sum('money');
            $vo['invest_wait_rate']  = Db::name('lc_invest')->where("uid = {$vo['id']} AND wait_interest > 0 AND status = 0")->sum('wait_interest');
            $vo['invest_wait_money']  = Db::name('lc_invest')->where("uid = {$vo['id']} AND money > 0 AND status = 0")->sum('money');
            $result = $ip->btreeSearch($vo['ip']);
            $vo['isp'] = isset($result['region']) ? $result['region'] : '';
            $vo['isp'] = str_replace(['内网IP', '0', '|'], '', $vo['isp']);
            $top_user = Db::name('LcUserRelation')->where("uid = {$vo['id']} AND level = 1")->find();
            if(!empty($top_user)){
                $top_user = Db::name('LcUser')->find($top_user['parentid']);
                if(!empty($top_user)){
                    $vo['top'] = $top_user['username'];
                }
            }
            $vip = Db::name('LcUserMember')->find($vo['mid']);
            $vo['vip_name'] = $vip['name'];
            
            $vo['tema_direct_count'] = Db::name('LcUserRelation')->where("parentid = {$vo['id']} AND level = 1")->count();
            $vo['tema_indirect_count'] = Db::name('LcUserRelation')->where("parentid = {$vo['id']} AND level <> 1")->count();
            $vo['tema_all_money'] = Db::name('LcUser u,lc_user_relation ur')->where("u.id=ur.uid AND ur.parentid = {$vo['id']}")->sum('u.money');
            
        }
    }

    /**
     * 表单数据处理
     * @param array $data
     * @auth true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _form_filter(&$vo)
    {
        if ($this->request->isPost()) {
            if(!empty($vo['password_temp'])) $vo['password'] = md5($vo['password_temp']);
            //有id则为更新，无id则为新增
            if(isset($vo['id'])){
                $money = Db::name($this->table)->where("id = {$vo['id']}")->value('money');
                if($money&&$money != $vo['money']){
                    $handle_money = $money-$vo['money'];
                    $type = $handle_money>0?2:1;
                    //添加流水
                    $language = getLanguageByTimezone($vo['time_zone']);
                    addFunding($vo['id'],abs($handle_money),changeMoneyByLanguage(abs($handle_money),$language),$type,1,$language);
                    sysoplog('用户管理', "修改用户资金，账号：{$vo['username']}");
                }else{
                    sysoplog('用户管理', "修改用户资料，账号：{$vo['username']}");
                }
            }else{
                if (Db::name($this->table)->where(['username' => $vo['username']])->count() > 0) {
                    $this->error("账号{$vo['username']}已经存在，请使用其它账号！");
                }
                //判断推荐人
                $top_user = Db::name($this->table)->where(['username' => $vo['top_user']])->find();
                if(!empty($vo['top_user'])){
                    if (empty($top_user)) {
                        $this->error("推荐人{$vo['top_user']}不存在，请使用其它账号！");
                    }
                }
                
                $vo['time'] = date('Y-m-d H:i:s');
                $vo['password'] = md5($vo['password']);
                $vo['act_time'] = dateTimeChangeByZone($vo['time'], 'Asia/Shanghai', $vo['time_zone'], 'Y-m-d H:i:s');
                
                
                //邀请码
                $max_id = Db::name($this->table)->max('id');
                $vo['id'] = $max_id + 1;
                $vo['invite_code'] = createCode($vo['id']);
                $vo['mid'] = Db::name($this->table_member)->min('id');
                
                //插入邀请人关系网数据
                if (!empty($top_user)) {
                    $info = Db::name('LcInfo')->find(1);
                    insertUserRelation($vo['id'],$top_user['id'],$info['invite_level']);
                }
                sysoplog('用户管理', '添加用户');
            }
        } else {
            $this->member = Db::name("LcUserMember")->order('id desc')->select();
            $this->currencies = Db::name("LcCurrency")->order('sort asc')->select();
            $vo['auth_email'] = isset($vo['auth_email'])?$vo['auth_email']:0;
            $vo['auth_google'] = isset($vo['auth_google'])?$vo['auth_google']:0;
            $vo['clock'] = isset($vo['clock'])?$vo['clock']:0;
        }
    }
    /**
     * 用户关系网
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function user_relation(){
        $this->title = '用户关系网';
        $username = $this->request->param('username');
        $type = $this->request->param('type');
        $user = Db::name('LcUser')->where(['username' => $username])->find();
        $where = '1=2';
        if(!empty($user)){
            $uid = $user['id'];
            if($type == 1){
                $where = "parentid = $uid";
            }else{
                $where = "uid = $uid";
            }
        }
        
        $query = $this->_query($this->table_relation)->where($where)->order('level asc,id asc')->page();
    }
    
    /**
     * 数据列表处理
     * @param array $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _user_relation_page_filter(&$data)
    {
        $type = $this->request->param('type');
        foreach($data as &$vo){
            $user_top;
            $user_my;
            if($type==1){
                $user_top = Db::name('LcUserRelation')->where("uid = {$vo['uid']} AND level = 1")->find();
                $user_my = Db::name('LcUser')->find($vo['uid']);
            }else{
                $user_top = Db::name('LcUserRelation')->where("uid = {$vo['parentid']} AND level = 1")->find();
                $user_my = Db::name('LcUser')->find($vo['parentid']);
            }
            
            if(!empty($user_top)){
                $user_top = Db::name('LcUser')->find($user_top['parentid']);
                $vo['top'] = $user_top['username'];
            }
            $vo['username'] = '--';
            $vo['time'] = '--';
            $vo['act_time'] = '--';
            $vo['time_zone'] = '--';
            $vo['team_direct_count'] = '--';
            $vo['team_indirect_count'] = '--';
            if(!empty($user_my)){
                $vo['username'] = $user_my['username'];
                $vo['time'] = $user_my['time'];
                $vo['act_time'] = $user_my['act_time'];
                $vo['time_zone'] = $user_my['time_zone'];
                $vo['team_direct_count'] = Db::name('LcUserRelation')->where("parentid = '{$user_my['id']}' AND level = 1")->count();
                $vo['team_indirect_count'] = Db::name('LcUserRelation')->where("parentid = '{$user_my['id']}' AND level <> 1")->count();
            }
        }
    }
    /**
     * 添加用户
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $this->applyCsrfToken();
        $this->_form($this->table, 'add_form');
    }

    /**
     * 编辑用户
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->applyCsrfToken();
        $this->_form($this->table, 'form');
    }

    /**
     * 禁用用户
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->applyCsrfToken();
        $this->_save($this->table, ['clock' => '1']);
    }

    /**
     * 启用用户
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->applyCsrfToken();
        $this->_save($this->table, ['clock' => '0']);
    }

    /**
     * 删除用户
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        $this->applyCsrfToken();
        sysoplog('用户管理', '删除用户');
        $this->_delete($this->table);
    }
}
