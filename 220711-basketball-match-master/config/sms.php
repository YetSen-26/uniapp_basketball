<?php
return [
    // HTTP 请求的超时时间（秒）
    'timeout' => 5.0,

    // 默认发送配置
    'default' => [
        // 网关调用策略，默认：顺序调用
        'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

        // 默认可用的发送网关
        'gateways' => [
            'aliyun',
        ],
    ],
    // 可用的网关配置
    'gateways' => [
        'aliyun' => [
            'access_key_id' => 'LTAI4GHHjHo7w2ReZgMfEaD5',
            'access_key_secret' => 'uQdOHw8ZaozRNk7PRY9TYQILP9BfH8',
            'sign_name' => '肥城市小东广告',
        ],
    ],
];
