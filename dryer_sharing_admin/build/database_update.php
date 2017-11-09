<?php
include_once('../common/database/dbInfo.php');
ini_set('date.timezone', 'Asia/Shanghai');
$dbInfo = new dbInfo();
$conn = new mysqli($dbInfo::HOST, $dbInfo::USER, $dbInfo::PWD);
$conn->set_charset("utf8");
if ($conn->connect_error) {
    die("连接失败:" . $conn->connect_error);
} else {
    echo "连接成功<br/>";
}

// 创建数据库
$sql = "DROP DATABASE IF EXISTS " . $dbInfo::DBNAME . ";";
if ($conn->query($sql) === true) {
    echo "数据库删除成功<br/>";
} else {
    echo "数据库删除失败<br/>";
    return;
}
$sql = "create database " . $dbInfo::DBNAME . " default character set utf8 collate utf8_general_ci;";
if ($conn->query($sql) === true) {
    echo "数据库创建成功<br/>";
} else {
    echo "数据库创建失败<br/>";
    return;
}

// 连接数据库
$conn = new mysqli($dbInfo::HOST, $dbInfo::USER, $dbInfo::PWD, $dbInfo::DBNAME);
if ($conn->connect_error) {
    die("连接失败:" . $conn->connect_error);
    return;
} else {
    echo "连接成功<br/>";
}

// 设置数据库字符集
$sql = "SET NAMES utf8";
if ($conn->query($sql) === true) {
    echo "数据库配置成功<br/>";
} else {
    echo "数据库配置失败<br/>";
    return;
}

// 干洗设备信息表
$device_info = "create table device_info          
(
dev_id INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '设备序列号，自增',
mac_addr CHAR(12) NOT NULL COMMENT '干衣机mac地址',
balance SMALLINT UNSIGNED DEFAULT 0 COMMENT '干衣机剩余时间，以分钟为单位',
status TINYINT(2) UNSIGNED DEFAULT 0 COMMENT '干衣机状态。状态说明：0->待机；1->运行；2->故障（如果为同一用户，在24h内则需要用户修复，即点击客户端的确认按钮；若不为同一用户，直接可付款）；',
QR_code CHAR(100) COMMENT '支付二维码地址',
allo_status TINYINT(1) UNSIGNED DEFAULT 0 COMMENT '分配状态。状态说明：0->未分配；1->已分配',
PRIMARY KEY(dev_id, mac_addr)
)DEFAULT CHARSET=utf8;";
if ($conn->query($device_info) === true) {
    echo "干洗设备信息表创建成功<br/>";
} else {
    echo "干洗设备信息表创建失败<br/>";
}

// 省份信息
$province_info = "create table province      
(
pro_id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '省份编号',
pro_name VARCHAR(32) NOT NULL COMMENT '省份名称'
)DEFAULT CHARSET=utf8;";
if ($conn->query($province_info) === true) {
    echo "省份信息表创建成功<br/>";
} else {
    echo "省份信息表创建失败<br/>";
}

// 城市信息
$city_info = "create table city      
(
city_id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '合作商家所在城市编号',
pro_id SMALLINT UNSIGNED NOT NULL COMMENT '省份编号，外键（province->pro_id）',
city_name VARCHAR(32) NOT NULL COMMENT '城市名称',
FOREIGN KEY (pro_id) REFERENCES province(pro_id)
)DEFAULT CHARSET=utf8;";
if ($conn->query($city_info) === true) {
    echo "城市信息表创建成功<br/>";
} else {
    echo "城市信息表创建失败<br/>";
}

// 县区信息
$county_info = "create table county      
(
county_id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '合作商家所在县区编号',
city_id SMALLINT UNSIGNED NOT NULL COMMENT '城市编号，外键（city->city_id）',
county_name VARCHAR(32) NOT NULL COMMENT '县区名称',
FOREIGN KEY (city_id) REFERENCES city(city_id)
)DEFAULT CHARSET=utf8;";
if ($conn->query($county_info) === true) {
    echo "县区信息表创建成功<br/>";
} else {
    echo "县区信息表创建失败<br/>";
}

// 从文件中读取数据到PHP变量
$json_string = file_get_contents('city.json');
// 把JSON字符串转成PHP数组
$data = json_decode($json_string, true);
// 插入省市县信息
for ($i = 0; $i < count($data['citylist']); $i++) {
    $pro = $data['citylist'][$i]['p'];
    $sql = "insert into province(pro_name) value('{$pro}')";
    if ($conn->query($sql) === true) {
        echo $pro . "插入成功<br/><br/>";
    } else {
        echo $pro . "插入失败<br/><br/>";
    }
    if(array_key_exists('c', $data['citylist'][$i])){
        for ($j = 0; $j < count($data['citylist'][$i]['c']); $j++) {
            $city = $data['citylist'][$i]['c'][$j]['n'];
            $pro_id = $i + 1;
            $sql = "insert into city(pro_id, city_name) value({$pro_id}, '{$city}')";
            if ($conn->query($sql) === true) {
                echo $city . "插入成功<br/><br/>";
            } else {
                echo $city . "插入失败<br/><br/>";
            }
            if(array_key_exists('a', $data['citylist'][$i]['c'][$j])){
                for ($z = 0; $z < count($data['citylist'][$i]['c'][$j]['a']); $z++) {
                    $county = $data['citylist'][$i]['c'][$j]['a'][$z]['s'];
                    $city_id = $z + 1;
                    $sql = "insert into county(city_id, county_name) value({$city_id}, '{$county}')";
                    if ($conn->query($sql) === true) {
                        echo $county . "插入成功<br/>";
                    } else {
                        echo $county . "插入失败<br/>";
                    }
                }
            }
        }
    }
}

// 价格分档表
$fee_bracket = "create table fee_bracket
(
f_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '价格编号，自增',
fee_1 TINYINT(4) UNSIGNED DEFAULT 10 COMMENT '第一档价格，默认10元',
fee_2 TINYINT(4) UNSIGNED DEFAULT 12 COMMENT '第二档价格，默认12元',
fee_add TINYINT(4) UNSIGNED DEFAULT 2 COMMENT '加时档价格，默认2元',
effect_s_date TIMESTAMP NOT NULL COMMENT '生效时间',
effect_e_date TIMESTAMP COMMENT '失效时间'
)DEFAULT CHARSET=utf8;";
if ($conn->query($fee_bracket) === true) {
    echo "价格分档表创建成功<br/>";
} else {
    echo "价格分档表创建失败<br/>";
}

$now = date("Y-m-d H:i:s");
$sql = "insert into fee_bracket(fee_1, fee_2, fee_add, effect_s_date) 
value(10, 12, 2, '{$now}')";
if ($conn->query($sql) === true) {
    echo "价格分档表插入成功<br/>";
} else {
    echo "价格分档表插入失败<br/>";
}

// 时长分档表
$len_bracket = "create table len_bracket
(
l_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY  COMMENT '时长编号，自增',
len_1 TINYINT(4) UNSIGNED DEFAULT 120 COMMENT '第一档时长，默认120分钟',
len_2 TINYINT(4) UNSIGNED DEFAULT 180 COMMENT '第二档时长，默认180分钟',
len_add TINYINT(4) UNSIGNED DEFAULT 30 COMMENT '加时档时长，默认30分钟',
effect_s_date TIMESTAMP NOT NULL COMMENT '生效时间',
effect_e_date TIMESTAMP COMMENT '失效时间'
)DEFAULT CHARSET=utf8;";
if ($conn->query($len_bracket) === true) {
    echo "时长分档表创建成功<br/>";
} else {
    echo "时长分档表创建失败<br/>";
}
$now = date("Y-m-d H:i:s");
$sql = "insert into len_bracket(len_1, len_2, len_add, effect_s_date) 
value(150, 180, 30, '{$now}')";
if ($conn->query($sql) === true) {
    echo "时长分档表插入成功<br/>";
} else {
    echo "时长分档表插入失败<br/>";
}

// 惠率分档表
$rate_bracket = "create table rate_bracket
(
r_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY  COMMENT '惠率编号，自增',
rate_1 FLOAT(4,2) DEFAULT 0 COMMENT '第一档惠率，0.1代表商家下的干衣机单价优惠10%',
rate_2 FLOAT(4,2) DEFAULT 0 COMMENT '第二档惠率，0.1代表商家下的干衣机单价优惠10%',
rate_add FLOAT(4,2) DEFAULT 0 COMMENT '加时档惠率，0.1代表商家下的干衣机单价优惠10%',
effect_s_date TIMESTAMP NOT NULL COMMENT '生效时间',
effect_e_date TIMESTAMP COMMENT '失效时间'
)DEFAULT CHARSET=utf8;";
if ($conn->query($rate_bracket) === true) {
    echo "惠率分档表创建成功<br/>";
} else {
    echo "惠率分档表创建失败<br/>";
}
$now = date("Y-m-d H:i:s");
$sql = "insert into rate_bracket(rate_1, rate_2, rate_add, effect_s_date) 
value(0, 0, 0, '{$now}')";
if ($conn->query($sql) === true) {
    echo "惠率分档表插入成功<br/>";
} else {
    echo "惠率分档表插入失败<br/>";
}

// 佣金比率表
$brokerage = "create table brokerage     
(
b_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY  COMMENT '佣金比率编号，自增',
brokerage FLOAT(4,2) DEFAULT 0.03 COMMENT '佣金比率,默认0.03',
effect_s_date TIMESTAMP NOT NULL COMMENT '生效时间',
effect_e_date TIMESTAMP COMMENT '失效时间'
)DEFAULT CHARSET=utf8;";
if ($conn->query($brokerage) === true) {
    echo "佣金比率表创建成功<br/>";
} else {
    echo "佣金比率表创建失败<br/>";
}
$now = date("Y-m-d H:i:s");
$sql = "insert into brokerage(brokerage, effect_s_date) 
value(0.03, '{$now}')";
if ($conn->query($sql) === true) {
    echo "佣金比率表插入成功<br/>";
} else {
    echo "佣金比率表插入失败<br/>";
}


// 商家信息表
$merchant_info = "create table merchant_info      
(
mer_id INT UNSIGNED AUTO_INCREMENT COMMENT '商家序列号，自增',
mer_name VARCHAR(128) NOT NULL COMMENT '商家名称',
mer_sub_id SMALLINT UNSIGNED NOT NULL COMMENT '商家分店编号',
city_id SMALLINT UNSIGNED COMMENT '商家所在城市编号, 外键（city->city_id）',
addr_detail VARCHAR(128) NOT NULL COMMENT '合作商家具体地址',
f_id INT UNSIGNED NOT NULL COMMENT '价格编号, 外键（fee_bracket->f_id）',
l_id INT UNSIGNED NOT NULL COMMENT '时长编号, 外键（len_bracket->l_id）',
r_id INT UNSIGNED NOT NULL COMMENT '惠率编号, 外键（rate_bracket->r_id）',
b_id INT UNSIGNED NOT NULL COMMENT '佣金比率编号，外键（brokerage->b_id）',
PRIMARY KEY (mer_id, mer_name, mer_sub_id),
FOREIGN KEY (city_id) REFERENCES city(city_id),
FOREIGN KEY (f_id) REFERENCES fee_bracket(f_id),
FOREIGN KEY (l_id) REFERENCES len_bracket(l_id),
FOREIGN KEY (r_id) REFERENCES rate_bracket(r_id),
FOREIGN KEY (b_id) REFERENCES brokerage(b_id)
)DEFAULT CHARSET=utf8;";
if ($conn->query($merchant_info) === true) {
    echo "合作商家信息表创建成功<br/>";
} else {
    echo "合作商家信息表创建失败<br/>";
}
$sql = "insert into merchant_info(mer_name, mer_sub_id, city_id, addr_detail, f_id, l_id, 
r_id, b_id) value('讯鑫科技', 1, 236, '天心区', 1, 1, 1, 1)";
if ($conn->query($sql) === true) {
    echo "合作商家信息插入成功<br/>";
} else {
    echo "合作商家信息插入失败<br/>";
}

// 干洗设备-合作商家表
$merchant_machine = "create table mer_dev     
(
mer_id INT UNSIGNED COMMENT '商家id，外键（merchant_info->mer_id）',
dev_id INT UNSIGNED COMMENT '设备id，外键（device_info->dev_id）',
PRIMARY KEY (mer_id, dev_id),
FOREIGN KEY (mer_id) REFERENCES merchant_info(mer_id),
FOREIGN KEY (dev_id) REFERENCES device_info(dev_id)
)DEFAULT CHARSET=utf8;";
if ($conn->query($merchant_machine) === true) {
    echo "干洗设备-合作商家表创建成功<br/>";
} else {
    echo "干洗设备-合作商家表创建失败<br/>";
}

// 消费记录表
$consume_rec = "create table consume_rec         
(
rec_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '记录编号',
dev_id INT UNSIGNED COMMENT '设备id，外键（device_info->dev_id）',
user_id VARCHAR(32) NOT NULL COMMENT '用户id（微信号）',
consume_t TIMESTAMP NOT NULL COMMENT '消费时间',
cost FLOAT(4,2) NOT NULL COMMENT '消费金额',
time_len INT UNSIGNED NOT NULL COMMENT '购买时长（分钟）',
FOREIGN KEY (dev_id) REFERENCES device_info(dev_id)
)DEFAULT CHARSET=utf8;";
if ($conn->query($consume_rec) === true) {
    echo "消费记录表创建成功<br/>";
} else {
    echo "消费记录表创建失败<br/>";
}

// 角色表
$role = "create table role_info
(
role_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '角色编号',
name VARCHAR (32) NOT NULL COMMENT '角色名称'
)DEFAULT CHARSET=utf8;";
if ($conn->query($role) === true) {
    echo "角色表创建成功<br/>";
} else {
    echo "角色表创建失败<br/>";
}

$sql = "insert into role_info(name) VALUE ('root'),
('normal')";
if ($conn->query($sql) === true) {
    echo "角色表插入成功<br/>";
} else {
    echo "角色表插入失败<br/>";
}

// 讯鑫科技管理后台
$admin = "create table admin
(
user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '用户编号',
role_id INT UNSIGNED NOT NULL COMMENT '角色编号',
username VARCHAR (32) NOT NULL COMMENT '账号',
pwd VARCHAR (40) NOT NULL COMMENT '密码',
name VARCHAR (16) NOT NULL COMMENT '用户姓名',
phone VARCHAR (16) NOT NULL COMMENT '电话号码',
email VARCHAR (64) NOT NULL COMMENT '常用邮箱',
mer_id INT UNSIGNED COMMENT '商户id，外键（merchant_info->mer_id）',
audit TINYINT(2) UNSIGNED DEFAULT 1 COMMENT '审核状态。状态说明：1->设置账户密码结束；2->填写公司信息结束；3->审核结束；4->审核失败',
FOREIGN KEY (mer_id) REFERENCES merchant_info(mer_id),
FOREIGN KEY (role_id) REFERENCES role_info(role_id)
)DEFAULT CHARSET=utf8;";
if ($conn->query($admin) === true) {
    echo "讯鑫科技管理后台表创建成功<br/>";
} else {
    echo "讯鑫科技管理后台表创建失败<br/>";
}
$sql = "insert into admin (role_id, username, pwd, name, phone, email, mer_id, audit) VALUES
(1, 'admin', '90b9aa7e25f80cf4f64e990b78a9fc5ebd6cecad', 'admin', '15116312021', '103265201@qq.com', 1, 3)";
if ($conn->query($sql) === true) {
    echo "讯鑫科技管理后台信息插入成功<br/>";
} else {
    echo "讯鑫科技管理后台信息插入失败<br/>";
}

// 设备信息视图
$dev_info = "create view dev_info_view as select device_info.dev_id,
merchant_info.mer_id, device_info.mac_addr, device_info.allo_status, 
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

// 统计视图
$statistic = "create view statistic_view as select consume_rec.dev_id, mer_dev.mer_id,
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

// 商家管理员视图
$admin_sys = "create view admin_merchant_view as select admin.user_id, admin.role_id, 
admin.mer_id,  merchant_info.mer_name, merchant_info.mer_sub_id, admin.username, 
admin.pwd, admin.name,admin.phone, admin.email, province.pro_name, city.city_name, 
merchant_info.addr_detail, admin.audit
from admin, merchant_info, province, city 
where admin.mer_id = merchant_info.mer_id
and city.pro_id = province.pro_id
and city.city_id = merchant_info.city_id
";
if ($conn->query($admin_sys) === true) {
    echo "商家管理员视图创建成功<br/>";
} else {
    echo "商家管理员视图创建失败<br/>";
}

//  各商户价格-时长-惠率-佣金视图
$price_len_rate = "create view mer_fee_len_rate_broke_view as select
merchant_info.mer_id,
admin.role_id,
concat(merchant_info.mer_name, '_' ,merchant_info.mer_sub_id) as mer_name, 
fee_bracket.fee_1*(1-rate_bracket.rate_1) as fee_1,
fee_bracket.fee_2*(1-rate_bracket.rate_2) as fee_2,
fee_bracket.fee_add*(1-rate_bracket.rate_add) as fee_add,
len_bracket.len_1 as len_1, 
len_bracket.len_2 as len_2,
len_bracket.len_add as len_add,
rate_bracket.rate_1,
rate_bracket.rate_2,
rate_bracket.rate_add,
brokerage.brokerage
from merchant_info, fee_bracket, len_bracket, rate_bracket, brokerage, admin
where merchant_info.f_id = fee_bracket.f_id and 
merchant_info.l_id = len_bracket.l_id and
merchant_info.r_id = rate_bracket.r_id and
merchant_info.b_id = brokerage.b_id and 
merchant_info.mer_id = admin.mer_id
";
if ($conn->query($price_len_rate) === true) {
    echo "价格-时长-惠率-佣金视图创建成功<br/>";
} else {
    echo "价格-时长-惠率-佣金视图创建失败<br/>";
}

$sql = "/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
";
if ($conn->query($sql) === true) {
    echo "数据库配置成功<br/>";
} else {
    return;
}