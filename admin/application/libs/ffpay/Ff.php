<?php
namespace app\libs\ffpay;

class Ff {

    //XXX替换对应的国家对应的地址
    const HOST_URL = 'https://ffpay8.com'; //网关地址切换正式环境不需要替换
    const PAY_CODE = 'PKR601';    //订单中的支付编码

		
	//以下3个参数需要开启正式商户号后替换.
    public $password = 'YwIMtFOIecvQ'; //商户秘钥
	public $authorizationKey = '91592463';  //商户号
    //
    //创建收银台订单
    static public $oderReceive = self::HOST_URL . '/pay/index';
    //创建代付订单
    static public $oderOut = self::HOST_URL . '/dfpay/add';
    //代付订单查询
    static public $oderQuery = self::HOST_URL . '/dfpay/query';

    /**
     * 获取签名
     */
    public function getSign($data) {
        ksort($data);
        $str = $this->password.http_build_query($data , '' , '&').$this->password;
        return md5(md5($str));
    }

    /**post请求
     * @param string $url
     * @param array $data
     * @return false|string
     */
    public function curlPost($url = '', $data=null)
    {
        $ch = curl_init();//初始化
        curl_setopt($ch, CURLOPT_URL, $url);//访问的URL
        curl_setopt($ch, CURLOPT_POST, true);//请求方式为post请求
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//只获取页面内容，但不输出
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//https请求 不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//https请求 不验证HOST
        $header = [
            'Content-type: application/json;charset=UTF-8',
            'X-SN: '. $this->authorizationKey,
            'X-SECRET: '. $this->password
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));//请求数据
        $result = curl_exec($ch);//执行请求
        curl_close($ch);//关闭curl，释放资源
        return $result;
    }
}