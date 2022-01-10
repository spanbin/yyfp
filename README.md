# 用友税务云SDK
基于ThinkPHP用友税务云SDK

## 安装
> composer require spanbin/yyfp

## 配置
```
// 测试环境yyfp配置
'yyfp' => [
    // 应用id
    'appId' => 'commontesterCA',
    // 接口域名
    'domain' => 'https://yesfp.yonyoucloud.com',
    // 接口证书
    'certificate' => __DIR__.'/pro22.pfx',
    // 证书密码
    'password' => 'password'
]

// 正式环境yyfp配置
'yyfp' => [
    // 应用id
    'appId' => '应用id',
    // 接口域名
    'domain' => 'https://tax.diwork.com',
    // 接口证书
    'certificate' => '申请到的证书路径',
    // 证书密码
    'password' => '申请到的证书密码'
]
```

## 使用
```
use spanbin\yyfp\Yyfp;

$yyfp = new Yyfp();

$fpqqlsh = 'SHTEST'.time();
$requestdatas = array(
    array(
        'FPQQLSH' => $fpqqlsh,
        'FPLX' => 4,
        'XSF_NSRSBH' => '201609140000001',
        'ORGCODE' => '20160914001',
        'GMF_MC' => '天津国联鸿泰科技有限公司',
        'GMF_DZDH' => '天津市河北区王串场街王串场四号路4号增19号 86-022-84847456',
        'GMF_YHZH' => '中国建设银行股份有限公司天津河北支行 12050166080000000517',
        'GMF_NSRSBH' => '11111111111',
        'ZDYBZ' => '这是放射所报名费xx单号的开票',
        'JSHJ' => 100.00,
        'items' => array(
            array(
                'XMMC' => '技术服务费',
                'SPBM' => '3040101',
                'XMJSHJ' => 100.00,
                'SL' => 0.13,
            )
        )
    )
);
$url = array(
    array(
        'fpqqlsh' => $fpqqlsh,
        'url' => 'https://github.com/spanbin/yyfp'
    )
);
$email = array(
      array(
      'fpqqlsh' => $fpqqlsh,
      'address' => 'shengjianbin@gmail.com'
      ) 
);
$params = array(
    'requestdatas' => json_encode($requestdatas),
    'email' => json_encode($email),          
    'url' => json_encode($url),
    'autoAudit' => 'true'
);

 echo '<pre/>';

$res = $yyfp->insertWithArray($params);
print_r($res);

$params = [
    'fpqqlsh' => $fpqqlsh
];

$res = $yyfp->queryInvoiceStatus($params);
print_r($res);

```

