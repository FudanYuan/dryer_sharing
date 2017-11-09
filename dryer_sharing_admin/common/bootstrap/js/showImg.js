/**
 * Created by Jeremy on 2017/6/8.
 */
// 定义id选择器
function Id(id) {
    return document.getElementById(id);
}

// 入口函数，两个参数分别为<input type='file'/>的id，还有一个就是图片的id，
// 然后会自动根据文件id得到图片，然后把图片放到指定id的图片标签中
function changeToop(fileid, imgid) {
    var file = Id(fileid);
    if (file.value == '') {
        Id(imgid).style.display = 'none';
        return '未选中任何文件';
    } else {
        return preImg(fileid, imgid);
    }
}

//获取input[file]图片的url Important
function getFileUrl(fileId) {
    var url;
    var file = Id(fileId);
    var agent = navigator.userAgent;
    if (agent.indexOf("MSIE") >= 1) {
        url = file.value;
    } else if (agent.indexOf("Firefox") > 0) {
        url = window.URL.createObjectURL(file.files.item(0));
    } else if (agent.indexOf("Chrome") > 0) {
        url = window.URL.createObjectURL(file.files.item(0));
    }
    return url;
}

//读取图片后预览
function preImg(fileId, imgId) {
    // 判读图片大小
    var img = new Image();
    img.src = getFileUrl(fileId);
    var imgSize = Id(fileId).files[0].size;
    if (imgSize <= 300 * 1024) {
        var imgPre = Id(imgId);
        imgPre.src = getFileUrl(fileId);
        imgPre.style.display = "block";
        return '*营业执照OK';
    }
    else {
        return '大小超过300kB';
    }
}