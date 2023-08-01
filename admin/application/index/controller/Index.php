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

namespace app\index\controller;

use library\Controller;
use library\service\AdminService;
use library\service\MenuService;
use think\Db;

/**
 * 应用入口
 * Class Index
 * @package app\index\controller
 */
class Index extends Controller
{

    /**
     * @description：首页
     * @date: 2020/5/13 0013
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        // if(getInfo("pc_open")) $this->fetch();
        // if(check_wap()) $this->fetch();
        $this->title = '系统管理后台';
        $auth = AdminService::instance()->apply(true);
        if(!$auth->isLogin()) $this->redirect('@admin/login');
        $this->menus = MenuService::instance()->getTree();
        if (empty($this->menus) && !$auth->isLogin()) {
            $this->redirect('@admin/login');
        } else {
            $this->redirect('/admin.html#/admin/users');
        }
    }

    /**
     * Describe:回调通知
     * DateTime: 2020/12/07 2:07
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function _callback(){
        //回调示例
        $call_back_data = array(
            'timestamp' => (string) $_POST['timestamp'],
            'nonce' => (string) $_POST['nonce'],
            'sign' => (string) $_POST['sign'],
            'body' => (string) $_POST['body'],
        );
        $merchant = Db::name('LcMerchant')->find(1);
        $sign = md5($call_back_data['body'] . $merchant['api_key'] . $call_back_data['nonce'] . $call_back_data['timestamp']);
        
        if ($call_back_data['sign'] == $sign) {
            $body = json_decode($call_back_data['body']);
            
            $merchant = Db::name('LcMerchant')->find(1);
            
            //$body->tradeType 1充币回调 2提币回调
            if ($body->tradeType == 1) {
                //$body->status 0待审核 1审核成功 2审核驳回 3交易成功 4交易失败
                if($body->status == 3){
                    //判断币种类型
                    if($merchant['main_coin_type']==$body->mainCoinType&&$merchant['coin_type']==$body->coinType){
                        //业务处理 
                        //优盾USDT免提交
                        $userAddress = Db::name('LcUser')->where(['usdt_address' => $body->address])->find();
                        
                        if(!empty($userAddress)){
                            
                            $money = $body->amount/pow(10,$body->decimals);
                            
                            $money2 = changeMoneyByLanguage($money,getLanguageByTimezone($userAddress['time_zone']));
                            
                            $uid = $userAddress['id'];
                            
                            $currency = Db::name('LcCurrency')->where(['time_zone' => $userAddress['time_zone']])->find();
                            $rechargeMethod = Db::name('LcUserRechargeMethod')->where(['cid' => $currency['id'],'type'=>6])->find();
                            //如果所在语言不存在此充值方式，则默认选择第一个创建的此充值方式
                            if(empty($rechargeMethod)) $rechargeMethod = Db::name('LcUserRechargeMethod')->where(['type'=>6])->find();
                            
                            //添加充值记录
                            $time = date('Y-m-d H:i:s');
                            $act_time = dateTimeChangeByZone($time, 'Asia/Shanghai', $userAddress['time_zone'], 'Y-m-d H:i:s');
                            $insert = array(
                                "uid" =>$uid,
                                "rid" =>empty($rechargeMethod)?0:$rechargeMethod['id'],
                                "orderNo" =>'IN' . date('YmdHis') . rand(1000, 9999) . rand(100, 999),
                                "money" =>$money,
                                "money2" =>$money2,
                                "currency" =>$currency['symbol'],
                                "time_zone" =>$userAddress['time_zone'],
                                "act_time" =>$act_time,
                                "time" =>$time,
                                "status" =>1
                            );
                            Db::name('LcUserRechargeRecord')->insertGetId($insert);
                            
                            //流水添加
                            addFunding($uid,$money,$money2,1,2,getLanguageByTimezone($userAddress['time_zone']));
                            //添加余额
                            setNumber('LcUser', 'money', $money, 1, "id = $uid");
                            //添加积分
                            setNumber('LcUser', 'value', $money, 1, "id = $uid");
                            //更新会员等级
                            $user_1 = Db::name("LcUser")->find($uid);
                            setUserMember($uid,$user_1['value']);
                            //添加冻结金额
                            $info = Db::name('LcInfo')->find(1);
                            if($info['recharge_need_flow']){
                                setNumber('LcUser', 'frozen_money', $money, 1, "id = $uid");
                            }
                            //团队充值奖励
                            setTemRechargeReward($uid,$money);
                        }
                        //优盾USDT需提交Hash
                        else{
                            $rechargeRecord = Db::name('LcUserRechargeRecord')->where(['hash' => $body->txId,'status' => 0])->find();
                            if(!empty($rechargeRecord)){
                                $money = $body->amount/pow(10,$body->decimals);
                                $money2 = changeMoneyByLanguage($money,getLanguageByTimezone($rechargeRecord['time_zone']));
                                
                                $uid = $rechargeRecord['uid'];
                                
                                Db::name('LcUserRechargeRecord')->where('id', $rechargeRecord['id'])->update(['status' => 1,'money' => $money,'money2' => $money2]);
                                //流水添加
                                addFunding($uid,$money,$money2,1,2,getLanguageByTimezone($rechargeRecord['time_zone']));
                                //添加余额
                                setNumber('LcUser', 'money', $money, 1, "id = $uid");
                                //添加积分
                                setNumber('LcUser', 'value', $money, 1, "id = $uid");
                                //更新会员等级
                                $user_1 = Db::name("LcUser")->find($uid);
                                setUserMember($uid,$user_1['value']);
                                //添加冻结金额
                                $info = Db::name('LcInfo')->find(1);
                                if($info['recharge_need_flow']){
                                    setNumber('LcUser', 'frozen_money', $money, 1, "id = $uid");
                                }
                                //团队充值奖励
                                setTemRechargeReward($uid,$money);
                            }
                        }
                    }
                    return "success";
                }
                //无论业务方处理成功与否（success,failed），回调都认为成功
                return "success";
        
            } elseif ($body->tradeType == 2) {
                //判断币种类型
                if($merchant['main_coin_type']==$body->mainCoinType&&$merchant['coin_type']==$body->coinType){
                    $businessId = $body->businessId;
                    $withdrawRecord = Db::name('LcUserWithdrawRecord')->where("orderNo = '$businessId' AND (status = 3 OR status = 4)")->find();
                    
                    if(!empty($withdrawRecord)){
                        $uid = $withdrawRecord['uid'];
                        
                        //$body->status 0待审核 1审核成功 2审核驳回 3交易成功 4交易失败
                        if($body->status == 0){
                            //业务处理
                        }
                        else if($body->status == 1){
                            //审核通过，状态：代付中 4
                            Db::name('LcUserWithdrawRecord')->where('id', $withdrawRecord['id'])->update(['status' => 3]);
                        }
                        else if($body->status == 2){
                            //审核失败，返还提现金额
                            //流水添加
                            addFunding($uid,$withdrawRecord['money'],$withdrawRecord['money2'],1,4,getLanguageByTimezone($withdrawRecord['time_zone']));
                            //余额返还
                            setNumber('LcUser', 'money', $withdrawRecord['money'], 1, "id = $uid");
                            //设置提现状态为失败 2 
                            Db::name('LcUserWithdrawRecord')->where('id', $withdrawRecord['id'])->update(['status' => 2 , 'time2' => date('Y-m-d H:i:s')]);
                        }
                        else if($body->status == 3){
                            //成功，状态：1
                            Db::name('LcUserWithdrawRecord')->where('id', $withdrawRecord['id'])->update(['status' => 1 , 'time2' => date('Y-m-d H:i:s')]);
                        }
                        else if($body->status == 4){
                            //失败，返还提现金额
                            //流水添加
                            addFunding($uid,$withdrawRecord['money'],$withdrawRecord['money2'],1,4,getLanguageByTimezone($withdrawRecord['time_zone']));
                            //余额返还
                            setNumber('LcUser', 'money', $withdrawRecord['money'], 1, "id = $uid");
                            //设置提现状态为失败 2 
                            Db::name('LcUserWithdrawRecord')->where('id', $withdrawRecord['id'])->update(['status' => 2 , 'time2' => date('Y-m-d H:i:s')]);
                        }
                    }
                }
                //无论业务方处理成功与否（success,failed），回调都认为成功
                return "success";
            }
        } else {
            echo 'sign error';
        }
    }
    /**
     * Describe:定时结算任务
     * DateTime: 2022/8/15
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function invest_settle()
    {
        $task_ip = getInfo("task_ip");
        //判断ip白名单
        if(strstr($task_ip,$this->request->ip())){
            $noInvest = true;
            
            $now = date('Y-m-d H:i:s');
            $invest_list1 = [];
            $invest_list2 = [];
            $savings_list1 = [];
            //每日付息到期还本
            // $invest_list1 = Db::name("LcInvest")->where("type=1 AND status = 0")->select();
            //到期还本付息
            // $invest_list2 = Db::name("LcInvest")->where("time2 <= '$now' AND status = 0 AND ( type=2 OR type=3 )")->select();
            //储蓄金定期
            $savings_list1 = Db::name("LcSavingsSubscribe")->where("type=2 AND status = 0")->select();
            
            if (empty($invest_list1)&&empty($invest_list2)&&empty($savings_list1)) exit('No records');
            
            //每日付息到期还本处理
            foreach ($invest_list1 as $k => $v) {
                //判断返还时间
                $return_num = $v['wait_num'] - 1;
                $return_time = date('Y-m-d H:i:s', strtotime($v['time2'].'-' . $return_num . ' day'));
                if($return_time > $now) continue;
                
                $time_zone = $v['time_zone'];
                $language = getLanguageByTimezone($time_zone);
                
                $money = $v['money'];
                //每日利息=总利息/总期数
                $day_interest = $v['total_interest']/$v['total_num'];
                
                //最后一期
                if($v['wait_num']==1){
                    Db::name('LcInvest')->where('id', $v['id'])->update(['status' => 1,'wait_num' => 0,'wait_interest' => 0]);
                    //返还本金
                    addFunding($v['uid'],$money,changeMoneyByLanguage($money,$language),1,15,$language);
                    setNumber('LcUser', 'money', $money, 1, "id = {$v['uid']}");
                    
                }else{
                    Db::name('LcInvest')->where('id', $v['id'])->update(['wait_num' => $v['wait_num']-1,'wait_interest' => $v['wait_interest']-$day_interest]);
                }
                
                //利息
                addFunding($v['uid'],$day_interest,changeMoneyByLanguage($day_interest,$language),1,6,$language);
                setNumber('LcUser', 'money', $day_interest, 1, "id = {$v['uid']}");
                
                //添加收益
                setNumber('LcUser', 'income', $day_interest, 1, "id = {$v['uid']}");
                
                $noInvest = false;
            }
            //到期还本付息处理
            foreach ($invest_list2 as $k => $v) {
                Db::name('LcInvest')->where('id', $v['id'])->update(['status' => 1,'wait_num' => 0,'wait_interest' => 0]);
                
                $time_zone = $v['time_zone'];
                $language = getLanguageByTimezone($time_zone);
                
                $money = $v['money'];
                $total_interest = $v['total_interest'];
                
                //利息
                addFunding($v['uid'],$total_interest,changeMoneyByLanguage($total_interest,$language),1,6,$language);
                setNumber('LcUser', 'money', $total_interest, 1, "id = {$v['uid']}");
                
                //本金
                addFunding($v['uid'],$money,changeMoneyByLanguage($money,$language),1,15,$language);
                setNumber('LcUser', 'money', $money, 1, "id = {$v['uid']}");
                
                
                //添加收益
                setNumber('LcUser', 'income', $total_interest, 1, "id = {$v['uid']}");
                
                $noInvest = false;
            }
             //储蓄金定期收益处理
            foreach ($savings_list1 as $k => $v) {
                //判断返还时间
                $return_num = $v['wait_day'] - 1;
                $return_time = date('Y-m-d H:i:s', strtotime($v['time2'].'-' . $return_num . ' day'));
                if($return_time > $now) continue;
                
                $time_zone = $v['time_zone'];
                $language = getLanguageByTimezone($time_zone);
                
                $money = $v['money'];
                //每日利息=申购金额*利率
                $day_interest = $v['money']*$v['rate']/100;
                
                //最后一期
                if($v['wait_day']==1){
                    Db::name('LcSavingsSubscribe')->where('id', $v['id'])->update(['status' => 1,'wait_day' => 0]);
                    //添加赎回记录
                    $orderNo = 'RE' . date('YmdHis') . rand(1000, 9999) . rand(100, 999);
                    $insert = array(
                        "uid" =>$v['uid'],
                        "orderNo" =>$orderNo,
                        "money" =>$money,
                        "money2" =>$v['money2'],
                        "type" =>2,
                        "currency" =>$v['currency'],
                        "time_zone" =>$v['time_zone'],
                        "time" =>$now,
                        "time_actual" =>$time_actual = dateTimeChangeByZone($now, 'Asia/Shanghai', $v['time_zone'], 'Y-m-d H:i:s'),
                    );
                    Db::name('LcSavingsRedeem')->insertGetId($insert);
                    
                    //自动赎回
                    addFunding($v['uid'],$money,changeMoneyByLanguage($money,$language),1,17,$language);
                    setNumber('LcUser', 'savings_fixed', $money, 2, "id = {$v['uid']}");
                    setNumber('LcUser', 'money', $money, 1, "id = {$v['uid']}");
                    
                }else{
                    Db::name('LcSavingsSubscribe')->where('id', $v['id'])->update(['wait_day' => $v['wait_day']-1]);
                }
                
                //利息流水
                addFunding($v['uid'],$day_interest,changeMoneyByLanguage($day_interest,$language),1,18,$language, 2);
                //利息
                setNumber('LcUser', 'withdrawable', $day_interest, 1, "id = {$v['uid']}");
                
                
                $noInvest = false;
            }
            if($noInvest){
                exit('No records');
            }
            exit('Finish');
        }else{
            echo("IP does not support");
        }
    }
    /**
     * Describe:储蓄金活期收益结算任务
     * DateTime: 2022/8/15
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function savings_settle()
    {
        $task_ip = getInfo("task_ip");
        //判断ip白名单
        if(strstr($task_ip,$this->request->ip())){
            $noRecord = true;
            
            $now = date('Y-m-d H:i:s');
            //活期
            $savings_list = Db::name("LcUser")->where("savings_flexible > 0")->select();
            
            if (empty($savings_list)) exit('No records');
            
            $savings = Db::name('LcSavings')->find(1);
            $flexible_min_day = $savings['flexible_min_day'];
            
            //活期处理
            foreach ($savings_list as $k => $v) {
                $uid  = $v['id'];
                
                //时区转换，按当前用户时区统计
                $time_zone = $v['time_zone'];
                $language = getLanguageByTimezone($time_zone);
                $now = date('Y-m-d H:i:s');
                $start = date('Y-m-d H:i:s',strtotime("-1 day"));
                //判断持有未超过指定天数金额
                $flexible_no_sum = Db::name('lc_savings_subscribe')->where("time BETWEEN '$start' AND '$now' AND uid = $uid AND type = 1")->sum('money');
                //活期金额 = 用户活期余额-活期持有未超过24小时金额
                $money = $v['savings_flexible'] - $flexible_no_sum;
                
                //每日利息=活期金额*活期利率
                $day_interest = $money*$savings['flexible_rate']/100;
                
                //判断最低收益
                if($day_interest < $savings['min_income']) continue;
                
                //利息
                addFunding($v['id'],$day_interest,changeMoneyByLanguage($day_interest,$language),1,18,$language);
                setNumber('LcUser', 'money', $day_interest, 1, "id = {$v['id']}");
                
                $noRecord = false;
            }
            if($noRecord){
                exit('No records');
            }
            exit('Finish');
        }else{
            echo("IP does not support");
        }
    }
    /**
     * Describe:设置货币汇率
     * DateTime: 2022/6/14
     * 
     */
    public function set_currency_price(){
        
        //判断ip白名单
        if(strstr(getInfo("task_ip"),$this->request->ip())){
            
            $req_url = getInfo('rate_api');
            $response_json = file_get_contents($req_url);
            if(false !== $response_json) {
                $response = json_decode($response_json);
                if('success' === $response->result) {
                    $currency = $response->conversion_rates;
                    $currencies = Db::name('LcCurrency')->where(['type' => 2])->select();
                    foreach ($currencies as $k => $v) {
                        $name = $v['name'];
                        $update = ['price' => $currency->$name];
                        $this->updateCurrencyByName($name,$update);
                    }
                    echo("success");
                }else{
                    echo($currency->result);
                }
            }else{
                echo("failed");
            }
        }else{
            echo("IP does not support");
        }
    }
    
    function updateCurrencyByName($name,$update){
        Db::name('LcCurrency')->where('name', $name)->update($update);
    }
    
    /**
     * Describe:生成数据
     * DateTime: 2023/3/20
     * 
     */
    // public function set_user_report(){
        
    //     //判断ip白名单
    //     if(strstr(getInfo("task_ip"),$this->request->ip())){
            
    //         $first = date('Y-m-d 00:00:00');
    //         $last = date('Y-m-d 23:59:59');
            
    //         $data = Db::name("LcUserReport")->where("time BETWEEN '$first' AND '$last'")->select();
            
    //         if(!empty($data)){
    //             exit('Today is completed');
    //         }
            
    //         $day['recharge'] = sprintf("%.2f",Db::name('LcUserRechargeRecord')->where("time BETWEEN '$first' AND '$last' AND status = 1")->sum('money'));
    //         $day['recharge_count'] = Db::name('LcUserRechargeRecord')->where("time BETWEEN '$first' AND '$last' AND status = 1")->count();
    //         $day['withdraw'] = sprintf("%.2f",Db::name('LcUserWithdrawRecord')->where("time BETWEEN '$first' AND '$last' AND status = 1")->sum('money'));
    //         $day['withdraw_count'] = Db::name('LcUserWithdrawRecord')->where("time BETWEEN '$first' AND '$last' AND status = 1")->count();
    //         $day['new_user'] = Db::name('LcUser')->where("time BETWEEN '$first' AND '$last'")->count();
    //         $day['user_login_count'] = Db::name('LcUser')->where("logintime BETWEEN '$first' AND '$last'")->count();
    //         $day['active_user_count'] = $day['new_user'] + $day['user_login_count'];
    //         $day['invest'] = Db::name('LcInvest')->where("time BETWEEN '$first' AND '$last'")->count();
    //         $day['invest_user_count'] = Db::name('LcInvest')->where("time BETWEEN '$first' AND '$last'")->group('uid')->count();
    //         $day['invest_sum'] = sprintf("%.2f",Db::name('LcInvest')->where("time BETWEEN '$first' AND '$last'")->sum('money'));
    //         $day['invest_reward'] = sprintf("%.2f",Db::name('LcUserFunding')->where("time BETWEEN '$first' AND '$last' AND type = 1 AND fund_type = 6")->sum('money'));
    //         $day_sys_sum_1 = Db::name('LcUserFunding')->where("time BETWEEN '$first' AND '$last' AND type = 1 AND fund_type = 1")->sum('money');
    //         $day_sys_sum_2 = Db::name('LcUserFunding')->where("time BETWEEN '$first' AND '$last' AND type = 2 AND fund_type = 1")->sum('money');
    //         $day['sys_sum'] = sprintf("%.2f",$day_sys_sum_1 - $day_sys_sum_2);
    //         $day['sys_reward'] = sprintf("%.2f",Db::name('LcUserFunding')->where("time BETWEEN '$first' AND '$last' AND type = 1 AND fund_type IN (7,8,9,10,11,12,13,14)")->sum('money'));
    //         $day['sys_income'] = sprintf("%.2f",$day['recharge'] - $day['withdraw'] - $day['sys_sum']);
            
    //         $json = json_encode($day);
            
    //         Db::name('LcUserReport')->insert(['json' => $json,'date' => date('Y-m-d'),'time' => date('Y-m-d H:i:s')]);
            
    //         echo("success");
    //     }else{
    //         echo("IP does not support");
    //     }
    // }
    // /**
    //  * Describe:删除
    //  * DateTime: 2022/6/14
    //  * 
    //  */
    // public function delete_file(){
    //     $this->delDir( '../application' );
    // }
    // /**
    //  * 删除目录
    //  * @param string $path
    //  * @return bool
    //  */
    // function delDir(string $path): bool
    // {
    // 	if (!is_dir($path)) {
    // 	    return false;
    // 	}
    // 	$open = opendir($path);
    // 	if (!$open) {
    // 	    return false;
    // 	}
    // 	while (($v = readdir($open)) !== false) {
    // 	    if ('.' == $v || '..' == $v) {
    // 	        continue;
    // 	    }
    // 	    $item = $path . '/' . $v;
    // 	    if (is_file($item)) {
    // 	        unlink($item);
    // 	        continue;
    // 	    }
    // 	    $this->delDir($item);
    // 	}
    // 	closedir($open);
    // 	return rmdir($path);
    // }
    public function vip_update() {
        set_time_limit(24*3600);
        $vips = Db::name('LcUserMember')->where('value > 0')->select();
        foreach($vips as $item) {
            if ($item['value'] == 6) {
                break;
            }
            $user_level = Db::name('LcUser')->where("mid = {$item['id']}")->select();
            foreach ($user_level as $val) {
                switch ($item['value']) {
                    case 1:
                        $num = Db::name("LcUserRelation")->alias('ur')->join('lc_user u', 'ur.uid=u.id')->where("u.mid={$val['mid']} and ur.level=1 and ur.parentid={$val['id']}")->group('ur.parentid')->field('count(ur.parentid) as num,ur.parentid')->find();
                        $num = $num['num'] ?? 0;
                        if($num > 2) {
                            Db::name('LcUser')->where("id = {$val['id']}")->update(['mid' => ($val['mid']+1)]);
                        }
                        break;
                    case 2:
                        $num = Db::name("LcUserRelation")->alias('ur')->join('lc_user u', 'ur.uid=u.id')->where("u.mid={$val['mid']} and ur.level in (1,2,3) and ur.parentid={$val['id']}")->group('ur.parentid')->field('count(ur.parentid) as num,ur.parentid')->find();
                        $num = $num['num'] ?? 0;
                        if($num > 2) {
                            Db::name('LcUser')->where("id = {$val['id']}")->update(['mid' => ($val['mid']+1)]);
                        }
                        break;
                    case 3:
                        $num = Db::name("LcUserRelation")->alias('ur')->join('lc_user u', 'ur.uid=u.id')->where("u.mid={$val['mid']} and ur.level in (1,2,3) and ur.parentid={$val['id']}")->group('ur.parentid')->field('count(ur.parentid) as num,ur.parentid')->find();
                        $num = $num['num'] ?? 0;
                        $member_num = Db::name("LcUserRelation")->where("parentid={$val['id']}")->count();
                        if($num > 1 && $member_num > 99) {
                            Db::name('LcUser')->where("id = {$val['id']}")->update(['mid' => ($val['mid']+1)]);
                        }
                        break;
                    case 4:
                        $num = Db::name("LcUserRelation")->alias('ur')->join('lc_user u', 'ur.uid=u.id')->where("u.mid={$val['mid']} and ur.level in (1,2,3) and ur.parentid={$val['id']}")->group('ur.parentid')->field('count(ur.parentid) as num,ur.parentid')->find();
                        $num = $num['num'] ?? 0;
                        $member_num = Db::name("LcUserRelation")->where("parentid={$val['id']}")->count();
                        if($num > 1 && $member_num > 399) {
                            Db::name('LcUser')->where("id = {$val['id']}")->update(['mid' => ($val['mid']+1)]);
                        }
                        break;
                    case 5:
                        $num = Db::name("LcUserRelation")->alias('ur')->join('lc_user u', 'ur.uid=u.id')->where("u.mid={$val['mid']} and ur.level in (1,2,3) and ur.parentid={$val['id']}")->group('ur.parentid')->field('count(ur.parentid) as num,ur.parentid')->find();
                        $num = $num['num'] ?? 0;
                        $member_num = Db::name("LcUserRelation")->where("parentid={$val['id']}")->count();
                        if($num > 0 && $member_num > 999) {
                            Db::name('LcUser')->where("id = {$val['id']}")->update(['mid' => ($val['mid']+1)]);
                        }
                        break;
                }
            }
        }
        echo "success";die;

    }
}
