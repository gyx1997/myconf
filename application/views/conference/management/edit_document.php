<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/11/11
 * Time: 20:07
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<?php include APPPATH . 'views/conference/header.php'; ?>
<?php include APPPATH . 'views/conference/management/left_bar.php'; ?>
<div class="col-md-10 content-rf">
    <div class="col-md-12">
        <div class="row">
            <form id="document_data" name="document_data" action="<?php echo $base_url; ?>/document/?do=submit"
                  method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h4 class="modal-title">Content Editor</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
                    <input type="hidden" id="document_id" name="document_id" value="<?php echo $document_id; ?>"/>
                    <div class="form-inline" style="display:none;">
                        <label for="document_title">Title</label>&nbsp;&nbsp;&nbsp;
                        <input style="width:800px;" class="form-control" type="text" name="document_title"
                               id="document_title"
                               value=""/>
                    </div>
                    <p></p>
                    <div id="document_html_editor" name="document_html_editor" type="text/plain" id="editor"
                         style="width:auto ;height:420px;">
                    </div>
                    <textarea style="display: none;" id="document_html" name="document_html">
			    </textarea>
                    <p></p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-secondary" onclick="SubmitFormDocument();">Submit</button>
                    <button type="button" class="btn btn-secondary"
                            onclick="window.location.href='<?php echo $base_url; ?>/category/'">Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="/static/ueditor/ueditor.config.js"></script>
<script type="text/javascript" src="/static/ueditor/ueditor.all.js"></script>
<link rel="stylesheet" href="/static/ueditor/themes/default/css/ueditor.css"/>
<script type="text/javascript">
    UE.Editor.prototype._bkGetActionUrl = UE.Editor.prototype.getActionUrl;
    UE.Editor.prototype.getActionUrl = function (action) {
        if (action == 'uploadimage') {
            return '/attachment/put/image/';
        } else if (action == 'uploadfile') {
            return '/attachment/put/file/';
        } else if (action == 'listimage') {
            return '/attachment/get-list/image/';
        } else if (action == 'listfile') {
            return '/attachment/get-list/file/';
        } else {
            return this._bkGetActionUrl.call(this, action);
        }
    };

    initEditor(<?php echo $document_id; ?>);

    function initEditor(document_id) {
        $.get("<?php echo $base_url; ?>/document/?do=get&id=" + document_id + "&ajax=true", function (result) {
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
                    scaleEnabled: false,
                    autoClearinitialContent: true,
                    elementPathEnabled: false,
                    catchRemoteImageEnable: false,
                    csrf_token: "<?php echo $csrf_hash;?>",
                    document_id: document_id
                }
            );
            editor.addListener('ready', function (edt) {
                editor.execCommand('insertHtml', result);
            });
        });
    }

    function SubmitFormDocument() {
        var text_box = document.getElementById('document_html');
        content = UE.getEditor('document_html_editor').getContent();
        text_box.value = content;
        return true;
    };
</script>

<?php include APPPATH . 'views/conference/footer.php'; ?>
