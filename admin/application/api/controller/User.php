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

namespace app\api\controller;

use library\Controller;
use Endroid\QrCode\QrCode;
use think\Db;
use think\facade\Session;
use library\File;
use think\facade\Cache;

/**
 * 用户中心
 * Class Index
 * @package app\index\controller
 */
class User extends Controller
{
    /**
     * Describe:用户信息
     * DateTime: 2022/3/15 3:19
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function info()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        
        //更新登录环境
        //更新登录环境
        Db::name('LcUser')->update(['device' => $params["isapp"],'id'=>$uid]);
        
        //判断每日登录奖励
        $reward = Db::name('LcReward')->find(1);
        if($reward['login']>0){
            $now = date('Y-m-d H:i:s');//现在
            $today = date('Y-m-d');//今天0点
            $login_today = getFundingByTime($today,$now,$uid,10);
            //判断今日是否奖励
            if(empty($login_today)){
                //流水添加
                addFunding($uid,$reward['login'],changeMoneyByLanguage($reward['login'],$language),1,10,$language);
                //添加余额
                setNumber('LcUser', 'money', $reward['login'], 1, "id = $uid");
                //添加冻结金额
                if(getInfo('reward_need_flow')){
                    setNumber('LcUser', 'frozen_money', $reward['login'], 1, "id = $uid");
                }
            }
        }
        
        $user = Db::name("LcUser")->find($uid);
        $uname = substr($user['username'],0,2).'***'.substr($user['username'],strlen($user['username'])-2,strlen($user['username']));
        $currency = Db::name('LcCurrency')->where(['country' => $language])->find();
        $member = Db::name("LcUserMember")->find($user['mid']);
        
        //判断今日是否签到
        $signin = true;
        $now = date('Y-m-d H:i:s');//现在
        $today = date('Y-m-d');//今天0点
        $signin_today = Db::name('LcUserSignin')->where("time >= '$today' AND time <= '$now' AND uid = '{$uid}'")->select();
        //判断今日是否奖励
        if(empty($signin_today)) $signin = false;
        
        $data = array(
            "username" => $uname,
            "fundBalance" => changeMoneyByLanguage($user['money'],$language),
            "fundBalanceUsd" => $user['money'],
            "currency" => $currency['symbol'],
            "uid" => $uid,
            "auth_email" => $user['auth_email'],
            "auth_phone" => $user['auth_phone'],
            "auth_google" => $user['auth_google'],
            "withdrawable" => $user['withdrawable'],
            "integral" => $user['integral'],
            "point" => $user['point'],
            "invite_code" => $user['invite_code'],
            "user_icon" => getInfo('user_img'),
            "vip_name" => $member['name'],
            "signin" => $signin,
        );
        
        
        $this->success("success", $data);
    }
    
    /**
     * Describe:签到
     * DateTime: 2023/3/27
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function signin()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        
        //判断今日是否已签到
        $reward = Db::name('LcReward')->find(1);
        if($reward['signin']>0){
            $now = date('Y-m-d H:i:s');//现在
            $today = date('Y-m-d');//今天0点
            $signin_today = Db::name('LcUserSignin')->where("time >= '$today' AND time <= '$now' AND uid = '{$uid}'")->select();
            //判断今日是否奖励
            if(!empty($signin_today)) $this->error('utils.parameterError',"",218);
            
            //时区转换
            $time = date('Y-m-d H:i:s');
            $time_zone = getTimezoneByLanguage($language);
            $act_time = dateTimeChangeByZone($time, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
            //插入签到记录
            $add = array(
                'uid' => $uid,
                'point' => $reward['signin'],
                'time' => $time,
                "time_zone" =>$time_zone,
                "act_time" =>$act_time,
            );
            Db::name('LcUserSignin')->insert($add);
            //添加签到积分
            setNumber('LcUser', 'point', $reward['signin'], 1, "id = $uid");
        }
        $this->success("success");
    }
    /**
     * @description：获取邮箱验证码
     * @date: 2020/6/2 0002
     */
    public function getEmailCode()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        //判断参数
        if(empty($params['email'])) $this->error('utils.parameterError',"",218);
        //判断邮箱是否正确
        if (!judge($params['email'],"email")) $this->error('auth.emailError',"",218);
        
        $userInfo = $this->userInfo;
        $user = Db::name('LcUser')->find($userInfo['id']);
        $email = $params['email'];
        //判断是否验证过
        if ($user['auth_email'] == 1) $this->error('auth.authed',"",218);
        
        $sms_time = Db::name("LcEmailCode")->where("email = '$email'")->order("id desc")->value('time');
        if ($sms_time && (strtotime($sms_time) + 300) > time()) $this->error('auth.codeValid',"",218);
        
        $rand_code = rand(1000, 9999);
        
        $msg = getTipsByLanguage('eamil_tips1',$language).$rand_code.getTipsByLanguage('eamil_tips2',$language);
        
        $data = array('email' => $email, 'msg' => $msg, 'code' => $rand_code, 'time' => date('Y-m-d H:i:s'), 'ip' => $this->request->ip());
        
        if(!sendMail($email,getTipsByLanguage('auth_eamil',$language),$msg)) $this->error('auth.sendFail',"",218);
        
        Db::name('LcEmailCode')->insert($data);
        
        $this->success("success");
    }
    
    /**
     * @description：邮箱认证
     * @date: 2020/5/15 0015
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function authEmail()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        //判断参数
        if(empty($params['email'])||empty($params['code'])) $this->error('utils.parameterError',"",218);
        //判断邮箱是否正确
        if (!judge($params['email'],"email")) $this->error('auth.emailError',"",218);
       
        $userInfo = $this->userInfo;
        $user = Db::name('LcUser')->find($userInfo['id']);
        $uid = $userInfo['id'];
        $email = $params['email'];
        
        if ($user['auth_email'] == 1) $this->error('auth.emailAuthed',"",218);
        
         //判断邮箱是否被使用
        if (Db::name('LcUser')->where(['email' => $params['email']])->find()) $this->error('auth.emailUsed',"",218);
        
        $sms_code = Db::name("LcEmailCode")->where("email = '$email'")->order("id desc")->find();
        //判断验证码是否获取
        if(!$sms_code) $this->error('auth.codeFirst',"",218);
        //判断验证码是否过期
        if ((strtotime($sms_code['time']) + 300) < time()) $this->error('auth.codeExpired',"",218);
        //判断验证码
        if ($params['code'] != $sms_code['code']) $this->error('auth.codeError',"",218);
        //认证奖励
        $reward = Db::name('LcReward')->find(1);
        if($reward['authentication']>0){
            //流水添加
            addFunding($uid,$reward['authentication'],changeMoneyByLanguage($reward['authentication'],$language),1,9,$language);
            //添加余额
            setNumber('LcUser', 'money', $reward['authentication'], 1, "id = $uid");
            //添加冻结金额
            if(getInfo('reward_need_flow')){
                setNumber('LcUser', 'frozen_money', $reward['authentication'], 1, "id = $uid");
            }
        }
        $data = ['auth_email' => 1,'email' => $email];
        $res = Db::name('LcUser')->where('id', $userInfo['id'])->update($data);
        if($res){
            $this->success("success");
        }
        
        $this->error('utils.authFail',"",218);
    }
    /**
     * @description：获取手机验证码
     * @date: 2022/7/30
     */
    public function getSmsCode()
    {
        $params = $this->request->param();
        $language = $params["language"];
        // $this->checkToken($language);
        $phone = $params["phone"];
        $code = $params["verify_code"];
        $country_code = $params["country_code"];
        $phone_code = $country_code.$phone;//拼接国家区号后的手机号
        $ip = $this->request->ip();
        
        //判断参数
        if(empty($phone)||empty($code)||empty($country_code)) $this->error('utils.parameterError',"",218);
        //校验验证码
        $this->type = input('login_captcha_type'.$ip, 'login_captcha_type'.$ip);
        if(strtolower(Cache::get($this->type)) != strtolower($code)) $this->error('auth.charError',"",218);
        
        //判断手机号是否正确
        if (!judge($phone,"mobile_phone")) $this->error('auth.phoneError',"",218);
        
        if (strlen($phone) < 6 || 16 < strlen($phone)) $this->error('auth.phoneError',"",218);
        //判断是否有发送过验证码，且是否还在5分钟有效期
        $sms_time = Db::name("LcSmsCode")->where("phone = '$phone_code'")->order("id desc")->value('time');
        if ($sms_time && (strtotime($sms_time) + 300) > time()) $this->error('auth.codeValid',"",218);
        //判断ip是否频繁发送，每5分钟只可发送一次
        $sms_time2 = Db::name("LcSmsCode")->where("ip = '$ip'")->order("id desc")->value('time');
        
        if ($sms_time2 && (strtotime($sms_time2) + 300) > time()) $this->error('auth.frequently',"",218);
        
        $rand_code = rand(1000, 9999);
        $msg = getTipsByLanguage('eamil_tips1',$language).$rand_code.getTipsByLanguage('eamil_tips2',$language);
        
        $lcSms = Db::name('LcSms')->find(1);
        
        $smsapi = $lcSms['host_wo'];
        $user = $lcSms['username']; //短信平台帐号
        $pass = md5($lcSms['password']); //短信平台密码
        $content=$msg;//要发送的短信内容
        $sendPhone = urlencode($phone_code);//要发送短信的手机号码
        
        if($country_code=="+86"){
            $smsapi = $lcSms['host_cn'];
            $sendPhone = $phone;
        }
        
        $sendurl = $smsapi."?u=".$user."&p=".$pass."&m=".$sendPhone."&c=".urlencode($content);
        $result  = file_get_contents($sendurl);
        if($result !=0){
            if($result ==51){
                $this->error('auth.phoneError',"",218);
            }else{
                $this->error('auth.frequently',"",218);
            }
        }
        
        $data = array('phone' => $phone_code, 'msg' => $msg, 'code' => $rand_code, 'time' => date('Y-m-d H:i:s'), 'ip' => $ip);
        
        Db::name('LcSmsCode')->insert($data);
        
        $this->success("success");
    }
    
    /**
     * @description：手机认证
     * @date: 2020/5/15 0015
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function authPhone()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        
        //判断参数
        if(empty($params['phone'])||empty($params['code'])||empty($params['country_code'])) $this->error('utils.parameterError',"",218);
        //判断手机号是否正确
        if (!judge($params['phone'],"mobile_phone")) $this->error('auth.phoneError',"",218);
       
        $userInfo = $this->userInfo;
        $user = Db::name('LcUser')->find($userInfo['id']);
        $uid = $user['id'];
        $phone = $params['phone'];
        $country_code = $params["country_code"];
        $phone_code = $country_code.$phone;//拼接国家区号后的手机号
        
        if ($user['auth_phone'] == 1) $this->error('auth.phoneAuthed',"",218);
        
         //判断手机号是否被使用
        if (Db::name('LcUser')->where(['phone' => $params['phone']])->find()) $this->error('auth.phoneUsed',"",218);
        
        $sms_code = Db::name("LcSmsCode")->where("phone = '$phone_code'")->order("id desc")->find();
        //判断验证码是否获取
        if(!$sms_code) $this->error('auth.codeFirst',"",218);
        //判断验证码是否过期
        if ((strtotime($sms_code['time']) + 300) < time()) $this->error('auth.codeExpired',"",218);
        //判断验证码
        if ($params['code'] != $sms_code['code']) $this->error('auth.codeError',"",218);
        //认证奖励
        $reward = Db::name('LcReward')->find(1);
        if($reward['authentication']>0){
            //流水添加
            addFunding($uid,$reward['authentication'],changeMoneyByLanguage($reward['authentication'],$language),1,9,$language);
            //添加余额
            setNumber('LcUser', 'money', $reward['authentication'], 1, "id = $uid");
            //添加冻结金额
            if(getInfo('reward_need_flow')){
                setNumber('LcUser', 'frozen_money', $reward['authentication'], 1, "id = $uid");
            }
        }
        $data = ['auth_phone' => 1,'phone' => $phone_code];
        $res = Db::name('LcUser')->where('id', $userInfo['id'])->update($data);
        if($res){
            $this->success("success");
        }
        
        $this->error('utils.authFail',"",218);
    }
    
    
    /**
     * Describe:认证状态
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAuthStatus()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        $info = Db::name('LcInfo')->find(1);
        if(empty($user['google_key'])){
            $google_key = $this->createSecret();
            $google_qrcode = ($this->getQRCodeGoogleUrl($user['username'],$google_key));
            Db::name('LcUser')->where(['id' => $uid])->update(['google_key' => $google_key,'google_qrcode' => $google_qrcode]);
        }
        $data = array(
            "auth_phone" =>$user['auth_phone'],
            "phone" =>$user['phone'],
            "auth_email" =>$user['auth_email'],
            "email" =>$user['email'],
            "auth_authenticator" =>$user['auth_google'],
            "authenticator_key" =>$user['google_key'],
            "authenticator_qrcode" =>$user['google_qrcode'],
            "auth_phone_status" =>$info['auth_phone'],
            "auth_email_status" =>$info['auth_email'],
            "auth_authenticator_status" =>$info['auth_google'],
        );
        $this->success("success", $data);
    }
    /**
     * Describe:Authenticator认证
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function authAuthenticator()
    {
        $params = $this->request->param();
        $language = $params["language"];
        if(empty($params['code'])) $this->error('utils.parameterError',"",218);
        
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        if(!$this->verifyCode($user['google_key'],$params["code"])){
            $this->error('auth.codeError',"",218);
        }
        
        Db::name('LcUser')->where(['id' => $uid])->update(['auth_google' => 1]);
        //认证奖励
        $reward = Db::name('LcReward')->find(1);
        if($reward['authentication']>0){
            //流水添加
            addFunding($uid,$reward['authentication'],changeMoneyByLanguage($reward['authentication'],$language),1,9,$language);
            //添加余额
            setNumber('LcUser', 'money', $reward['authentication'], 1, "id = $uid");
            //添加冻结金额
            if(getInfo('reward_need_flow')){
                setNumber('LcUser', 'frozen_money', $reward['authentication'], 1, "id = $uid");
            }
        }
        $this->success("success");
    }
    /**
     * Describe:获取提现方式
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getWithdrawalMethod()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        
        //判断认证状态
        if(getUserNeedAuth($uid)) $this->error('auth.authFirst',"",405);
        
        //获取指定国别的提现方式
        $currency = Db::name('LcCurrency')->where(['country' => $language])->find();
        if(empty($currency)) $this->error('utils.parameterError',"",218);
        
        $withdrawMethod = Db::name('LcUserWithdrawMethod')->field("id,type,name,logo")->where(['show' => 1,'delete' => 0,'cid' => $currency['id']])->order('sort asc,id desc')->select();
        
        $wallets = Db::name('LcUserWallet')->field("id,wid,type,wname,account,bid")->where(['uid' => $uid,'cid' => $currency['id']])->select();
        foreach ($wallets as &$wallet) {
            if($wallet['type']==1){
                $wallet['account'] = substr($wallet['account'],0,4).'******'.substr($wallet['account'],strlen($wallet['account'])-4,strlen($wallet['account']));
            }else{
                $wallet['account'] = substr($wallet['account'],0,2).'******'.substr($wallet['account'],strlen($wallet['account'])-2,strlen($wallet['account']));
                
                if($wallet['type']==4){
                    $wbank = Db::name("LcUserWithdrawBank")->field('logo')->find($wallet['bid']);
                    $wallet['bank'] = $wbank;
                }
            }
        }
        
        $data = array(
            "withdrawMethod" =>$withdrawMethod,
            "wallets" =>$wallets,
        );
        $this->success("success", $data);
    }
    /**
     * Describe:获取提现方式详情
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getWithdrawMethodById()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $withdraw = Db::name('LcUserWithdrawMethod')->field("id,name,type")->where(['show' => 1,'delete' => 0])->find($params['id']);
        if(empty($withdraw)) $this->error('utils.parameterError',"",218);
        //银行卡则获取可用银行列表
        if($withdraw['type']==4){
            //获取指定国别的银行卡
            $currency = Db::name('LcCurrency')->where(['country' => $language])->find();
            if(empty($currency)) $this->error('utils.parameterError',"",218);
            
            $banks = Db::name('LcUserWithdrawBank')->field("id,logo,name,code")->where(['cid' => $currency['id']])->order('sort asc,id desc')->select();
            
            $withdraw['banks'] = $banks;
        }
        
        $data = array(
            "withdraw" =>$withdraw,
        );
        $this->success("success", $data);
    }
    /**
     * Describe:添加提现方式
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function setWallet()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        
        $withdraw = Db::name('LcUserWithdrawMethod')->find($params['id']);
        if(empty($withdraw)) $this->error('utils.parameterError',"",218);
        //判断是否绑定过
        $wallet = Db::name('LcUserWallet')->where(['uid' => $uid,'wid' => $withdraw['id']])->select();
        if(!empty($wallet)) $this->error('utils.parameterError',"",218);
        $currency = Db::name('LcCurrency')->where(['country' => $language])->find();
        
        $add = array(
            "uid"=>$uid,
            "wid"=>$withdraw['id'],
            'cid' => $currency['id'],
            "wname"=>$withdraw['name'],
            "type"=>$withdraw['type'],
            'time' => date('Y-m-d H:i:s'),
        );
        
        switch($withdraw['type']){
            //USDT
            case 1:
                //判断参数
                if(empty($params['account'])||empty($params['img'])) $this->error('utils.parameterError',"",218);
                if (strlen($params['account']) < 2 || 34 < strlen($params['account'])) $this->error('utils.parameterError',"",218);
                //判断地址有效性
                if(getInfo("check_address")){
                    //判断地址有效性
                    $json= json_decode(checkAddress($params['account']), true);
                    if($json['code']!=200){
                        $this->error('wallet.addressError',"",218);
                    }
                }
                $add['account'] = $params['account'];
                $add['img'] = $params['img'];
                break;
            //银行卡
            case 4:
                //判断参数
                if(empty($params['name'])||empty($params['account'])) $this->error('utils.parameterError',"",218);
                
                if (!judge($params['account'],"digit")) $this->error('utils.parameterError',"",218);
                if (strlen($params['name']) < 2 || 16 < strlen($params['name'])) $this->error('utils.parameterError',"",218);
                if (strlen($params['account']) < 2 || 20 < strlen($params['account'])) $this->error('utils.parameterError',"",218);
                
                $add['name'] = $params['name'];
                $add['account'] = $params['account'];
                $add['wname'] = $params['bank'];
                $add['bid'] = $params['bank_id'];
                break;
            //扫码
            default:
                //判断参数
                if(empty($params['account'])||empty($params['img'])) $this->error('utils.parameterError',"",218);
                if (strlen($params['account']) < 2 || 32 < strlen($params['account'])) $this->error('utils.parameterError',"",218);
                
                $add['account'] = $params['account'];
                $add['img'] = $params['img'];
        }
        
        
        Db::name('LcUserWallet')->insert($add);
        $this->success("success");
    }
    /**
     * Describe:获取提现账户
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getWallets()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        
        //判断认证状态
        if(getUserNeedAuth($uid)) $this->error('auth.authFirst',"",405);
        
        //获取指定国别的提现账户
        $currency = Db::name('LcCurrency')->where(['country' => $language])->find();
        $wallets = Db::name('LcUserWallet')->field('id,wname,type,account')->where(['uid' => $uid,'cid' => $currency['id']])->select();
        foreach ($wallets as &$wallet) {
            if($wallet['type']==1){
                $wallet['account'] = substr($wallet['account'],0,4).'****'.substr($wallet['account'],strlen($wallet['account'])-4,strlen($wallet['account']));
            }else{
                $wallet['account'] = substr($wallet['account'],0,2).'****'.substr($wallet['account'],strlen($wallet['account'])-2,strlen($wallet['account']));
            }
        }
        // $availableAmount = $user['money'] - $user['frozen_money'];
        $availableAmount = $user['withdrawable'] - $user['frozen_money'];
        $data = array(
            "wallets" =>$wallets,
            "withdrawNum" =>$currency['withdraw_num'],
            "withdrawMin" =>$currency['withdraw_min'],
            "userBalance" =>$availableAmount,
            "frozenAmount" =>$user['frozen_money'],
        );
        $this->success("success", $data);
    }
    
    /**
     * Describe:图片上传
     * DateTime: 2022/3/15 20:01
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function imgUpload()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        if (!($file = $this->getUploadFile()) || empty($file)) $this->error('upload.uploadError',"",218);
        if (!$file->checkExt(strtolower(sysconf('storage_local_exts')))) $this->error('upload.uploadFailed',"",218);
        if ($file->checkExt('php,sh')) $this->error('upload.uploadFailed',"",218);
        $this->safe = boolval(input('safe'));
        $this->uptype = $this->getUploadType();
        $this->extend = pathinfo($file->getInfo('name'), PATHINFO_EXTENSION);
        $name = File::name($file->getPathname(), $this->extend, '', 'md5_file');
        $info = File::instance($this->uptype)->save($name, file_get_contents($file->getRealPath()), $this->safe);
        if (is_array($info) && isset($info['url'])) {
            $img = $this->safe ? $name : getInfo('domain_api').$info['url'];
        } else {
            $this->error('upload.uploadFailed',"",218);
        }
        $this->success("success", $img);
    }
    /**
     * 获取文件上传方式
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private function getUploadType()
    {
        $this->uptype = input('uptype');
        if (!in_array($this->uptype, ['local', 'oss', 'qiniu'])) {
            $this->uptype = sysconf('storage_type');
        }
        return $this->uptype;
    }

    /**
     * Describe:获取本地上传文件
     * DateTime: 2022/3/15 19:46
     * @return array|\think\File|null
     */
    private function getUploadFile()
    {
        try {
            return $this->request->file('file');
        } catch (\Exception $e) {
            $this->error(lang($e->getMessage()));
        }
    }
    
    /**
     * Describe:提现申请
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function withdraw()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        
		//用户被锁定
        if ($user['clock'] == 1) $this->error('login.userLocked',"",218);

        //是否允许提现
        if ($user['is_withdrawal'] == 1) $this->error('login.userLocked',"",218);
		
        //判断认证状态
        if(getUserNeedAuth($uid)) $this->error('auth.authFirst',"",405);
        
        //判断参数
        if(empty($params['id'])||empty($params['money'])) $this->error('utils.parameterError',"",218);
        
        $wallet = Db::name('LcUserWallet')->field('id,wid,type,name,wname,account')->find($params['id']);
        if(empty($wallet)) $this->error('utils.parameterError',"",218);
        $wname = '';
        if($wallet['type']==1){
            $wname = $wallet['wname']." (".substr($wallet['account'],0,4).'****'.substr($wallet['account'],strlen($wallet['account'])-4,strlen($wallet['account'])).")";
        }else{
            $wname = $wallet['wname']." (".substr($wallet['account'],0,2).'****'.substr($wallet['account'],strlen($wallet['account'])-2,strlen($wallet['account'])).")";
        }
        
        //判断余额，可提现金额=用户余额-冻结金额
        //判断实际余额
        // $act_user_money = $user['money']-$user['frozen_money'];
        $act_user_money = $user['withdrawable']-$user['frozen_money'];
        
        $money_usd = $params['money'];
        
        if($act_user_money<$params['money']) $this->error('utils.parameterError',"",218);
        
        //判断最低提现金额
        $currency = Db::name('LcCurrency')->where(['country' => $language])->find();
        if($currency['withdraw_min']>$params['money']) $this->error('utils.parameterError',"",218);
        
        $orderNo = 'OUT' . date('YmdHis') . rand(1000, 9999) . rand(100, 999);
        //提现状态：处理中0
        $status = 0;
        
        //时区转换
        $time = date('Y-m-d H:i:s');
        $time_zone = getTimezoneByLanguage($language);
        $act_time = dateTimeChangeByZone($time, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
        $currency = getCurrencyByLanguage($language);
        
        //判断手续费
        $charge = 0;
        $withdrawMethod = Db::name('LcUserWithdrawMethod')->find($wallet['wid']);
        if($withdrawMethod['charge']>0){
            $charge = $withdrawMethod['charge']*$money_usd/100;
        }
        
        //提现类型为USDT
        if($wallet['type']==1){
            //判断优盾钱包
            $merchant = Db::name('LcMerchant')->find(1);
            //开启了自动代付
            if (!empty($merchant)&&$merchant['auto']) {
                //判断代付金额
                $dfMoney = explode(",",$merchant['df_money']);
                
                if($money_usd>=$dfMoney[0]&&$money_usd<=$dfMoney[1]){
                    //判断地址有效性
                    $json= json_decode(checkAddress($wallet['account']), true);
                    if($json['code']==200){
                        //发起代付
                        $json= json_decode(proxypay($wallet['account'],$money_usd-$charge,$orderNo), true);
                        if($json['code']==200){
                            //判断是否开启自动审核
                            if ($merchant['auto_audit']) {
                                $status = 4;
                            }else{
                                $status = 3;
                            }
                        }
                    }
                }
            }
            
        }
        
        //添加提现记录
        $insert = array(
            "uid" =>$uid,
            "wid" =>$wallet['id'],
            "wname" =>$wname,
            "orderNo" =>$orderNo,
            "status" =>$status,
            "money" =>$money_usd,
            "money2" =>changeMoneyByLanguage($money_usd,$language),
            "charge" =>$charge,
            "currency" =>$currency,
            "time_zone" =>$time_zone,
            "act_time" =>$act_time,
            "time" =>$time
        );
        $wrid = Db::name('LcUserWithdrawRecord')->insertGetId($insert);
        if(!empty($wrid)){
            //流水添加
            addFunding($uid,$money_usd,changeMoneyByLanguage($params['money'],$language),2,3,$language);
            //余额扣除
            setNumber('LcUser', 'money', $money_usd, 2, "id = $uid");
        }
        
        $this->success("success");
    }
    /**
     * Describe:提现记录
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function withdrawRecord()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        
        $page = $params["page"];
        $listRows = $params["listRows"];
        
        $list = Db::name('LcUserWithdrawRecord uwr,lc_user_wallet uw')->field("uwr.money,uwr.money2,uwr.currency,uwr.status,uwr.act_time,uwr.wname,uw.type as wtype")->where("uwr.uid = $uid AND uwr.wid = uw.id AND uwr.money > 0")->order("uwr.act_time desc")->page($page,$listRows)->select();
        $length = Db::name('LcUserWithdrawRecord uwr,lc_user_wallet uw')->where("uwr.uid = $uid AND uwr.wid = uw.id AND uwr.money > 0")->order("uwr.act_time desc")->count();
        
        $data = array(
            'list' => $list,
            'length' => $length
        );
        $this->success("success", $data);
    }
    /**
     * Describe:资金记录
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fundingRecord()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        
        $page = $params["page"];
        $listRows = $params["listRows"];
        $type = $params["type"];
        //tabs状态，0全部1收入2支出
        $where = "";
        if($type==1){
            $where = 'type = 1';
        }elseif ($type==2) {
            $where = 'type = 2';
        }
        
        $list = Db::name('LcUserFunding')->field("money,money2,type,fund_type,currency,act_time")->where("uid = $uid")->where($where)->order("act_time desc")->page($page,$listRows)->select();
        $length = Db::name('LcUserFunding')->where("uid = $uid")->where($where)->order("act_time desc")->count();
        
        $data = array(
            'list' => $list,
            'length' => $length
        );
        $this->success("success", $data);
    }
    /**
     * Describe:获取充值方式
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRechargeMethod()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        
        //判断认证状态
        if(getUserNeedAuth($uid)) $this->error('auth.authFirst',"",405);
        
        //获取指定国别的充值方式
        $currency = Db::name('LcCurrency')->where(['country' => $language])->find();
        if(empty($currency)) $this->error('utils.parameterError',"",218);
        
        $rechargeMethod = Db::name('LcUserRechargeMethod')->field("id,type,name,logo")->where(['show' => 1,'delete' => 0,'cid' => $currency['id']])->order('sort asc,id desc')->select();
        
        $commonMoney = explode(",",$currency['common_money']);
        
        $data = array(
            "rechargeMethod" =>$rechargeMethod,
            "minMoney" =>$currency['recharge_min'],
            "commonMoney" =>$commonMoney,
            "userBalance" =>changeMoneyByLanguage($user['money'],$language),
        );
        $this->success("success", $data);
    }
    /**
     * Describe:获取充值详情
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getRechargeById()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        
        //判断认证状态
        if(getUserNeedAuth($uid)) $this->error('auth.authFirst',"",405);
        
        $recharge = Db::name('LcUserRechargeMethod')->field("id,name,account,img,logo,bank_name,type")->where(['show' => 1,'delete' => 0])->find($params['id']);
        if(empty($recharge)) $this->error('utils.parameterError',"",218);
        $currency = Db::name('LcCurrency')->where(['country' => $language])->find();
        if($currency['recharge_min']-$params['money']>0) $this->error('utils.parameterError',"",218);
        
        //充值方式为优盾USDT免提交
        if($recharge['type']==6){
            $usdt_qrcode = $user["usdt_qrcode"];
            $usdt_address = $user["usdt_address"];
            
            //判断用户钱包地址是否创建
            if(empty($user['usdt_address'])){
                //创建usdt钱包
                $json= json_decode(createAddress(), true);
                if($json['code']!=200){
                    $this->error('utils.networkException',"",218);
                }
                $qrCode = new QrCode();
                $qrCode->setText($json['data']['address']);
                $qrCode->setSize(300);
                $usdt_qrcode = $qrCode->getDataUri();
                $usdt_address = $json['data']['address'] ;
                Db::name('LcUser')->where('id', $uid)->update(['usdt_address'=>$usdt_address,'usdt_qrcode'=>$usdt_qrcode]);
            }
            
            $recharge['account'] = $usdt_address;
            $recharge['img'] = $usdt_qrcode;
        }
        
        $data = array(
            "recharge" =>$recharge,
            "rate" =>$currency['price'],
            "currency" => $currency['symbol']
        );
        $this->success("success", $data);
    }
    /**
     * Describe:充值
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function recharge()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        
        //判断认证状态
        if(getUserNeedAuth($uid)) $this->error('auth.authFirst',"",405);

        // 判断是否允许充值
        if(empty($user['is_recharge'])) $this->error('login.userLocked',"",218);
        
        //判断参数
        if(empty($params['id'])||empty($params['money'])) $this->error('utils.parameterError',"",218);
        $rechargeMethod = Db::name('LcUserRechargeMethod')->find($params['id']);
        if(empty($rechargeMethod)) $this->error('utils.parameterError',"",218);
        $hash = '';
        $voucher = '';
        
        //优盾USDT充值，需填写hash
        if($rechargeMethod['type']==5){
            if(empty($params['hash'])) $this->error('utils.parameterError',"",218);
            $hash = $params['hash'];
        //其他充值方式需上传凭证    
        }else{
            if(empty($params['voucher'])) $this->error('utils.parameterError',"",218);
            $voucher = $params['voucher'];
        }
        
        //金额转换
        $money_usd = $params['money'];
        
        $orderNo = 'IN' . date('YmdHis') . rand(1000, 9999) . rand(100, 999);
        
        //时区转换
        $time = date('Y-m-d H:i:s');
        $time_zone = getTimezoneByLanguage($language);
        $act_time = dateTimeChangeByZone($time, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
        $currency = getCurrencyByLanguage($language);
        
        //添加充值记录
        $insert = array(
            "uid" =>$uid,
            "rid" =>$rechargeMethod['id'],
            "orderNo" =>$orderNo,
            "money" =>$money_usd,
            "money2" =>changeMoneyByLanguage($money_usd,$language),
            "hash" =>$hash,
            "voucher" =>$voucher,
            "currency" =>$currency,
            "time_zone" =>$time_zone,
            "act_time" =>$act_time,
            "time" =>$time
        );
        $rrid = Db::name('LcUserRechargeRecord')->insertGetId($insert);
        if(!empty($rrid)){
            
            $this->success("success");
        }
        $this->error("error");
    }
    /**
     * Describe:充值记录
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function rechargeRecord()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        
        $page = $params["page"];
        $listRows = $params["listRows"];
        
        $list = Db::name('LcUserRechargeRecord urr,lc_user_recharge_method urm')->field("urr.money,urr.money2,urr.currency,urr.status,urr.act_time,urm.name,urm.type")->where("urr.uid = $uid AND urr.rid = urm.id AND urr.money > 0")->order("urr.act_time desc")->page($page,$listRows)->select();
        $length = Db::name('LcUserRechargeRecord urr,lc_user_recharge_method urm')->field("urr.money,urr.money2,urr.currency,urr.status,urr.act_time,urm.name")->where("urr.uid = $uid AND urr.rid = urm.id AND urr.money > 0")->order("urr.act_time desc")->count();
        
        $data = array(
            'list' => $list,
            'length' => $length
        );
        $this->success("success", $data);
    }
    /**
     * Describe:我的团队
     * DateTime: 2022/3/15 3:19
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function myTeam()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        $uname = substr($user['username'],0,2).'***'.substr($user['username'],strlen($user['username'])-2,strlen($user['username']));
        
        //时区转换，按当前用户时区统计
        $time_zone = getTimezoneByLanguage($language);
        $now = dateTimeChangeByZone(date('Y-m-d H:i:s'), 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');//当前用户时区 现在
        $today = date('Y-m-d 00:00:00',strtotime($now));//当前用户时区 今天0点
        $yesterday = date('Y-m-d 00:00:00', strtotime($now)-86400);//当前用户时区 昨天0点
        
        //数据统计
        $direct_count = Db::name('LcUserRelation')->where("parentid = $uid AND level = 1")->count();
        $indirect_count = Db::name('LcUserRelation')->where("parentid = $uid AND level <> 1")->count();
        
        $add_count = Db::name('LcUser u,lc_user_relation ur')->where("u.id=ur.uid AND ur.parentid = $uid")->count();
        $add_count_to = Db::name('LcUser u,lc_user_relation ur')->where("u.act_time BETWEEN '$today' AND '$now' AND u.id=ur.uid AND ur.parentid = $uid")->count();
        $add_count_ye = Db::name('LcUser u,lc_user_relation ur')->where("u.act_time BETWEEN '$yesterday' AND '$today' AND u.id=ur.uid AND ur.parentid = $uid")->count();
        
        $income_sum = Db::name('lc_user_funding')->where("uid = $uid AND ( fund_type = 12 OR fund_type = 13 )")->sum('money');
        $income_sum_to = Db::name('lc_user_funding')->where("act_time BETWEEN '$today' AND '$now' AND uid = $uid AND ( fund_type = 12 OR fund_type = 13 )")->sum('money');
        $income_sum_ye = Db::name('lc_user_funding')->where("act_time BETWEEN '$yesterday' AND '$today' AND uid = $uid AND ( fund_type = 12 OR fund_type = 13 )")->sum('money');
        
        $qrCode = new QrCode();
        $qrCode->setText(getInfo('domain') . '/#/register?code=' . $user['invite_code']);
        $qrCode->setSize(300);
        $shareCode = $qrCode->getDataUri();
        $shareLink = getInfo('domain') . '/#/register?code=' . $user['invite_code'];
        
        $user_info = array(
            "username" => $uname,
            "auth_email" => $user['auth_email'],
            "auth_google" => $user['auth_google'],
            "auth_phone" => $user['auth_phone'],
            "invite_code" => $user['invite_code'],
            "user_icon" => getInfo('user_img'),
            "share_code" => $shareCode,
            "share_link" => $shareLink
        );
        $report = array(
            "direct_count" => $direct_count,
            "indirect_count" => $indirect_count,
            "add_count" => $add_count,
            "add_count_to" => $add_count_to,
            "add_count_ye" => $add_count_ye,
            "income_to" => $income_sum_to,
            "income" =>$income_sum,
            "income_ye" => $income_sum_ye
        );
        
        $data = array(
            "user_info" => $user_info,
            "report" => $report
        );
        $this->success("success", $data);
    }
    /**
     * Describe:团队列表
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function teamList()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        
        $page = $params["page"];
        $listRows = $params["listRows"];
        
        $list = Db::name('LcUser u,lc_user_relation ur')->field('u.id,u.username,u.act_time,ur.level')->where("ur.parentid = $uid AND ur.uid = u.id")->order("u.act_time desc")->page($page,$listRows)->select();
        $length = Db::name('LcUser u,lc_user_relation ur')->where("ur.parentid = $uid AND ur.uid = u.id")->order("u.act_time desc")->count();
        
        foreach ($list as &$user) {
            $uid2 = $user['id'];
            $recharge_sum = Db::name('lc_user_recharge_record')->where("uid=$uid2 AND status=1")->sum('money');
            $user['recharge_sum'] = $recharge_sum;
            $user['username'] = substr($user['username'],0,2).'***'.substr($user['username'],strlen($user['username'])-2,strlen($user['username']));
        }
        
        $data = array(
            'list' => $list,
            'length' => $length
        );
        $this->success("success", $data);
    }
    /**
     * Describe:获取会员详情
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getVip()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        $uname = substr($user['username'],0,2).'***'.substr($user['username'],strlen($user['username'])-2,strlen($user['username']));

        $vip = Db::name('LcUserMember')->field("name,logo,invest_num,rewards_direct,rewards_undirect,value as this_value")->find($user['mid']);
        if(empty($vip)) $this->error('utils.parameterError',"",218);
        
        $vip_next = Db::name('LcUserMember')->where("value > '{$vip['this_value']}'")->order('value asc')->find();
        //不存在下一个级别则为最高级
        if(empty($vip_next)){
            $vip_next = Db::name('LcUserMember')->order('value desc')->find();
        }
        $vip['next_value'] = $vip_next['value'];
        
        $user_info = array(
            "user_icon" => getInfo('user_img'),
            "username" =>$uname,
            "invite_code" =>$user['invite_code'],
            "user_value" =>$user['value'],
            "balance" => $user['money'],
            "income" =>0,
            );
        
        $data = array(
            "vip" =>$vip,
            "user" =>$user_info,
        );
        $this->success("success", $data);
    }
    /**
     * Describe:获取奖励详情
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getReward()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        
        $reward= Db::name('LcReward')->field("register,invite,authentication,login,invest")->find(1);
        if(empty($reward)) $this->error('utils.parameterError',"",218);
        
        $reward['invite'] = $reward['invite'];
        $reward['authentication'] = $reward['authentication'];
        $reward['invest'] = $reward['invest'];
        $reward['authentication_status'] = false;
        $reward['invest_status'] = false;
        
        //判断领取状态
        $auth_status = $user['auth_email']+$user['auth_phone']+$user['auth_google'];
        
        $invest_status = 0;
        $now = date('Y-m-d H:i:s');//现在
        $today = date('Y-m-d');//今天0点
        $yesterday = date('Y-m-d 00:00:00', strtotime($now)-86400);//当前用户时区 昨天0点
        $invest_today = getFundingByTime($today,$now,$uid,11);
        
        $reward_today = Db::name('LcUserFunding')->where("time >= '$today' AND time <= '$now' AND uid = '{$uid}' AND fund_type in (7,8,9,10,11)")->sum('money');
        // $reward_yesterday = Db::name('LcUserFunding')->where("time >= '$yesterday' AND time <= '$today' AND uid = '{$uid}' AND fund_type in (7,8,9,10,11)")->sum('money');
        $reward_total = Db::name('LcUserFunding')->where("uid = '{$uid}' AND fund_type in (7,8,9,10,11)")->sum('money');
        
        if(!empty($invest_today)) $invest_status = 1;
        
        $user_info = array(
            "balance" =>$user['money'],
            "today" =>$reward_today,
            "yesterday" =>0,
            "total" =>$reward_total,
            "auth_status"=>$auth_status==0?0:($auth_status."/3"),
            "invest_status"=>$invest_status
            );
        
        $data = array(
            "reward" =>$reward,
            "user" =>$user_info,
        );
        $this->success("success", $data);
    }
    /**
     * Describe:奖励记录
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function rewardRecord()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        
        $page = $params["page"];
        $listRows = $params["listRows"];
        
        $list = Db::name('LcUserFunding')->field("money,money2,type,fund_type,currency,act_time")->where("uid = $uid")->where("fund_type in (7,8,9,10,11)")->order("act_time desc")->page($page,$listRows)->select();
        $length = Db::name('LcUserFunding')->where("uid = $uid")->where("fund_type in (7,8,9,10,11)")->order("act_time desc")->count();
        
        $data = array(
            'list' => $list,
            'length' => $length
        );
        $this->success("success", $data);
    }
    /**
     * Describe:投资
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function invest()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        
        //判断认证状态
        if(getUserNeedAuth($uid)) $this->error('auth.authFirst',"",405);
        
        //判断参数
        if(empty($params['id'])) $this->error('utils.parameterError',"",218);
        $item = Db::name('LcItem')->find($params['id']);
        if(empty($item)) $this->error('utils.parameterError',"",218);

        // 判断vip等级是否满足
        if ($user['mid'] < $item['vip_level']) {
            $this->error('auth.parameterError',"",405);
        }

        // 判断积分是否足够
        if (!empty($item['need_integral']) && $item['need_integral'] > $user['point'] && !$params['is_withdrawal_purchase']) {
            $this->error('auth.parameterError',"",405);
        }

        // 判断项目是否上架
        if (empty($item['show'])) {
            $this->error('auth.parameterError',"",405);
        }
        
         $money_usd = $item['min'];
        //金额转换
        //判断余额/提现余额>投资金额
        if ($params['is_withdrawal_purchase']) {
            $money_usd = floor($money_usd*$item['withdrawal_purchase']) / 100;
            if($user['withdrawable']<$money_usd) $this->error('utils.parameterError',"",218);
        }else {
            if($user['money']<$money_usd) $this->error('utils.parameterError',"",218);
        }
        
        //判断投资金额
        // if ($money_usd - $item['max'] > 0 || $money_usd - $item['min'] < 0) $this->error('utils.parameterError',"",218);
       
        //判断投资次数
        $investCount = Db::name('LcInvest')->where(['itemid' => $item['id'],'uid' => $uid])->count();
        if($investCount>=$item['num']) $this->error('utils.parameterError',"",218);
        
        //判断会员投资次数
        $now = date('Y-m-d H:i:s');//现在
        $today = date('Y-m-d');//今天0点
        $investCountToday = Db::name('LcInvest')->where(['itemid' => $item['id'],'uid' => $uid])->where("time >= '$today' AND time <= '$now'")->count();
        $vip = Db::name("LcUserMember")->find($user['mid']);
        if($investCountToday>=$vip['invest_num']) $this->error('utils.parameterError',"",218);
        
        //时区转换
        $time = date('Y-m-d H:i:s');
        $time_zone = getTimezoneByLanguage($language);
        $time_actual = dateTimeChangeByZone($time, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
        $currency = getCurrencyByLanguage($language);
       
        $time2 = date('Y-m-d H:i:s', strtotime($time.'+' . $item['day'] . ' day'));
        $total_interest = $money_usd * $item['rate'] / 100;
        $total_num = 1;
        
        //到期还本付息（时）
        if($item['type']==3){
            //按时
            $time2 = date('Y-m-d H:i:s', strtotime($time.'+' . $item['day'] . ' hour'));
        }
        //每日付息到期还本
        elseif($item['type']==1){
            //日利率
            $total_interest = $money_usd * $item['rate'] * $item['day'] / 100;
            //返息期数
            $total_num = $item['day'];
        }
        $time2_actual = dateTimeChangeByZone($time2, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
        
        $orderNo = 'ST' . date('YmdHis') . rand(1000, 9999) . rand(100, 999);
        
        //添加投资记录
        $insert = array(
            "uid" =>$uid,
            "itemid" =>$item['id'],
            "orderNo" =>$orderNo,
            "money" =>$item['min'],
            "money2" =>$money_usd,
            "total_interest" =>$total_interest,
            "wait_interest" =>$total_interest,
            "total_num" =>$total_num,
            "wait_num" =>$total_num,
            "day" =>$item['day'],
            "rate" =>$item['rate'],
            "type" =>$item['type'],
            "is_distribution" =>$item['is_distribution'],
            "currency" =>$currency,
            "time_zone" =>$time_zone,
            "time" =>$time,
            "time_actual" =>$time_actual,
            "time2" =>$time2,
            "time2_actual" =>$time2_actual,
        );
        
        Db::startTrans();
        $iid = Db::name('LcInvest')->insertGetId($insert);
        if(!empty($iid)){
           
            if ($params['is_withdrawal_purchase']) {
                 //流水添加
                addFunding($uid,$money_usd,changeMoneyByLanguage($money_usd,$language),2,5,$language,2);
                //提现余额扣除
                setNumber('LcUser', 'withdrawable', $money_usd, 2, "id = $uid");
            }else {
                 //流水添加
                addFunding($uid,$money_usd,changeMoneyByLanguage($money_usd,$language),2,5,$language);
                //余额扣除
                setNumber('LcUser', 'money', $money_usd, 2, "id = $uid");
            }
            //冻结金额扣除
            if($user['frozen_money']>$money_usd){
                setNumber('LcUser', 'frozen_money', $money_usd, 2, "id = $uid");
            }else{
                setNumber('LcUser', 'frozen_money', $user['frozen_money'], 2, "id = $uid");
            }
            if (!$params['is_withdrawal_purchase']) {
                // 积分扣除
                setNumber('LcUser', 'point', $item['need_integral'], 2, "id = $uid");
                addIntegral($uid,$item['need_integral'],2,2,$language);
            }
            // 积分赠送
            setNumber('LcUser', 'point', $item['gifts_integral'], 1, "id = $uid");
            addIntegral($uid,$item['gifts_integral'],1,2,$language);
            //添加每日投资奖励
            $reward = Db::name('LcReward')->find(1);
            if($reward['invest']>0){
                $now = date('Y-m-d H:i:s');//现在
                $today = date('Y-m-d');//今天0点
                $login_today = getFundingByTime($today,$now,$uid,11);
                //判断今日是否奖励
                if(empty($login_today)){
                    //流水添加
                    addFunding($uid,$reward['invest'],changeMoneyByLanguage($reward['invest'],$language),1,11,$language);
                    //添加余额
                    setNumber('LcUser', 'money', $reward['invest'], 1, "id = $uid");
                    //添加冻结金额
                    if(getInfo('recharge_need_flow')){
                        setNumber('LcUser', 'frozen_money', $reward['invest'], 1, "id = $uid");
                    }
                }
            }
            //添加抽奖次数
            // $draw = Db::name('LcDraw')->find(1);
            // if($draw['invest']>0){
            //     setNumber('LcUser', 'draw_num', $draw['invest'], 1, "id = $uid");
            // }
            if ($item['superior_draw_num'] > 0) {
                // 上级
                $parentid = Db::table('lc_user_relation')->where("uid=$uid and level=1")->value('parentid');
                if ($parentid) {
                    setNumber('LcUser', 'draw_num', $item['superior_draw_num'], 1, "id = $parentid");
                }
            }
            if ($item['draw_num'] > 0) {
                // 购买者
                setNumber('LcUser', 'draw_num', $item['draw_num'], 1, "id = $uid");
            }

            // 当前用户没有等级的话就直接升等级一            
            if ($vip['value'] == 0) {
                $vip_next = Db::name('LcUserMember')->where("value > '{$vip['value']}'")->order('value asc')->find();
                if(empty($vip_next)){
                    $vip_next = Db::name('LcUser')->where("id = {$user['id']}")->update(['mid' => $vip_next['id']]);
                }
            }
            

            Db::commit();
            $this->success("success");
        }else{
            Db::rollback();
        }
        $this->error("error");
    }
    /**
     * Describe:兑换商品
     * DateTime: 2023/4/15
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goods_exchange()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        $id = $params["id"];
        
        $goods  = Db::name('LcGoods')->find($id);
        if(empty($goods)) $this->error('utils.parameterError',"",218);
        
        //判断积分
        if($user['point']-$goods['point']<0) $this->error('utils.parameterError',"",218);
        
        
        //时区转换
        $time = date('Y-m-d H:i:s');
        $time_zone = getTimezoneByLanguage($language);
        $act_time = dateTimeChangeByZone($time, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
        
        $add = array(
            'uid' => $uid,
            'gid' => $goods['id'],
            'point' => $goods['point'],
            'time_zone' => $time_zone,
            'time' => $time,
            'act_time' => $act_time,
        );
        $iid = Db::name('LcGoodsRecord')->insertGetId($add);
        if(empty($iid)) $this->error('utils.networkException',"",218);
        //积分减少
        $point_now = $user['point'] - $goods['point'];
        Db::name('LcUser')->where(['id' => $uid])->update(['point' => $point_now]);
        
        $this->success("success");
    }
    /**
     * Describe:兑换记录
     * DateTime: 2023/4/16
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodsRecord()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        
        $page = $params["page"];
        $listRows = $params["listRows"];
        
        $list = Db::name('lc_goods_record gr,lc_goods g')->field("gr.act_time,g.title_$language as title,g.img,g.point")->where("gr.gid = g.id")->where("uid = $uid")->order("gr.act_time desc")->page($page,$listRows)->select();
        $length = Db::name('lc_goods_record gr,lc_goods g')->field("gr.act_time,g.title_$language as title,g.img,g.point")->where("gr.gid = g.id")->where("uid = $uid")->order("gr.act_time desc")->count();
        
        $data = array(
            'list' => $list,
            'length' => $length
        );
        $this->success("success", $data);
    }
    /**
     * Describe:投资记录
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function investRecord()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        
        $page = $params["page"];
        $listRows = $params["listRows"];
        
        $list = Db::name('LcInvest')->field("id,itemid,money,day,rate,total_interest,wait_interest,type,status,currency,time_zone,time_actual,time2_actual,time,time2,total_num,wait_num,pause_time")->where("uid = $uid")->order("time_actual desc")->page($page,$listRows)->select();
        $length = Db::name('LcInvest')->where("uid = $uid")->count();
        
        foreach ($list as &$invest) {
            $invest['is_receive'] = date('Y-m-d H:i:s') > $invest['time2'] ? 1 : 0;
            $item = Db::name('LcItem')->find($invest['itemid']);
            // 判断是否有领取
            if ($invest['type'] == 1) {
                $Date_1=date("Y-m-d");
                $Date_2=date("Y-m-d", strtotime($invest['time']));
                $d1=strtotime($Date_1);
                $d2=strtotime($Date_2);
                $day_diff=round(($d1-$d2)/3600/24);
                if (!empty($day_diff)) {
                    $wait_day = $day_diff - ($invest['total_num'] - $invest['wait_num']);
                    $invest['is_receive'] = $wait_day > 0 ? 1 : 0;
                }
            }
            if ($invest['status'] == 1) {
                $invest['is_receive'] = 0;
            }
            $invest['title'] = "--";
            if(!empty($item)){
                $invest['title'] = $item["title_$language"];
            }
        }
        
        $data = array(
            'list' => $list,
            'length' => $length
        );
        $this->success("success", $data);
    }
    /**
     * Describe:获取转盘抽奖奖品列表
     * DateTime: 2022/8/27
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function prizeList()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        
        //判断认证状态
        if(getUserNeedAuth($uid)) $this->error('auth.authFirst',"",405);
        
        $set = Db::name('LcDraw')->field("content_$language as content,point")->find(1);
        $prizeList = Db::name('LcDrawPrize')->field("id,title_$language as title,img,type")->order('sort asc,id asc')->select();
        
        $drawRecord = Db::name('LcDrawRecord dr,lcDrawPrize dp,lcUser u')->field("dp.title_$language as title,u.username")->where('dr.pid = dp.id AND dr.uid = u.id AND dp.type!=3')->order('dr.act_time desc')->limit(8)->select();
       
        foreach ($drawRecord as &$dr) {
            $dr['username'] = dataDesensitization($dr['username'], 2, 4);
        }
        $data = array(
            'prizeList' => $prizeList,
            'drawRecord' => $drawRecord,
            'set' => $set,
            'drawNum' => $user['draw_num'],
            'point_total' => $user['point'],
            'point' => $set['point'],
        );
        $this->success("success", $data);
    }
    /**
     * Describe:抽奖
     * DateTime: 2022/8/29 21:59
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function draw()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        $set  = Db::name('LcDraw')->find(1);
        
        
        // if($user['point'] - $set['point'] < 0) $this->error('utils.parameterError',"",218);
        if($user['draw_num'] < 1) $this->error('utils.parameterError',"",218);
        //时区转换
        $time = date('Y-m-d H:i:s');
        $time_zone = getTimezoneByLanguage($language);
        $act_time = dateTimeChangeByZone($time, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
        
        
        // 是否存在必中
        $draw_appoint  = Db::table('lc_draw_appoint')->where('uid', $uid)->whereNull('use_time')->find();
        if ($draw_appoint) {
            $draw = Db::name('LcDrawPrize')->where('id', $draw_appoint['draw_prize_id'])->field("id,title_$language as title,img,type,probability,money")->find();
            Db::table('lc_draw_appoint')->where('id', $draw_appoint['id'])->update(['time_zone' => $time_zone, 'act_use_time' => $act_time, 'use_time' => $time]);
        }else {
            $prizeList  = Db::name('LcDrawPrize')->field("id,title_$language as title,img,type,probability,money")->order('sort asc,id desc')->select();

            //概率算法
            $list = [];
            foreach($prizeList as $k2=>$v2) {
                $list[$k2] = $v2['probability'];
            }
            $draw = $prizeList[get_rand($list)];
        }
        
        $add = array(
            'uid' => $uid,
            'pid' => $draw['id'],
            'dtype' => $draw['type'],
            'time_zone' => $time_zone,
            'time' => $time,
            'act_time' => $act_time,
        );
        $did = Db::name('LcDrawRecord')->insertGetId($add);
        if(empty($did)) $this->error('utils.networkException',"",218);
        //抽奖次数减少
        // $point_now = $user['point'] - $set['point'];
        // Db::name('LcUser')->where(['id' => $uid])->update(['point' => $point_now]);
        Db::name('LcUser')->where(['id' => $uid])->setDec('draw_num');
        
        //现金则添加到账户余额
        if($draw['type']==2){
            //流水添加
            addFunding($uid,$draw['money'],changeMoneyByLanguage($draw['money'],$language),1,14,$language);
            //添加余额
            setNumber('LcUser', 'withdrawable', $draw['money'], 1, "id = $uid");
            //添加冻结金额
            if(getInfo('recharge_need_flow')){
                setNumber('LcUser', 'frozen_money', $draw['money'], 1, "id = $uid");
            }
        }
        
        $data = array(
            'draw' => $draw,
        );
        $this->success("success", $data);
    }
    /**
     * Describe:抽奖记录
     * DateTime: 2022/8/29 21:59
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function drawRecord()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        
        $page = $params["page"];
        $listRows = $params["listRows"];
        
        $list = Db::name('lc_draw_record dr,lc_draw_prize dp')->field("dr.act_time,dp.title_$language as title,dp.img,dp.type,dp.money")->where("dr.pid = dp.id")->where("uid = $uid")->order("dr.act_time desc")->page($page,$listRows)->select();
        $length = Db::name('lc_draw_record dr,lc_draw_prize dp')->where("dr.pid = dp.id")->where("uid = $uid")->order("dr.act_time desc")->count();
        
        $data = array(
            'list' => $list,
            'length' => $length
        );
        $this->success("success", $data);
    }
    
    
    /**
     * @description：储蓄金详情
     * @date: 2023/2/4
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function savingsDetail()
    {
        $params = $this->request->param();
        $language = $params["language"];
        
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        
        $savings = Db::name('LcSavings')->field("content_$language as content")->find(1);
        
        $income =  Db::name('LcUserFunding')->where("uid = '{$uid}' AND fund_type = 18")->sum('money');
        
        $data = array(
            'savings' => $savings,
            'income' => $income,
            // 'flexible'=>changeMoneyByLanguage($user['savings_flexible'],$language),
            'flexible_usd'=>$user['savings_flexible'],
            // 'fixed'=>changeMoneyByLanguage($user['savings_fixed'],$language),
            // 'fixed_usd'=>$user['savings_fixed']
        );
        $this->success("success", $data);
    }
    /**
     * Describe:申购详情
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function savingsSubscribeDetail()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        
        //判断参数
        if(empty($params['type'])) $this->error('utils.parameterError',"",218);
        if($params['type']!=1&&$params['type']!=2) $this->error('utils.parameterError',"",218);
        
        $savings = Db::name('LcSavings')->find(1);
        $data = array();
        
        //时区转换，按当前用户时区统计
        $time_zone = getTimezoneByLanguage($language);
        
        $date_now = date('Y-m-d H:i:s');
        $now = dateTimeChangeByZone($date_now, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');//当前用户时区 现在
        
        //活期
        if($params['type']==1){
            $data = array(
                "rate" =>$savings['flexible_rate'],
                "max_rate" =>$savings['flexible_rate'],
                "inc_rate" =>0,
                "min" =>$savings['flexible_min'],
                "day" =>$savings['flexible_min_day'],
                "userBalance" =>$user['money'],
                "time1" =>date("Y-m-d H:i",strtotime(dateTimeChangeByZone(date('Y-m-d H:i:s'), 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s'))),
                "time2" =>date("Y-m-d H:i",strtotime(dateTimeChangeByZone(date('Y-m-d 23:30:00'), 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s')."+1 day"))
            );
        }
        //定期
        else{
            $data = array(
                "rate" =>$savings['fixed_rate'],
                "max_rate" =>$savings['fixed_rate_max'],
                "inc_rate" =>$savings['fixed_inc_rate'],
                "min" =>$savings['fixed_min'],
                "day" =>$savings['fixed_min_day'],
                "userBalance" =>$user['money'],
                "time1" =>date("Y-m-d H:i",strtotime(dateTimeChangeByZone(date('Y-m-d H:i:s'), 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s'))),
                "time2" =>date("Y-m-d H:i",strtotime(dateTimeChangeByZone(date('Y-m-d H:i:s'), 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s')."+1 day"))
            );
        }
        
        $this->success("success", $data);
    }
    /**
     * Describe:发起申购
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function savingsSubscribe()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        
        //判断认证状态
        if(getUserNeedAuth($uid)) $this->error('auth.authFirst',"",405);
        
        //判断参数
        if(empty($params['type'])||empty($params['money'])) $this->error('utils.parameterError',"",218);
        if($params['type']!=1&&$params['type']!=2) $this->error('utils.parameterError',"",218);
        
        $savings = Db::name('LcSavings')->find(1);
        
        //金额转换
        $money_usd = $params['money'];
        
        //判断余额>最低金额
        $act_user_money = $user['money'];
        if($act_user_money<$params['money']) $this->error('utils.parameterError',"",218);
        
        
        $day = 0;
        $wait_day = 0;
        $rate = 0;
        $status = -1;
        
        //时区转换
        $time = date('Y-m-d H:i:s');
        $time_zone = getTimezoneByLanguage($language);
        $time_actual = dateTimeChangeByZone($time, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
        $currency = getCurrencyByLanguage($language);
        
        $time2 = "0000-00-00 00:00:00";
        $time2_actual = "0000-00-00 00:00:00";
        
        //活期
        if($params['type']==1){
            //判断申购金额
            if ($money_usd - $savings['flexible_min'] < 0) $this->error('utils.parameterError',"",218);
            
            $rate = $savings['flexible_rate'];
        }
        //定期
        else{
            //判断申购金额
            if ($money_usd - $savings['fixed_min'] < 0) $this->error('utils.parameterError',"",218);
            //判断申购天数
            if(empty($params['days'])) $this->error('utils.parameterError',"",218);
            if ($params['days'] - $savings['fixed_min_day'] < 0) $this->error('utils.parameterError',"",218);
            
            $day = $params['days'];
            $status = 0;
            $rate = $savings['fixed_rate'];
            
            $rate = $rate + ($day - $savings['fixed_min_day'])*$savings['fixed_inc_rate'];
            
            if($rate<$savings['fixed_rate']) $rate = $savings['fixed_rate'];
            if($rate>$savings['fixed_rate_max']) $rate = $savings['fixed_rate_max'];
            
            $time2 = date('Y-m-d H:i:s', strtotime($time.'+' . $day . ' day'));
            $time2_actual = dateTimeChangeByZone($time2, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
            $wait_day = $day;
        }
        
        $orderNo = 'SU' . date('YmdHis') . rand(1000, 9999) . rand(100, 999);
        
        //添加申购记录
        $insert = array(
            "uid" =>$uid,
            "orderNo" =>$orderNo,
            "money" =>$money_usd,
            "money2" =>$params['money'],
            "day" =>$day,
            "wait_day" =>$wait_day,
            "rate" =>$rate,
            "status" =>$status,
            "type" =>$params['type'],
            "currency" =>$currency,
            "time_zone" =>$time_zone,
            "time" =>$time,
            "time_actual" =>$time_actual,
            "time2" =>$time2,
            "time2_actual" =>$time2_actual,
        );
        
        Db::startTrans();
        $iid = Db::name('LcSavingsSubscribe')->insertGetId($insert);
        if(!empty($iid)){
            //流水添加
            addFunding($uid,$money_usd,changeMoneyByLanguage($params['money'],$language),2,16,$language);
            //余额扣除
            setNumber('LcUser', 'money', $money_usd, 2, "id = $uid");
            //活期/定期余额添加
            if($params['type']==1){
                setNumber('LcUser', 'savings_flexible', $money_usd, 1, "id = $uid");
            }else{
                setNumber('LcUser', 'savings_fixed', $money_usd, 1, "id = $uid");
            }
            //冻结金额扣除
            if($user['frozen_money']>$money_usd){
                setNumber('LcUser', 'frozen_money', $money_usd, 2, "id = $uid");
            }else{
                setNumber('LcUser', 'frozen_money', $user['frozen_money'], 2, "id = $uid");
            }
            Db::commit();
            $this->success("success");
        }else{
            Db::rollback();
        }
        $this->error("error");
    }
    /**
     * Describe:申购记录
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function savingsSubscribeRecord()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        
        $page = $params["page"];
        $listRows = $params["listRows"];
        
        $list = Db::name('lc_savings_subscribe')->field("money,money2,currency,day,rate,type,status,time_actual as act_time")->where("uid = $uid")->order("time_actual desc")->page($page,$listRows)->select();
        $length = Db::name('lc_savings_subscribe')->field("money,money2,currency,day,rate,type,status,time_actual as act_time")->where("uid = $uid")->order("time_actual desc")->count();
        
        $data = array(
            'list' => $list,
            'length' => $length
        );
        $this->success("success", $data);
    }
    
    /**
     * Describe:赎回详情
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function savingsRedeemDetail()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        
        
        $savings = Db::name('LcSavings')->find(1);
        $flexible_min_day = $savings['flexible_min_day'];
        
        //时区转换，按当前用户时区统计
        $time_zone = getTimezoneByLanguage($language);
        $now = dateTimeChangeByZone(date('Y-m-d H:i:s'), 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');//当前用户时区 现在
        
        $start = dateTimeChangeByZone(date('Y-m-d H:i:s',strtotime("-$flexible_min_day day")), 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
        
        $flexible_no_sum = Db::name('lc_savings_subscribe')->where("time_actual BETWEEN '$start' AND '$now' AND uid = $uid AND type = 1")->sum('money');
        
        //可赎回金额 = 用户活期余额-活期持有未到期金额
        $money_usd = $user['savings_flexible'] - $flexible_no_sum;
        
        $data = array(
            "balance" =>$money_usd,
            "days" =>$savings['flexible_min_day'],
            "time" =>date("Y-m-d H:i",strtotime(dateTimeChangeByZone(date('Y-m-d H:i:s'), 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s'))),
            );
        
        $this->success("success", $data);
    }
    /**
     * Describe:发起赎回
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function savingsRedeem()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name("LcUser")->find($uid);
        
        //判断认证状态
        if(getUserNeedAuth($uid)) $this->error('auth.authFirst',"",405);
        
        //判断参数
        if(empty($params['money'])) $this->error('utils.parameterError',"",218);
        
        $savings = Db::name('LcSavings')->find(1);
        $flexible_min_day = $savings['flexible_min_day'];
        
        //金额转换
        $money_usd = $params['money'];
        
        //判断可赎回金额>赎回金额
        //时区转换，按当前用户时区统计
        $time_zone = getTimezoneByLanguage($language);
        $now = dateTimeChangeByZone(date('Y-m-d H:i:s'), 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');//当前用户时区 现在
        $start = dateTimeChangeByZone(date('Y-m-d H:i:s',strtotime("-$flexible_min_day day")), 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');//当前用户时区 现在
        $flexible_no_sum = Db::name('lc_savings_subscribe')->where("time_actual BETWEEN '$start' AND '$now' AND uid = $uid AND type = 1")->sum('money');
        //可赎回金额 = 用户定期余额-活期持有未到期金额
        $flexible_usd = $user['savings_flexible'] - $flexible_no_sum;
        
        $used_money = $flexible_usd;
        if($used_money<$params['money']) $this->error('utils.parameterError',"",218);
        
        //时区转换
        $time = date('Y-m-d H:i:s');
        $time_zone = getTimezoneByLanguage($language);
        $time_actual = dateTimeChangeByZone($time, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
        $currency = getCurrencyByLanguage($language);
        
        $orderNo = 'RE' . date('YmdHis') . rand(1000, 9999) . rand(100, 999);
        
        //添加赎回记录
        $insert = array(
            "uid" =>$uid,
            "orderNo" =>$orderNo,
            "money" =>$money_usd,
            "money2" =>$params['money'],
            "type" =>1,
            "currency" =>$currency,
            "time_zone" =>$time_zone,
            "time" =>$time,
            "time_actual" =>$time_actual,
        );
        
        Db::startTrans();
        $iid = Db::name('LcSavingsRedeem')->insertGetId($insert);
        if(!empty($iid)){
            //流水添加
            addFunding($uid,$money_usd,changeMoneyByLanguage($params['money'],$language),1,17,$language);
            //余额添加
            setNumber('LcUser', 'money', $money_usd, 1, "id = $uid");
            //活期余额扣除
            setNumber('LcUser', 'savings_flexible', $money_usd, 2, "id = $uid");
            Db::commit();
            $this->success("success");
        }else{
            Db::rollback();
        }
        $this->error("error");
    }
    /**
     * Describe:赎回记录
     * DateTime: 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function savingsRedeemRecord()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        
        $page = $params["page"];
        $listRows = $params["listRows"];
        
        $list = Db::name('lc_savings_redeem')->field("money,money2,currency,type,time_actual as act_time")->where("uid = $uid")->order("time_actual desc")->page($page,$listRows)->select();
        $length = Db::name('lc_savings_redeem')->field("money,money2,currency,type,time_actual as act_time")->where("uid = $uid")->order("time_actual desc")->count();
        
        $data = array(
            'list' => $list,
            'length' => $length
        );
        $this->success("success", $data);
    }

    // 领取利息
    public function invest_settle()
    {
        $noInvest = true;
        $params = $this->request->param();
        $id = $params['invest_id'];
        $type = $params['type'];

        // $uid = $this->userInfo['id'];
        
        $now = date('Y-m-d H:i:s');
        $invest_list1 = [];
        $invest_list2 = [];
        $savings_list1 = [];
        switch ($type) {
            case 1:
                //每日付息到期还本
                $invest_list1 = Db::name("LcInvest")->where("id = $id and type=1 AND status = 0")->select();
                break;
            case 2:
                //到期还本付息
                $invest_list2 = Db::name("LcInvest")->where("id = $id and time2 <= '$now' AND status = 0 AND ( type=2 OR type=3 )")->select();
                break;
            case 3:
                //储蓄金定期
                $savings_list1 = Db::name("LcSavingsSubscribe")->where("id = $id and type=2 AND status = 0")->select();
                break;
        }
        if (empty($invest_list1)&&empty($invest_list2)&&empty($savings_list1)) $this->error('error');
        
        //每日付息到期还本处理
        foreach ($invest_list1 as $k => $v) {
            // 判断是否隔天没有领取
            $wait_day = 0;
            $Date_1=date("Y-m-d");
            $Date_2=date("Y-m-d", strtotime($v['time']));
            $d1=strtotime($Date_1);
            $d2=strtotime($Date_2);
            $day_diff=round(($d1-$d2)/3600/24);
            if (!empty($day_diff)) {
                $wait_day = $day_diff - ($v['total_num'] - $v['wait_num']) - 1;
            }
            
            //判断返还时间
            $return_num = $v['wait_num'] - 1;
            $return_time = date('Y-m-d H:i:s', (strtotime($v['time2'].'-' . $return_num . ' day') + (3600*24*$wait_day)));
            if($return_time > $now && empty($wait_day)) continue;
            
            $time_zone = $v['time_zone'];
            $language = getLanguageByTimezone($time_zone);
            
            $money = $v['money'];
            //每日利息=总利息/总期数
            $day_interest = $v['total_interest']/$v['total_num'];

            // 添加返利
            if ($v['is_distribution']) {
                $fusers = Db::name("LcUserRelation")->alias('ur')->join('lc_user u', 'ur.parentid=u.mid')->join('lc_user_member um', 'um.id=u.mid')->where("ur.uid = {$v['uid']}")->limit(3)->select();
                $fusers = array_reverse($fusers); 
                foreach($fusers as $key => $val) {
                    $level = '';
                    switch ($key) {
                        case 1:
                            $level = $val['level_b'];
                            break;
                        case 2:
                            $level = $val['level_c'];
                            break;
                        case 3:
                            $level = $val['level_d'];
                            break;
                        
                    }
                    $interest_rate = floor($day_interest*$level*100) / 100;
                    // 添加收益
                    setNumber('LcUser', 'withdrawable', $interest_rate, 2, "id = {$val['parentid']}");
                    // 添加总收益
                    setNumber('LcUser', 'income', $interest_rate, 1, "id = {$val['parentid']}");
                    //流水添加
                    addFunding($val['parentid'],$interest_rate,changeMoneyByLanguage($interest_rate,$language),2,19,$language);
                }
            }
            
            
            //最后一期
            if($v['wait_num']==1){
                Db::name('LcInvest')->where('id', $v['id'])->update(['status' => 1,'wait_num' => 0,'wait_interest' => 0]);
                //返还本金
                addFunding($v['uid'],$v['money2'],changeMoneyByLanguage($v['money2'],$language),1,15,$language);
                setNumber('LcUser', 'money', $v['money2'], 1, "id = {$v['uid']}");
                
            }else{
                $time2 = date('Y-m-d H:i:s', strtotime($v['time2'].'+' . $wait_day . ' day'));
                $time = date('Y-m-d H:i:s', strtotime($v['time'].'+' . $wait_day . ' day'));
                Db::name('LcInvest')->where('id', $v['id'])->update(['wait_num' => $v['wait_num']-1,'wait_interest' => $v['wait_interest']-$day_interest, 'time' => $time, 'time2' => $time2, 'time2_actual' => $time2]);
            }
            
            //利息
            addFunding($v['uid'],$day_interest,changeMoneyByLanguage($day_interest,$language),1,6,$language, 2);
            setNumber('LcUser', 'withdrawable', $day_interest, 1, "id = {$v['uid']}");
            
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

            // 添加返利
            if ($v['is_distribution']) {
                $fusers = Db::name("LcUserRelation")->alias('ur')->join('lc_user u', 'ur.parentid=u.mid')->join('lc_user_member um', 'um.id=u.mid')->where("ur.uid = {$v['uid']}")->limit(3)->select();
                $fusers = array_reverse($fusers); 
                foreach($fusers as $key => $val) {
                    $level = '';
                    switch ($key) {
                        case 1:
                            $level = $val['level_b'];
                            break;
                        case 2:
                            $level = $val['level_c'];
                            break;
                        case 3:
                            $level = $val['level_d'];
                            break;
                        
                    }
                    $interest_rate = floor($day_interest*$level*100) / 100;
                    // 添加收益
                    setNumber('LcUser', 'withdrawable', $interest_rate, 2, "id = {$val['parentid']}");
                    // 添加总收益
                    setNumber('LcUser', 'income', $interest_rate, 1, "id = {$val['parentid']}");
                    //流水添加
                    addFunding($val['parentid'],$interest_rate,changeMoneyByLanguage($interest_rate,$language),2,19,$language);
                }
            }
            
            //利息
            addFunding($v['uid'],$total_interest,changeMoneyByLanguage($total_interest,$language),1,6,$language, 2);
            setNumber('LcUser', 'withdrawable', $total_interest, 1, "id = {$v['uid']}");
            
            //本金
            addFunding($v['uid'],$v['money2'],changeMoneyByLanguage($v['money2'],$language),1,15,$language);
            setNumber('LcUser', 'money', $v['money2'], 1, "id = {$v['uid']}");
            
            
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
            $this->error('error');
        }
        $this->success('success');
    }
}
