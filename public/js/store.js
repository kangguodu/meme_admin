var url='/memecoinsapi/public/admin/';

function apply(id) {
    if(confirm('您確認已經處理完此申請了嗎？')){
        token = $('input[name="_token"]').val();
        $.post(url+'storeapply/handle',{'id':id,'_token':token},function (data) {
            if (data=='success') {
                window.location.reload();
            }else {
                alert('系統出錯');
            }
        })
    }
}

function handle(id) {
    if(confirm('您確認已經處理完此申請了嗎？')){
        token = $('input[name="_token"]').val();
        $.post(url+'cooperation/handle',{'id':id,'_token':token},function (data) {
            if (data=='success') {
                window.location.reload();
            }else {
                alert('系統出錯');
            }
        })
    }
}

function destory(id) {
    if(confirm('您確認刪除此條記錄？')){
        token = $('input[name="_token"]').val();
        $.post(url+'storeapply/delete',{'_token':token,'id':id},function (msg) {
            window.location.reload();
        });
    }
}

function cooperation_delete(id) {
    if(confirm('您確認刪除此條記錄？')){
        token = $('input[name="_token"]').val();
        $.post(url+'cooperation/delete',{'_token':token,'id':id},function (msg) {
            window.location.reload();
        });
    }
}