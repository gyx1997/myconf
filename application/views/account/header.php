<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/11/27
 * Time: 16:40
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div id="body">
    <div class="col-md-12" style="border-bottom-left-radius: 5px; border-bottom-right-radius: 5px;padding-top: 20px;">
        <nav class="breadcrumb">
            <a class="breadcrumb-item" href="/">myConf</a>
            <a class="breadcrumb-item" href="/account">Account</a>
            <span class="breadcrumb-item active">
            <?php
            switch ($method) {
                case 'my-settings':
                    echo 'Settings';
                    break;
                case 'my-conferences':
                    echo 'Conferences';
                    break;
                case 'my-messages':
                    echo 'Messages & Notices';
                    break;
            }
            ?>
        </span>
        </nav>
        <div class="row mt-3"></div>
        <div class="row">
            <div class="col-md-2" style="font-size: 80%;">
                <div class="container">
                    <div class="list-group">
                        <?php if ($method == 'my-settings') { ?>
                            <span class="list-group-item list-group-item-secondary">Settings</span>
                        <?php } else { ?>
                            <a href="/account/my-settings/" class="list-group-item list-group-item-action">Settings</a>
                        <?php } ?>
                        <?php if ($method == 'my-conferences') { ?>
                            <span class="list-group-item list-group-item-secondary">Conferences</span>
                        <?php } else { ?>
                            <a href="/account/my-conferences/" class="list-group-item list-group-item-action">Conferences</a>
                        <?php } ?>
                        <?php if ($method == 'my-messages') { ?>
                            <span class="list-group-item list-group-item-secondary">Messages</span>
                        <?php } else { ?>
                            <a href="/account/my-messages/" class="list-group-item list-group-item-action">Messages</a>
                        <?php } ?>
                    </div>
                </div>
            </div>