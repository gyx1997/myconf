<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/18
 * Time: 23:03
 */

defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div id="body">
    <div
            style="margin: 0 auto; width:400px;height: 350px;text-align: left;border: 1px solid #D0D0D0; border-radius: 3px;">
        <div style="padding:20px;">
            <div style="margin: 0 auto;text-align: center;">登录管理面板</div>
            <form id="login_form" role="form" action="/admin/login-submit/" method="post" onsubmit="LoginSubmit();">
                <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
                <div class="form-group">
                    <label for="password_text">管理密码</label>
                    <input type="password" class="form-control" id="password_text" name="password_text"
                           placeholder="请输入管理密码"/>
                </div>
                <?php if ($err_code == 'wrong_password') { ?>
                    <div class="alert alert-warning" id="err_tip">
                        <a href="#" class="close" data-dismiss="alert" onclick="HideErr()">
                            &times;
                        </a>
                        <span id="err_text">密码错误。</span>请检查是否漏掉了某些字符、或者大小写没有区分。
                    </div>
                <?php } ?>
                <button type="submit" class="btn btn-success">登录</button>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    function LoginSubmit() {
        var password_input = document.getElementById("password_text");
        password_input.value = $.md5(password_input.value);
        return true;
    }
</script>


