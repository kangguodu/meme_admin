var protocol = window.location.protocol;
var host = window.location.host;
//var baseUrl = $('meta[name="admin_host"]')[0]['content'];
//var appUrl = $('meta[name="app_host"]')[0]['content'];
var baseUrl = 'https://localhost:8082/memecoinsapi/public/admin';
var appUrl = 'https://localhost:8082/memecoinsapi/public';
// var baseUrl = protocol+'//'+host+'/memecoinsapi/public/admin';
function passWithdraw(id,obj) {
    var confirm = layer.confirm("您確認通過該請款審核？",{
        btn: ['確認','取消']
    },function () {
        layer.close(confirm);
        var lay = layer.load(0, '提交中');
        $.get(baseUrl+'/withdraw/pass?id='+id,function (res) {
            layer.close(lay);
            if(res.status == true){
                $.pjax.reload('#pjax-container');
            }else {
                layer.msg(res.message);
            }
        });
    })
}
function rejectWithdraw(id,obj) {
    layer.prompt({title: '輸入駁回請款的原因', formType: 3}, function(string, index) {
        layer.close(index);
        var lay = layer.load(0, '提交中');
        $.get(baseUrl+'/withdraw/reject?id='+id+'&handle_note='+string,function (res) {
            layer.close(lay);
            layer.msg(res.message);
            if (res.status){
                $.pjax.reload('#pjax-container');
            }
        },'json');
    });
}
function passImgSignApp(id) {
    var confirm = layer.confirm("您確認通過該立牌訂單申請？",{
        btn: ['確認','取消']
    },function () {
        layer.close(confirm);
        var lay = layer.load(0, '提交中');
        $.post(baseUrl+'/lipai_apply',{'id': id, 'handle': 'pass'},function (res) {
            layer.close(lay);
            layer.msg(res.message);
            if(res.status == true){
                $.pjax.reload('#pjax-container');
            }
        });
    })
}
function overImgSignApp(id) {
    var confirm = layer.confirm("您確認已經完成該立牌訂單？",{
        btn: ['確認','取消']
    },function () {
        layer.close(confirm);
        var lay = layer.load(0, '提交中');
        $.post(baseUrl+'/lipai_apply',{'id': id, 'handle': 'over'},function (res) {
            layer.close(lay);
            layer.msg(res.message);
            if(res.status == true){
                $.pjax.reload('#pjax-container');
            }
        }, 'json');
    })
}
function rejectImgSignApp(id) {
    layer.prompt({title: '輸入駁回請款的原因', formType: 3}, function(string, index) {
        layer.close(index);
        var lay = layer.load(0, '提交中');
        $.post(baseUrl+'/lipai_apply',{'id': id, 'handle': 'reject', 'note': string},function (res) {
            layer.close(lay);
            layer.msg(res.message);
            if(res.status == true){
                $.pjax.reload('#pjax-container');
            }
        }, 'json');
    });
}
function chargeStore(id) {
    layer.prompt({title: '輸入充值的金額', formType: 3}, function(amount, index) {
        layer.close(index);
        var lay = layer.load(0, '提交中');
        $.get(baseUrl+'/store/'+id,{'handle':'charge','amount':amount},function (res) {
            layer.close(lay);
            layer.msg(res.message);
            if (res.status){
                $.pjax.reload('#pjax-container');
            }
        },'json');
    });
}

function handleStore(id, status) {
    str = status==1?"您確認恢復該店鋪權力？":"您確認停權該店鋪？";
    var confirm = layer.confirm(str,{
        btn: ['確認','取消']
    },function () {
        layer.close(confirm);
        var lay = layer.load(0, '提交中');
        $.post(baseUrl+'/store/'+id,{'_method': 'DELETE', 'handle': 'status', 'status': status},function (res) {
            layer.close(lay);
            layer.msg(res.message);
            if(res.status == true){
                $.pjax.reload('#pjax-container');
            }
        }, 'json');
    })
}

function pushMiMi(id) {
    var lay = layer.load();
    $.get(baseUrl+'/store/mimi',function (res) {
        layer.close(lay);
        layer.open({
            type: 1,
            title: '蜜蜜推荐',
            skin: 'layui-layer-rim', //加上边框
            area: ['520px', '400px'], //宽高
            content: '<div class="text-center"><h3>蜜蜜推荐店家</h3>' +
            '<small>请填入店家ID，置空不做修改</small></div>' +
            '<div style="width: 480px;margin-top: 20px">' +
            '<form class="form-horizontal" action="'+baseUrl+'/store/handle/mimi">' +
            // '<input type="hidden" name="id" value="'+id+'">' +
            '<div class="form-group row">' +
                '<label class="col-sm-3 control-label" for="title">排名1</label>' +
                '<div class="col-sm-8">' +
                    '<div class="input-group">' +
                        '<span class="input-group-addon"><i class="fa fa-pencil"></i></span>' +
                        '<input id="first" name="first" value="" class="form-control" placeholder="'+res[1]+'" type="text">' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="form-group row">' +
                '<label class="col-sm-3 control-label" for="content">排名2</label>' +
                '<div class="col-sm-8">' +
                    '<div class="input-group">' +
                        '<span class="input-group-addon"><i class="fa fa-pencil"></i></span>' +
                        '<input id="second" name="second" value="" class="form-control" placeholder="'+res[2]+'" type="text">' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="form-group row">' +
            '<label class="col-sm-3 control-label" for="content">排名3</label>' +
            '<div class="col-sm-8">' +
            '<div class="input-group">' +
            '<span class="input-group-addon"><i class="fa fa-pencil"></i></span>' +
            '<input id="content" name="third" value="" class="form-control" placeholder="'+res[3]+'" type="text">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="row" style="text-align: center">' +
                '<input class="btn btn-success" type="submit" value="提交">' +
            '</div>' +
            '</form>' +
            '</div>'
        });
    },'json');
    setTimeout(function () {
        layer.closeAll('loading');
    },4000);
}
function sendActivity(form) {
    var title = form.title.value;
    var content = form.content.value;
    var id = form.id.value;
    $.get(baseUrl+'/activity/pushActivity',{'title':title,'content':content,'id':id},function (res) {
        if (res.status){
            layer.msg(res.message);
        }else {
            layer.alert(res.message, {
                skin: 'layui-layer-lan'
                ,closeBtn: 0
                ,anim: 4 //动画类型
            });
        }

    },'json');
}
function alertImg(url) {
    var json = {
        "title": "", //相册标题
        "id": 123, //相册id
        "start": 0, //初始显示的图片序号，默认0
        "data": [   //相册包含的图片，数组格式
            {
                "alt": "图片名",
                "pid": 666, //图片id
                "src": url, //原图地址
                "thumb": "" //缩略图地址
            }
        ]
    }
    layer.photos({
        photos: json,
        anim: 5
    });
}
function storeTransfer(id, status) {
    var statusArr = {
        'pending': '重新處理',
        'processing': '通過',
        'refunded': '退還',
        'cancelled': '取消',
        'completed': '完成',
    };
    var str = "您確認"+statusArr[status]+"該申請？";
    var confirm = layer.confirm(str,{
        btn: ['確認','取消']
    },function () {
        layer.close(confirm);
        var lay = layer.load(0, '提交中');
        $.post(baseUrl+'/store_transfer/'+id,{'_method': 'PUT', 'status': status},function (res) {
            layer.close(lay);
            layer.msg(res.message);
            if(res.status == true){
                $.pjax.reload('#pjax-container');
            }
        }, 'json');
    })
}