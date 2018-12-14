<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="modal fade" id="registerDialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="register_form" role="form" action="/account/register/?do=submit" method="post"
                  onsubmit="return false;">
                <div class="modal-header">
                    <h4 class="modal-title">Register</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>

                    <div class="form-group">
                        <label for="register_username">Username(No longer than 32 characters)</label>
                        <input type="text" class="form-control" id="register_username" name="register_username"
                               placeholder="Your username."/>
                        <div id="err_username" style="padding:5px 0px 0px 0px;display: none;">
                            <div class="alert alert-info">
                                <span>Username already exists.</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="register_email">Email</label>
                        <input type="text" class="form-control" id="register_email" name="register_email"
                               placeholder="Your email."/>
                        <div id="err_email" style="padding:5px 0px 0px 0px;display: none;">
                            <div class="alert alert-info">
                                <span>Email already exists.</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="register_password">Password</label>
                        <input type="password" class="form-control" id="register_password" name="register_password"
                               placeholder="Your password."/>
                    </div>

                    <div class="form-group">
                        <label for="register_captcha">Captcha(Only include UPPERCASE letters)
                            <img id="captcha_image" src="/misc/captcha/" style="width: 100px; height:30px;"/>
                        </label>
                        <input type="text" class="form-control" id="register_captcha" name="register_captcha"
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
    register_password.onchange = function () {
        has_md5 = false;
    };
    submit_button.onclick = function () {
        $('#err_captcha').hide();
        $('#err_username').hide();
        $('#err_email').hide();
        if (has_md5 === false) {
            var password_input = document.getElementById("register_password");
            password_input.value = $.md5(password_input.value);
        }
        $.ajax({
            async: true,
            type: "POST",
            url: '/account/register/?do=submit',
            contentType: "application/x-www-form-urlencoded; charset=utf-8",
            data: $("#register_form").serialize(),
            dataType: "text",
            success: function (data) {
                var d = eval('(' + data + ')');
                if (d.status === 'SUCCESS') {
                    window.location.href = "/account/my-settings/";
                }
                if (d.status === 'CAPTCHA_ERR') {
                    $('#err_captcha').show();
                } else if (d.status === 'USERNAME_EXISTS') {
                    $('#err_username').show();
                } else if (d.status === 'EMAIL_EXISTS') {
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


