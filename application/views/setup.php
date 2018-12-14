<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div style="padding: 20px;">
    <img src="/static/img/bg1.png" style="width:100%;height:200px;">
</div>
<div id="body">
    <h2>设置向导</h2>
    <div id="body">
        <div
                style="margin: 0 auto; width:400px;height: 350px;text-align: left;border: 1px solid #D0D0D0; border-radius: 3px;">
            <div style="padding:20px;">
                <div style="margin: 0 auto;text-align: center;">设置基本信息</div>
                <form role="form" action="/setup/submit/" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
                    <input type="hidden" name="salt_text" id="salt_text" value="<?php echo $salt; ?>"/>
                    <div class="form-group">
                        <label for="password_text">设置管理密码</label>
                        <input type="text" class="form-control" name="password_text" id="password_text"
                               placeholder="请输入管理密码"/>
                    </div>
                    <div class="form-group">
                        <label for="icp_text">设置网站备案号（没有就不填）</label>
                        <input type="text" class="form-control" name="icp_text" id="icp_text" placeholder="请输入ICP备案号"/>
                    </div>
                    <div class="form-group">
                        <label for="banner_image">设置网站头图</label>
                        <input type="file" accept=".jpg,.jpeg,.png" name="banner_image" class="form-control"
                               id="banner_image" placeholder="上传网站的图片"/>
                    </div>
                    <button type="submit" class="btn btn-success">完成</button>
                </form>
            </div>
        </div>
    </div>
</div>

