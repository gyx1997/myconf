var authorList = [];

var addToList = function (author_email, author_first_name, author_last_name, author_institution, author_department, author_address, author_prefix, author_chn_full_name) {
    authorList[authorList.length] = {
        "email": author_email,
        "first_name": author_first_name,
        "last_name": author_last_name,
        "institution": author_institution,
        "department": author_department,
        "address": author_address,
        "prefix": author_prefix,
        "chn_full_name": author_chn_full_name
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

var searchAuthor = function (confUrl, email) {
    var searchMessage = $('#searchMessage');
    if (emailCheck(email)) {
        if (existInList(email) === false) {
            $.ajax({
                type: 'GET',
                dataType: 'json',
                async: true,
                url: confUrl + '/paper-submit/author/?do=get&ajax=true&email=' + window.btoa(email),
                success: function (data) {
                    if (data.found === true) {
                        addToList(
                            data.data.scholar_email,
                            data.data.scholar_first_name,
                            data.data.scholar_last_name,
                            data.data.scholar_institution,
                            data.data.scholar_department,
                            data.data.scholar_address,
                            data.data.scholar_prefix,
                            data.data.scholar_chn_full_name
                        );
                        refreshAuthorListView();
                    } else {
                        $('#searchMessageMain').html('<div class="alert alert-info">No author has the email you have just entered. <br>' +
                            'Would you like to <button type="button" class="btn btn-link" id="createNewAuthorButton">create a new author</button> with the email entered?</div>');
                        //TODO 创建一个新的作者
                        $('#createNewAuthorButton').click(function (e) {
                            editAuthor(addToList($('#addAuthorEmailToSearch').val(), '', '', '', '', '', ''));
                        });
                        searchMessage.show();

                    }
                }
            });
        }
    } else {
        $('#searchMessageMain').html('<div class="alert alert-danger">The email you have just entered is not valid.</div>');
        searchMessage.show();
    }
};

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
                    authorList[i]["prefix"] + " &nbsp;" + authorList[i]["first_name"] + ", " + authorList[i]["last_name"],
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
    $('#editAuthorPrefix').val(authorList[i]['prefix']);
    $('#editAuthorFirstName').val(authorList[i]['first_name']);
    $('#editAuthorLastName').val(authorList[i]['last_name']);
    $('#editAuthorInstitution').val(authorList[i]['institution']);
    $('#editAuthorDepartment').val(authorList[i]['department']);
    $('#editAuthorAddress').val(authorList[i]['address']);
    modalDialog.modal();
    modalDialog.on('hide.bs.modal', function (e) {
        if (editAuthorId !== -1) {
            authorList[editAuthorId]['email'] = $('#editAuthorEmail').val();
            authorList[editAuthorId]['prefix'] = $('#editAuthorPrefix').val();
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