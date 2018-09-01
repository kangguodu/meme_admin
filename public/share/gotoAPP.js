var isAndroid;
var isiOS;
var search;
var url='memecoins://memecoins.com';
window.onload=function(){

    var u=navigator.userAgent;
    isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1;
    isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
    if(isAndroid){
        console.log("android");
    }

    if(isiOS){
        console.log("ios");
    }

    var fileName = window.location.href;
    fileName=fileName.substring(fileName.lastIndexOf("/") + 1);

    var id = fileName.substring(0, fileName.lastIndexOf(".htm"));

    console.log('id', id);

    url += '/shops-detail/' + id;

    console.log('url', url);

    goApp();
}




function goApp(){
    console.log("go app...");
    if(isAndroid){
        window.location.href = url;
        setTimeout(function() {
            window.location.href="https://play.google.com/store/apps/details?id=com.techrare.memecoins";
    },2000)
    }

    if(isiOS){
        window.location.href=url;
        setTimeout(function() {
            window.location.href="https://itunes.apple.com/cn/app/memecoins/id1406646928?mt=8";
    },2000)
    }
}