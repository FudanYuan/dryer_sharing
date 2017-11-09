<?php
include_once('../common/database/dbInfo.php');
$dbInfo = new dbInfo();
$conn = new mysqli($dbInfo::HOST, $dbInfo::USER, $dbInfo::PWD);
$conn->set_charset("utf8");
if ($conn->connect_error) {
    die("连接失败:" . $conn->connect_error);
} else {
    echo "连接成功<br/>";
}
// 创建数据库
$sql = "create database " . $dbInfo::DBNAME . " default character set utf8 collate utf8_general_ci;";
$conn->query("SET NAMES 'utf8'");
if ($conn->query($sql) === true) {
    echo "数据库创建成功<br/>";
}

// 连接数据库
$conn = new mysqli($dbInfo::HOST, $dbInfo::USER, $dbInfo::PWD, $dbInfo::DBNAME);
if ($conn->connect_error) {
    die("连接失败:" . $conn->connect_error);
}

// 干洗设备信息表
$machine_info = "create table device_info          
(
dev_id INT NOT NULL AUTO_INCREMENT COMMENT '设备序列号，自增',
mac_addr CHAR(12) NOT NULL COMMENT '干衣机mac地址',
balance SMALLINT DEFAULT 0 COMMENT '干衣机剩余时间，以分钟为单位',
status TINYINT DEFAULT 0 COMMENT '干衣机状态。状态说明：0->待机；1->运行；2->故障',
QR_code CHAR(50) COMMENT '支付二维码地址',
allo_status TINYINT DEFAULT 0 COMMENT '分配状态。状态说明：0->未分配；1->已分配',
PRIMARY KEY(dev_id, mac_addr)
)";
if ($conn->query($machine_info) === true) {
    echo "干洗设备信息表创建成功<br/>";
} else {
    echo "干洗设备信息表创建失败<br/>";
}

// 省份信息
$province_info = "create table province      
(
pro_id TINYINT AUTO_INCREMENT PRIMARY KEY COMMENT '省份编号',
pro_name VARCHAR(32) NOT NULL COMMENT '省份名称'
)";
if ($conn->query($province_info) === true) {
    echo "省份信息表创建成功<br/>";
} else {
    echo "省份信息表创建失败<br/>";
}

// 城市信息
$city_info = "create table city      
(
city_id SMALLINT AUTO_INCREMENT PRIMARY KEY COMMENT '合作商家所在城市编号',
pro_id TINYINT NOT NULL COMMENT '省份编号，外键（province->pro_id）',
city_name VARCHAR(32) NOT NULL COMMENT '城市名称',
FOREIGN KEY (pro_id) REFERENCES province(pro_id)
)";
if ($conn->query($city_info) === true) {
    echo "城市信息表创建成功<br/>";
} else {
    echo "城市信息表创建失败<br/>";
}

// 价格分档表
$fee_bracket = "create table fee_bracket
(
f_id SMALLINT AUTO_INCREMENT PRIMARY KEY COMMENT '价格编号，自增',
fee_1 TINYINT DEFAULT 8 COMMENT '第一档价格，默认8元',
fee_2 TINYINT DEFAULT 10 COMMENT '第二档价格，默认10元',
fee_add TINYINT DEFAULT 2 COMMENT '加时档价格，默认2元',
effect_s_date TIMESTAMP NOT NULL COMMENT '生效时间',
effect_e_date TIMESTAMP COMMENT '失效时间'
)";
if ($conn->query($fee_bracket) === true) {
    echo "价格分档表创建成功<br/>";
} else {
    echo "价格分档表创建失败<br/>";
}

// 时长分档表
$len_bracket = "create table len_bracket
(
l_id SMALLINT AUTO_INCREMENT PRIMARY KEY  COMMENT '时长编号，自增',
len_1 TINYINT DEFAULT 100 COMMENT '第一档时长，默认100分钟',
len_2 TINYINT DEFAULT 120 COMMENT '第二档时长，默认120分钟',
len_add TINYINT DEFAULT 20 COMMENT '加时档时长，默认20分钟',
effect_s_date TIMESTAMP NOT NULL COMMENT '生效时间',
effect_e_date TIMESTAMP COMMENT '失效时间'
)";
if ($conn->query($len_bracket) === true) {
    echo "时长分档表创建成功<br/>";
} else {
    echo "时长分档表创建失败<br/>";
}

// 商家信息表
$merchant_info = "create table merchant_info      
(
mer_id INT AUTO_INCREMENT COMMENT '商家序列号，自增',
mer_name VARCHAR(128) NOT NULL COMMENT '商家名称',
mer_sub_id SMALLINT NOT NULL COMMENT '商家分店编号',
city_id SMALLINT COMMENT '商家所在城市编号, 外键（city->city_id）',
addr_detail VARCHAR(128) NOT NULL COMMENT '合作商家具体地址',
f_id SMALLINT NOT NULL COMMENT '价格编号, 外键（fee_bracket->f_id）',
fee_rate_1 FLOAT(4,2) DEFAULT 0 COMMENT '第一档惠率，0.1代表商家下的干衣机单价优惠10%',
fee_rate_2 FLOAT(4,2) DEFAULT 0 COMMENT '第二档惠率，0.1代表商家下的干衣机单价优惠10%',
fee_rate_add FLOAT(4,2) DEFAULT 0 COMMENT '加时档惠率，0.1代表商家下的干衣机单价优惠10%',
l_id SMALLINT NOT NULL COMMENT '时长编号, 外键（len_bracket->l_id）',
brokerage FLOAT(4,2) DEFAULT 0 COMMENT '佣金，0.1代表给商家提成10%',
PRIMARY KEY (mer_id, mer_name, mer_sub_id),
FOREIGN KEY (city_id) REFERENCES city(city_id),
FOREIGN KEY (f_id) REFERENCES fee_bracket(f_id),
FOREIGN KEY (l_id) REFERENCES len_bracket(l_id)
)";
if ($conn->query($merchant_info) === true) {
    echo "合作商家信息表创建成功<br/>";
} else {
    echo "合作商家信息表创建失败<br/>";
}

// 干洗设备-合作商家表
$merchant_machine = "create table mer_dev     
(
mer_id INT COMMENT '商家id，外键（merchant_info->mer_id）',
dev_id INT COMMENT '设备id，外键（device_info->dev_id）',
PRIMARY KEY (mer_id, dev_id),
FOREIGN KEY (mer_id) REFERENCES merchant_info(mer_id),
FOREIGN KEY (dev_id) REFERENCES device_info(dev_id)
)";
if ($conn->query($merchant_machine) === true) {
    echo "干洗设备-合作商家表创建成功<br/>";
} else {
    echo "干洗设备-合作商家表创建失败<br/>";
}

// 折扣历史记录表
$discount_bak = "create table discount_bak     
(
mer_id INT COMMENT '商家id，外键（merchant_info->mer_id）',
effect_date TIMESTAMP NOT NULL COMMENT '生效时间',
brokerage FLOAT(4,2) DEFAULT 0 COMMENT '佣金',
PRIMARY KEY (mer_id, effect_date),
FOREIGN KEY (mer_id) REFERENCES merchant_info(mer_id)
)";
if ($conn->query($discount_bak) === true) {
    echo "折扣历史记录表创建成功<br/>";
} else {
    echo "折扣历史记录表创建失败<br/>";
}

// 消费记录表
$consume_rec = "create table consume_rec         
(
rec_id INT AUTO_INCREMENT PRIMARY KEY COMMENT '记录编号',
dev_id INT COMMENT '设备id，外键（device_info->dev_id）',
user_id VARCHAR(32) NOT NULL COMMENT '用户id（微信号）',
consume_t TIMESTAMP NOT NULL COMMENT '消费时间',
cost FLOAT(4,2) NOT NULL COMMENT '消费金额',
time_len SMALLINT NOT NULL COMMENT '购买时长（分钟）',
FOREIGN KEY (dev_id) REFERENCES device_info(dev_id)
)";
if ($conn->query($consume_rec) === true) {
    echo "消费记录表创建成功<br/>";
} else {
    echo "消费记录表创建失败<br/>";
}

// 讯鑫科技管理后台
$admin = "create table admin
(
username VARCHAR (32) PRIMARY KEY COMMENT '账号',
pwd VARCHAR (40) NOT NULL COMMENT '密码',
name VARCHAR (16) NOT NULL COMMENT '用户姓名',
phone VARCHAR (16) NOT NULL COMMENT '电话号码',
email VARCHAR (64) NOT NULL COMMENT '常用邮箱',
mer_id INT COMMENT '商户id，外键（merchant_info->mer_id）',
audit TINYINT DEFAULT 1 COMMENT '审核状态。状态说明：1->设置账户密码结束；2->填写公司信息结束；3->审核结束；4->审核失败',
FOREIGN KEY (mer_id) REFERENCES merchant_info(mer_id)
)";
if ($conn->query($admin) === true) {
    echo "讯鑫科技管理后台表创建成功<br/>";
} else {
    echo "讯鑫科技管理后台表创建失败<br/>";
}

// 统计视图
$statistic = "create view statistic as select consume_rec.dev_id, mer_dev.mer_id,
merchant_info.mer_name, merchant_info.mer_sub_id, province.pro_name,
city.city_name, consume_rec.consume_t, consume_rec.cost, consume_rec.time_len
from consume_rec, mer_dev, merchant_info, city, province where
consume_rec.dev_id = mer_dev.dev_id and
merchant_info.mer_id = mer_dev.mer_id and
merchant_info.city_id = city.city_id and 
province.pro_id = city.pro_id
";
if ($conn->query($statistic) === true) {
    echo "统计视图创建成功<br/>";
} else {
    echo "统计视图创建失败<br/>";
}

//  各商户价格-时长-惠率视图
$price_len_rate = "create view price_len_rate as select 
DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name, 
merchant_info.mer_id as mer_id,
fee_bracket.fee_1*(1-merchant_info.fee_rate_1) as fee_1,
fee_bracket.fee_2*(1-merchant_info.fee_rate_2) as fee_2,
fee_bracket.fee_add*(1-merchant_info.fee_rate_add) as fee_add,
len_bracket.len_1 as len_1, 
len_bracket.len_2 as len_2,
len_bracket.len_add as len_add,
merchant_info.fee_rate_1,
merchant_info.fee_rate_2,
merchant_info.fee_rate_add
from merchant_info, fee_bracket, len_bracket
where merchant_info.f_id = fee_bracket.f_id and 
merchant_info.l_id = len_bracket.l_id
";
if ($conn->query($price_len_rate) === true) {
    echo "价格视图创建成功<br/>";
} else {
    echo "价格视图创建失败<br/>";
}

// 甲方预设的价格-时长视图
$price_len_set = "create view price_len_set as select 
DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name,
fee_bracket.f_id, len_bracket.l_id, 
fee_bracket.fee_1, fee_bracket.fee_2, fee_bracket.fee_add,
len_bracket.len_1, len_bracket.len_2, len_bracket.len_add
from fee_bracket, len_bracket, merchant_info
where fee_bracket.f_id = (select max(fee_bracket.f_id) from fee_bracket) and
len_bracket.l_id = (select max(len_bracket.l_id) from len_bracket) and 
merchant_info.mer_name = '讯鑫科技'
";
if ($conn->query($price_len_set) === true) {
    echo "价格视图创建成功<br/>";
} else {
    echo "价格视图创建失败<br/>";
}

// 商家管理员视图
$admin_sys = "create view admin_mer as select admin.username, admin.pwd, admin.name,
admin.phone, admin.email, merchant_info.mer_name, merchant_info.mer_sub_id,
merchant_info.addr_detail from admin, merchant_info
where admin.mer_id = merchant_info.mer_id
";
if ($conn->query($admin_sys) === true) {
    echo "商家管理员视图视图表2创建成功<br/>";
} else {
    echo "商家管理员视图创建失败<br/>";
}

// 设备信息视图
$dev_info = "create view dev_info as select device_info.dev_id, 
device_info.mac_addr, device_info.allo_status, 
concat(mer_name, '_' ,mer_sub_id) as merchant_name,
pro_name, city_name, addr_detail, status 
from device_info, merchant_info, mer_dev, province, city 
where device_info.dev_id = mer_dev.dev_id and
merchant_info.mer_id = mer_dev.mer_id and
city.city_id = merchant_info.city_id and
province.pro_id = city.pro_id";
if ($conn->query($dev_info) === true) {
    echo "设备信息视图创建成功<br/>";
} else {
    echo "设备信息视图创建失败<br/>";
}
