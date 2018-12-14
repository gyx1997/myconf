<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/11/05
 * Time: 11:04
 */

defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div style="width: 100%; margin: 15px;">
    <div style="padding-left:35px;">
        <div style="padding:5px;"></div>
        <div style="padding:5px;"></div>
        <form role="form" action="/admin/user/" method="post" enctype="multipart/form-data"
              onkeydown="if(event.keyCode==13){return false;}">
            <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
            <div class="form-inline">
                <label>
                    用户名&nbsp;&nbsp;&nbsp;
                    <input class="form-control" type="text" name="username_text" id="username_text"
                           value="<?php if (isset($target_user_name)) echo $target_user_name; ?>"/>
                </label>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <label>
                    角色&nbsp;&nbsp;&nbsp;
                    <select name="user_role_id" class="form-control">
                        <option value="0" <?php if ($target_role_id == '0') echo 'selected="selected"'; ?>>普通用户</option>
                        <option value="1" <?php if ($target_role_id == '1') echo 'selected="selected"'; ?>>审稿人</option>
                        <option value="2" <?php if ($target_role_id == '2') echo 'selected="selected"'; ?>>编辑</option>
                        <option value="3" <?php if (!isset($target_role_id) || $target_role_id == '3') echo 'selected="selected"'; ?>>
                            不限
                        </option>
                    </select>
                </label>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <button type="submit" class="btn btn-success">查询</button>
            </div>
            <div style="padding: 5px;"></div>
            <div class="alert alert-info" style="padding: 5px;">用户名支持模糊匹配，例如输入“abc”包含所有名字中含有abc的用户。</div>
        </form>
        <div style="padding-top: 5px; padding-bottom: 5px;">
            <?php if ($user_count == 0) { ?>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>没有符合条件的用户</th>
                    </tr>
                    </thead>
                </table>
            <?php } else { ?>
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>用户名</th>
                        <th>角色</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user) { ?>
                        <tr>
                            <form id="user_<?php echo $user['user_id']; ?>" role="form"
                                  action="/admin/user/assign-role/" method="post"
                                  enctype="multipart/form-data">
                                <input type="hidden" name="<?php echo $csrf_name; ?>"
                                       value="<?php echo $csrf_hash; ?>"/>
                                <input type="hidden" name="user_id_text" value="<?php echo $user['user_id'] ?>"/>
                                <td>
                                    <div class="form-inline">
                                        <?php echo $user['user_name']; ?>
                                    </div>
                                </td>
                                <td>

                                    <select name="user_role_text" class="form-control">
                                        <option value="0" <?php if ($user['user_role'] == 'scholar') echo 'selected="selected"'; ?>>
                                            普通用户
                                        </option>
                                        <option value="1" <?php if ($user['user_role'] == 'reviewer') echo 'selected="selected"'; ?>>
                                            审稿人
                                        </option>
                                        <option value="2" <?php if ($user['user_role'] == 'editor') echo 'selected="selected"'; ?>>
                                            编辑
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <button name="submit_user_<?php echo $user['user_id']; ?>" type="submit"
                                            class="btn btn-submit"> 修改
                                    </button>
                                </td>
                            </form>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>
</div>
<script type="text/javascript">

</script>

