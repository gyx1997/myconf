var doSubmit = function (action, paperId, paperVer, confUrl) {
    $('#authors').val(JSON.stringify(authorList));
    if (action === 'submit') {
        var ehandler = function(){
            if (validateForm() === true) {
                $.ajax({
                    async: true,
                    type: "POST",
                    url: confUrl + "/paper-submit/edit/?id=" + paperId + "&ver=" + paperVer + "&do=" + action + "&ajax=true",
                    contentType: false,
                    data: new FormData($('#paper_data')[0]),
                    processData: false,
                    cache: false,
                    dataType: "json",
                    success: function (d) {
                        if (d.status === 'SUCCESS') {
                            messageBox('Paper ' + (action === 'save' ? 'saved' : 'submitted') + ' successfully.', 'Message', null);
                            setTimeout(function () {
                                window.location.href = confUrl + '/paper-submit/';
                            }, 2000);
                        } else if (d.status === 'FILE_ERROR') {
                            messageBox('File upload error. Do you forget to choose Paper content file or Copyright file?', 'Message');
                        } else if (d.status === 'AUTHOR_EMPTY') {
                            messageBox('Please select authors.', 'Message');
                        } else if (d.status === 'OUT_OF_DATE') {
                            messageBox('The submission has been closed after deadline.', 'Message');
                        }
                    },
                    error: function () {
                        messageBox('An internal server error occurred during submitting your paper.', 'Message');
                    }
                });
            }};
        messageBox('Are you sure to submit the paper? You cannot edit it any more after clicking the OK button!', 'Message', ehandler, true);
    } else {
        $.ajax({
            async: true,
            type: "POST",
            url: confUrl + "/paper-submit/edit/?id=" + paperId + "&ver=" + paperVer + "&do=" + action + "&ajax=true",
            contentType: false,
            data: new FormData($('#paper_data')[0]),
            processData: false,
            cache: false,
            dataType: "json",
            success: function (d) {
                if (d.status === 'SUCCESS') {
                    messageBox('Paper ' + (action === 'save' ? 'saved' : 'submitted') + ' successfully.', 'Message', null);
                    setTimeout(function () {
                        window.location.href = confUrl + '/paper-submit/';
                    }, 2000);
                } else if (d.status === 'FILE_ERROR') {
                    messageBox('File upload error. ', 'Message');
                }
            },
            error: function () {
                messageBox('An internal server error occurred during submitting your paper.', 'Message');
            }
        });
    }
};