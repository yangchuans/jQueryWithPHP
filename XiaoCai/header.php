<!DOCTYPE html>
<html manifest="xiaocai.appcache">
<head>
	<title>晓菜</title>
	<meta charset="utf8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="extension/bootstrap.min.css" />
	<script type="text/javascript" src="extension/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="extension/bootstrap.min.js"></script>
	<script type="text/javascript" src="extension/unslider.min.js"></script>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<link rel="stylesheet" type="text/css" href="extension/buttons.css">
	<script type="text/javascript">
		/******************************页面访问记录栈******************************/
		//构造函数
		function StorageStack(current,prev){
			this.currentPage=current;
			this.pageVisitedCount=0;
			this.prevPage=new Array();
			this.prevPage[this.pageVisitedCount]=prev;
		}
			//进栈
			StorageStack.prototype.push=function(val){
				this.pageVisitedCount+=1;
				this.prevPage[this.pageVisitedCount]=val;
			}
			//出栈
			StorageStack.prototype.pop=function(){
				if(this.pageVisitedCount!=0){
					return this.prevPage[this.pageVisitedCount--];
				}else{
					return null;
				}
			
			}
			//取栈顶
			StorageStack.prototype.top=function(i){
				return this.prevPage[i];
			}
			//是否空
			StorageStack.prototype.isEmpty=function(){
				return this.pageVisitedCount==0 ? true:false;
			}
			//更改当前页面
			StorageStack.prototype.changeCurrentPage=function(current){
				this.currentPage=current;
			}

			StorageStack.prototype.forEach=function(f){
				for(var i=0;i<=this.pageVisitedCount;i++){
					f(this.prevPage[i]);
				}
			}

			StorageStack.prototype.toString=function(){
				return JSON.stringify(this);
			}

		//将JSON数据转换为栈
		function JSON2Stack(o){
			var stackObj=JSON.parse(o);
			var lsObj=new StorageStack(stackObj.currentPage,stackObj.prevPage[0]);
			lsObj.pageVisitedCount=stackObj.pageVisitedCount;
			for(var i=1;i<=stackObj.pageVisitedCount;i++){
				lsObj.push(stackObj.prevPage[i]);
			}
			return lsObj;
		}
		/******************************页面访问记录栈******************************/

		/**********************************函数库**********************************/
		
		//当前页面可否滚动,在加载页面和弹出右侧工具栏的时候禁止滚动,默认可滚动
		var docIsMoved=1;
		//控制页面是否可滚动
		function setNoTouchMove(){docIsMoved=0;}
		function setTouchMove(){docIsMoved=1;}

		//返回上一个页面
		function backPreviosPage(currentPage){
			$('.loading').fadeIn();
			setNoTouchMove();
			var stackifyJSONStack=JSON2Stack(localStorage.pageStack);
			var pageLoaded=stackifyJSONStack.pop();
			$('body').load(pageLoaded,function(){
				$('.loading').fadeOut();
				setTouchMove();
				stackifyJSONStack.pageVisitedCount-=1;
				stackifyJSONStack.currentPage=pageLoaded;
				localStorage.pageStack=stackifyJSONStack;//更新localStorage
			});	
		}

		//加载新页面,pageName为要加载的页面名,elem为存放元素
		function loadPagesA(pageName,elem){
			$('.loading').fadeIn();
			setNoTouchMove();
			$(elem).load(pageName,function(){
				$('.loading').fadeOut();
				setTouchMove();
				var stackifyJSONStack=JSON2Stack(localStorage.pageStack);
				stackifyJSONStack.pageVisitedCount+=1;
				stackifyJSONStack.push(stackifyJSONStack.currentPage);
				stackifyJSONStack.currentPage=pageName;
				localStorage.pageStack=stackifyJSONStack;
			});
		}

		//显示信息提示框
		function displayALertForm(text,timeInterval){
			timeInterval=timeInterval==null ? 2000:timeInterval;
			var alertForm="<div class=\"alert-form\"></div>";
			$('body').append(alertForm);
			$('.alert-form').html(text);
			$('.alert-form').fadeIn();
			setTimeout(function(){
				$('.alert-form').fadeOut();
			},timeInterval);
		}

		function checkMobile(sMobile){
    		if(!(/^1[3|4|5|8][0-9]\d{4,8}$/.test(sMobile))){
        		return false;
    		}else{
    			return true;
    		}
		}

		function inputInfoIsNull(elem){
			var flag=0;
			$(elem).each(function(index,element){
				if($(element).find('input').val()==''){
					flag+=1;
				}
			});
			return flag===0;
		}

		/*********************************AJAX请求*********************************/

		var rootURL="curl/";

		/**
		* 通过手机号注册用户
		* @param mobile 手机号
		* @param password 密码
		* @param repassword 确认密码
		* @param code 手机验证码
		* @return JSONObject [uid|mobile|token_id]
		**/

		function regByMobile(p_mobile,p_password,p_repassword,p_code,callback){
			$.post(
				rootURL+"regbymobile.php",
				{
					mobile:p_mobile,
					password:p_password,
					repassword:p_repassword,
					code:p_code
				},callback);
		}

		/**
		* 通过手机号登录
		* @param mobile 手机号
		* @param password 密码
		* @return JSONObject [uid|nickname|is_reply(是否有回复留言 1有 0无)|headimgurl|token_id]
		*/

		function signInByMobile(p_mobile,p_password,callback){
			$.post(
				rootURL+"login.php",
				{
					mobile:p_mobile,
					password:p_password
				},callback);
		}

		/**
		* 注销登出
		* @param token_id 登录返回的token_id
		* @return Nothing
		*/

		function logOut(p_token_id,callback){
			$.post(
				rootURL+"logout.php",
				{token_id:p_token_id},
				callback);
		}

		/**
		* 修改昵称和头像
		* @param token_id 登录返回的token_id
		* @param nickname 新昵称
		* @param headimgurl 二进制文件头像
		* @return JSONObject [uid|nickname|headimgurl|token_id]
		*/

		function changeUserData(p_token_id,p_nickname,p_headimgurl,callback){
			$.post(
				rootURL+"changedata.php",
				{
					token_id:p_token_id,
					nickname:p_nickname,
					headimgurl:p_headimgurl
				},callback);
		}

		/**
		* @param mobile 手机号
		* @param type 发送类型 1 注册 2 忘记密码
		* @return Nothing
		*/

		function sendSms(p_mobile,p_type,callback){
			$.post(
				rootURL+"sendsms.php",
				{
					mobile:p_mobile,
					type:p_type
				},callback);
		}

		/**
		* 找回密码
		* @param mobile 手机号
		* @param password 密码
		* @param repassword 确认密码
		* @param code 手机验证码
		* @return JSONObject
		*/

		function forgotPassword(p_mobile,p_password,p_repassword,p_code,callback){
			$.post(
				rootURL+"forgotpassword.php",
				{
					mobile:p_mobile,
					password:p_password,
					repassword:p_repassword,
					code:p_code
				},callback);
		}

		/**
		* 修改密码
		* @param mobile 手机号
		* @param password 密码
		* @param repassword 确认密码
		* @param oldpassword 旧密码
		* @return JSONObject
		*/

		function changePassword(p_mobile,p_password,p_repassword,p_oldpassword,callback){
			$.post(
				rootURL+"changepassword.php",
				{
					mobile:p_mobile,
					password:p_repassword,
					repassword:p_repassword,
					oldpassword:p_oldpassword
				},callback);
		}

		/**
		* 关于页面
		* @return About
		*/

		function getAbout(callback){
			$.post(rootURL+"about.php",{},callback);
		}

		/**
		* 留言列表
		* @param token_id
		* @return JSONObject
		* @return article_image 留言的文章的图片地址
		* @return article_title 留言的文章的标题
		* @return content 留言内容
		* @return type 模块类型 1一手好菜 2玩转厨房 3首页文章 4专题
		* @return status 状态0正常 1后台回复未读 2用户已读
		* @return created_time 留言时间
		*/

		function getReply(p_token_id,callback){
			$.post(
				rootURL+"reply.php",
				{token_id:p_token_id},
				callback);
		}

     	/**
	  	* @param nil
     	* @return id
     	* @return image
     	* @return title
     	* @return type
     	* @return id
     	* @return title
     	* @return papaer
     	* @return browse_num
     	* @return title
     	* @return created_time
     	* @return video_id
     	* @return video_url_360
     	* @return video_url_480video_url_720
     	* @return video_url_1080
     	* @return arrange_image_url
     	* @return is_vip
     	* @return image
     	* @return big
     	* @return image
     	* @return small_image
     	*/

      	function getHome(callback){
          	$.post(
                rootURL+'home.php',
            	{},callback);
     	}
     
     	/**
     	* @param nil
     	* @return id
     	* @return title
     	* @return icon
     	* @return childern
     	*/

      	function getRecipeClassify(callback){
          	$.post(
                rootURL+'recipeclassify.php',
              	{},callback);
     	}
     
     	/**
     	* @param id
     	* @return id
     	* @return title
     	* @return paper
     	* @return browse_num
     	* @return title
     	* @return created_time
     	* @return video_id
     	* @return video_url_360
     	* @return video_url_480video_url_720
     	* @return video_url_1080
     	* @return arrange_image_url
     	* @return is_vip
     	* @return image
     	*/

	    function getRecipeList(p_id,callback){
	        $.post(
	            rootURL+'recipelist.php',
	            {
	                id:p_id
	            },callback);
	    }
     
	    /**
	    * @param id
	    * @param comments_id
	    * @return id
	   	* @return title
	    * @return paper
	    * @return browse_num
     	* @return title
	    * @return created_time
	    * @return video_id
	    * @return video_url_360
	    * @return video_url_480video_url_720
	    * @return video_url_1080
	    * @return arrange_image_url
	    * @return prepare_time
	    * @return image
	    * @return cooking_time
	    * @return enjoy_num
	    * @return id
	    * @return user_id
	    * @return username
	    * @return content
	    * @return headimgurl
	    * @return created_time
	    * @return reply_username
	    * @return reply_content
	    * @return reply_time
	    */

	    function getRecipeInfo(p_id,p_comments_id,callback){
	        $.post(
	            rootURL+'recipeinfo.php',
	            {
	                id:p_id,
	                comments_id:p_comments_id
	            },callback);
	    }

	    /**
	    * @param id
	  	* @return id
	    * @return content
	    * @return children
	    * @return id
	    * @return type
		* @return content
	    * @return tips
	    * @return recommened
	    */

	    function getRecipeInfoFormula2(p_id,callback){
	        $.post(
	            rootURL+'recipeinfoformula16.php',
	            {
	                id:p_id
	            },callback);
	    }

	    /**
	    * @param id
	  	* @return id
	    * @return content
	    * @return children
	    * @return id
	    * @return type
		* @return content
	    * @return messsage
	    * @return node
	    */

	    function getRecipeInfoFormula(p_id,callback){
	        $.post(
	            rootURL+'recipeinfoformula.php',
	            {
	                id:p_id
	            },callback);
	    }

	    /**
	    * @param nil
	    * @return id
	    * @return title
	    * @return paper
	    * @return browse
	    * @return num
	    * @return small_image
	    * @return big_image
	    * @return created_time
	    */

	    function getSkillsList(callback){
	        $.post(
	            rootURL+'skillslist.php',
	            {},callback);
	    }

	    /**
	    * @param id
	    * @param comment_id
	    * @return id
	    * @return title
	  	* @return papaer
	    * @return content
	    * @return browse_num
	    * @return small_image
	    * @return big_image
	    * @return created_time
	    * @return id
	    * @return user_id
	    * @return username
	    * @return content
	    * @return headimgurl
	    * @return created_time
	    * @return reply_username
	    * @return reply_content
	    * @return reply_time
	    */

	    function getSkillsInfo(p_id,p_comment_id,callback){
	        $.post(
	            rootURL+'skillsinfo.php',
	            {
	                id:p_id,
	                comment_id:p_comment_id
	            },callback);
	    }

	    /**
	    * @param type
	    * @param token_id
	    * @param article_id
	    * @param content
	    * @return Nothing
	    */

	    function getComments(p_type,p_token_id,p_article_id,p_content,callback){
	        $.post(
	       	    rootURL+'comments.php',
	            {
	                type:p_type,
	                token_id:p_token_id,
	                article_id:p_article_id,
	                content:p_content
	            },callback);
	    }


		/*********************************AJAX请求*********************************/

		/**********************************函数库**********************************/

		/*******************************全局变量区域*******************************/
		
		var isSlided=false;//侧边栏是否被滑出
		var footerIsDisplayed=false;//底部是否被显示

		//使用localSorage存储当前页面
		var pages=new StorageStack('index.php','index.php');
		localStorage.pageStack=pages;
		
		/*******************************全局变量区域*******************************/

	</script>
</head>

<body>
<?php include('login_column.php'); ?>

