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
    <script type="text/javascript" src="../../common/bootstrap/js/jquery.cityselect.js"></script>
    <script src="../../common/bootstrap/js/loc_mer.js"></script>
    <title>讯鑫科技-待分配</title>
    <style>
        #allocate_all {
            background: #00AA88;
            color: white;
            vertical-align: middle;
        }
    </style>
</head>
<body onload="show_wait_allocation()">

<!--查询结果-->
<div class="result">
    <table id="mytab" class="table table-hover"></table>
    <div class="all_operate" style="position: absolute; top: 10px; z-index: 1;">
        <button class="btn" id="allocate_all" disabled="disabled" onclick="pre_allocate('', 'all')">一键分配</button>
    </div>
</div>

<!--分配设备-->
<div class="modal fade" id="allocateModal" tabindex="-1" role="dialog" aria-labelledby="allocateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="allocateModalLabel">
                    分配设备
                </h4>
            </div>

            <div class="modal-body">
                <span id="label">分配地址:</span>
                <label id="address">
                    <select onblur="getMerName('audited')" class="prov" name="prov" id="prov"
                            style="height: 30px"></select>
                    <select onblur="getMerName('audited')" class="city" name="city" id="city"
                            style="height: 30px"></select>
                </label>
                <input onchange="getMerName('audited')" class="dist" style="width: 40%; margin-top: 0; height: 30px" type="text" name="dist" id="dist"
                       placeholder="具体地址" value=""/>

                <br/>

                <span id="label">商家名称:</span>
                <label id="merchant_name">
                    <select name="merchant" id="merchant" style="height: 30px">
                        <option value="tip" selected="selected" disabled="disabled">请选择合作商家</option>
                    </select>
                </label>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" onclick="allocate()">分配</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal -->
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

    // 显示已分配的设备信息
    function show_wait_allocation() {

        // 初始化表格
        var TableInit = function () {
            var oTableInit = new Object();

            //初始化Table
            oTableInit.Init = function () {
                $('#mytab').bootstrapTable({
                    url: 'get_device_info.php',     //请求后台的URL（*）
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
                                title: "全选",//标题
                                field: 'state',
                                width: 5, //宽度
                                checkbox: true,
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                title: "序号",//标题
                                field: "id",//键名
                                width: 5, //宽度
                                sortable: false,//是否可排序
                                order: "desc",//默认排序方式
                                align: "center", //水平
                                valign: "middle", //垂直
                                formatter: function (value, row, index) {
                                    return index + 1;
                                }
                            },
                            {
                                title: "设备ID",//标题
                                field: 'dev_id',//键名
                                width: 5, //宽度
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                title: "MAC地址",//标题
                                field: "mac_addr",//键名
                                width: 10, //宽度
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle" //垂直
                            },
                            {
                                title: "二维码",//标题
                                field: "QR_code",//键名
                                width: 10, //宽度
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle", //垂直
                                formatter: function (value, row, index) {
                                    return "<img src='"+ value +"' style='width: 50px; height: 50px;'>";
                                }
                            },
                            {
                                title: "分配状态",//标题
                                field: "allo_status",//键名
                                width: 10, //宽度
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle", //垂直
                                formatter: function (value, row, index) {
                                    if (value == 1) {
                                        return '已分配';
                                    }
                                    if (value == null) {
                                        return '未分配';
                                    }
                                }
                            },
                            {
                                title: "操作",//标题
                                field: "operate",//键名
                                width: 5, //宽度
                                sortable: false,//是否可排序
                                align: "center", //水平
                                valign: "middle", //垂直
                                formatter: function (value, row, index) {
                                    if (row['allo_status'] == null) {
                                        return "<div onclick='pre_allocate(\"" + row['dev_id'] + "\", \"\")' style=\"cursor: pointer; padding: 5px 0;\"><img style=\"size:10%;\" " +
                                            "src='../../common/glyphicons_free/glyphicons/png/glyphicons-422-send.png'" +
                                            "><span style='font-size: 1em;'>分配    </span></div>";
                                    }
                                }
                            }
                        ]
                    ]
                });
            };

            //得到查询的参数
            oTableInit.queryParams = function (params) {
                var temp = {   //这里的键的名字和控制器的变量名必须一直，这边改动，控制器也需要改成一样的
                    limit: params.limit,   //页面大小
                    offset: params.offset,  //页码
                    allocation_status: '未分配', //分配状态
                    pro: '',   // 设备地址-省
                    city: '',  // 设备地址-市
                    dist: '',  // 设备地址-具体地址
                    status: '' // 设备状态
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

    // 元素禁用
    function disabled(id, status) {
        $("#" + id).attr("disabled", status);
    }

    // 预分配设备至商户
    var msg;
    var dev_ids;
    function pre_allocate(data, type) {
        msg = '';
        dev_ids = new Array(data);
        if (type == 'all') {
            msg = '全部';
            // 获得选中的行
            dev_ids = $.map($('#mytab').bootstrapTable('getSelections'), function (row) {
                return row.dev_id;
            });
        }

        $("#address").citySelect({
            url: "../../common/bootstrap/js/city.min.js",
            prov: "北京", //省份
            city: "东城区" //城市
        });

        // 关闭弹框
        $('#merchant').find("option").not(":first").remove();
        $('#merchant').val('tip');
        // 显示弹框
        $('#allocateModal').modal('show');
    }

    // 分配
    function allocate() {
        // 得到选择的地址
        var merchant = new Array($('#merchant').val());

        if (merchant == '') {
            dialog('警告', '请选择要分配商户', '确认', '', function () {
            });
        } else {
            dialog('确认操作', '您确定要' + msg + '分配至' + merchant + '？',
                msg + '分配', '取消',
                function () {
                    $.ajax({
                        url: "operate.php",
                        data: {"operate": 'allocate', 'dev_ids': dev_ids, 'merchants': merchant},
                        dataType: "text",
                        success: function (res) {
                            // 关闭弹框
                            $('#merchant').find("option").not(":first").remove();
                            $('#merchant').val('tip');
                            $('#allocateModal').modal('hide');
                            $('#mytab').bootstrapTable('refresh');
                            toast(msg + '分配成功');
                        }
                    });
                },
                function () {
                }
            );
        }
    }

    // 判断是否多选
    $(function () {
        var $table = $('#mytab'),
            $allocate_all = $('#allocate_all');
        $table.on('check.bs.table uncheck.bs.table check-all.bs.table uncheck-all.bs.table', function () {
            $allocate_all.prop('disabled', !$table.bootstrapTable('getSelections').length);
        });
    });

</script>
