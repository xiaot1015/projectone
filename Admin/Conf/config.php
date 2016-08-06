<?php
return array(
    'DB_TYPE' => 'mysql',
    'DB_HOST' => 'localhost',
    'DB_PORT' => 3306,
    'DB_USER' => 'root',
    'DB_PWD' => '123456',
    'DB_CHARSET' => 'utf8',
    'DB_NAME' => 'hy',
    'DB_PREFIX' => 'hy_',
    'LAYOUT_ON' => true, // layout model open
    'LAYOUT_NAME' => 'layout', //define layout template name
    'DEFAULT_CHARSET' => 'utf-8',
    'URL_MODEL' => 1, //pathinfo模式
    'URL_CASE_INSENSITIVE' => true, //set all url-visited to lower case
    'URL_ROUTER_ON' => true, //开启路由
    'URL_ROUTE_RULES' => array(//定义路由规则
        ':user/school/index' => 'school/index',
        ':user/school/add' => 'school/add',
        ':user/academy/index' => 'academy/index',
        ':user/academy/add' => 'academy/add',
        ':user/major/index' => 'major/index',
        ':user/major/add' => 'major/add',
        ':user/grade/index' => 'grade/index',
        ':user/grade/add' => 'grade/add',
        ':user/class/index' => 'class/index',
        ':user/class/add' => 'class/add',
        ':user/group/index' => 'group/index',
        ':user/group/add' => 'group/add',
    ),

    'HOST_ADMIN' => 'http://test.honyanadc.com:89/',
    'DEFAULT_THEME' => 'default',
    'PAGE_NUMBER' => 10,
    'VM_URL'=>'http://test.honyanadc.com:89/virtzh/',
);
?>
