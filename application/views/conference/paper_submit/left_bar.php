<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/12/4
 * Time: 17:05
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="col-md-2">
    <?php if ($action != '') { ?>
        <a class="btn btn-block btn-light content-lf-btn"
           href="/conference/<?php echo $conference['conference_url']; ?>/paper-submit/">
            Overview
        </a>
    <?php } else { ?>
        <span class="btn btn-block btn-light active content-lf-btn">
                Overview
        </span>
    <?php } ?>
    <?php if ($action != 'new') { ?>
        <a class="btn btn-block btn-light content-lf-btn"
           href="/conference/<?php echo $conference['conference_url']; ?>/paper-submit/new/">
            Submit New Paper
        </a>
    <?php } else { ?>
        <span class="btn btn-block btn-light active content-lf-btn">
            Submit New Paper
        </span>
    <?php } ?>
    <?php if ($action != 'edit') { ?>
        <a class="btn btn-block btn-light content-lf-btn"
           href="/conference/<?php echo $conference['conference_url']; ?>/paper-submit/new/">
            Edit Paper
        </a>
    <?php } else { ?>
        <span class="btn btn-block btn-light active content-lf-btn">
            Edit Paper
        </span>
    <?php } ?>
</div>