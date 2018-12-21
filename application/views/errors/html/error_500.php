<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Error</title>
    <style type="text/css">

        ::selection {
            background-color: #E13300;
            color: white;
        }

        ::-moz-selection {
            background-color: #E13300;
            color: white;
        }

        body {
            background-color: #fff;
            margin: 40px;
            font: 13px/20px normal Helvetica, Arial, sans-serif;
            color: #4F5155;
        }

        a {
            color: #003399;
            background-color: transparent;
            font-weight: normal;
        }

        h1 {
            color: #444;
            background-color: transparent;
            border-bottom: 1px solid #D0D0D0;
            font-size: 19px;
            font-weight: normal;
            margin: 0 0 14px 0;
            padding: 14px 15px 10px 15px;
        }

        code {
            font-family: Consolas, Monaco, Courier New, Courier, monospace;
            font-size: 12px;
            background-color: #f9f9f9;
            border: 1px solid #D0D0D0;
            color: #002166;
            display: block;
            margin: 14px 0 14px 0;
            padding: 12px 10px 12px 10px;
        }

        #container {
            margin: 10px;
            border: 1px solid #D0D0D0;
            box-shadow: 0 0 8px #D0D0D0;
        }

        p {
            margin: 12px 15px 12px 15px;
        }
    </style>
</head>
<body>
<div id="container">
    <h1><?php echo $heading; ?></h1>
    <p><?php echo $message; ?></p>
    <?php
    echo '<p>We are so sorry for the inconvenience we have caused. Critical information has been written to our logging system, and if you have any problem, please contact us via the email <strong>csqrwc@126.com</strong>. </p><p>Thank you for your support!</p>
<p style="text-align: right;"> myConf Team, ' . date('M jS, Y') . '</p>'
    ?>
</div>
</body>
</html>
