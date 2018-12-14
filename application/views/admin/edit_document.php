<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/21
 * Time: 20:21
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div style="width: 100%; margin: 15px;">
    <div style="padding-left:35px;">
        <form id="document_data" name="document_data" action="/admin/category/submit-doc/<?php echo $category_id ?>/"
              method="post" enctype="multipart/form-data">
            <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
            <div class="form-inline">
                <label for="document_title">文章标题</label>&nbsp;&nbsp;&nbsp;
                <input style="width:800px;" class="form-control" type="text" name="document_title" id="document_title"
                       value="<?php echo $document_title; ?>"/>
            </div>
            <p></p>
            <div id="document_html_editor" name="document_html_editor" type="text/plain" id="editor"
                 style="width:960px;height:360px;">
            </div>
            <textarea style="display: none;" id="document_html" name="document_html">
				<?php echo $document_html; ?>
			</textarea>
            <p></p>
            <button onclick="SubmitForm();return false;" id="submit_button" class="btn btn-success" type="button">提交更改
            </button>
        </form>
        <script type="text/javascript" src="/static/ueditor/ueditor.config.js"></script>
        <script type="text/javascript" src="/static/ueditor/ueditor.all.js"></script>
        <link rel="stylesheet" href="/static/ueditor/themes/default/css/ueditor.css"/>
        <script type="text/javascript">
            UE.Editor.prototype._bkGetActionUrl = UE.Editor.prototype.getActionUrl;
            UE.Editor.prototype.getActionUrl = function (action) {
                if (action == 'uploadimage') {
                    return '/attachment/upload/image/';
                } else if (action == 'uploadfile') {
                    return '/attachment/upload/file/';
                } else if (action == 'listimage') {
                    return '/attachment/get-list/image/';
                } else if (action == 'listfile') {
                    return '/attachment/get-list/file/';
                } else {
                    return this._bkGetActionUrl.call(this, action);
                }
            }


            var editor = UE.getEditor(
                'document_html_editor',
                {

                    toolbars: [
                        [
                            'fullscreen',
                            'source',
                            'undo',
                            'redo',
                            '|',
                            'fontfamily',
                            'fontsize',
                            'paragraph',
                            'forecolor',
                            'backcolor',
                            '|',
                            'bold',
                            'italic',
                            'underline',
                            'subscript',
                            'superscript',
                            '|',
                            'time',
                            'date',
                            'link',
                            'unlink',
                            'insertimage',
                            'attachment'
                        ]
                    ],
                    autoClearinitialContent: true,
                    elementPathEnabled: false,
                    catchRemoteImageEnable: false,
                    csrf_token: "<?php echo $csrf_hash;?>",
                    document_id: "<?php echo $document_id;?>"
                }
            );

            editor.addListener('ready', function (edt) {
                var content_old = $('#document_html').val();
                if (content_old != '') {
                    editor.execCommand('insertHtml', content_old);
                }
            });

            function SubmitForm() {
                var text_box = document.getElementById('document_html');
                content = editor.getContent();
                text_box.value = content;
                document.getElementById('document_data').submit();
                return false;
            };
        </script>
    </div>
</div>
