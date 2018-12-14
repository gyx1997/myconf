<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php include VIEWPATH . 'account/header.php'; ?>

<div class="col-md-10">
    <div class="col-md-12">
        <div class="row">
            <div class="container col-md-6">
                <div class="card">
                    <form id="setting_form_general" name="setting_form_general" enctype="multipart/form-data"
                          action="/account/my-settings/general/?do=submit" method="post">
                        <div class="card-header">General Settings</div>
                        <div class="card-body">

                            <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
                            <div class="form-group">
                                <lable for="account_name"> User Name</lable>
                                <input type="text" class="form-control" id="account_name" name="account_name"
                                       disabled="disabled" value="<?php echo $login_user['user_name']; ?>"/>
                            </div>

                            <div class="row mt-3"></div>

                            <div class="form-group">
                                <lable for="account_email"> User Email</lable>
                                <input type="text" class="form-control" id="account_email" name="account_email"
                                       disabled="disabled" value="<?php echo $login_user['user_email']; ?>"/>
                            </div>

                            <div class="row mt-3"></div>
                            <div class="form-group">
                                <label for="account_org"> Organization </label>
                                <input type="text" class="form-control" id="account_org" name="account_org"
                                       placeholder="Your Organization"
                                       value="<?php echo $login_user['organization']; ?>"/>
                            </div>
                            <div class="row mt-3"></div>
                            <div class="form-group">
                                <button type="submit" class="form-control">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="container col-md-6">
                <div class="card">
                    <form id="setting_form_avatar" name="setting_form_avatar" enctype="multipart/form-data"
                          action="/account/my-settings/avatar/?do=submit" method="post">
                        <div class="card-header">Avatar</div>
                        <div class="card-body">
                            <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
                            <div class="form-inline">
                                <div class="col-md-12">
                                    <div class="row">
                                        <button class="btn"
                                                onclick="$('#avatar_image').click();return false;">
                                            Click here to upload
                                        </button>
                                        <input type="file" style="display:none;" accept=".jpg,.jpeg,.png"
                                               name="avatar_image"
                                               class="form-control" id="avatar_image"
                                               placeholder="Upload Your Avatar."/>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3"></div>
                            <div class="form-group">
                                <img alt="Avatar" src="<?php echo '/data/avatar/' . $login_user['avatar']; ?>"
                                     style="height:200px; width: 200px;"/>
                            </div>
                        </div>
                         
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#avatar_image').change(function () {
        document.getElementById('setting_form_avatar').submit();
    });
</script>

<?php include VIEWPATH . 'account/footer.php'; ?>

