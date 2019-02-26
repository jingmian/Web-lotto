   <?php
  // 限定微信登陆的限定头
    require_once "../jssdk1.php";
    if(!$_COOKIE['openid']){
            header("修改成自己的微信公众号相关的信息");
            die();
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>幸运降临，大奖开启</title>
<link href="style.css" rel="stylesheet" type="text/css">
<audio src="./media/lotto.mp3" autoplay autoloop loop controls style="display: none;"></audio>
<script type="text/javascript" src="js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="js/awardRotate.js"></script>

<script type="text/javascript">
var turnplate={
		restaraunts:[],				//大转盘奖品名称
		colors:[],					//大转盘奖品区块对应背景颜色
		outsideRadius:192,			//大转盘外圆的半径
		textRadius:155,				//大转盘奖品位置距离圆心的距离
		insideRadius:68,			//大转盘内圆的半径
		startAngle:0,				//开始角度
		bRotate:false				//false:停止;ture:旋转
};

$(document).ready(function(){
	//动态添加大转盘的奖品与奖品区域背景颜色
	//此处的奖品列表数组，应该从你后台进行请求成数组，ajax返回json格式即
	$.ajax({
			type:"post",
			url:"自己后台网站",
			data: {},
			dataType: 'json',
			async : false,//设置为同步操作就可以给全局变量赋值成功
			success:function(data){
				 turnplate.restaraunts = data;//usersname为前面声明的全局变量
			}
});

	turnplate.colors = ["#FFF4D6", "#FFFFFF", "#FFF4D6", "#FFFFFF","#FFF4D6", "#FFFFFF", "#FFF4D6", "#FFFFFF","#FFF4D6", "#FFFFFF"];
	var rotateTimeOut = function (){
		$('#wheelcanvas').rotate({
			angle:0,
			animateTo:2160,
			duration:8000,
			callback:function (){
				alert('网络超时，请检查您的网络设置！');
			}
		});
	};

	//旋转转盘 item:奖品位置; txt：提示语;
	var rotateFn = function (item, txt){
		var angles = item * (360 / turnplate.restaraunts.length) - (360 / (turnplate.restaraunts.length*2));
		if(angles<270){
			angles = 270 - angles;
		}else{
			angles = 360 - angles + 270;
		}
		$('#wheelcanvas').stopRotate();
		$('#wheelcanvas').rotate({
			angle:0,
			animateTo:angles+1800,
			duration:8000,//持续时间(毫秒)
			callback:function (){
				alert(txt);
				turnplate.bRotate = !turnplate.bRotate;
			}
		});
	};


//在点击之后判断用户是否中过奖，如果中过奖
//用户可以继续抽奖，但是抽奖信息永远定格在谢谢参与这一栏
	$('.pointer').click(function (){
		if(turnplate.bRotate)return;
		turnplate.bRotate = !turnplate.bRotate;
		//奖品数量等于10,指针落在对应奖品区域的中心角度[252, 216, 180, 144, 108, 72, 36, 360, 324, 288]
		//！！！！！概率修改处，将后台概率获得的奖品号放在下面的6处，奖品就是6对应的奖品信息
		if (/*判断信息*/) {
				var url ="自己后台网站";
				$.get(url,"",function(data){
						rotateFn(data, turnplate.restaraunts[data-1]);
				},"json");
		}else{
			//如果用户已经中过奖，或者奖品库存已经为0
			//让其信息永远提示在谢谢参与这一栏
			rotateFn(6, turnplate.restaraunts[6-1]);
		}
	});
});




function rnd(n, m){
	var random = Math.floor(Math.random()*(m-n+1)+n);
	return random;
}

//页面所有元素加载完毕后执行drawRouletteWheel()方法对转盘进行渲染
window.onload=function(){
	drawRouletteWheel();
};

function drawRouletteWheel() {
  var canvas = document.getElementById("wheelcanvas");
  if (canvas.getContext) {
	  //根据奖品个数计算圆周角度
	  var arc = Math.PI / (turnplate.restaraunts.length/2);
	  var ctx = canvas.getContext("2d");
	  //在给定矩形内清空一个矩形
	  ctx.clearRect(0,0,422,422);
	  //strokeStyle 属性设置或返回用于笔触的颜色、渐变或模式
	  ctx.strokeStyle = "#FFBE04";
	  //font 属性设置或返回画布上文本内容的当前字体属性
	  ctx.font = '16px Microsoft YaHei';
	  for(var i = 0; i < turnplate.restaraunts.length; i++) {
		  var angle = turnplate.startAngle + i * arc;
		  ctx.fillStyle = turnplate.colors[i];
		  ctx.beginPath();
		  //arc(x,y,r,起始角,结束角,绘制方向) 方法创建弧/曲线（用于创建圆或部分圆）
		  ctx.arc(211, 211, turnplate.outsideRadius, angle, angle + arc, false);
		  ctx.arc(211, 211, turnplate.insideRadius, angle + arc, angle, true);
		  ctx.stroke();
		  ctx.fill();
		  //锁画布(为了保存之前的画布状态)
		  ctx.save();

		  //----绘制奖品开始----
		  ctx.fillStyle = "#E5302F";
		  var text = turnplate.restaraunts[i];
		  var line_height = 17;
		  //translate方法重新映射画布上的 (0,0) 位置
		  ctx.translate(211 + Math.cos(angle + arc / 2) * turnplate.textRadius, 211 + Math.sin(angle + arc / 2) * turnplate.textRadius);

		  //rotate方法旋转当前的绘图
		  ctx.rotate(angle + arc / 2 + Math.PI / 2);

		  /** 下面代码根据奖品类型、奖品名称长度渲染不同效果，如字体、颜色、图片效果。(具体根据实际情况改变) **/
		  if(text.indexOf("M")>0){//流量包
			  var texts = text.split("M");
			  for(var j = 0; j<texts.length; j++){
				  ctx.font = j == 0?'bold 20px Microsoft YaHei':'16px Microsoft YaHei';
				  if(j == 0){
					  ctx.fillText(texts[j]+"M", -ctx.measureText(texts[j]+"M").width / 2, j * line_height);
				  }else{
					  ctx.fillText(texts[j], -ctx.measureText(texts[j]).width / 2, j * line_height);
				  }
			  }
		  }else if(text.indexOf("M") == -1 && text.length>6){//奖品名称长度超过一定范围
			  text = text.substring(0,6)+"||"+text.substring(6);
			  var texts = text.split("||");
			  for(var j = 0; j<texts.length; j++){
				  ctx.fillText(texts[j], -ctx.measureText(texts[j]).width / 2, j * line_height);
			  }
		  }else{
			  //在画布上绘制填色的文本。文本的默认颜色是黑色
			  //measureText()方法返回包含一个对象，该对象包含以像素计的指定字体宽度
			  ctx.fillText(text, -ctx.measureText(text).width / 2, 0);
		  }
		  //添加对应图标
		  //把当前画布返回（调整）到上一个save()状态之前
		  ctx.restore();
		  //----绘制奖品结束----
	  }
  }
}

</script>
</head>
<body style="background:#e62d2d;overflow-x:hidden;">
    <img src="images/1.png" id="shan-img" style="display:none;" />
    <img src="images/2.png" id="sorry-img" style="display:none;" />
	<div class="banner">
		<div class="turnplate" style="background-image:url(images/turnplate-bg.png);background-size:100% 100%;">
			<canvas class="item" id="wheelcanvas" width="422px" height="422px"></canvas>
			<img class="pointer" src="images/turnplate-pointer.png"/>
		</div>
	</div>
</body>
    <!--微信分享start-->
    <script src="https://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <!-- <script type="text/javascript"  src="../jquery-2.1.4.min.js"></script> -->
    <script>
        var appid,timestamp,nonceStr,signature,jsApiList;
        var openid="<?php echo $_COOKIE['openid']; ?>";
        var nickname="<?php echo $_COOKIE['nickname']; ?>";
        var headimgurl="<?php echo $_COOKIE['headimgurl']; ?>";


        wx.config({
            debug: false,
            appId: '<?php echo $signPackage["appId"];?>',
            timestamp: <?php echo $signPackage["timestamp"];?>,
            nonceStr: '<?php echo $signPackage["nonceStr"];?>',
            signature: '<?php echo $signPackage["signature"];?>',
            jsApiList: [
                'checkJsApi',
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'onMenuShareQQ',
                'onMenuShareWeibo',
                'chooseImage',
                'previewImage',
                'uploadImage',
                'downloadImage'
             ]
        });

        // 微信分享
        wx.ready(function(){

            var title = "恭喜"+nickname+'抽到了大奖，我也去试试吧!';     //分享标题
            var desc = "恭喜"+nickname+'抽到了大奖，我也去试试吧!';      //分享描述
            var desc1 = "恭喜"+nickname+'抽到了大奖，我也去试试吧!';      //分享描述
            var imgurl = headimgurl;  //分享图片
            var shareurl='自己网站';   //分享链接
            var link_url="自己网站";   //分享完跳转链接

            wx.onMenuShareTimeline({

                title: desc1,
                link: shareurl,
                imgUrl: imgurl,
                trigger: function (res) {
                   //alert('用户点击分享到朋友圈');
                },
                success: function (res) {
                   // _hmt.push(['_trackEvent', 'sharewx', 'fxcg', '']);
                   // window.location.href = link_url;
                },
                cancel: function (res) {
                //alert('已取消');
                },
                fail: function (res) {
                //alert(JSON.stringify(res));
                }
            });
            wx.onMenuShareAppMessage({
                title: title, // 分享标题
                desc: desc, // 分享描述
                link: shareurl, // 分享链接
                imgUrl: imgurl, // 分享图标
                type: '', // 分享类型,music、video或link，不填默认为link
                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                success: function () {
                    // 用户确认分享后执行的回调函数
                    // _hmt.push(['_trackEvent', 'sharewx', 'fxcg', '']);
                    // window.location.href = link_url;

                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                }
            });
            wx.onMenuShareQQ({
                title: title, // 分享标题
                desc: desc, // 分享描述
                link: shareurl, // 分享链接
                imgUrl: imgurl, // 分享图标
                success: function () {
                   // 用户确认分享后执行的回调函数
                   // window.location.href = link_url;
                },
                cancel: function () {
                   // 用户取消分享后执行的回调函数
                }
            });
            wx.onMenuShareWeibo({
                title: title, // 分享标题
                desc: desc, // 分享描述
                link: shareurl,// 分享链接
                imgUrl: imgurl, // 分享图标
                success: function () {
                   // 用户确认分享后执行的回调函数
                   // window.location.href = link_url;
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                }
            });

            //wx.hideOptionMenu();

        });

        wx.error(function(res){
            //alert(res+"111");
        });


        function setShareword(str){

            var title = "恭喜"+nickname+'抽到了大奖，我也去试试吧!';     //分享标题
            var desc = str;                     //分享描述
            // var desc = '美的智能冰箱你值得拥有!';      //分享描述
            var imgurl =headimgurl;  //分享图片
            var shareurl='自己网站';   //分享链接
            var link_url="自己网站";   //分享完跳转链接

            wx.onMenuShareTimeline({
            title: desc,
            link: link_url,
            imgUrl: imgurl,
                trigger: function (res) {
                   //alert('用户点击分享到朋友圈');
                },
                success: function (res) {
                   // 用户确认分享后执行的回调函数
                    // window.location.href = link_url;
                   //alert('已分享');
                },
                cancel: function (res) {
                //alert('已取消');
                },
                fail: function (res) {
                //alert(JSON.stringify(res));
                }
            });
            wx.onMenuShareAppMessage({
                title: title, // 分享标题
                desc: desc, // 分享描述
                link: link_url, // 分享链接
                imgUrl: imgurl, // 分享图标
                type: '', // 分享类型,music、video或link，不填默认为link
                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                success: function () { 
                    // 用户确认分享后执行的回调函数 
                },
                cancel: function () { 
                    // 用户取消分享后执行的回调函数
                }
            });
            wx.onMenuShareQQ({
                title: title, // 分享标题
                desc: desc, // 分享描述
                link: link_url, // 分享链接
                imgUrl: imgurl, // 分享图标
                success: function () {
                   // 用户确认分享后执行的回调函数

                },
                cancel: function () {
                   // 用户取消分享后执行的回调函数
                }
            });
            wx.onMenuShareWeibo({
                title: title, // 分享标题
                desc: desc, // 分享描述
                link: link_url, // 分享链接
                imgUrl: imgurl, // 分享图标
                success: function () { 
                   // 用户确认分享后执行的回调函数  
                },
                cancel: function () { 
                    // 用户取消分享后执行的回调函数
                }
            });
        }

    </script>
    <!-- 微信分享end -->
</html>