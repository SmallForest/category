<?php
include_once "vendor/autoload.php";
include_once "model/category.php";
use think\facade\Db;
Db::setConfig([
    // 默认数据连接标识
    'default'     => 'mysql',
    // 数据库连接信息
    'connections' => [
        'mysql' => [
            // 数据库类型
            'type'     => 'mysql',
            // 主机地址
            'hostname' => 'mysql01',
            // 用户名
            'username' => 'root',
            // 密码
            'password' => '123456',
            // 数据库名
            'database' => 'fenlei',
            // 数据库编码默认采用utf8
            'charset'  => 'utf8mb4',
            // 数据库表前缀
            'prefix'   => '',
            // 端口
            'hostport' => 3306,
            // 数据库调试模式
            'debug'    => true,
        ],
    ],
    // 关闭自动时间戳
    'datetime_format' => false,
]);
// 实例化category类
$category = new \model\category();
// 调用add方法
$id = $category->add(0, "顶级分类");
$id = $category->add($id, "二级分类");
$id = $category->add($id, "三级分类");
$id = $category->add($id, "四级分类");
$id = $category->add($id, "五级分类");
$id = $category->add(0, "顶级分类2");
$id = $category->add($id, "二级分类2");
$id = $category->add($id, "三级分类2");
$id = $category->add($id, "四级分类2");
$id = $category->add($id, "五级分类2");
// 打印id
var_dump($id);
//删除id=1所有子级
$category->deleteSons(1);
//将id=6的所有子级都挪动到1下面
$category->changePid(7,1);
//获取id=1的所有子级的数量
$count = $category->getSonCount(1);
var_dump($count);
//获取id=1的下一子级的数量
$count = $category->getNextSonCount(1);
var_dump($count);