<?php 

	require('header.php'); 
	require('packages/wechat.php');

  	oauth2(0);

?>

<div class="login-main-page">
	

<header>
	<nav style="padding-top: 8px;padding-bottom:30px;">
		<div class="header-title">
			<div class="header-back"><span class="glyphicon glyphicon-menu-left"></span></div>
			<div class="header-main-title">登录</div>
		</div>
	</nav>
</header>

<section>
	<div class="logo-area register-area">
		<img width="180" height="80" src="images/xiaocai_logo.svg" />
	</div>
	
	<div class="setting-list change-password-input">
		<ul>
			<li id="login-phone-num-input">
				<input type="tel" max="11" placeholder="手机号" />
			<li id="login-password-o-input"><input type="password" placeholder="密码" /></li>
		</ul>
	</div>

	<div class="change-password-submit-button">
		<a id="btn-confirm-login" style="background:rgb(229,0,45)" class="button button-caution button-pill">登录</a>
		<div class="fast-register true-register">
			<span onclick="javascript:window.location.href='register.php'" id="login-fast-register">快速注册</span>
			<span onclick="javascript:window.location.href='password_find.php'" id="login-find-pw">找回密码</span>
		</div>
		<div class="fast-register">————— 或 —————</div>
		<div class="wechat-logo">
			<img src="images/wechat.png">
		</div>
	</div>

	<div class="loading">
		<div class="loading-main"><span class="glyphicon glyphicon-option-horizontal"></span><span class="glyphicon glyphicon-option-horizontal"></span></div>
	</div>

</section>

</div>

<script type="text/javascript">

	$(document).ready(function(){

		$('.header-back').click(function(){
			history.go(-1);
		});

		$('.change-password-input #login-phone-num-input input').attr('value',localStorage.mobileNum);

		$('#btn-confirm-login').click(function(){
			displayALertForm('正在为您登录,请稍候...');
			if(inputInfoIsNull('.change-password-input ul li')){
				var smobile=$('.change-password-input ul #login-phone-num-input input').val();
				var password=$('.change-password-input ul #login-password-o-input input').val();
				signInByMobile(smobile,password,function(data){
					var jsonData=JSON.parse(data);
					displayALertForm(jsonData['msg']);
					if(jsonData['msg']=='登录成功'){
						localStorage.uid=jsonData['data']['uid'];
						localStorage.nickname=jsonData['data']['nickname'];
						localStorage.tokenID=jsonData['data']['token_id'];
						localStorage.headimgurl=jsonData['data']['headimgurl']==''?'images/default_photo.png':jsonData['data']['headimgurl'];
						localStorage.isReply=jsonData['data']['is_reply'];
						localStorage.mobileNum=smobile;
						localStorage.loginByWechat=false;
						localStorage.isLogin=true;
						displayALertForm('登录成功,2秒后将自动跳转...',2000);
						if(browser.versions.iPhone){
							alert('登录成功,点击确定跳转');
							self.location="index.php";
						}else if(browser.versions.webKit){
							alert('登录成功,点击确定跳转');
							self.location="index.php";
						}else{
							setTimeout("location.href='index.php'",2000);
						}
					}else{
						localStorage.isLogin=false;
					}
				});
			}else{
				displayALertForm('请完整填写信息');
			}
		});
	
		$('section').css('marginTop',$('header').height()+50);
		$('footer').hide();

		$('.wechat-logo').click(function(){
			displayALertForm('正在为您跳转到微信登录...');
			WECHAT_REDIRECT_URI=window.location.href;
			var WECHAT_GET_CODE="https://open.weixin.qq.com/connect/oauth2/authorize?appid="+WECHAT_APPID+"&redirect_uri="+WECHAT_REDIRECT_URI+"&response_type=code&scope=snsapi_userinfo#wechat_redirect";
			// var WECHAT_GET_CODE="https://open.weixin.qq.com/connect/qrconnect?appid="+WECHAT_APPID+"&redirect_uri="+WECHAT_REDIRECT_URI+"&response_type=code&scope=snsapi_login#wechat_redirect";
			window.location.href=WECHAT_GET_CODE;
		});

	});

</script>

<?php  require('footer.php'); ?>
