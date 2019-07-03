/**
 *
 * @param fileFieldId
 * @param percentageDiv
 * @param csrfName
 * @param csrfToken
 * @param attachmentIdInputId
 */
function putPDF(fileFieldId, percentageDiv, csrfName, csrfToken, attachmentIdInputId) {
    var fd = new FormData();
    var file = document.getElementById(fileFieldId).files[0];
    var percentDivObj = document.getElementById(percentageDiv);
    var attachmentIdInputObj = document.getElementById(attachmentIdInputId);
    if (file.size <= 8*1024*1024) {
        fd.append('upfile', document.getElementById(fileFieldId).files[0]);
        fd.append(csrfName, csrfToken);
        var xhr = new XMLHttpRequest();
        xhr.upload.addEventListener("progress", function (evt) {
            if (evt.lengthComputable) {
                var percentComplete = Math.round(evt.loaded * 100 / evt.total);
                percentDivObj.innerHTML = '<div class="alert alert-info">Uploading... ' + percentComplete.toString() + ' %' + '</div>';
            }
            else {
                percentDivObj.innerHTML = '<div class="alert alert-info">Uploading...</div>';
            }
        }, false);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                console.log(JSON.parse(xhr.responseText));
                attachmentIdInputObj.value = JSON.parse(xhr.responseText).aid;
            }
        };
        xhr.addEventListener("load", function () {
            percentDivObj.innerHTML = '<div class="alert alert-success">Upload Success.</div>';
        }, false);
        xhr.addEventListener("error", function () {
            percentDivObj.innerHTML = '<div class="alert alert-danger">There was an error when trying to upload this file.</div>';
        }, false);
        xhr.addEventListener("abort", function () {
            percentDivObj.innerHTML = '<div class="alert alert-warning">The upload has been cancelled, or the browser dropped the connection.</div>';
        }, false);
        xhr.open("POST", "/attachment/put/file/?ff=upfile");//修改成自己的接口
        xhr.send(fd);

    } else {
        percentDivObj.innerHTML = '<div class="alert alert-danger">The size of this file must be <strong>less than 8 MBytes</strong>.</div>';
    }
}