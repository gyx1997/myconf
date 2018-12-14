<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/18
 * Time: 23:03
 */

defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div style="width: 100%; margin: 15px;">
    <div style="padding-left:35px;">
        <?php if ($op == 'submit') { ?>
            <div>
                <?php if ($banner_failed == FALSE && $qrcode_failed == FALSE) { ?>
                    <div class="alert alert-success" id="err_tip">
                        <strong>设置更改完成。</strong> 页面将在3秒后返回。
                    </div>
                <?php } else { ?>
                    <div class="alert alert-warning" id="err_tip">
                        <strong>设置部分更改完成。
                            <?php if ($banner_failed) {
                                echo '头图 ';
                            } ?>
                            <?php if ($qrcode_failed) {
                                echo '二维码 ';
                            } ?>
                            上传失败。
                        </strong>
                        页面将在3秒后返回。
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <div style="padding:5px;"></div>
        <form id="form_data" role="form" action="/admin/general/submit/" method="post" enctype="multipart/form-data"
              onkeydown="if(event.keyCode==13){return false;}">
            <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
            <div class="form-inline">
                <label for="icp_text">网站（会议）标题&nbsp;&nbsp;&nbsp;</label>
                <input style="width:650px;" <?php if ($op == 'submit') echo 'disabled="disabled"'; ?> type="text"
                       class="form-control" name="title_text" id="title_text" placeholder="网站标题"
                       value="<?php echo $title; ?>"/>
            </div>
            <div style="padding: 5px;"></div>
            <div class="form-group">
                <label for="footer1_text">左侧第一行页脚</label>
                <textarea <?php if ($op == 'submit') echo 'disabled="disabled"'; ?> rows="2" style="width:790px;"
                                                                                    class="form-control"
                                                                                    name="footer1_text"
                                                                                    id="footer1_text"
                                                                                    placeholder="第1行页脚"><?php echo $footer1; ?></textarea>
            </div>
            <div style="padding: 5px;"></div>
            <div class="form-group">
                <label for="footer2_text">左侧第二行页脚</label>
                <textarea <?php if ($op == 'submit') echo 'disabled="disabled"'; ?> rows="2" style="width:790px;"
                                                                                    class="form-control"
                                                                                    name="footer2_text"
                                                                                    id="footer2_text"
                                                                                    placeholder="第2行页脚"><?php echo $footer2; ?></textarea>
            </div>
            <div style="padding: 5px;"></div>
            <div class="form-inline">
                <label for="banner_image">网站头图&nbsp;&nbsp;&nbsp;</label>
                <div class="input-group">
                    <input id="banner_image_text" disabled="disabled" class="form-control" type="text"
                           style="width: auto;">
                    &nbsp;&nbsp;
                    <button class="btn btn-info" onclick="$('#banner_image').click();return false;">上传</button>
                </div>
                <input style="display:none;" type="file" accept=".jpg,.jpeg,.png" name="banner_image"
                       class="form-control" id="banner_image" placeholder="上传网站的图片"/>

            </div>
            <div style="padding: 5px;"></div>
            <div class="form-group" style="width:790px">
                <?php if ($banner != '') { ?>
                    <div class="alert alert-success">
                        <img src='<?php echo($banner); ?>' height='100' width='700'/>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-warning">
                        <span id="err_text">目前没有头图。</span>是否还未上传，或者之前上传错误？
                    </div>
                <?php } ?>
            </div>
            <div class="form-inline">
                <label class="form-label">页脚二维码&nbsp;&nbsp;&nbsp;</label>
                <div class="input-group">
                    <input id="qr_code_text" disabled="disabled" class="form-control" type="text" style="width: auto;">
                    &nbsp;&nbsp;
                    <button class="btn btn-info" onclick="$('#qr_code').click();return false;">上传</button>
                </div>
                <input style="display:none;" type="file" accept=".jpg,.jpeg,.png" name="qr_code" class="form-control"
                       id="qr_code" placeholder="上传页脚二维码"/>

            </div>
            <div style="padding: 5px;"></div>
            <div class="form-group" style="width:790px">
                <?php if ($qrcode != '') { ?>
                    <div class="alert alert-success">
                        <img src='<?php echo($qrcode); ?>' height='100' width='100'/>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-warning">
                        <span id="err_text">目前没有二维码。</span>是否还未上传，或者之前上传错误？
                    </div>
                <?php } ?>
            </div>
            <div style="padding: 5px;"></div>
            <div class="form-inline">
                <label for="icp_text">网站备案号（没有就不填）&nbsp;&nbsp;&nbsp;</label>
                <input style="width:587px;" <?php if ($op == 'submit') echo 'disabled="disabled"'; ?> type="text"
                       class="form-control" name="icp_text" id="icp_text" placeholder="ICP备案号"
                       value="<?php echo $mitbeian; ?>"/>
            </div>
            <?php if ($op != 'submit') { ?>
                <div style="padding: 5px;"></div>
                <button id="submit_button" type="submit" class="btn btn-success">完成</button>
            <?php } ?>
        </form>
    </div>
</div>
<script type="text/javascript">
    function getFileName(o) {
        var pos = o.lastIndexOf("\\");
        return o.substring(pos + 1);
    }

    $('#qr_code').change(function () {
        $('#qr_code_text').val(getFileName($(this).val()));
    });
    $('#banner_image').change(function () {
        $('#banner_image_text').val(getFileName($(this).val()));
    });
</script>
