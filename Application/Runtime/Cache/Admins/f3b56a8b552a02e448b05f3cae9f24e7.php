<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>登录入口_<?php echo ($WEB_NAME); ?>|管理系统</title>
    <script src="/zyxl_refact/Public/Common/script/jquery-1.11.1.min.js" type="text/javascript"></script>
    <script src="/zyxl_refact/Public/Admins/script/global.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/zyxl_refact/Public/Admins/style/adnim.css" type="text/css">
    <style type="text/css">
        body {
            background-color: #003FA9;
            background-image: none;
        }

        #loginBox {
            width: 630px;
            height: 324px;
            margin-top: -162px;
            margin-left: -315px;
            position: absolute;
            left: 50%;
            top: 50%;
            background-color: #fff;
            padding: 4px;
            overflow: hidden;
        }

        .loginbox {
            width: 630px;
            height: 324px;
            background: url(/zyxl_refact/Public/Admins/images/loginbj.jpg) no-repeat;
            overflow: hidden;
        }

        .login {
            margin-top: 35px;
        }

        .ukeep {
            color: #fff;
        }

        .webinfo {
            margin-top: 155px;
            margin-left: 30px;
        }

        .webname {
            font-size: 16px;
            font-family: '微软雅黑';
            color: #C00;
        }

        .domain {
            font-family: Tahoma, Geneva, sans-serif;
            color: #666;
        }

        .logincode {
            border: 1px solid #80c4ee;
            height: 16px;
            line-height: 16px;
            width: 67px;
            float: left;
            margin-top: 2px;
        }

        .loginint {
            border: 1px solid #80c4ee;
            height: 16px;
            line-height: 16px;
            width: 140px;
        }

        .logininput {
            color: #fff;
            line-height: 25px;
            height: 25px;
        }

        .logininput img {
            margin-left: 5px;
            display: block;
            float: left;
            margin-top: 2px;
            cursor: pointer;
        }

        .logininput span {
            display: block;
            float: left;
        }

        .logimg {
            margin-left: 5px;
            cursor: pointer;
        }

        .yanzhengcode {
            height: 30px;
            line-height: 30px;
            color: #ff0;
        }

        .yanzhengcode label {
            padding: 0 6px;
            cursor: pointer;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#checkcode").bind("click", function () {
                var url = $('#checkcode').attr('src');
                $('#checkcode').attr('src', url + '&' + new Date())
            });
        });
        function CheckLogin() {
            var PassValue = $.trim($("#upass").val());
            var NameValue = $.trim($("#uname").val());
            var CodeValue = $.trim($("#ucode").val());
            if (NameValue.length < 4) {
                alert('请输入4位以上的账号!');
                $('#uname').css('border', '1px solid #f00');
                return false;
            }
            if (PassValue.length < 6) {
                alert('请输入管理员密码!');
                $('#upass').css('border', '1px solid #f00');
                return false;
            }
            if (CodeValue.length != 4) {
                alert('请输入验证码!');
                $('#ucode').css('border', '1px solid #f00');
                chagecode();
                return false;
            }
        }
    </script>
</head>
<body>
<div id="loginBox">
    <div class="loginbox">
        <div class="webinfo">
            <div class="webname"><?php echo ($WEB_NAME); ?>_后台管理</div>
            <div class="domain"><?php echo ($WEB_URL); ?></div>
        </div>
        <div class="login">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <form id="form1" name="form1" method="post" onSubmit="return CheckLogin()" action="<?php echo U('login/login');?>">
                    <tr>
                        <td width="30%" height="75">&nbsp;</td>
                        <td width="40%" height="75">
                            <div class="logininput">管理账号：<input name="uname" type="text" class="loginint" id="uname"
                                                                maxlength="20"/></div>
                            <div class="logininput">登录密码：<input name="upass" type="password" class="loginint" id="upass"
                                                                maxlength="16"/></div>
                            <div class="logininput"><span>登录验证：</span><input name="ucode" type="text" class="logincode"
                                                                             id="ucode" maxlength="4"/><img
                                    id="checkcode" alt="点击换更换验证码。" src="<?php echo U('Login/getValidateCode');?>"/></div>
                        </td>
                        <td width="30%" height="75" rowspan="4">
                            <div class="yanzhengcode"><input type="checkbox" name="loginkeep" id="loginkeep" value="1"/><label
                                    for="loginkeep">保持一周登录状态</label></div>
                            <div class="denglu"><input type="image" src="/zyxl_refact/Public/Admins/images/dl.gif"/></div>
                        </td>
                    </tr>
                </form>
            </table>
        </div>
    </div>
</div>
<script src="/zyxl_refact/Public/Admins/script/admin.js" type="text/javascript"></script>
</body>
</html>