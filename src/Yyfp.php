<?php

namespace spanbin\yyfp;

use \Firebase\JWT\JWT;
use Think\Log;

class Yyfp 
{
    // 接口配置
    private $config = [
        // 应用id
        'appId' => 'commontesterCA',
        // 接口域名
        'domain' => 'https://yesfp.yonyoucloud.com',
        // 接口证书
        'certificate' => __DIR__.'/pro22.pfx',
        // 证书密码
        'password' => 'password'
    ];

    // 构造函数
    public function __construct()
    {   
        // 如当前ThinkPHP配置存在
        if(C('yyfp')) {
            // 读取配置
            $config = C('yyfp');
            // 合并配置
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * 开票蓝票请求服务
     * @param array $params 入参
     * @return array 出参
     */
    public function insertWithArray($params = [])
    {
        return $this->exec('/invoiceclient-web/api/invoiceApply/insertWithArray', $params);
    }

    /**
     * 开票蓝票请求服务--发票拆分
     * @param array $params 入参
     * @return array 出参
     */
    public function insertWithSplit($params = [])
    {
        return $this->exec('/invoiceclient-web/api/invoiceApply/insertWithSplit', $params);
    }

    /**
     * 开票状态查询服务
     * @param array $params 入参
     * @return array 出参
     */
    public function queryInvoiceStatus($params = [])
    {
        return $this->exec('/invoiceclient-web/api/invoiceApply/queryInvoiceStatus', $params);
    }

    /**
     * 发票红冲请求服务
     * @param array $params 入参
     * @return array 出参
     */
    public function red($params = [])
    {
        return $this->exec('/invoiceclient-web/api/invoiceApply/red', $params);
    }

    /**
     * 开票申请审核通过
     * @param array $params 入参
     * @return array 出参
     */
    public function issue($params = [])
    {
        return $this->exec('/invoiceclient-web/api/invoiceApply/issue', $params);
    }

    /**
     * 电子发票部分红冲
     * @param array $params 入参
     * @return array 出参
     */
    public function partRed($params = [])
    {
        return $this->exec('/invoiceclient-web/api/invoiceApply/part-red', $params);
    }
    
    /**
     * 纸质发票作废
     * @param array $params 入参
     * @return array 出参
     */
    public function invalid($params = [])
    {
        return $this->exec('/invoiceclient-web/api/invoiceApply/invalid', $params);
    }

    /**
     * 获取专票
     * @param array $params 入参
     * @return array 出参
     */
    public function queryInvoice($params = [])
    {
        return $this->exec('/invoiceclient-web/api/vat/queryInvoice', $params, [
            'header' => [
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    /**
     * 发票勾选
     * @param array $params 入参
     * @return array 出参
     */
    public function gxtj($params = [])
    {
        return $this->exec('/invoiceclient-web/api/vat/gxtj', $params, [
            'header' => [
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    /**
     * 撤销勾选
     * @param array $params 入参
     * @return array 出参
     */
    public function cxgx($params = [])
    {
        return $this->exec('/invoiceclient-web/api/vat/cxgx', $params, [
            'header' => [
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    /**
     * 确认认证
     * @param array $params 入参
     * @return array 出参
     */
    public function qrrz($params = [])
    {
        return $this->exec('/invoiceclient-web/api/vat/qrrz', $params, [
            'header' => [
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    // 调用接口
    protected function exec($api, array $params, $options = [
        'header' => [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]
    ]) 
    {
        // 拼接接口地址
        $api = $this->config['domain'] . $api . "?appid=" . $this->config['appId'];
        // 记录日志
        Log::write('接口地址，'.$api,Log::INFO);
        // 添加签名header
        $options['header']['sign'] = $this->sign($params);
        // 发起post请求
        return $this->post($api, $params, $options);
    }
    
    // jwt签名
    private function sign(array $params)
    {
        $ts = time();
        $signParams = array(
            'sub' => 'tester',
            'iss' => 'einvoice',
            'aud' => 'einvoice',
            'jti' => $ts,
            'iat' => $ts,
            'exp' => $ts+300,
            'nbf' => $ts-300
        );
        // 需要将表单参数requestdatas的数据进行md5加密，然后放到签名数据的requestdatas中。
        // 此签名数据必须存在，否则在验证签名时会不通过。
        if(isset($params['requestdatas'])) $requestdatas = $params['requestdatas'];
        if(!empty($requestdatas)) {
            $signParams['requestdatas'] = md5($requestdatas);
        }
        // 读取CA证书与PEM格式证书需要根据实际证书使用情况而定,目前这两种都支持 
        $privateKey = $this->loadPrivateKeyOfCA($this->config['certificate']);
        // $privateKey = $this->loadPrivateKeyOfPem($this->config['certificate']);     
        $sign = JWT::encode($signParams, $privateKey, 'RS256');
        return $sign;
    }

    // 读取PEM编码格式
    private function loadPrivateKeyOfPem($file) 
    {
        if(!file_exists($file)) {
            throw new \Exception("Error: Key file $file is not exists.");
        }
        if(!$key = file_get_contents($file)) {
            throw new \Exception("Error: Key file $file is empty.");
        }
        return $key;
    }

    // 读取证书私钥
    private function loadPrivateKeyOfCA($file) 
    {
        if(!file_exists($file)) {
            throw new \Exception("Error: Cert file $file is not exists.");
        }
        if (!$cert_store = file_get_contents($file)) {
            throw new \Exception("Error: Unable to read the cert file $file .");
        }
        if (openssl_pkcs12_read($cert_store, $cert_info, $this->config['password'])) {
            return $cert_info['pkey'];
        } else {
            throw new \Exception("Error: Unable to read the cert store from $file .");
        }
    }

    // post请求
    private function post($url, $params, array $options=null) 
    {
        $ch = curl_init();
        $this->setOption($ch, $options);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, count($params));
        // 记录请求头
        Log::write('请求头：'.json_encode($options,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),Log::INFO);
        // 记录入参数
        Log::write('入参：'.json_encode($params,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),Log::INFO);
        // 如存在Content-Type参数
        if(isset($options['header']['Content-Type'])){
            // 处理入参
            switch($options['header']['Content-Type']){
                case 'application/json':
                    $params = json_encode($params,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
                break;

                case 'application/x-www-form-urlencoded':
                    $params = http_build_query($params);
                break;
            }
        }else{
            $params = http_build_query($params);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $content = curl_exec($ch);
        // 记录日志
        Log::write('出参：'.$content,Log::INFO);
        $errorCode = curl_errno($ch);
        curl_close($ch);
        return array($errorCode, $content);
    }

    // curl配置
    private function setOption($ch, array $options=null) 
    {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        if($options === null) {
            $options = array();
        }
        if(isset($options["cookie"]) && is_array($options["cookie"])) {
            $cookieArr = array();
            foreach($options["cookie"] as $key=>$value) {
                $cookieArr[] = "$key=$value";
            }
            $cookie = implode("; ", $cookieArr);
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        $timeout = 30;
        if(isset($options["timeout"])) {
            $timeout = $options["timeout"];
        }
        if(isset($options["ua"])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $options["ua"]);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        if(isset($options['header'])) {
            // 不显示响应头
            curl_setopt($ch, CURLOPT_HEADER, false);
            $header = array();
            foreach($options['header'] as $k=>$v) {
                $header[] = $k.": ".$v;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
    }
}