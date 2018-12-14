<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <link href="https://cdn.bootcss.com/twitter-bootstrap/4.1.0/css/bootstrap-grid.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/twitter-bootstrap/4.1.0/css/bootstrap-reboot.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/twitter-bootstrap/4.1.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="/static/myconf.css" rel="stylesheet">

    <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdn.staticfile.org/popper.js/1.12.5/umd/popper.min.js"></script>
    <script src="https://cdn.staticfile.org/twitter-bootstrap/4.1.0/js/bootstrap.min.js"></script>

    <script type="text/javascript" src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="/static/md5.js"></script>
    <script type="text/javascript" src="/static/jquery.base64.js"></script>
    <script type="text/javascript" src="/static/bs-table/bootstrap-table.js"></script>
    <script type="text/javascript" src="/static/bs-table/locale/bootstrap-table-en-US.js"></script>
    <link href="/static/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="/static/font-awesome/css/awesome-bootstrap-checkbox.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-sm bg-light fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <span class="navbar-brand" href="#">myconf</span>
        </div>
        <ul class="nav navbar-nav navbar-right">
            <?php if (isset($login_status)) { ?>
                <div class="btn-group" style="padding-right: 100px;">
                    <?php if ($login_status === TRUE) { ?>
                        <a class="" data-toggle="dropdown"><?php echo $login_user['user_name']; ?></a>
                        <a class="dropdown-toggle dropdown-toggle-split" data-toggle="dropdown">
                            <span class="caret"></span>
                        </a>
                        <div class="dropdown-menu">
                            <div class="dropdown-header">Messages & Notices</div>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-header">Account</div>
                            <a class="dropdown-item" href="/account/my-settings/">Settings</a>
                            <a class="dropdown-item" href="/account/logout/?redirect=<?php echo $url; ?>">Logout</a>
                        </div>
                    <?php } else { ?>
                        <li class="nav-item"><a class="nav-link"
                                                href="/account/login/?redirect=<?php echo $url; ?>"><span
                                        class="glyphicon glyphicon-user"></span> Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="/account/register/"><span
                                        class="glyphicon glyphicon-user"></span> Register</a></li>
                    <?php } ?>
                </div>
            <?php } ?>
        </ul>
    </div>
</nav>
<div class="top-blank"></div>
<div id="container" class="col-md-9 container-main" style="">

