/**
 * Created by Jeremy on 2017/3/31.
 */
// 自定义弹框
function dialog(title, msg, btn1, btn2, callback1, callback2) {
    var dialog1, dialog2;
    dialog1 = '\
           <div class="weui-dialog_confirm" id="dialog1" style="display: none;">\
              <div class="weui-mask"></div>\
              <div class="weui-dialog">\
                  <div class="weui-dialog__hd"><strong class="weui-dialog__title">' + title + '</strong></div>\
                  <div class="weui-dialog__bd">' + msg + '</div>\
                  <div class="weui-dialog__ft">\
                      <a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_default">' + btn2 + '</a>\
                      <a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary">' + btn1 + '</a>\
                  </div>\
              </div>\
           </div>\
        ';
    dialog2 = '\
           <div class="weui-dialog_confirm" id="dialog2" style="display: none;">\
              <div class="weui-mask"></div>\
              <div class="weui-dialog">\
                  <div class="weui-dialog__hd"><strong class="weui-dialog__title">' + title + '</strong></div>\
                  <div class="weui-dialog__bd">' + msg + '</div>\
                  <div class="weui-dialog__ft">\
                      <a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary">' + btn1 + '</a>\
                  </div>\
              </div>\
           </div>\
        ';
    if (arguments[5]&&arguments[3]!='') {
        $('body').append(dialog1);
        $('#dialog1').fadeIn('fast');
        $('#dialog1 .weui-dialog__btn_primary').on('click', function () {
            callback1();
            $('#dialog1').remove();
        });
        $('#dialog1 .weui-dialog__btn_default').on('click', function () {
            callback2();
            $('#dialog1').fadeOut('fast', function () {
                $('#dialog1').remove();
            });
        });
    }
    else if(arguments[4]&&arguments[3]!=''){
        $('body').append(dialog1);
        $('#dialog1').fadeIn('fast');
        $('#dialog1 .weui-dialog__btn_primary').on('click', function () {
            callback1();
            $('#dialog1').remove();
        });
        $('#dialog1 .weui-dialog__btn_default').on('click', function () {
            $('#dialog1').fadeOut('fast', function () {
                $('#dialog1').remove();
            });
        });
    }
    else if(arguments[4]&&arguments[3]==''){
        if (!$('#dialog2').length) {
            $('body').append(dialog2);
        } else {
            $('#dialog2.weui-dialog__title').html(title);
            $('#dialog2.weui-dialog__bd').html(msg);
        }
        $('#dialog2').fadeIn('fast');
        $('#dialog2 .weui-dialog__btn_primary').on('click', function () {
            $('#dialog2').fadeOut('fast');
            callback1();
        });
    }

    if(arguments[3]==''){
        if (!$('#dialog2').length) {
            $('body').append(dialog2);
        } else {
            $('#dialog2.weui-dialog__title').html(title);
            $('#dialog2.weui-dialog__bd').html(msg);
        }
        $('#dialog2').fadeIn('fast');
        $('#dialog2 .weui-dialog__btn_primary').on('click', function () {
            $('#dialog2').fadeOut('fast');
        });
    }
}

