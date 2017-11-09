<?php
// 引入数据库操作函数
require_once '../../common/database/adminDB.php';

// 会话
session_start();
if (!isset($_SESSION['system'])) {
    header('location: ../login.html');
}
$id = $_SESSION['system'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">

    <!--css-->
    <link rel="stylesheet" href="../../common/bootstrap/css/weui.css"/>
    <link rel="stylesheet" href="../../common/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../common/bootstrap/css/bootstrap-table.css"/>
    <link rel="stylesheet" href="../../common/bootstrap/css/bootstrap-editable.css"/>
    <link rel="stylesheet" href="../../common/bootstrap/css/table.css"/>

    <!--js-->
    <script src="../../common/bootstrap/js/jquery.js"></script>
    <script src='../../common/bootstrap/js/dialog.js'></script>
    <script src="../../common/bootstrap/js/bootstrap.min.js"></script>
    <script src="../../common/bootstrap/js/bootstrap-table.js"></script>
    <script src="../../common/bootstrap/js/bootstrap-editable.js"></script>
    <script src="../../common/bootstrap/js/bootstrap-table-editable.js"></script>
    <script src="../../common/bootstrap/js/bootstrap-table-zh-CN.js"></script>
    <script src="../../common/bootstrap-table/dist/extensions/export/bootstrap-table-export.min.js"></script>
    <script src="../../common/bootstrap-table/dist/extensions/export/tableExport.js"></script>

    <title>讯鑫科技-参数设置</title>
</head>
<body onload="show_fee_len_rate_brokerage()">

<!--查询结果-->
<div class="result">
    <table id="mytab" class="table table-hover"></table>
</div>

<!--数据加载-->
<div id="loadingToast" style="display:none;">
    <div class="weui-mask_transparent"></div>
    <div class="weui-toast">
        <i class="weui-loading weui-icon_toast"></i>
        <p class="weui-toast__content">努力查询中</p>
    </div>
</div>

<!--弹窗-->
<div id="toast" style="display: none;">
    <div class="weui-mask_transparent"></div>
    <div class="weui-toast">
        <i class="weui-icon-success-no-circle weui-icon_toast"></i>
        <p class="weui-toast__content"></p>
    </div>
</div>
</body>
</html>

<script>
    // 显示时价信息
    function show_fee_len_rate_brokerage() {
        query();
    }
    // 查询
    function query() {
        // 初始化表格
        var TableInit = function () {
            var oTableInit = new Object();

            //初始化Table
            oTableInit.Init = function () {
                $('#mytab').bootstrapTable({
                    url: 'get_fee_len_rate_brokerage.php',     //请求后台的URL（*）
                    method: 'post',       //请求方式（*）
                    dataField: "rows",    //服务端返回数据键值 就是说记录放的键值是rows，分页时使用总记录数的键值为total
                    height: tableHeight(),//高度调整
                    search: false,        //是否搜索
                    sortable: false,      //是否排序
                    showToggle: true,
                    editable: true,
                    striped: true,        //是否隔色显示
                    pagination: true,     //是否分页
                    showExport: true,                     //是否显示导出
                    exportDataType: "all",              //basic', 'all', 'selected'.
                    contentType: "application/x-www-form-urlencoded",//请求数据内容格式 默认是 application/json 自己根据格式自行服务端处理
                    dataType: "json",     //期待返回数据类型
                    searchAlign: "left",  //查询框对齐方式
                    queryParamsType: "limit",//查询参数组织方式
                    queryParams: oTableInit.queryParams,//传递参数（*）
                    sidePagination: "server",           //分页方式：client客户端分页，server服务端分页（*）
                    pageNumber: 1,                       //初始化加载第一页，默认第一页
                    pageSize: 50,                       //每页的记录行数（*）
                    pageList: [10, 25, 50, 100],        //可供选择的每页的行数（*）
                    strictSearch: false,
                    clickToSelect: true,                //是否启用点击选中行
                    uniqueId: "id",                     //每一行的唯一标识，一般为主键列
                    cardView: false,                    //是否显示详细视图
                    detailView: false,                   //是否显示父子表
                    searchOnEnterKey: false,//回车搜索
                    showRefresh: true,      //刷新按钮
                    showColumns: true,      //列选择按钮
                    buttonsAlign: "right",  //按钮对齐方式
                    toolbar: "#toolbar",    //指定工具栏
                    toolbarAlign: "right",  //工具栏对齐方式
                    columns: [
                        [
                            {
                                title: "序号",//标题
                                field: "id",//键名
                                width: 20, //宽度
                                rowspan: 2,
                                sortable: false,//是否可排序
                                order: "desc",//默认排序方式
                                align: "center", //水平
                                valign: "middle", //垂直
                                formatter: function (value, row, index) {
                                    return index + 1;
                                }
                            },
                            {
                                title: "商户名称",//标题
                                field: 'mer_name',//键名
                                width: 100, //宽度
                                rowspan: 2,
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                title: "第一档",//标题
                                field: "brac_1",//键名
                                width: 60, //宽度
                                colspan: 3,
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                title: "第二档",//标题
                                field: "brac_2",//键名
                                width: 60, //宽度
                                colspan: 3,
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                title: "加时档",//标题
                                field: "brac_add",//键名
                                width: 60, //宽度
                                colspan: 3,
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                title: "佣金比例（%）",//标题
                                field: "brokerage",//键名
                                width: 60, //宽度
                                rowspan: 2,
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle", //垂直
                                editable: {
                                    type: 'number',
                                    title: '佣金比例（%）',
                                    validate: function (v) {
                                        if (!v) return '比例不能为空';
                                        else if (v < 0) {
                                            return '请填写大于0的数字';
                                        }
                                    }
                                },
                                formatter: function (value, row, index) {
                                    return value * 100;
                                }
                            }
                        ],
                        [
                            {
                                field: "len_1",
                                title: "时长（分）",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle", //垂直
                                editable: {
                                    type: 'number',
                                    title: '时长（分）',
                                    validate: function (v) {
                                        if (!v) return '价格不能为空';
                                        else if (v < 0) {
                                            return '请填写大于0的数字';
                                        }
                                    }
                                }
                            },
                            {
                                field: "fee_1",
                                title: "价格（元）",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle", //垂直
                                editable: {
                                    type: 'number',
                                    title: '价格（元）',
                                    validate: function (v) {
                                        if (!v) return '价格不能为空';
                                        else if (v < 0) {
                                            return '请填写大于0的数字';
                                        }
                                    }
                                }
                            },
                            {
                                field: "rate_1",
                                title: "惠率（%）",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle", //垂直
                                editable: {
                                    type: 'number',
                                    title: '惠率（%）',
                                    validate: function (v) {
                                        if (!v) return '惠率不能为空';
                                        else if (v < 0) {
                                            return '请填写大于0的数字';
                                        }
                                    }
                                },
                                formatter: function (value, row, index) {
                                    return value * 100;
                                }
                            },
                            {
                                field: "len_2",
                                title: "时长（分）",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle", //垂直
                                editable: {
                                    type: 'number',
                                    title: '时长（分）',
                                    validate: function (v) {
                                        if (!v) return '价格不能为空';
                                        else if (v < 0) {
                                            return '请填写大于0的数字';
                                        }
                                    }
                                }
                            },
                            {
                                field: "fee_2",
                                title: "价格（元）",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle", //垂直
                                editable: {
                                    type: 'number',
                                    title: '价格（元）',
                                    validate: function (v) {
                                        if (!v) return '价格不能为空';
                                        else if (v < 0) {
                                            return '请填写大于0的数字';
                                        }
                                    }
                                }
                            },
                            {
                                field: "rate_2",
                                title: "惠率（%）",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle", //垂直
                                editable: {
                                    type: 'number',
                                    title: '惠率（%）',
                                    validate: function (v) {
                                        if (!v) return '惠率不能为空';
                                        else if (v < 0) {
                                            return '请填写大于0的数字';
                                        }
                                    }
                                },
                                formatter: function (value, row, index) {
                                    return value * 100;
                                }
                            },
                            {
                                field: "len_add",
                                title: "时长（分）",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle", //垂直
                                editable: {
                                    type: 'number',
                                    title: '时长（分）',
                                    validate: function (v) {
                                        if (!v) return '价格不能为空';
                                        else if (v < 0) {
                                            return '请填写大于0的数字';
                                        }
                                    }
                                }
                            },
                            {
                                field: "fee_add",
                                title: "价格（元）",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle", //垂直
                                editable: {
                                    type: 'number',
                                    title: '价格（元）',
                                    validate: function (v) {
                                        if (!v) return '价格不能为空';
                                        else if (v < 0) {
                                            return '请填写大于0的数字';
                                        }
                                    }
                                }
                            },
                            {
                                field: "rate_add",
                                title: "惠率（%）",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle", //垂直
                                editable: {
                                    type: 'number',
                                    title: '惠率（%）',
                                    validate: function (v) {
                                        if (!v) return '惠率不能为空';
                                        else if (v < 0) {
                                            return '请填写大于0的数字';
                                        }
                                    }
                                },
                                formatter: function (value, row, index) {
                                    return value * 100;
                                }
                            }
                        ]
                    ],

                    onEditableSave: function (field, row, oldValue, $el) {
                        // alert(JSON.stringify(row));
                        // 得到更改的商户名称
                        var url = 'edit_fee_len_rate_brokerage.php';
                        $.post(url, {'data': row}, function (res) {//注意jquery的$.post的第2个参数必须是键值对形式
                            $('#mytab').bootstrapTable('refresh');
                        });
                    }
                });
            };

            //得到查询的参数
            oTableInit.queryParams = function (params) {
                var temp = {   //这里的键的名字和控制器的变量名必须一直，这边改动，控制器也需要改成一样的
                    limit: params.limit,   //页面大小
                    offset: params.offset,  //页码
                    merchant: getMer()
                };
                return temp;
            };
            return oTableInit;
        };

        // 表格高度
        function tableHeight() {
            return $(window).height();
        }

        $('#mytab').bootstrapTable('refresh');
        $(function () {
            //1.初始化Table
            var oTable = new TableInit();
            oTable.Init();
        });
    }

    // 获取商户名称
    function getMer() {
        return "讯鑫科技";
    }
</script>
