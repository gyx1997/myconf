<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/21
 * Time: 18:56
 */

defined('BASEPATH') OR exit('No direct script access allowed') ?>
<div id="body">
    <div
            style="margin: 0 auto; width:auto ;height: 900px;text-align: left;border: 1px solid #D0D0D0; border-radius: 8px;">
        <div class="page-header">
            <h1>管理面板</h1>
        </div>
        <div style="float:left;display: inline-flex;">
            <div style="width:200px; margin: 15px;">
                <div style="padding:3px;">
                    <button class="btn btn-info btn-lg btn-block"
                            <?php if ($mod == '') echo 'disabled="disabled"' ?>onclick="window.location.href='/admin/'">
                        欢迎
                    </button>
                </div>
                <div style="padding:3px;">
                    <button
                            class="btn btn-info btn-lg btn-block" <?php if ($mod == 'general') echo 'disabled="disabled"' ?>
                            onclick="window.location.href='/admin/general/'">基本设置
                    </button>
                </div>
                <div style="padding:3px;">
                    <button class="btn btn-info btn-lg btn-block"
                            <?php if ($mod == 'category') echo 'disabled="disabled"' ?>onclick="window.location.href='/admin/category/'">
                        栏目设置
                    </button>
                </div>
                <div style="padding:3px;">
                    <button class="btn btn-info btn-lg btn-block"
                            <?php if ($mod == 'user') echo 'disabled="disabled"' ?>onclick="window.location.href='/admin/user/'">
                        用户角色设置
                    </button>
                </div>
                <div style="padding:3px;">
                    <button class="btn btn-info btn-lg btn-block"
                            <?php if ($mod == 'attachment') echo 'disabled="disabled"' ?>onclick="window.location.href='/admin/attachment/'">
                        附件设置
                    </button>
                </div>
                <div style="padding:3px;">
                    <button class="btn btn-info btn-lg btn-block"
                            <?php if ($mod == 'sponsor') echo 'disabled="disabled"' ?>onclick="window.location.href='/admin/sponsor/'">
                        赞助设置
                    </button>
                </div>
                <div style="padding:3px;">
                    <button class="btn btn-info btn-lg btn-block" onclick="window.location.href='/admin/logout/'">退出面板
                    </button>
                </div>
            </div>
