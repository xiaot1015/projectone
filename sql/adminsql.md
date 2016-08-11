### 后台表配置

* 头条表

```sql```
CREATE TABLE `tb_msg` ( 
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID', 
  `type` int(1) DEFAULT '0' COMMENT '消息类型 0 动态消息 1 头条消息 2 展览 3 获奖名单', 
  `title` varchar(255) DEFAULT '' COMMENT '标题', 
  `introduction` varchar(255) NULL DEFAULT '0' COMMENT '简介', 
  `auther` varchar(255) NULL DEFAULT '0' COMMENT '作者', 
  `thumb_img` varchar(1000) NULL DEFAULT '' COMMENT '缩略图地址，json格式',
  `video_url` varchar(200) NULL DEFAULT '' COMMENT '视频地址',
  `ctime` datetime  NULL COMMENT '插入时间', 
  `content` text NULL comment '内容', 
  `status` int(1) NULL DEFAULT '0' comment '状态', 
  PRIMARY KEY (`id`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 
```

* 轮播图

```sql```
CREATE TABLE `tb_scroll_img` ( 
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID', 
  `imgs` varchar(1000) NULL DEFAULT '' COMMENT '轮播图地址，json格式',
  `title` varchar(200) NULL DEFAULT '' COMMENT '图标题',
  `ctime` datetime  NULL COMMENT '插入时间',
  `status` int(1) NULL DEFAULT '0' comment '状态', 
  PRIMARY KEY (`id`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 
```


