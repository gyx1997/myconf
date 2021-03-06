var doSubmit = function (action, confUrl) {
    if (action === 'submit') {
        var ehandler = function() {
            if(validateForm() === true) {
                $('#authors').val(JSON.stringify(authorList));
                $.ajax({
                    async: true,
                    type: "POST",
                    url: confUrl + "/paper-submit/new/?do=" + action + "&ajax=true",
                    contentType: false,
                    data: new FormData($('#paper_data')[0]),
                    processData: false,
                    cache: false,
                    dataType: "json",
                    success: function (d) {
                        if (d.status === 'SUCCESS') {
                            messageBoxSecondary('Paper ' + (action === 'save' ? 'saved' : 'submitted') + ' successfully.', 'Message', null);
                            setTimeout(function () {
                                window.location.href = confUrl + '/paper-submit/';
                            }, 2000);
                        } else if (d.status === 'FILE_ERROR') {
                            messageBoxSecondary('File upload error.', 'Message');
                        } else if (d.status === 'AUTHOR_EMPTY') {
                            messageBoxSecondary('Please select authors.', 'Message');
                        } else if (d.status === 'OUT_OF_DATE') {
                            console.log('out of date');
                            messageBoxSecondary('The submission has been closed after deadline.', 'Message');
                        }
                    },
                    error: function () {
                        messageBoxSecondary('An internal server error occurred during submitting your paper.', 'Message');
                    }
                });
            }
        };
        messageBox('Are you sure to submit the paper? You cannot edit it any more after clicking the OK button!', 'Message', ehandler, true);

    } else {
        $('#authors').val(JSON.stringify(authorList));
        $.ajax({
            async: true,
            type: "POST",
            url: confUrl + "/paper-submit/new/?do=" + action + "&ajax=true",
            contentType: false,
            data: new FormData($('#paper_data')[0]),
            processData: false,
            cache: false,
            dataType: "json",
            success: function (d) {
                if (d.status === 'SUCCESS') {
                    messageBoxSecondary('Paper ' + (action === 'save' ? 'saved' : 'submitted') + ' successfully.', 'Message', null);
                    setTimeout(function () {
                        window.location.href = confUrl + '/paper-submit/';
                    }, 2000);
                } else if (d.status === 'FILE_ERROR') {
                    messageBoxSecondary('File upload error.', 'Message');
                } else if (d.status === 'AUTHOR_EMPTY') {
                    messageBoxSecondary('Please select authors.', 'Message');
                } else if (d.status === 'OUT_OF_DATE') {
                    console.log('out of date');
                    messageBoxSecondary('The submission has been closed after deadline.', 'Message');
                }
            },
            error: function () {
                messageBox('An internal server error occurred during submitting your paper.', 'Message');
            }
        });
    }
};
