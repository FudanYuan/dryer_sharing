<?php
require_once '../../common/database/adminDB.php';

session_start();
if(!isset($_SESSION['merchant']))
{
    header('location: ../login.html');
}
$id = $_SESSION['merchant'];

// 获取商户名称及自编号
$sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as merchant_name from merchant_info where mer_id = (select mer_id from admin where username = '{$id}')";
$merchant = $adminDB->ExecSQL($sql, $conn);
$merchant = $merchant[0]['merchant_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <!--css-->
    <link rel="stylesheet" href="../../common/bootstrap/css/weui.css"/>
    <link rel="stylesheet" href="../../common/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" media="all" href="../../common/bootstrap/css/daterangepicker.css"/>
    <link rel="stylesheet" href="../../common/bootstrap/css/bootstrap-table.css"/>
    <link rel="stylesheet" href="../../common/bootstrap/css/table.css"/>
    <!--js-->
    <script src="../../common/bootstrap/js/jquery.js"></script>
    <script src="../../common/bootstrap/js/echarts.min.js"></script>
    <script type="text/javascript" src="../../common/bootstrap/js/moment.js"></script>
    <script type="text/javascript" src="../../common/bootstrap/js/daterangepicker.js"></script>
    <script src='../../common/bootstrap/js/dialog.js'></script>
    <script src="../../common/bootstrap/js/bootstrap.min.js"></script>
    <script src="../../common/bootstrap/js/bootstrap-table.js"></script>
    <script src="../../common/bootstrap/js/bootstrap-table-zh-CN.js"></script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <title>讯鑫科技-数据统计</title>

</head>
<body onload="query()">
<div id="form">
    <span id="label">统计时段:</span>
    <label class="statistic_time" id="statistic_time">
        <input type="text" class="form-control date-picker" id="dateTimeRange" value=""/>
        <input type="hidden" name="beginTime" id="beginTime" value=""/>
        <input type="hidden" name="endTime" id="endTime" value=""/>
    </label>
    <br/>

    <label>
        <button class="btn" id="query" onclick="query()">
            <span class="bt text">查询</span>
        </button>
    </label>
</div>

<!--查询结果-->
<div class="result">
    <label id="label">数据可视化</label>
    <div id="chart"></div>

    <label class="statistic_sum">
        <span id="label">统计结果：</span>
        <label id="time" style="width: 140px"><span id="label">总时长：</span><span id="timeSum"></span></label>
        <label id="broke" style="width: 140px"><span id="label">利润：</span><span id="brokeSum"></span></label>
    </label>

    <label id="label">数据详情</label>
    <table id="mytab" class="table table-hover"></table>
</div>

<!--数据加载-->
<div id="loadingToast" style="display:none;">
    <div class="weui-mask_transparent"></div>
    <div class="weui-toast">
        <i class="weui-loading weui-icon_toast"></i>
        <p class="weui-toast__content">数据查询中</p>
    </div>
</div>

<!--弹窗-->
<div id="toast" style="display: none;">
    <div class="weui-mask_transparent"></div>
    <div class="weui-toast">
        <i class="weui-icon-success-no-circle weui-icon_toast"></i>
        <p class="weui-toast__content">查询成功</p>
    </div>
</div>
</body>
</html>

<script>
    // 异步验证用户名和密码
    var xmlHttp;
    function query() {
        $('#mytab').bootstrapTable('refresh');
        $(function () {
            //1.初始化Table
            var oTable = new TableInit();
            oTable.Init();

            //2.初始化Button的点击事件
            /* var oButtonInit = new ButtonInit();
             oButtonInit.Init(); */
        });

        // get type of statistic time
        var beginTime = document.getElementById("beginTime").value;
        var endTime = document.getElementById("endTime").value;

        // 异步通信
        xmlHttp = GetXmlHttpObject();
        if (xmlHttp == null) {
            alert("Browser does not support HTTP Request");
            return;
        }
        var url = "get_statistic_chart.php";
        var data = "merchant=" + getMer()
            + "&beginTime=" + beginTime
            + "&endTime=" + endTime;

        xmlHttp.onreadystatechange = getResult;
        xmlHttp.open("POST", url, true);
        xmlHttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xmlHttp.send(data);
        // loading
        var $loadingToast = $('#loadingToast');
        $loadingToast.fadeIn(100);
        t = setTimeout(function () {
            $loadingToast.fadeOut(100);
        }, 2000);
    }

    // 获取服务器返回
    function getResult() {
        if (xmlHttp.readyState == 4 || xmlHttp.readyState == "OK") {
            // 结束加载
            var $loadingToast = $('#loadingToast');
            clearTimeout(t);
            $loadingToast.fadeOut();

            // 解析支付参数
            var result = JSON.parse(xmlHttp.responseText);

            var time = result['time'];
            //var money =  result['money'];
            var timeMax = result['timeMax'];
            //var moneyMax = result['moneyMax'];
            var xAxis = result['xAxis'];
            var timeSum = result['timeSum'];
            var brokeSum = result['brokeSum'];
            //将总金额和总时间显示在界面上
            document.getElementById('timeSum').innerText = timeSum+'分钟';
            document.getElementById('brokeSum').innerText = brokeSum + '元';

            // 画曲线
            drawMyChart(xAxis, timeMax, time);
        }
    }

    // 得到服务器返回值json格式
    function GetXmlHttpObject() {
        var xmlHttp = null;
        try {
            // Firefox, Opera 8.0+, Safari
            xmlHttp = new XMLHttpRequest();
        }
        catch (e) {
            //Internet Explorer
            try {
                xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch (e) {
                xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
            }
        }
        return xmlHttp;
    }

    // 显示图表
    var myChart;
    var chartOption;
    var category = [];

    function showMyChart() {
        // 基于准备好的dom，初始化echarts实例
        myChart = echarts.init(document.getElementById('chart'));
        myChart.showLoading();

        chartOption = {
            //backgroundColor: '#f1f1f1',
            animation: true,
            tooltip: {
                trigger: 'axis'
            },

            legend: {
                top: 25,
                right: 'center',
                textStyle: {
                    color: 'black'
                },
                data: ['使用时长（分钟）']
            },

            grid: {
                show: false,
                left: 'center',
                width: 800,
                height: 330,
                splitLine: {
                    lineStyle: {
                        color: 'rgba(255,255,255,0.7)'
                    }
                },
                containLabel: true
            },

            xAxis: [{
                type: 'category',
                name: '时间',
                data: [],
                axisPointer: {
                    type: 'shadow'
                }
            }],

            yAxis: [
                {
                    type: 'value',
                    name: '使用时长（分钟）',
                    position: 'left',
                    axisLabel: {
                        formatter: '{value}'
                    },
                    interval: 100,
                    data: []
                }],

            series: [
                {
                    name: '使用时长（分钟）',
                    type: 'bar',
                    itemStyle: {
                        normal: {
                            color: '#a5cab8'
                        }
                    },
                    barWidth: 30,
                    data: []
                }

//                {
//                    lineStyle: {
//                        normal: {
//                            type: 'dotted'
//                        }
//                    },
//                    name: '平均值',
//                    type: 'line',
//                    smooth: true,
//                    itemStyle: {
//                        normal: {
//                            color: 'yellow'
//                        }
//                    },
//                    data: []
//                }
            ],
            animationEasing: 'elasticOut'
        };
        myChart.hideLoading();
        myChart.setOption(chartOption);
    }
    showMyChart();

    // 动态显示
    function drawMyChart(xAxis, timeMax, time) {
        myChart.setOption({
            xAxis: [
                {
                    name: '时间',
                    data: xAxis
                }],
            yAxis: [
                {
                    name: '使用时长（分钟）',
                    max: timeMax
                }],
            series: [
                {
                    name: '使用时长（分钟）',
                    label: {
                        normal: {
                            show: true,
                            position: 'inside',
                            textStyle:{
                                color: 'white'
                            }
                        }
                    },
                    data: time
                }
            ],
            animationEasing: 'elasticOut'
        });
    }

    // 获取商户名称
    function getMer() {
        return "<?php echo $merchant;?>";
    }

    // 初始化表格
    var TableInit = function () {
        var oTableInit = new Object();
        //初始化Table
        oTableInit.Init = function () {
            $('#mytab').bootstrapTable({
                url: 'get_statistic_table.php',     //请求后台的URL（*）
                method: 'post',       //请求方式（*）
                dataField: "rows",    //服务端返回数据键值 就是说记录放的键值是rows，分页时使用总记录数的键值为total
                height: tableHeight(),//高度调整
                search: false,        //是否搜索
                sortable: false,      //是否排序
                showToggle: true,
                striped: true,        //是否隔色显示
                pagination: true,     //是否分页
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
                            width: 5, //宽度
                            sortable: false,//是否可排序
                            order: "desc",//默认排序方式
                            align: "center", //水平
                            valign: "middle", //垂直
                            formatter: function (value, row, index) {
                                return index+1;
                            }
                        },
                        {
                            title: "商户名称",//标题
                            field: "mer_name",//键名
                            width: 60, //宽度
                            sortable: false,//是否可排序
                            align: "center", //水平
                            valign: "middle" //垂直
                        },
                        {
                            title: "设备ID",//标题
                            field: "dev_id",//键名
                            width: 20, //宽度
                            sortable: false,//是否可排序
                            align: "center", //水平
                            valign: "middle" //垂直
                        },
                        {
                            title: "省份",//标题
                            field: "pro_name",//键名
                            width: 20, //宽度
                            sortable: false,//是否可排序
                            align: "center", //水平
                            valign: "middle" //垂直
                        },
                        {
                            title: "城市",//标题
                            field: "city_name",//键名
                            width: 20, //宽度
                            sortable: false,//是否可排序
                            align: "center", //水平
                            valign: "middle" //垂直
                        },
                        {
                            title: "消费时间",//标题
                            field: "consume_t",//键名
                            width: 40, //宽度
                            sortable: false,//是否可排序
                            align: "center", //水平
                            valign: "middle" //垂直
                        },
                        {
                            title: "购买时长（分）",//标题
                            field: "time_len",//键名
                            width: 20, //宽度
                            sortable: false,//是否可排序
                            align: "center", //水平
                            valign: "middle" //垂直
                        }
//                        ,
//                        {
//                            title: "消费金额（元）",//标题
//                            field: "cost",//键名
//                            width: 30, //宽度
//                            sortable: false,//是否可排序
//                            align: "center", //水平
//                            valign: "middle" //垂直
//                        }
                    ]
                ]
            });
        };

        //得到查询的参数
        oTableInit.queryParams = function (params) {
            var temp = {   //这里的键的名字和控制器的变量名必须一直，这边改动，控制器也需要改成一样的
                limit: params.limit,   //页面大小
                offset: params.offset,  //页码
                merchant: getMer(),
                beginTime: $('#beginTime').val(),
                endTime: $('#endTime').val()
            };
            return temp;
        };
        return oTableInit;
    };

    // 表格高度
    function tableHeight() {
        return $(window).height() - 50;
    }

    // 格式化显示信息
    function infoFormatter(value, row, index) {
        return "id:" + row.id + " name:" + row.name + " age:" + row.age;
    }
</script>

<script>
    $(function () {
        $('#dateTimeRange')
            .daterangepicker({
                applyClass: 'btn-sm btn-success',
                cancelClass: 'btn-sm btn-default',
                locale: {
                    applyLabel: '确认',
                    cancelLabel: '取消',
                    fromLabel: '起始时间',
                    toLabel: '结束时间',
                    customRangeLabel: '自定义',
                    daysOfWeek:['日', '一', '二', '三', '四', '五', '六'],
                    monthNames:['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
                    firstDay: 1
                },
                ranges: {
                    '今日': [moment().startOf('day'), moment()],
                    '昨日': [moment().subtract('days', 1).startOf('day'), moment().subtract('days', 1).endOf('day')],
                    '最近7日': [moment().subtract('days', 6), moment()],
                    '最近30日': [moment().subtract('days', 29), moment()],
                    '本月': [moment().startOf("month"), moment().endOf("month")],
                    '上个月': [moment().subtract(1, "month").startOf("month"), moment().subtract(1, "month").endOf("month")],
                    '最近90日': [moment().subtract('days', 89), moment()],
                    '本季度': [moment().startOf("quarter"), moment()],
                    '上个季度': [moment().subtract(1, "quarter").startOf("quarter"), moment().subtract(1, "quarter").endOf("quarter")],
                    '今年': [moment().startOf("year"), moment()],
                    '去年': [moment().subtract(1, "year").startOf("year"), moment().subtract(1, "year").endOf("year")]
                },
                opens: 'center',    // 日期选择框的弹出位置
                separator: ' 至 ',
                showWeekNumbers: true,     // 是否显示第几周

                //timePicker: true,
                //timePickerIncrement : 10, // 时间的增量，单位为分钟
                //timePicker12Hour : false, // 是否使用12小时制来显示时间

                maxDate : moment(),           // 最大时间
                format: 'YYYY-MM-DD'

            }, function (start, end, label) { // 格式化日期显示框
                $('#beginTime').val(start.format('YYYY-MM-DD'));
                $('#endTime').val(end.format('YYYY-MM-DD'));
            }).next().on('click', function () {
            $(this).prev().focus();
        });
    });
</script>