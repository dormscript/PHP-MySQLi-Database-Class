<?php
include "Db\Mysqli.php";
include "Db\Table.php";
include "UserModel.php";

$dbConfig = array(
    'default' => array(
        'read'  => array(
            'host'   => '192.168.8.18',
            'user'   => 'root',
            'passwd' => 'gc7232275',
            'port'   => '',
            'db'     => 'test',
        ),
        'write' => array(
            'host'   => '192.168.8.18',
            'user'   => 'root',
            'passwd' => 'gc7232275',
            'port'   => '',
            'db'     => 'test',
        ),
    ),
    'product' => array(
        'read'  => array(
            'host'   => '192.168.8.18',
            'user'   => 'root',
            'passwd' => 'gc7232275',
            'port'   => '',
            'db'     => 'test',
        ),
        'write' => array(
            'host'   => '192.168.8.18',
            'user'   => 'root',
            'passwd' => 'gc7232275',
            'port'   => '',
            'db'     => 'test',
        ),
    ),
);
//设置数据库
Db\Table::setConfig($dbConfig);

$ob = new Db\UserModel();
 
//自定义SQl查询
$rs = $ob->query("select * from gongchanginfo.gc_company where cid in( ?, ?) and status = ? ", array(19, 30, 1));
print_r($rs);
 
//根据where组合查询
$rs = $ob->where("cid", array(24, 50), 'BETWEEN')
         //->where("province", 26)
         //->orwhere("city", 11)
         //->where("(cate1 = ? or cate2 in (?,?) )", array(11, 4,5))
         //->orderBy("cid", "asc")
         //->orderBy('addtime', 'asc')
         //->getCount();
         ->findAll(array("cid", "companyname"), array(5, 4))
         //->findOne(array('cid', 'companyname'))
;
//在findAll调用之前，可以调用test输出测试信息
//$ob->test();exit();

print_r($rs);
//exit();


//insert demo
$data = array(
    'companyname' => '测试企业名称',
    'uid'         => 23,
    'username'    => 'dodododod',
    'product'     => 'led|jpg|手机',
    'cate1'       => 133,
    'cate2'       => 1212,
    'website'     => 'www.demo.com',
    'addtime'     => '',
);
$b = $ob->insert($data);
var_dump($b); 

//update demo
$updata = array(
    'companyname' => "测试一个名字",
    'addtime' => '121111111111',
);
$a  = $ob->where("cid", $b)
    ->update($updata);
var_dump($a);

//find demo
$c = $ob->where("cid", $b)
    ->findAll();
print_r($c);
 

//delete demo
$d = $ob->where("cid", $b)
    ->delete();
//$ob->test();
var_dump($d);
 

//find demo
$e = $ob->where("cid", $b)
    ->findAll();
var_dump($e);

//echo $rs . "\n";

