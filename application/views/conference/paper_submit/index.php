<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/11/30
 * Time: 13:35
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<?php include APPPATH . 'views/conference/header.php'; ?>
<?php include APPPATH . 'views/conference/paper_submit/left_bar.php'; ?>
<div class="col-md-10 content-rf content-rf-doc">
    <?php if ($has_joint === FALSE) { ?>
        <div class="alert alert-warning">
            You have not entered in this conference.
            <br/>
            To submit your paper, you should
            <a class="alert_link"
               href="/conference/<?php echo $conference['conference_url']; ?>/member/?do=register&redirect=<?php echo $url; ?>">register
                in <?php echo $conference['conference_name']; ?>
            </a>
            .
        </div>
    <?php } else { ?>
        <div>
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Submitted Papers</h4>
                    <div class="alert alert-info">
                        You have not submitted a paper yet.
                    </div>
                    <table>

                    </table>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
<?php include APPPATH . 'views/conference/footer.php'; ?>
