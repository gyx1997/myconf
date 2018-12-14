<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/11/27
 * Time: 21:08
 */
defined('BASEPATH') OR exit('No direct script access allowed');
$base_url = '/conference/' . $conference['conference_url'] . '/management';
$columns = array(
    'overview' => 'Overview',
    'category' => 'Categories',
    'participant' => 'Participants',
    'attachment' => 'Attachments'
);
?>
<div class="col-md-2 content-lf">
    <?php foreach ($columns as $action_name => $display_name) { ?>
        <?php if ($action_name == $action) { ?>
            <span class="btn btn-block btn-light active content-lf-btn">
                <?php echo $display_name; ?>
             </span>
        <?php } else { ?>
            <a class="btn btn-block btn-light content-lf-btn"
               href="<?php echo $base_url . '/' . $action_name; ?>">
                <?php echo $display_name; ?>
            </a>
        <?php } ?>
    <?php } ?>
</div>