<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php include APPPATH . 'views/conference/header.php'; ?>

<div class="col-md-2">
    <?php foreach ($category_list as $category) { ?>
        <?php if ($category['category_id'] != $active_category_id) { ?>
            <a class="btn btn-block btn-light content-lf-btn"
               href="/conference/<?php echo $conference['conference_url']; ?>/?cid=<?php echo $category['category_id']; ?>">
                <?php echo $category['category_title']; ?>
            </a>
        <?php } else { ?>
            <span class="btn btn-block btn-light active content-lf-btn">
                <?php echo $category['category_title']; ?>
            </span>
        <?php } ?>
    <?php } ?>
</div>

<div class="col-md-10 content-rf content-rf-doc">
    <?php echo $active_document_content; ?>
</div>

<?php include APPPATH . 'views/conference/footer.php'; ?>


