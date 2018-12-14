<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/12/5
 * Time: 10:03
 */
?>
<?php include APPPATH . '/views/conference/header.php'; ?>
<?php include APPPATH . '/views/conference/management/left_bar.php'; ?>
    <div class="col-md-10 content-rf">
    <div class="modal-header">
        <h4 class="modal-title">Conference Participants</h4>
    </div>
    <div class="modal-body">
        <div class="card">
            <div class="card-header">
                Search Options
                <button id="toggleSearchBox" class="btn btn-sm float-right">
                    <span id="hideSearchBox" class="fa fa-minus"></span>
                    <span id="showSearchBox" class="fa fa-plus"></span>
                </button>
            </div>
            <div class="card-body" id="searchBox">
                <div class="form-group" role="form">
                    <div class="form-group">
                        <div class="alert alert-info">
                            Optional fields to improve search accuracy. Left blank when unnecessary.
                        </div>
                    </div>
                    <div class="row mt-3"></div>
                    <div class="form-inline">
                        <label class="control-label" for="searchUserName">User Name</label>
                        &nbsp;
                        &nbsp;
                        <input name="searchUserName" id="searchUserName" class="form-control" style="width:auto;"/>
                    </div>
                    <div class="row mt-3"></div>
                    <div class="form-inline">
                        <label class="control-label" for="searchUserEmail">User Email</label>
                        &nbsp;
                        &nbsp;
                        <input name="searchUserEmail" id="searchUserEmail" class="form-control"/>
                    </div>
                    <div class="row mt-3"></div>
                    <div class="form-inline">
                        <label class="control-label">Roles Restriction</label>
                        &nbsp;&nbsp;
                        <div class="float-right" id="showRoles"></div>
                    </div>
                </div>
            </div>
            <div class="card-footer" id="searchBoxFooter">
                <button class="btn btn-default" id="doSearch" onclick="search();">Go</button>
            </div>
        </div>
        <div class="row mt-3"></div>
        <div class="card">
            <div class="card-header">
                Search Result
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="thead-secondary">
                    <tr>
                        <th>User</th>
                        <th>Role(s)</th>
                    </tr>
                    </thead>
                    <tbody id="tbody-data">
                    </tbody>
                </table>
                <ul class="pagination" id="page-display">
                    <li class="page-item"><a class="page-link" href="#">1</a></li>
                    <li class="page-item active"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>

                </ul>
            </div>
        </div>
        <div class="modal-footer">

        </div>
    </div>
    <script>
        var clickToggle = function (id) {
            $('#' + id).click(function () {
                includes[id] = !includes[id];
                var chkBox = $('#' + id + 'Chk');
                if (includes[id]) {
                    chkBox.addClass("fa-check-square");
                    chkBox.removeClass("fa-square-o");
                } else {
                    chkBox.removeClass("fa-check-square");
                    chkBox.addClass("fa-square-o");
                }
            });
        };

        //权限信息应该可以由后端获取
        var includesInner = {
            'showAdmin': 'admin',
            'showEditor': 'editor',
            'showReviewer': 'reviewer',
            'showCreator': 'creator'
        };
        var includes = {'showAdmin': false, 'showEditor': false, 'showReviewer': false, 'showCreator': false};
        var includesName = {
            'showAdmin': 'Administrator',
            'showEditor': 'Editor',
            'showReviewer': 'Reviewer',
            'showCreator': 'Creator'
        };
        var page = 1;
        var email = '';
        var userName = '';

        for (var key in includes) {
            var id = key;
            $('#showRoles').append(
                '<span id="' + key + '" class="badge badge-light" style="cursor:pointer">' +
                '<span class="fa fa-square-o" id="' + key + 'Chk"></span>&nbsp;' +
                includesName[key] +
                '</span>&nbsp;&nbsp;');
            clickToggle(key);
        }

        //界面显示
        $('#showSearchBox').toggle();
        $('#toggleSearchBox').click(function () {
            $('#searchBox').toggle(200);
            $('#searchBoxFooter').toggle(200);
            $('#showSearchBox').toggle();
            $('#hideSearchBox').toggle();
        });

        var loadData = function () {
            $.ajax({
                type: "GET",
                url: "<?php echo $base_url; ?>/participant/?do=get&page=" + page,
                dataType: "json",
                async: true,
                data: {
                    email: email,
                    username: userName,
                    page: page,
                    admin: includes['showAdmin'] ? 'yes' : 'no',
                    editor: includes['showEditor'] ? 'yes' : 'no',
                    reviewer: includes['showReviewer'] ? 'yes' : 'no'
                },
                success: function (data) {
                    var tableObj = $('#tbody-data');
                    tableObj.html('');
                    if (data.page_count === 0) {
                        tableObj.append('<td><td colspan=2><span>No such record found.</span></td></tr>');
                        return;
                    }
                    var dataSet = data.data;
                    for (var index in dataSet) {
                        var inputPrefix = dataSet[index].user_id;
                        tableObj.append('<tr><td>' + dataSet[index].user_name + '</td><td><div id="user_' + dataSet[index].user_id + '" class="checkbox checkbox-primary"></div></td></tr>');
                        for (var key in includesInner) {
                            var chkName = inputPrefix + '_role_' + includesInner[key];
                            var chkStr = '<input type="checkbox" data-user-id="' + dataSet[index].user_id + '" data-role="' + includesInner[key] + '" name="' + chkName + '" id="' + chkName + '"';
                            if ($.inArray(includesInner[key], dataSet[index].user_roles) !== -1) {
                                chkStr += ' checked="checked"';
                            }
                            if (includesInner[key] == 'creator') {
                                chkStr += ' disabled="disabled"';
                            }
                            chkStr += '/><label for="' + chkName + '">' + includesName[key] + '</label>&nbsp;&nbsp;&nbsp;&nbsp;';
                            $('#user_' + dataSet[index].user_id).append(chkStr);
                            $('#' + chkName).change(function (e) {
                                $.ajax({
                                    type: 'GET',
                                    url: '<?php echo $base_url;?>/participant/?do=toggleRole&uid=' + e.target.getAttribute('data-user-id') + '&role=' + e.target.getAttribute('data-role'),
                                    async: true,
                                    dataType: 'json',
                                    success: function (rdata) {
                                        if (rdata.status === 'SUCCESS') {
                                            messageBox('Role changed successfully.', 'Message');
                                            loadData();
                                        }
                                    }
                                });
                            });
                        }
                        var pageStr = '';
                        for (var i = 1; i <= data.page_count; i++) {
                            pageStr += '<li class="page-item ' + (i === page ? 'active' : '') + '"><button class="page-link" onclick="goPage(' + i + ')">' + i + '</button></li>'
                        }
                        $('#page-display').html(pageStr);
                        //console.log(tableObj.html());
                    }

                }
            });
        };

        loadData();
        var goPage = function (p) {
            page = p;
            loadData();
        };
        var search = function () {
            userName = $('#searchUserName').val();
            email = $('#searchUserEmail').val();
            loadData();
        };
    </script>
<?php include APPPATH . '/views/conference/footer.php'; ?>