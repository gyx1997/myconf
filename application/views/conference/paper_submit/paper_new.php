<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<?php include APPPATH . 'views/conference/header.php'; ?>
<?php include APPPATH . 'views/conference/paper_submit/left_bar.php'; ?>
<div class="col-md-10 content-rf content-rf-doc">
    <div class="col-md-12">
        <div class="row">
        </div>
        <div class="modal-header">
            <h4 class="modal-title">Submit new paper</h4>
        </div>
        <div class="modal-body">
            <form id="paper_data" name="paper_data" action=""
                  method="post" enctype="multipart/form-data">
                <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
                <input type="hidden" name="user_id" value="<?php echo $login_user['user_id']; ?>"/>
                <input type="hidden" name="authors" id="authors" value=""/>
                <div class="form-group">
                    <label for="paper_title_text">Title&nbsp;&nbsp;<span class="badge badge-info">LESS THAN 200 characters</span></label>
                    <textarea class="form form-control" id="paper_title_text" name="paper_title_text"
                              rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label for="paper_abstract_text">Abstract&nbsp;&nbsp;<span class="badge badge-info">LESS THAN 200 words</span></label>
                    <textarea class="form form-control" id="paper_abstract_text" name="paper_abstract_text"
                              rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Authors&nbsp;&nbsp;</label>
                    <div id="authorList" class="card">
                        <div class="card-header">
                            <div class="form-inline">
                                <label style="width:20%;float:left;" for="addAuthorEmailToSearch">Author Email</label>
                                <input class="form-control" type="text" name="addAuthorEmailToSearch"
                                       id="addAuthorEmailToSearch" style="width:60%;float:left;"/>
                                <span style="width:5%;"></span>
                                <button type="button" class="btn btn-default" style="width:10%;float:right;"
                                        onclick="searchAuthor($('#addAuthorEmailToSearch').val());">Add
                                </button>
                            </div>
                            <div class="mt-3 row" id="searchMessageLineSpace"></div>
                            <div id="searchMessage" style="display:none;">
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Selected Authors</label>
                                <ul class="list-group" id="authorListView">
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="paper_pdf">
                        Paper Content (PDF Upload)&nbsp;&nbsp;
                        <span class="badge badge-info">LESS THAN 5MB.</span>
                    </label>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="file" class="" id="paper_pdf" name="paper_pdf">
                </div>
                <div class="form-group">
                    <label for="paper_copyright_pdf">
                        Paper Copyright (PDF Upload)&nbsp;&nbsp;
                        <span class="badge badge-info">LESS THAN 5MB</span>
                    </label>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <input type="file" class="" id="paper_copyright_pdf" name="paper_copyright_pdf">
                </div>
                <div class="form-inline">
                    <label for="paper_type_text">Type&nbsp;&nbsp;&nbsp;
                        <select id="paper_type_text" name="paper_type_text" class="form form-control">
                            <option value="paper" selected="selected">Paper</option>
                            <option value="abstract">Abstract</option>
                        </select>
                    </label>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <label for="paper_suggested_session">Suggested Session&nbsp;&nbsp;&nbsp; <span
                                class="badge badge-secondary">Not available now</span>&nbsp;&nbsp;&nbsp;
                        <select id="paper_suggested_session" name="paper_suggested_session" class="form form-control"
                                disabled="disabled">
                            <option value="default" selected="selected">default</option>
                        </select>
                    </label>
                </div>
                <div class="row mt-3"></div>
                <button id="submit_button" class="btn btn-primary" type="submit">Submit</button>
                <button id="save_button" class="btn btn-secondary" type="button" onclick="saveToServer();">Save To
                    Server
                </button>
            </form>
        </div>
    </div>
</div>
<div id="invisibleTemplate" style="display:none;">
    <li class="list-group-item list-group-item-action" style="font-size:80%;">
        <h5 style="line-height: 50px;">{0}&nbsp;&nbsp;&nbsp;<span class="badge badge-light">{1}</span>
            <span class="float-right">
                <div class="btn-group">
                    <button type="button" data-toggle="tooltip" class="btn btn-light fa fa-edit" id="author_edit{2}"
                            data-author-id="{3}" title="Edit author information."></button>
                    <button type="button" data-toggle="tooltip" class="btn btn-light fa fa-angle-up" id="author_up{4}"
                            data-author-id="{5}" title="Move up."></button>
                    <button type="button" data-toggle="tooltip" class="btn btn-light fa fa-angle-down"
                            id="author_down{6}" data-author-id="{7}" title="Move down."></button>
                    <button type="button" data-toggle="tooltip" class="btn btn-light" id="author_delete{8}"
                            data-author-id="{9}" title="Remove this author from this paper.">&times;</button>
                </div>
            </span>
        </h5>
        <p>{10}</p>
        <p>{11}</p>
    </li>
</div>
<script type="text/javascript">
    var saveToServer = function () {

        $('#authors').val(JSON.stringify(authorList));
        console.log(JSON.stringify(authorList));
        $.ajax({
            async: true,
            type: "POST",
            url: "<?php echo $conf_url;?>/paper-submit/new/?do=save",
            contentType: false,
            data: new FormData($('#paper_data')[0]),
            processData: false,
            cache: false,
            dataType: "json",
            success: function (d) {
                if (d.status === 'SUCCESS') {
                    messageBox('Paper saved successfully.', 'Message', null);
                }
            },
            error: function () {

            }
        });
    };
    var submitPaper = function () {

    };
</script>
<script type="text/javascript">
    var authorList = [];
    var addToList = function (author_email, author_first_name, author_last_name, author_institution, author_department, author_address, author_prefix) {
        authorList[authorList.length] = {
            "email": author_email,
            "first_name": author_first_name,
            "last_name": author_last_name,
            "institution": author_institution,
            "department": author_department,
            "address": author_address,
            "prefix": author_prefix
        };
        return authorList.length - 1;
    };

    var existInList = function (email) {
        for (var i = 0; i < authorList.length; i++) {
            if (authorList[i]['email'] === email) {
                return true;
            }
        }
        return false;
    };

    var editAuthorId = -1;
    var editAuthorMode = 'edit';

    var removeFromList = function (author_local_id) {
        var authorListBackup = authorList;
        authorList = [];
        var id = parseInt(author_local_id);
        for (var i = 0; i < authorListBackup.length; i++) {
            if (i === id) {
                continue;
            }
            authorList[authorList.length] = authorListBackup[i];
        }
    };

    var moveUp = function (author_local_id) {
        var i = parseInt(author_local_id);
        if (i === 0) {
            return;
        }
        var tmp = authorList[i - 1];
        authorList[i - 1] = authorList[i];
        authorList[i] = tmp;
    };

    var moveDown = function (author_local_id) {
        var i = parseInt(author_local_id);
        if (i === authorList.length - 1) {
            return;
        }
        var tmp = authorList[i + 1];
        authorList[i + 1] = authorList[i];
        authorList[i] = tmp;
    };

    var fillStr = function () {
        var num = arguments.length;
        var oStr = arguments[0];
        for (var i = 1; i < num; i++) {
            var pattern = "\\{" + (i - 1) + "\\}";
            var re = new RegExp(pattern, "g");
            oStr = oStr.replace(re, arguments[i]);
        }
        return oStr;
    };

    var searchAuthor = function (email) {
        if (existInList(email) === false) {
            $.ajax({
                type: 'GET',
                dataType: 'json',
                async: true,
                url: '<?php echo $conf_url; ?>/paper-submit/author/?do=get&email=' + window.btoa(email),
                success: function (data) {
                    if (data.found === true) {
                        addToList(
                            data.data.scholar_email,
                            data.data.scholar_first_name,
                            data.data.scholar_last_name,
                            data.data.scholar_institution,
                            data.data.scholar_department,
                            data.data.scholar_address,
                            data.data.scholar_prefix
                        );
                        refreshAuthorListView();
                    } else {
                        var searchMessage = $('#searchMessage');
                        searchMessage.html('<div class="alert alert-info">No author has the email you have just entered. <br>' +
                            'Would you like to <button type="button" class="btn btn-link" id="createNewAuthorButton">create a new author</button> with the email entered?</div>');
                        //TODO 创建一个新的作者
                        $('#createNewAuthorButton').click(function (e) {
                            editAuthor(addToList($('#addAuthorEmailToSearch').val(), '', '', '', '', '', ''));
                        });
                        searchMessage.show();
                        $('#searchMessageLineSpace').show();
                    }
                }
            });
        }
    };
</script>
<script>
    var refreshAuthorListView = function () {
        var listView = $('#authorListView');
        if (authorList.length === 0) {
            listView.html('<li class="list-group-item list-group-item-danger">No Author Selected.</li>');
        } else {
            listView.html('');
            for (var i = 0; i < authorList.length; i++) {
                listView.append(
                    fillStr(
                        $('#invisibleTemplate').html(),
                        authorList[i]["first_name"] + ", " + authorList[i]["last_name"],
                        authorList[i]["email"],
                        i, i, i, i, i, i, i, i,
                        authorList[i]['institution'] + ", " + authorList[i]["department"],
                        authorList[i]['address']
                    )
                );
                $('#author_edit' + i).click(function (e) {
                    editAuthor(e.target.getAttribute('data-author-id'));
                });
                $('#author_up' + i).click(function (e) {
                    moveUp(e.target.getAttribute('data-author-id'));
                    refreshAuthorListView();
                });
                $('#author_down' + i).click(function (e) {
                    moveDown(e.target.getAttribute('data-author-id'));
                    refreshAuthorListView();
                });
                $('#author_delete' + i).click(function (e) {
                    removeFromList(e.target.getAttribute('data-author-id'));
                    refreshAuthorListView();
                });
            }
        }
        $('#searchMessage').hide();
        $('#searchMessageLineSpace').hide();
    };

    var editAuthor = function (author_local_id) {
        var i = parseInt(author_local_id);
        var modalDialog = $('#modalEditAuthor');
        editAuthorId = i;
        editAuthorMode = 'edit';
        $('#editAuthorEmail').val(authorList[i]['email']);
        $('#editAuthorFirstName').val(authorList[i]['first_name']);
        $('#editAuthorLastName').val(authorList[i]['last_name']);
        $('#editAuthorInstitution').val(authorList[i]['institution']);
        $('#editAuthorDepartment').val(authorList[i]['department']);
        $('#editAuthorAddress').val(authorList[i]['address']);
        modalDialog.modal();
        modalDialog.on('hide.bs.modal', function (e) {
            if (editAuthorId !== -1) {
                authorList[editAuthorId]['email'] = $('#editAuthorEmail').val();
                authorList[editAuthorId]['first_name'] = $('#editAuthorFirstName').val();
                authorList[editAuthorId]['last_name'] = $('#editAuthorLastName').val();
                authorList[editAuthorId]['institution'] = $('#editAuthorInstitution').val();
                authorList[editAuthorId]['department'] = $('#editAuthorDepartment').val();
                authorList[editAuthorId]['address'] = $('#editAuthorAddress').val();
            }
            editAuthorId = -1;
            refreshAuthorListView();
        });
    };
    refreshAuthorListView();
</script>
<!--编辑作者模态框-->
<div class="modal fade" id="modalEditAuthor">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Author</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-head">

                    </div>
                    <div class="card-body">
                        <table style="width:100%;">
                            <tr>
                                <td style="width:38%;">
                                    <label for="editAuthorEmail">Email</label>
                                </td>
                                <td style="width:62%;">
                                    <input class="form-control" name="editAuthorEmail" id="editAuthorEmail" type="text"
                                           style="width:100%;"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="row mt-3"></div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:38%;">
                                    <label for="editAuthorFirstName">First Name</label>
                                </td>
                                <td style="width:62%;">
                                    <input class="form-control" name="editAuthorFirstName" id="editAuthorFirstName"
                                           type="text"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="row mt-3"></div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:38%;">
                                    <label for="editAuthorLastName">Last Name</label>
                                </td>
                                <td style="width:62%;">
                                    <input class="form-control" name="editAuthorLastName" id="editAuthorLastName"
                                           type="text"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="row mt-3"></div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:38%;">
                                    <label for="editAuthorInstitution">Institution&nbsp;&nbsp;<span
                                                class="badge badge-info">LESS THAN 160 characters</span></label>
                                </td>
                                <td style="width:62%;">
                                    <input class="form-control" name="editAuthorInstitution" id="editAuthorInstitution"
                                           type="text"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="row mt-3"></div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:38%;">
                                    <label for="editAuthorDepartment">Department&nbsp;&nbsp;<span
                                                class="badge badge-info">LESS THAN 60 characters</span></label>
                                </td>
                                <td style="width:62%;">
                                    <input class="form-control" name="editAuthorDepartment" id="editAuthorDepartment"
                                           type="text"/>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="row mt-3"></div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width:38%;">
                                    <label for="editAuthorAddress">Address&nbsp;&nbsp;<span class="badge badge-info">LESS THAN 250 Characters</span></label>
                                </td>
                                <td style="width:62%;">
                                    <input class="form-control" name="editAuthorAddress" id="editAuthorAddress"
                                           type="text"/>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
<?php include APPPATH . 'views/conference/footer.php'; ?>



