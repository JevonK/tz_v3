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
use library\File;
use think\facade\Session;
use library\tools\Data;
use think\Image;
use think\facade\Cache;
use library\service\CaptchaService;

/**
 * 首页
 * Class Index
 * @package app\index\controller
 */
class Index extends Controller
{
    /**
     * Describe:网站配置
     * DateTime: 2022/3/15 1:08
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function webconfig()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $info = Db::name('LcInfo')->find(1);
        
        if(empty($params["reload"])){
            if($info['auto_lang']){
                $language = getLanguageByIp($this->request->ip());
            }
        }
        $currency = Db::name('LcCurrency')->where(['country' => $language])->find();
        
        $data = array(
            "webname" => $info['webname'],
            "currency" => $currency['symbol'],
            "language" => $currency['country'],
            "language_logo" => $currency['logo'],
            "precision" => $currency['precision'],
            "logo2" => $info['logo_img2'],
            "logo" => $info['logo_img']
        );
        $this->success("success", $data);
    }
    /**
     * Describe:网站信息
     * DateTime: 2022/3/15 1:08
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getWebInfo()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $info = Db::name('LcInfo')->find(1);
        
        $data = array(
            "logo" => $info['logo_img'],
            "invite_code" => $info['invite_code'],
            "register_phone" => $info['phone_register']
        );
        $this->success("success", $data);
    }
    /**
     * Describe:首页初始化
     * DateTime: 2022/3/15 1:08
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function int()
    {
        $params = $this->request->param();
        $language = $params["language"];
        
        $version = Db::name('LcVersion')->field("app_name as name ,down_url as url , app_logo as logo,show")->find(1);
        
        $notice = Db::name('LcNotice')->field("id,title_$language as title")->where(['show' => 1,'type' =>1])->order('sort asc')->find();
        
        $banner = Db::name('LcSlide')->field("$language as img,url")->where(['show' => 1,'type' =>0])->order('sort asc,id desc')->select();
       
        $popup = Db::name('LcPopup')->field("content_$language as content,show,num")->find(1);
        
        $langs = Db::name('LcCurrency')->field("logo,symbol,price")->where(['show' => 1])->order('sort asc,id desc')->select();
        
        $items = Db::name('LcItem')->field("title_$language as title,img2,min,day,rate,id,type")->where(['show' => 1])->order('sort asc,id desc')->limit(10)->select();

        $news = Db::name('LcArticle')->where("type=13 and `show`=1")->order('release_time desc')->field("*, date_format(release_time, '%d %M %Y · %H:%i') as release_time")->select();
        // foreach ($items as &$item) {
        //     $item['min'] = changeMoneyByLanguage($item['min'],$language);
        //     $item['max'] = changeMoneyByLanguage($item['max'],$language);
        // }
        
        $data = array(
            'notice' => $notice,
            'banner' => $banner,
            'popup' => $popup,
            'items' => $items,
            'langs' => $langs,
            'news'  => $news,
            "version" => $version
        );
        $this->success("success", $data);
    }
    /**
     * @description：语言列表
     * @date: 2022/3/15 0004
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLanguages()
    {
        $params = $this->request->param();
        $list = Db::name('LcCurrency')->field("country,country_loc,logo,country_code")->where(['show' => 1])->order('sort asc,id desc')->select();
        $data = array(
            'list' => $list
            );
        $this->success("success", $data);
    }
    /**
     * Describe:切换语言
     * DateTime: 2022/3/15 1:08
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function changeLang()
    {
        $params = $this->request->param();
        $language = $params["language"];
        
        $currency = Db::name('LcCurrency')->where(['country' => $language])->find();
        
        $data = array(
            "currency" => $currency['symbol'],
            "language_logo" => $currency['logo'],
            "precision" => $currency['precision']
        );
        $this->success("success", $data);
    }
    /**
     * @description：货币价格
     * @date: 2022/8/15 
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCurrencyPrice()
    {
        $params = $this->request->param();
        $list = Db::name('LcCurrency')->field("logo,symbol,price")->where(['show' => 1])->order('sort asc,id desc')->select();
        $data = array(
            'list' => $list
            );
        $this->success("success", $data);
    }
    /**
     * @description：
     * @date: 2022/3/15 0015
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function login()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $language = $params["language"];
            $ip = $this->request->ip();
            
            //判断参数
            if(empty($params['username'])||empty($params['password'])||empty($params['code'])) $this->error('utils.parameterError',"",218);
            //判断用户名(4-16数字、字母、下划线)
            if (!judge($params['username'],"username")) $this->error('utils.parameterError',"",218);
            if (strlen($params['username']) < 4 || 16 < strlen($params['username'])) $this->error('utils.parameterError',"",218);
            
            //判断密码(6-16数字、字母、下划线)
            if (!judge($params['password'],"username")) $this->error('utils.parameterError',"",218);
            if (strlen($params['password']) < 6 || 16 < strlen($params['password'])) $this->error('utils.parameterError',"",218);
            
            //校验验证码
            $this->type = input('login_captcha_type'.$ip, 'login_captcha_type'.$ip);
            if(strtolower(Cache::get($this->type)) != strtolower($params['code'])) $this->error('login.codeError',"",218);
            
            $user = Db::name('LcUser')->where(['username' => $params['username']])->find();
            //用户不存在
            if (!$user) $this->error('login.usernameError',"",218);
            //密码错误
            if ($user['password'] != md5($params['password'])) $this->error('login.passwordError',"",218);
            //用户被锁定
            if ($user['clock'] == 1) $this->error('login.userLocked',"",218);
            
            $time = date('Y-m-d H:i:s');
            $time_zone = getTimezoneByLanguage($language);
            $act_time = dateTimeChangeByZone($time, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
            
            Db::name('LcUser')->where(['id' => $user['id']])->update(['logintime' => $act_time]);
            $result = array(
                'token' => $this->getToken(['id' => $user['id'], 'username' => $user['username']]),
            );
            $this->success("success", $result);
        }
    }
    /**
     * @description：注册
     * @date: 2022/3/15 0015
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function register()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            $language = $params["language"];
            $info = Db::name('LcInfo')->find(1);
            $reward = Db::name('LcReward')->find(1);
            $draw = Db::name('LcDraw')->find(1);
            $ip = $this->request->ip();
            
            $username = $params['username'];
            
            $auth_phone = 0;
            $phone_="";
            
            $phone = $params['phone'];
            $country_code = $params["country_code"];
            $phone_code = $country_code.$phone;//拼接国家区号后的手机号
            //手机号注册
            if($info['phone_register']){
                //判断参数
                if(empty($params['phone'])||empty($params['password'])||empty($params['smsCode'])) $this->error('utils.parameterError',"",218);
                
                //判断手机号是否正确
                if (!judge($params['phone'],"mobile_phone")) $this->error('auth.phoneError',"",218);
                
                //判断手机号是否被注册
                if (Db::name('LcUser')->where(['username' => $params['phone']])->find()) $this->error('register.phoneExists',"",218);
                
                //判断手机号是否被绑定
                if (Db::name('LcUser')->where(['phone' => $params['phone']])->find()) $this->error('register.phoneExists',"",218);
                
                $sms_code = Db::name("LcSmsCode")->where("phone = '$phone_code'")->order("id desc")->find();
                //判断验证码是否获取
                if(!$sms_code) $this->error('auth.codeFirst',"",218);
                //判断验证码是否过期
                if ((strtotime($sms_code['time']) + 300) < time()) $this->error('auth.codeExpired',"",218);
                //判断验证码
                if ($params['smsCode'] != $sms_code['code']) $this->error('auth.codeError',"",218);
                
                $auth_phone = 1;
                $username = $phone;
                $phone_ = $phone;
            }
            //非手机号注册
            else{
                //判断参数
                if(empty($params['username'])||empty($params['password'])||empty($params['code'])) $this->error('utils.parameterError',"",218);
                
                //判断用户名(4-16数字、字母、下划线)
                if (!judge($params['username'],"username")) $this->error('utils.parameterError',"",218);
                if (strlen($params['username']) < 4 || 16 < strlen($params['username'])) $this->error('utils.parameterError',"",218);
                
                //校验验证码
                $this->type = input('login_captcha_type'.$ip, 'login_captcha_type'.$ip);
                if(strtolower(Cache::get($this->type)) != strtolower($params['code'])) $this->error('login.codeError',"",218);
                
                //判断用户名是否被注册
                if (Db::name('LcUser')->where(['username' => $params['username']])->find()) $this->error('register.usernameExists',"",218);
            }
            
            //邀请码为必填时需填写邀请码
            if($info['invite_code']&&empty($params['invite_code'])) $this->error('utils.parameterError',"",218);
            
            //判断密码(6-16数字、字母、下划线)
            if (!judge($params['password'],"username")) $this->error('utils.parameterError',"",218);
            if (strlen($params['password']) < 6 || 16 < strlen($params['password'])) $this->error('utils.parameterError',"",218);
            
            //判断ip限制
            if (Db::name('LcUser')->where(['ip' => $this->request->ip()])->count()>=$info['num_ip']) $this->error('register.ipLimit',"",218);

            // 判断如果为系统用户是
            $system_user_id = 10000;
            if ($params['is_system'] == 1) {
                $system_user_id =  Db::name('SystemUser')->where(['invite_code' => $params['invite_code']])->value('id') ?? 0;
                unset($params['invite_code']);
            }
            
            //判断邀请码
            $topUser = [];
            if(!empty($params['invite_code'])){
                $topUser = Db::name('LcUser')->where(['invite_code' => $params['invite_code']])->find();
                if(empty($topUser)) $this->error('register.inviteCodeError',"",218);
                if(empty($topUser['is_invite'])) $this->error('This invitation code is forbidden to be invited',"",218);
                $system_user_id = $topUser['system_user_id'];
            }
            
            //时区转换
            $time = date('Y-m-d H:i:s');
            $time_zone = getTimezoneByLanguage($language);
            $act_time = dateTimeChangeByZone($time, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
            
            //设置会员初始等级
            $vip = Db::name('LcUserMember')->order('value asc')->find();

            $add = array(
                'mid' => $vip['id'],
                'username' => $username,
                'password' => md5($params['password']),
                'auth_phone' => $auth_phone,
                'phone' => $phone_,
                'logintime' => $act_time,
                'time' => $time,
                "time_zone" =>$time_zone,
                "act_time" =>$act_time,
                "system_user_id" =>$system_user_id,
                'ip' => $this->request->ip(),
            );
            $uid = Db::name('LcUser')->insertGetId($add);
            if (empty($uid)) $this->error('register.registerFail',"",218);
            //创建邀请码
            Db::name('LcUser')->where(['id' => $uid])->update(['invite_code' => createCode($uid)]);
            //创建用户关系
            if(!empty($params['invite_code'])){
                //插入邀请人关系网数据
                insertUserRelation($uid,$topUser['id'],$info['invite_level']);
            }
            //注册奖励
            if($reward['register']>0){
                //流水添加
                addFunding($uid,$reward['register'],changeMoneyByLanguage($reward['register'],$language),1,7,$language);
                //添加余额
                setNumber('LcUser', 'money', $reward['register'], 1, "id = $uid");
                //添加冻结金额
                if(getInfo('reward_need_flow')){
                    setNumber('LcUser', 'frozen_money', $reward['register'], 1, "id = $uid");
                }
            }
            // 注册奖励送产品
            if ($reward['item_id'] > 0) {
                //时区转换
                $item = Db::name('LcItem')->find($reward['item_id']);
                $money_usd = $item['min'];
                $time = date('Y-m-d H:i:s');
                $time_zone = getTimezoneByLanguage($language);
                $time_actual = dateTimeChangeByZone($time, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
                $currency = getCurrencyByLanguage($language);
            
                $time2 = date('Y-m-d H:i:s', strtotime($time.'+' . $item['day'] . ' day'));
                $total_interest = $money_usd * $item['rate'] / 100;
                $total_num = 1;
                $time2_actual = dateTimeChangeByZone($time2, 'Asia/Shanghai', $time_zone, 'Y-m-d H:i:s');
                $orderNo = 'ST' . date('YmdHis') . rand(1000, 9999) . rand(100, 999);
                //到期还本付息（时）
                if($item['type']==3){
                    //按时
                    $time2 = date('Y-m-d H:i:s', strtotime($time.'+' . $item['day'] . ' hour'));
                }
                //每日付息到期还本
                elseif($item['type']==1 || $item['type']==4){
                    //日利率
                    $total_interest = $money_usd * $item['rate'] * $item['day'] / 100;
                    //返息期数
                    $total_num = $item['day'];
                }
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
                    "is_draw" => 1,
                    "source" => 3,
                    "not_receive" =>$item['not_receive'],
                    "is_distribution" =>$item['is_distribution'],
                    "currency" =>$currency,
                    "time_zone" =>$time_zone,
                    "time" =>$time,
                    "time_actual" =>$time_actual,
                    "time2" =>$time2,
                    "time2_actual" =>$time2_actual,
                );
                
                Db::name('LcInvest')->insertGetId($insert);
            }
            //邀请奖励
            if(!empty($params['invite_code'])){
                $topUserId = $topUser['id'];
                if($reward['invite']>0){
                    //流水添加
                    addFunding($topUserId,$reward['invite'],changeMoneyByLanguage($reward['invite'],$language),1,8,$language);
                    //添加余额
                    setNumber('LcUser', 'money', $reward['invite'], 1, "id = $topUserId");
                    //添加冻结金额
                    if(getInfo('reward_need_flow')){
                        setNumber('LcUser', 'frozen_money', $reward['invite'], 1, "id = $topUserId");
                    }
                }
                //添加抽奖次数
                if($draw['invite']>0){
                    setNumber('LcUser', 'draw_num', $draw['invite'], 1, "id = $topUserId");
                }
            }
            //添加抽奖次数
            if($draw['register']>0){
                setNumber('LcUser', 'draw_num', $draw['register'], 1, "id = $uid");
            }
            
            $data = array(
                'token' => $this->getToken(['id' => $uid, 'username' => $params['username']])
            );
            $this->success("success", $data);
        }
    }
    /**
     * @description：验证邮箱
     * @date: 2020/6/2 0002
     */
    public function auth_email_code()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $this->checkToken($language);
        //判断参数
        if(empty($params['email'])) $this->error('utils.parameterError',"",218);
        //判断邮箱是否正确
        if (!judge($params['email'],"email")) $this->error('authEmail.emailError',"",218);
        
        $userInfo = $this->userInfo;
        $user = Db::name('LcUser')->find($userInfo['id']);
        $email = $params['email'];
        //判断是否验证过
        if ($user['auth'] == 1) $this->error('authEmail.authed',"",218);
        
        $sms_time = Db::name("LcSmsList")->where("phone = '$email'")->order("id desc")->value('time');
        if ($sms_time && (strtotime($sms_time) + 300) > time()) $this->error('authEmail.codeValid',"",218);
        
        $rand_code = rand(1000, 9999);
        
        Session::set('authSmsCode', $rand_code);
        
        $msg =Db::name('LcTips')->where(['id' => '1'])->value($language).$rand_code.Db::name('LcTips')->where(['id' => '2'])->value($language);
        
        $data = array('phone' => $email, 'msg' => $msg, 'code' => "验证邮箱", 'time' => date('Y-m-d H:i:s'), 'ip' => $rand_code);
        
        if(!$this->sendMail($email,Db::name('LcTips')->where(['id' => '3'])->value($language),$msg)) $this->error('authEmail.sendFail',"",218);
        
        Db::name('LcSmsList')->insert($data);
        
        $this->success("success");
    }
    
    /**
     * @description：客服列表
     * @date: 2022/3/15 0004
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function service_list()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $login = $this->checkLogin();
        $system_user_id = 10000;
        if ($login) {
            $uid = $this->userInfo['id'];
            $user = Db::name("LcUser")->find($uid);
            $system_user_id = $user['system_user_id'];
        }
        
        $list = Db::name('LcService')->field("id,title_$language as title,logo,url,type")->where(['show' => 1, 'system_user_id' => $system_user_id])->order('sort asc,id desc')->select();
        $data = array(
            'list' => $list
            );
        $this->success("success", $data);
    }
    
    /**
     * @description：客服详情
     * @date: 2022/3/15 0004
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function service_detail()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $service = Db::name('LcService')->field('url,type')->where(['show' => 1])->find($params["id"]);
        $data = array(
            'service' => $service
        );
        $this->success("success", $data);
    }
    /**
     * @description：通知栏列表
     * @date: 2022/7/15 0004
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function notice_list()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $page = $params["page"];
        $listRows = $params["listRows"];
        
        $list = Db::name('LcNotice')->field("id,title_$language as title,content_$language as content,time")->where(['show' => 1])->order('sort asc,id desc')->page($page,$listRows)->select();
        $length = Db::name('LcNotice')->field("id,title_$language as title,content_$language as content,time")->where(['show' => 1])->order('sort asc,id desc')->count();
        
        $data = array(
            'list' => $list,
            'length' => $length
            );
        $this->success("success", $data);
    }
    /**
     * Describe:常见问题列表
     * DateTime: 2022/3/15 1:22
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function questions()
    {
     $params = $this->request->param();
        $language = $params["language"];
        $articles = Db::name('LcArticle')->field("id,title_$language as title")->where(['show' => 1, 'type' => 11])->order('sort asc,id desc')->select();
        $this->success("success", ['list' => $articles]);
    }
    
    /**
     * @description：项目分类
     * @date: 2022/8/15
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function item_class()
    {
        $params = $this->request->param();
        $language = $params["language"];
        
        $classes = Db::name('LcItemClass')->field("id,$language as title")->order('sort asc,id desc')->select();
        $this->success("success", ['classes' => $classes]);
        
        
    }
    
    /**
     * @description：项目列表
     * @date: 2022/8/15
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function item_list()
    {
        $params = $this->request->param();
        $language = $params["language"];
        
        $page = $params["page"];
        $listRows = $params["listRows"];
        
        $cid = $params["type"];
        
        $where = '';
        if($cid!=0){
            $where = "cid=$cid";
        }
        
        $items = Db::name('LcItem')->field("title_$language as title,content_$language as content,id,img2 as img,min,day,rate,type")->where(['show' => 1])->where($where)->order('sort asc,id desc')->page($page,$listRows)->select();
        
        $length = Db::name('LcItem')->field("title_$language as title,content_$language as content,id,img2 as img,min,day,rate,type")->where(['show' => 1])->where($where)->order('sort asc,id desc')->count();
        
        // foreach ($items as &$item) {
        //     $item['min'] = changeMoneyByLanguage($item['min'],$language);
        //     $item['max'] = changeMoneyByLanguage($item['max'],$language);
        // }
        
        $data = array(
            'list' => $items,
            'length' => $length
        );
        $this->success("success", $data);
    }
    
    /**
     * @description：项目详情
     * @date: 2022/8/15
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function item_detail()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $id = $params["id"];
        
        $item = Db::name('LcItem')->field("title_$language as title,content_$language as content,id,img2 as img,min,day,rate,num,type,k_x,k_y_12m,withdrawal_purchase")->where(['show' => 1])->find($id);
        
        if(empty($item)) $this->error('utils.parameterError',"",218);
        // $item['min'] = changeMoneyByLanguage($item['min'],$language);
        // $item['max'] = changeMoneyByLanguage($item['max'],$language);
        
        $item['k_x'] = array_map('floatval', explode(",",$item['k_x']));
        $item['k_y_12m'] = explode(",",$item['k_y_12m']);
        
        //判断用户登录状态
        $login = $this->checkLogin();
        $user=array(
            "login"=>false,
            "limit"=>false,
            "limit_today"=>false
        );
        if($login){
            $uid = $this->userInfo['id'];
            $user1 = Db::name('LcUser')->find($uid);
            $user['balance'] = $user1['money'];
            $user['withdrawable'] = $user1['withdrawable'];
            $user['login'] = true;
            //判断项目投资次数
            $investCount = Db::name('LcInvest')->where(['itemid' => $id,'uid' => $uid])->count();
            if($investCount>=$item['num']){
                $user['limit'] = true;
            }
            //判断会员投资次数
            $vip = Db::name("LcUserMember")->find($user1['mid']);
            $now = date('Y-m-d H:i:s');//现在
            $today = date('Y-m-d');//今天0点
            $investCountToday = Db::name('LcInvest')->where("uid = '$uid' AND time >= '$today' AND time <= '$now'")->count();
            if($investCountToday>=$vip['invest_num']){
                $user['limit_today'] = true;
            }
        }
        
        $data = array(
            'item' => $item,
            'user'=>$user
        );
        $this->success("success", $data);
    }
    
    /**
     * @description：商品列表
     * @date: 2023/4/15
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goods_list()
    {
        $params = $this->request->param();
        $language = $params["language"];
        
        $page = $params["page"];
        $listRows = $params["listRows"];
        
        
        $items = Db::name('LcGoods')->field("title_$language as title,id,img,point")->where(['show' => 1])->order('sort asc,id desc')->page($page,$listRows)->select();
        
        $length = Db::name('LcGoods')->field("title_$language as title,id,img,point")->where(['show' => 1])->order('sort asc,id desc')->count();
        
        //判断用户登录状态
        $login = $this->checkLogin();
        $user=array(
            "login"=>false,
            "point"=>0,
        );
        if($login){
            $uid = $this->userInfo['id'];
            $user1 = Db::name('LcUser')->find($uid);
            $user['login'] = true;
            $user['point'] = $user1['point'];;
        }
        
        $data = array(
            'list' => $items,
            'length' => $length,
            'user' => $user
        );
        $this->success("success", $data);
    }
    
    /**
     * @description：商品详情
     * @date: 2023/4/15
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goods_detail()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $id = $params["id"];
        $this->checkToken($language);
        $uid = $this->userInfo['id'];
        $user = Db::name('LcUser')->find($uid);
        
        $goods = Db::name('LcGoods')->field("title_$language as title,content_$language as content,id,img,point")->where(['show' => 1])->find($id);
        
        if(empty($goods)) $this->error('utils.parameterError',"",218);
        
        $user1=array(
            "point"=>$user['point'],
        );
        
        $data = array(
            'goods' => $goods,
            'user' => $user1
        );
        $this->success("success", $data);
    }
    
    

    /**
     * @description：活动列表
     * @date: 2022/3/15 0004
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function activity_list()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $list = Db::name('LcActivity')->field("id,title_$language,desc_$language,img_$language,url,time")->where(['show' => 1])->order('sort asc,id desc')->select();
        $data = array(
            'list' => $list
            );
        $this->success("success", $data);
    }
    /**
     * @description：活动详情
     * @date: 2022/3/15 0004
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function activity_detail()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $activity = Db::name('LcActivity')->where(['show' => 1])->find($params["id"]);
        $data = array(
            'title' => $activity["title_$language"],
            'content' => $activity["content_$language"],
            'time'  => $activity["time"]
        );
        $this->success("success", $data);
    }


    /**
     * Describe:关于我们列表
     * DateTime: 2022/3/15 1:22
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function about()
    {
        $article = Db::name('LcArticle')->where(['show' => 1, 'type' => 9])->order('sort asc,id desc')->select();
        $this->success("success", ['list' => $article]);
    }
    


    /**
     * Describe:文章详情
     * DateTime: 2022/3/15 1:22
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function article_detail()
    {
        $params = $this->request->param();
        $language = $params["language"];
        $id = $this->request->param('id');
        $article = Db::name('LcArticle')->field("title_$language,content_$language")->find($id);
        Db::name('LcArticle')->where("id=$id")->setInc('read_num');
        $data = array(
            'title' => $article["title_$language"],
            'content' => $article["content_$language"]
            );
        $this->success("success", $data);
    }

    

     /**
     * 生成验证码
     * 需要指定类型及令牌
     */
    public function captcha()
    {
        $ip = $this->request->ip();
        $image = CaptchaService::instance();
        $this->type = input('login_captcha_type'.$ip, 'login_captcha_type'.$ip);
        $this->token = input('login_captcha_token'.$ip, 'login_captcha_token'.$ip);
        $captcha = ['image' => $image->getData(), 'uniqid' => $image->getUniqid()];
        Cache::set($this->type, $image->getCode());
        $this->success('success', $captcha);
    }

}
