<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/11/11
 * Time: 20:07
 */

defined('BASEPATH') OR exit('No direct script access allowed');
?>
<?php include APPPATH . '/views/conference/header.php'; ?>
<?php include APPPATH . '/views/conference/management/left_bar.php'; ?>
<div class="col-md-10 content-rf">
    <div class="col-md-12">
        <div class="row">
        </div>
        <div class="modal-header">
            <h4 class="modal-title">Categories</h4>
        </div>
        <div class="modal-body">
            <div class="col-md-12  col-md-offset-1 form-horizontal">
                <div class="col-md-12">
                    <form id="form_data" role="form"
                          action="<?php echo $base_url; ?>/category/?do=add"
                          method="post" enctype="multipart/form-data"
                          onkeydown="if(event.keyCode===13){return false;}">
                        <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
                        <input type="hidden" name="conference_id" value="<?php echo $conference['conference_id']; ?>"/>
                        <div class="row form-inline">
                            <label for="category_name_text" class="col-md-2">
                                Title
                            </label>
                            <input class="form-control col-md-5" type="text" name="category_name_text"
                                   id="category_name_text"/>
                            <label for="category_type_id" class="col-md-1">
                                Type
                            </label>
                            <select disabled="disabled" name="category_type_id" class="form-control col-md-3">
                                <option value="0" selected="selected">Single document</option>
                                <option value="1">Document list</option>
                            </select>
                            <button type="button" class="btn btn-primary col-md-1" onclick="submitForm();return false;">
                                Add
                            </button>

                        </div>
                        <div class="row mt-3"></div>
                    </form>
                </div>
            </div>
            <div class="row mt-3"></div>
            <div class="col-md-12 col-md-offset-1 form-horizontal">
                <div class="row">
                    <div style="margin:0 auto; padding-left:45px;">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Category List</th>
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($category_list as $cat) { ?>
                                <tr>
                                    <form id="category_<?php echo $cat['category_id']; ?>" role="form"
                                          action="<?php echo $base_url; ?>/category/?do=rename" method="post"
                                          enctype="multipart/form-data">
                                        <input type="hidden" name="<?php echo $csrf_name; ?>"
                                               value="<?php echo $csrf_hash; ?>"/>
                                        <input type="hidden" name="category_id"
                                               value="<?php echo $cat['category_id']; ?>"/>
                                        <td>
                                            <div class="form-inline">
                                                <input name="category_name_text" id="category_name_text" type="text"
                                                       class="form-control"
                                                       value="<?php echo $cat['category_title']; ?>"
                                                       style="width:200px;"/>
                                                &nbsp;&nbsp;
                                                <?php if ($cat['category_type'] == 0) { ?>
                                                    <select disabled="disabled" name="category_type_id"
                                                            class="form-control" style="display:none;">
                                                        <option value="0" selected="selected">Single Document</option>
                                                        <option value="1">Document List</option>
                                                    </select>
                                                <?php } else { ?>
                                                    <select disabled="disabled" name="category_type_id"
                                                            class="form-control" style="display:none;">
                                                        <option value="0">Single Document</option>
                                                        <option value="1" selected="selected">Document</option>
                                                    </select>
                                                <?php } ?>
                                                <button type="submit" class="btn btn-link">
                                                    Submit
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <a class="btn btn-link"
                                               href="<?php echo $base_url; ?>/category/?do=up&cid=<?php echo $cat['category_id']; ?>">
                                                Up
                                            </a>
                                            <a class="btn btn-link"
                                               href="<?php echo $base_url; ?>/category/?do=down&cid=<?php echo $cat['category_id']; ?>">
                                                Down
                                            </a>
                                        </td>
                                        <td>
                                            <a class="btn btn-link"
                                               href="<?php echo $base_url; ?>/category/?do=remove&cid=<?php echo $cat['category_id']; ?>">
                                                Remove
                                            </a>
                                            <?php if ($cat['category_type'] == 0) { ?>
                                                <button type="button" class="btn btn-link"
                                                        onclick="window.location.href='<?php echo $base_url; ?>/document/?do=edit&id=<?php echo $cat['first_document_id']; ?>';">
                                                    Edit Content
                                                </button>
                                            <?php } else { ?>
                                                <button type="button" class="btn"
                                                        onclick="window.location.href='/admin/category/mod-doc-list/<?php echo $cat['category_id']; ?>/'">
                                                    Edit Document List
                                                </button>
                                            <?php } ?>
                                        </td>
                                    </form>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<script type="text/javascript">
    function submitForm() {
        $.ajax({
            async: true,
            type: "POST",
            url: "/conference/<?php echo $conference['conference_url'];?>/management/category/?do=add&ajax=true",
            contentType: "application/x-www-form-urlencoded; charset=utf-8",
            data: $('#form_data').serialize(),
            dataType: "text",
            processData: false,
            cache: false,
            success: function (data_result) {
                var d = eval("(" + data_result + ")");
                if (d.status === 'SUCCESS') {
                    window.location.href = "/conference/<?php echo $conference['conference_url'];?>/management/category/";
                }
            },
            error: function () {

            }
        });
        return false;
    }
</script>
<?php include APPPATH . '/views/conference/footer.php'; ?>

