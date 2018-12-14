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
        <form id="form_data" role="form" method="post" enctype="multipart/form-data"
              class="col-md-12 col-md-offset-1 form-horizontal"
              action="/conference/<?php echo $conference['conference_url']; ?>/management/overview/?do=submit"
              onkeydown="if(event.keyCode==13){return false;}">
            <div class="modal-header">
                <h4 class="modal-title">Conference Overview</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
                <input type="hidden" name="conference_id" value="<?php echo $conference['conference_id']; ?>"/>
                <div class="form-inline">
                    <div class="col-md-12">
                        <div class="row">
                            <label class="control-label col-md-3" for="conference_url_text">Conference URL</label>
                            <input disabled="disabled" type="text" class="form-control col-md-3"
                                   name="conference_url_text" id="conference_url_text"
                                   value="<?php echo $conference['conference_url']; ?>"/>
                            <label class="control-label col-md-3" for="conference_create_time_text">Create Date</label>
                            <input disabled="disabled" type="text" class="form-control col-md-3"
                                   name="conference_create_time_text" id="conference_create_time_text"
                                   value="<?php echo date('Y-m-d', intval($conference['conference_create_time'])); ?>"/>
                        </div>
                    </div>
                </div>
                <div class="row mt-3"></div>
                <div class="form-inline">
                    <div class=" col-md-12">
                        <div class="row">
                            <label for="conference_host_text" class="col-md-3">Host</label>
                            <input type="text" name="conference_host_text" id="conference_host_text"
                                   placeholder="Enter the host of the conference."
                                   class="form-control col-md-3"
                                   value="<?php echo $conference['host']; ?>"/>
                            <label for="conference_date_text" class="col-md-3">Start Date</label>
                            <input type="text" name="conference_date_text" id="conference_date_text"
                                   placeholder="Enter the start date of the conference."
                                   class="form-control col-md-3"
                                   value="<?php echo date('Y-m-d', intval($conference['conference_start_time'])); ?>"/>
                        </div>
                    </div>
                </div>
                <div class="row mt-3"></div>
                <div class="form-inline">
                    <div class="col-md-12">
                        <div class="row">
                            <label class="col-md-3" for="conference_name_text">Conference Name</label>
                            <input type="text" class="form-control col-md-9"
                                   placeholder="Enter full name of conference here"
                                   id="conference_name_text" name="conference_name_text"
                                   value="<?php echo $conference['conference_name']; ?>"/>
                        </div>
                    </div>
                </div>
                <div class="row mt-3"></div>
                <div class="form-inline">
                    <div class="col-md-12">
                        <div class="row">
                            <label for="banner_image_text" class="col-md-5">
                                Banner(jpeg OR png, < 1MB)
                            </label>
                            <input id="banner_image_text" disabled="disabled"
                                   class="form-control col-md-4" type="text"
                                   style="padding-right: 3px;">
                            <button class="btn btn-secondary col-md-3"
                                    onclick="$('#banner_image').click();return false;">
                                Click here to upload
                            </button>
                            <input type="file" style="display:none;" accept=".jpg,.jpeg,.png" name="banner_image"
                                   class="form-control" id="banner_image" placeholder="Upload banner image."/>
                        </div>
                    </div>
                </div>
                <div class="row mt-3"></div>
                <div class="form-group col-md-12">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <?php if (isset($conference['banner_image']) && $conference['banner_image'] != '') { ?>
                            <div class="alert alert-success col-md-9">
                                <img src='/static/img/<?php echo($conference['banner_image']); ?>' height='100'
                                     width='600'/>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-warning col-md-9">
                                <span id="err_text">No banner image here, or there was an error occurred during recently upload.</span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="row mt-3"></div>
                <div class="form-inline">
                    <div class="col-md-12">
                        <div class="row">
                            <label for="qr_code_text" class="col-md-5">
                                QR Code(jpeg OR png, < 1MB)
                            </label>
                            <input id="qr_code_text" disabled="disabled"
                                   class="form-control col-md-4" type="text"
                                   style="padding-right: 3px;">
                            <button class="btn btn-secondary col-md-3"
                                    onclick="$('#qr_code').click();return false;">
                                Click here to upload
                            </button>
                            <input type="file" style="display:none;" accept=".jpg,.jpeg,.png" name="qr_code"
                                   class="form-control" id="qr_code" placeholder="Upload banner image."/>
                        </div>
                    </div>
                </div>
                <div class="row mt-3"></div>
                <div class="form-group col-md-12">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <?php if (isset($conference['qr_code']) && $conference['qr_code'] != '') { ?>
                            <div class="alert alert-success col-md-9">
                                <img src='/static/img/<?php echo($conference['qr_code']); ?>' height='100' width='100'/>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-warning col-md-9">
                                <span id="err_text">No QR Code image here, or there was an error occurred during recently upload.</span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="submit_button" type="button" class="btn btn-primary" onclick="submitForm();return false;">
                    Submit
                </button>
            </div>
        </form>
    </div>
    <script type="text/javascript">
        function submitForm() {
            $.ajax({
                async: true,
                type: "POST",
                url: "/conference/<?php echo $conference['conference_url']?>/management/overview/?do=submit&ajax=true",
                contentType: false,
                data: new FormData($('#form_data')[0]),
                processData: false,
                cache: false,
                success: function (data_result) {
                    var d = eval(data_result);
                    if (d.status === 'SUCCESS') {
                        window.location.href = "/conference/<?php echo $conference['conference_url']?>/management/overview/";
                    }
                },
                error: function () {

                }
            });
            return false;
        }

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

<?php include APPPATH . 'views/conference/footer.php'; ?>