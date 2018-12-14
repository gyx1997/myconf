<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/12
 * Time: 10:40
 */
?>
<div class="modal fade" id="msgBox-main">
    <div class="modal-dialog model-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="msgBox-title">模态框头部</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="msgBox-message">
                模态框内容..
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
<script>
    var messageBox = function (message, title, ok_event_handler) {
        $('#msgBox-title').html(title);
        $('#msgBox-message').html(message);
        $('#msgBox-main').modal('show');
    }
</script>