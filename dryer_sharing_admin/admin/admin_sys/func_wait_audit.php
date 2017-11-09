<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/6/8
 * Time: 19:31
 */
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
    <script src="../../common/bootstrap/js/jquery.cityselect.js"></script>
    <script src="../../common/bootstrap/js/loc_mer.js"></script>
    <title>讯鑫科技-待审核</title>
    <style>
        #pass_all {
            background: #00AA88;
            color: white;
            vertical-align: middle;
        }

        #reject_all {
            background: #AA0000;
            color: white;
            vertical-align: middle;
        }
    </style>
</head>
<body onload="show_wait_audit()">

<div id="form">
    <span id="label">商家地址:</span>
    <label id="address">
        <select onblur="getMerName('wait_audit')" class="prov" name="prov" id="prov"></select>
        <select onblur="getMerName('wait_audit')" class="city" name="city" id="city"></select>
    </label>
    <input onchange="getMerName('wait_audit')" class="dist" style="width: 10%; margin-top: 15px; height: 30px" type="text" name="dist" id="dist"
           placeholder="具体地址" value=""/>

    <span id="label">商家名称:</span>
    <label id="merchant_name">
        <select name="merchant" id="merchant" style="height: 30px; display: block">
            <option value="all" selected="selected">不限</option>
        </select>
    </label>

    <label id="label">
        <button class="btn" id="query" onclick="query()">
            <span class="bt text">查询</span>
        </button>
    </label>
</div>

<!--查询结果-->
<div class="result">
    <table id="mytab" class="table table-hover"></table>
    <div class="all_operate" style="position: absolute; top: 10px; z-index: 1;">
        <button class="btn" id="pass_all" disabled="disabled" onclick="pass('', 'all')">全部通过</button>
        <button class="btn" id="reject_all" disabled="disabled" onclick="reject('', 'all')">全部拒绝</button>
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
    function show_wait_audit() {
        $("#address").citySelect({
            url: "../../common/bootstrap/js/city_filter.js",
            prov: "不限", //省份
            city: "不限" //城市
        });
        query();
    }

    // 显示审核的商户信息
    function query() {
        // 初始化表格
        var TableInit = function () {
            var oTableInit = new Object();

            //初始化Table
            oTableInit.Init = function () {
                $('#mytab').bootstrapTable({
                    url: 'get_merchant_info.php',     //请求后台的URL（*）
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
                    exportDataType: "selected",              //basic', 'all', 'selected'.
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
                                title: "全选",//标题
                                field: 'state',
                                width: 5, //宽度
                                rowspan: 2,
                                checkbox: true,
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                title: "序号",//标题
                                field: "id",//键名
                                width: 5, //宽度
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
                                field: "mer_name",//键名
                                width: 20, //宽度
                                rowspan: 2,
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                title: "商户地址",//标题
                                field: "address",//键名
                                width: 15, //宽度
                                colspan: 3,
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                title: "商户管理员",//标题
                                field: "mer_admin",//键名
                                width: 15, //宽度
                                colspan: 3,
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                title: "审核状态",//标题
                                field: "audit",//键名
                                width: 5, //宽度
                                rowspan: 2,
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle", //垂直
                                formatter: function (value, row, index) {
                                    if (value == 1) {
                                        return "<p style='color: #3c3c3c; align-content: center; vertical-align: middle;'>信息不全</p>";
                                    }
                                    else if (value == 2) {
                                        return "<p style='color: #AA0000; align-content: center; vertical-align: middle;'>待审核</p>";
                                    }
                                    else if (value == 3) {
                                        return "<p style='color: #00AA88; align-content: center; vertical-align: middle;'>通过审核</p>";
                                    }
                                    else if (value == 4) {
                                        return "<p style='color: #985f0d; align-content: center; vertical-align: middle;'>审核失败</p>";
                                    }
                                }
                            },
                            {
                                title: "操作",//标题
                                field: "operate",//键名
                                rowspan: 2,
                                width: 5, //宽度
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle", //垂直
                                formatter: function (value, row, index) {
                                    return "<div onclick='pass(\"" + row['mer_name'] + "\", \"\")' style=\"cursor: pointer;\"><img style=\"size:10%;\" " +
                                        "src='../../common/glyphicons_free/glyphicons/png/glyphicons-199-ok-circle.png'" +
                                        "><span style='font-size: 1em;'>通过    </span></div>" +
                                        "<div onclick='reject(\"" + row['mer_name'] + "\", \"\")' style='cursor: pointer;'><img style='size: 10%; ' " +
                                        "src='../../common/glyphicons_free/glyphicons/png/glyphicons-200-ban-circle.png'" +
                                        "><span style='font-size: 1em;'>拒绝    </span></div>";
                                }
                            }
                        ],
                        [
                            {
                                field: "pro_name",
                                title: "省份",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                field: "city_name",
                                title: "城市",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                field: "addr_detail",
                                title: "具体地址",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                field: "name",
                                title: "姓名",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                field: "phone",
                                title: "联系方式",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                field: "email",
                                title: "常用邮箱",
                                sortable: false,
                                align: "center", //水平
                                valign: "middle" //垂直
                            }
                        ]
                    ]
                });
            };

            //得到查询的参数
            oTableInit.queryParams = function (params) {
                var temp = {   //这里的键的名字和控制器的变量名必须一直，这边改动，控制器也需要改成一样的
                    limit: params.limit,   //页面大小
                    offset: params.offset, //页码
                    pro: getCheckBox('prov'), // 商家地址-省
                    city: getCheckBox('city'), // 商家地址-市
                    dist: getInput('dist'), // 商家地址-具体地址
                    merchant: getCheckBox('merchant'), // 商家-商户对应
                    status: 'wait_audit'   // 查询状态  //页码
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

    // 通过审核信息
    function pass(data, type) {
        var msg = '';
        var mer_names=new Array(data);
        if (type == 'all') {
            msg = '全部';
            // 获得选中的行
            mer_names = $.map($('#mytab').bootstrapTable('getSelections'), function (row) {
                return row.mer_name;
            });
        }

        dialog('确认操作', '您确定要' + msg + '通过？', msg + '通过', '取消',
            function () {
                $.ajax({
                    url: "operate.php",
                    data: {"operate": 'pass', 'merchants': mer_names},
                    dataType: "text",
                    timeout: 5000, //5秒超时，可自定义设置
                    success: function () {
                        toast(msg + '通过成功');
                        window.location.reload(true);
                    }
                });
            },
            function () {
            }
        );
    }

    // 拒绝审核信息
    function reject(data, type) {
        var msg = '';
        var mer_names=new Array(data);
        if (type == 'all') {
            msg = '全部';
            // 获得选中的行
            mer_names = $.map($('#mytab').bootstrapTable('getSelections'), function (row) {
                return row.mer_name;
            });
        }

        dialog('确认操作', '您确定要' + msg + '拒绝？', msg + '拒绝', '取消',
            function () {
                $.ajax({
                    url: "operate.php",
                    data: {"operate": 'reject', 'merchants': mer_names},
                    dataType: "text",
                    timeout: 5000, //5秒超时，可自定义设置
                    success: function () {
                        toast(msg + '拒绝成功');
                        window.location.reload(true);
                    }
                });
            },
            function () {
            }
        );
    }

    // 判断是否多选
    $(function () {
        var $table = $('#mytab'),
            $pass_all = $('#pass_all'),
            $reject_all = $('#reject_all');

        $table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
            $pass_all.prop('disabled', !$table.bootstrapTable('getSelections').length);
            $reject_all.prop('disabled', !$table.bootstrapTable('getSelections').length);
        });
    });
</script>
