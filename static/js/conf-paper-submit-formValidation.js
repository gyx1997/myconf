var validateForm = function() {
    if (suggestedSessionId === '-2') {
        messageBoxSecondary('Please select suggested session.', 'Message');
        return false;
    }

    if (authorList.length === 0) {
        messageBoxSecondary('Please select authors.', 'Message');
        return false;
    }

    if (!$('#paper_pdf').get(0).files[0]) {
        messageBoxSecondary('Please select paper file.', 'Message');
        return false;
    }

    if (!$('#paper_copyright_pdf').get(0).files[0]) {
        messageBoxSecondary('Please select copyright file.', 'Message');
        return false;
    }
    return true;
};