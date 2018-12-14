<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<?php include APPPATH . 'views/common/msgbox.php'; ?>
</div>
<div class="bottom-blank"></div>
<nav class="navbar navbar-expand-sm bg-light fixed-bottom" role="navigation" style="font-size:80%;">
    <div class="container-fluid">
        <div class="navbar-header">
            <span class="navbar-brand" style="font-size:100%;"><?php echo $footer1; ?></span>
        </div>
        <ul class="nav navbar-nav navbar-right">
            <li><?php echo $mitbeian ?>&nbsp;&nbsp;&nbsp;&nbsp;</li>
            <li>Processed in <strong>{elapsed_time}</strong> seconds, <strong><?php echo $sql_queries; ?></strong>
                queries.
            </li>
        </ul>
    </div>
</nav>
</body>
</html>
