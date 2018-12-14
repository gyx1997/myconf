<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/14
 * Time: 16:09
 */
defined('BASEPATH') OR die('No direct script access allowed.');
?>
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="modal fade" id="registerDialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reset_pwd_form" role="form" action="" method="post"
                  onsubmit="return false;">
                <div class="modal-header">
                    <h4 class="modal-title">Reset Password</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
                    <div><?php echo $status . '  ' . $email; ?></div>
                    <div class="alert alert-primary">
                        Do not close this page unless you have received the verification email and finish the form
                        below!
                    </div>
                    <div class="form-group">
                        <label for="user_email">Email</label>
                        <input type="text" class="form-control" id="user_email" name="user_email"
                               placeholder="Your Email." value="<?php echo $email; ?>"/>
                        <div id="err_email" style="padding:5px 0px 0px 0px;display: none;">
                            <div class="alert alert-info">
                                <span>Email does not exist.</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="verification_key">Key</label>
                        <input type="text" class="form-control" id="verification_key" name="verification_key"
                               placeholder="Key in the verification email."/>
                        <div id="err_key" style="padding:5px 0px 0px 0px;display: none;">
                            <div class="alert alert-info">
                                <span>Wrong key.</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_password">Password</label>
                        <input type="password" class="form-control" id="user_password" name="user_password"
                               placeholder="Your new password."/>
                    </div>

                    <div class="form-group">
                        <label for="reset_pwd_captcha">Captcha <span class="badge badge-info">Only include UPPERCASE letters</span>
                            <img id="captcha_image" src="/misc/captcha/" style="width: 100px; height:30px;"/>
                        </label>
                        <input type="text" class="form-control" id="reset_pwd_captcha" name="reset_pwd_captcha"
                               placeholder="Enter the text in the captcha above."/>
                        <div id="err_captcha" style="padding:5px 0px 0px 0px;display: none;">
                            <div class="alert alert-info">
                                <span>Wrong captcha.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="submit_button" type="button" class="btn btn-success">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>

    $('#registerDialog').modal({backdrop: 'static', keyboard: false});

    var has_md5 = false;
    user_password.onchange = function () {
        has_md5 = false;
    };
    submit_button.onclick = function () {
        $('#err_captcha').hide();
        $('#err_username').hide();
        $('#err_email').hide();
        if (has_md5 === false) {
            var password_input = document.getElementById("user_password");
            password_input.value = $.md5(password_input.value);
        }
        $.ajax({
            async: true,
            type: "POST",
            url: '/account/reset-password/?do=submitNewPwd',
            contentType: "application/x-www-form-urlencoded; charset=utf-8",
            data: $("#reset_pwd_form").serialize(),
            dataType: "json",
            success: function (data) {
                var d = data;
                if (d.status === 'SUCCESS') {
                    window.location.href = "/account/my-settings/";
                }
                if (d.status === 'CAPTCHA_ERR') {
                    $('#err_captcha').show();
                } else if (d.status === 'EMAIL_VERIFY_FAILED') {
                    $('#err_key').show();
                } else if (d.status === 'EMAIL_ERROR') {
                    $('#err_email').show();
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
</script>



