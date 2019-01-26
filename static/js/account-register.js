
$('#registerDialog').modal({backdrop: 'static', keyboard: false});
var has_md5 = false;

$('#checkEmailButton').click(function(){
    $('#err_email2').hide();
    $('#err_email_format_error').hide();
    $('#err_email').hide();

    if(emailCheck($('#register_email').val()) === false){
        $('#err_email_format_error').show();
    } else {
        $.ajax({
            async: true,
            type: "GET",
            url: '/account/register/?do=checkEmail&ajax=true',
            contentType: "application/x-www-form-urlencoded; charset=utf-8",
            data: {email: window.btoa($('#register_email').val())},
            dataType: "json",
            success: function (d) {
                if (d.status === 'SUCCESS') {
                    $('#checkEmailButton').attr('disabled', 'disabled');
                    $('#err_email2').show();
                }
                if (d.status === 'EMAIL_EXISTS') {
                    $('#err_email').show();
                }
            },
            error: function () {

            }
        });
    }
});

register_password.onchange = function () {
    has_md5 = false;
};
submit_button.onclick = function () {
    $('#err_captcha').hide();
    $('#err_username').hide();
    $('#err_email').hide();
    $('#err_verify').hide();
    $('#err_password').hide();
    $('#err_password2').hide();


    var password = $('#register_password');
    var passwordRepeat = $('#register_password_repeat');

    if(password.val() === '') {
        $('#err_password').show();
    }else {
        if (password.val() !== passwordRepeat.val()) {
            $('#err_password2').show();
        } else {
            if (has_md5 === false) {
                password.val($.md5(password.val()));
                passwordRepeat.val($.md5(passwordRepeat.val()));
            }
            $.ajax({
                async: true,
                type: "POST",
                url: '/account/register/?do=submit&ajax=true',
                contentType: "application/x-www-form-urlencoded; charset=utf-8",
                data: $("#register_form").serialize(),
                dataType: "json",
                success: function (d) {
                    if (d.status === 'SUCCESS') {
                        window.location.href = "/account/my-settings/";
                    }
                    if (d.status === 'CAPTCHA_ERR') {
                        $('#err_captcha').show();
                    } else if (d.status === 'EMAIL_EXISTS') {
                        $('#err_email').show();
                    } else if (d.status === 'VERIFY_FAILED') {
                        $('#err_verify').show();
                    }
                },
                error: function () {
                    messageBox('An internal server error occurred.', 'Message');
                }
            });
        }
    }
    return false;
};
captcha_image.onclick = function () {
    this.src = "/misc/captcha/?refresh=" + Math.random();
};