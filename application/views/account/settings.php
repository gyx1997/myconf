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
                        <div class="card-header">Account Settings</div>
                        <div class="card-body">
                            <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
                            <div class="form-group">
                                <lable for="account_name"> User Name</lable>
                                <input type="text" class="form-control" id="account_name" name="account_name"
                                       disabled="disabled" value="<?php echo $login_user['user_name']; ?>"/>
                            </div>
                            <div class="row mt-3"></div>
                            <div class="form-group">
                                <lable for="account_email">User Email&nbsp;&nbsp;&nbsp;<span class="badge badge-info">Can not be changed until next version.</span>
                                </lable>
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
        <div class="row mt-3"></div>
        <div class="row">
            <div class="container col-md-12">
                <div class="card">
                    <form id="setting_form_scholar" enctype="application/x-www-form-urlencoded"
                          action="/account/my-settings/scholar/?do=submit" method="post">
                        <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
                        <input type="hidden" name="scholarEmail" value="<?php echo $scholar_info['scholar_email']; ?>"/>
                        <div class="card-header">Author Information</div>
                        <div class="card-body">
                            <table style="width:100%;">
                                <tr>
                                    <td colspan="2">
                                        <div class="row mt-3"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:38%;">
                                        <label for="scholarFirstName">First Name</label>
                                    </td>
                                    <td style="width:62%;">
                                        <input class="form-control" name="scholarFirstName" id="scholarFirstName"
                                               type="text" value="<?php echo $scholar_info['scholar_first_name'] ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="row mt-3"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:38%;">
                                        <label for="scholarLastName">Last Name</label>
                                    </td>
                                    <td style="width:62%;">
                                        <input class="form-control" name="scholarLastName" id="scholarLastName"
                                               type="text" value="<?php echo $scholar_info['scholar_last_name'] ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="row mt-3"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:38%;">
                                        <label for="scholarInstitution">Institution&nbsp;&nbsp;<span
                                                    class="badge badge-info">LESS THAN 160 characters</span></label>
                                    </td>
                                    <td style="width:62%;">
                                        <input class="form-control" name="scholarInstitution" id="scholarInstitution"
                                               type="text" value="<?php echo $scholar_info['scholar_institution'] ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="row mt-3"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:38%;">
                                        <label for="scholarDepartment">Department&nbsp;&nbsp;<span
                                                    class="badge badge-info">LESS THAN 60 characters</span></label>
                                    </td>
                                    <td style="width:62%;">
                                        <input class="form-control" name="scholarDepartment" id="scholarDepartment"
                                               type="text" value="<?php echo $scholar_info['scholar_department'] ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="row mt-3"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width:38%;">
                                        <label for="scholarAddress">Address&nbsp;&nbsp;<span class="badge badge-info">LESS THAN 250 Characters</span></label>
                                    </td>
                                    <td style="width:62%;">
                                        <input class="form-control" name="scholarAddress" id="scholarAddress"
                                               type="text" value="<?php echo $scholar_info['scholar_address'] ?>"/>
                                    </td>
                                </tr>
                            </table>
                            <div class="row mt-3"></div>
                            <button type="submit" class="form-control " class="float-right">Update author information
                            </button>
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

