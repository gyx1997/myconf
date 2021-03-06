<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>


<div class="modal fade" id="loginDialog">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="login_form" role="form" action="/account/login/?do=submit" method="post" onsubmit="return false;">
                <div class="modal-header">
                    <h4 class="modal-title">Login</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
                    <input type="hidden" name="redirect" value="<?php echo $redirect; ?>"/>
                    <div class="form-group">
                        <label for="register_username">Username OR Email</label>
                        <input type="text" class="form-control" id="login_entry" name="login_entry"
                               placeholder="Your username."/>
                        <div id="err_username" style="padding:5px 0px 0px 0px;display: none;">
                            <div class="alert alert-info">
                                <span>Username OR Email does NOT EXIST.</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="login_password">Password</label>
                        <input type="password" class="form-control" id="login_password" name="login_password"
                               placeholder="Your password."/>
                        <div id="err_password" style="padding:5px 0px 0px 0px;display: none;">
                            <div class="alert alert-info">
                                <span>Wrong password.</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="login_captcha">Captcha&nbsp;&nbsp;<span
                                    class="badge badge-info">Uppercase Only</span>
                            <div class="row mt-3"></div>
                            <img id="captcha_image" src="/misc/captcha/" style="width: 100px; height:30px;"/>
                        </label>
                        <input type="text" class="form-control" id="login_captcha" name="login_captcha"
                               placeholder="Enter the text in the captcha above."/>
                        <div id="err_captcha" style="padding:5px 0px 0px 0px;display: none;">
                            <div class="alert alert-info">
                                <span>Wrong captcha.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="submit_button" type="button" class="btn btn-primary">Submit</button>
                    <button id="reset_pwd_button" type="button" class="btn btn-secondary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('#loginDialog').modal({backdrop: 'static', keyboard: false});
    var has_md5 = false;
    login_password.onchange = function () {
        has_md5 = false;
    };

    submit_button.onclick = function () {
        $('#err_captcha').hide();
        $('#err_username').hide();
        $('#err_email').hide();
        if (has_md5 === false) {
            var password_input = document.getElementById("login_password");
            password_input.value = $.md5(password_input.value);
            has_md5 = true;
        }
        $.ajax({
            async: true,
            type: "POST",
            url: '/account/login/?do=submit',
            contentType: "application/x-www-form-urlencoded; charset=utf-8",
            data: $("#login_form").serialize(),
            dataType: "text",
            success: function (data) {
                var d = eval('(' + data + ')');
                if (d.status === 'SUCCESS') {
                    window.location.href = d.redirect;
                } else {
                    $('#captcha_image').src = "/misc/captcha/?" + Math.random();
                    if (d.status === 'CAPTCHA_ERR') {
                        $('#err_captcha').show();
                    } else if (d.status === 'USERNAME_ERROR') {
                        $('#err_username').show();
                    } else if (d.status === 'PASSWORD_ERROR') {
                        $('#err_password').show();
                    }
                }
            },
            error: function () {

            }
        });
        return false;
    };
    captcha_image.onclick = function () {
        this.src = "/misc/captcha/?refresh=" + Math.random();
    };
    $('#reset_pwd_button').click(function () {
        window.location.href = "/account/reset-password/?do=verifyKey&email=" + window.btoa($('#login_entry').val());
    });
</script>


