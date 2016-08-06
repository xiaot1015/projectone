<?php

return array(
//'配置项'=>'配置值'
    'DB_TYPE' => 'mysqli',
    'DB_HOST' => 'localhost',
    'DB_PORT' => 3306,
    'DB_USER' => 'root',
    'DB_PWD' => '123456',
    'DB_CHARSET' => 'utf8',
    'DB_NAME' => 'hy',
    'DB_PREFIX' => 'hy_',
    'TOKEN_ON' => true,
    'TMPL_TEMPLATE_SUFFIX' => '.phtml',
    'URL_MODEL' => 1, //pathinfo模式
    'URL_CASE_INSENSITIVE' => true, //设置url小写
    'TOKEN_ON' => false,
    'ADMIN_URL' => $_SERVER['host'] . '/admin.php', //后台入口
    'API' => '/data1/www/virtzh/api.php',
    'linuxvm' => 'att_linux', // linux 攻击机
    'windowsvm' => 'att_windows', // windows 攻击机
);
?>
