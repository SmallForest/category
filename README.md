# category
## sql语句
```SQL
CREATE TABLE `category_optimization` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0' COMMENT '父级的id',
  `name` varchar(255) DEFAULT '' COMMENT '分类名字',
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '是否被删除：0未删除 1已经删除',
  `level_str` varchar(2000) DEFAULT '' COMMENT '级别字符串',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```