<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/11/12
 * Time: 0:16
 */
?>
<?php
$tabs = array();
$conf_url = '/conference/' . $conference['conference_url'];
?>
<div class="page-header" style="text-align: left;">
    <div class="banner-image" style="background-image: url(/static/img/<?php echo $conference['banner_image']; ?>);">
    </div>
    <div class="row mt-3"></div>
</div>
<div id="body">
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link <?php if ($tab_page == '') { ?>active disabled<?php } ?>"
               href="/conference/<?php echo $conference['conference_url']; ?>/">Home</a>
        </li>
        <?php if ($login_status) { ?>
            <li class="nav-item">
                <a class="nav-link <?php if ($tab_page == 'paper-submit') { ?>active disabled<?php } ?>"
                   href="/conference/<?php echo $conference['conference_url']; ?>/paper-submit/">Online Paper
                    Submission</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled <?php if ($tab_page == 'downloads') { ?>active disabled<?php } ?>">Downloads</a>
            </li>
        <?php } ?>
        <!-- TODO 增加权限判断 -->
        <?php if ($auth_review || $auth_arrange_review) { ?>
            <li class="nav-item">
                <a class="nav-link <?php if ($tab_page == 'paper_submit-review') { ?>active disabled<?php } ?>"
                   href="/conference/<?php echo $conference['conference_url']; ?>/paper-review/">Paper Review</a>
            </li>
        <?php } ?>
        <?php if ($auth_management) { ?>
            <li class="nav-item">
                <a class="nav-link <?php if ($tab_page == 'management') { ?>active disabled<?php } ?>"
                   href="/conference/<?php echo $conference['conference_url']; ?>/management/">Management</a>
            </li>
        <?php } ?>

    </ul>
    <div class="row mt-2"></div>
    <div class="col-md-12" style="border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;">
        <div class="row">