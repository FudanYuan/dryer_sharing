/**
 * Created by Jeremy on 2017/6/18.
 */
// 获取checkBox所选项
function getCheckBox(id) {
    return $('#'+id).val();
}

// 获取checkBox所选项
function setCheckBox(id, opt) {
    var obj = $('#' + id);
    obj.empty();
    var option = $("<option>").val(opt).text(opt);
    obj.append(option);
}

// 获取input所填项
function getInput(id) {
    return $('#' + id).val();
}

// 设置input所填项
function setInput(id, text) {
    $('#' + id).val(text);
}

// 在checkbox内添加新的商户
function addMer(id, data) {
    var obj = $('#' + id);
    obj.find("option").not(":first").remove();
    for (var i = 0; i < data.length; i++) {
        var option = $("<option>").val(data[i]).text(data[i]);
        obj.append(option);
    }
}

// 根据地址显示商户
function getMerName(type) {
    // 得到选择的地址
    var pro = $('#address #prov').val();
    var city = $('#address #city').val();
    var dist = $('#dist').val();
    $.ajax({
        url: "get_mer_by_addr.php",
        data: {'pro': pro, 'city': city, 'dist': dist, 'type': type},
        dataType: "text",
        success: function (data) {
            data = JSON.parse(data);
            if (data) {
                addMer('merchant', data);
            }
        }
    });
}
