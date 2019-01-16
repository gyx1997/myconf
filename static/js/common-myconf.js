/**
 * 判断字符串emailAddr是否为合法的email格式
 * 主要判断'@'及'.'是否出现，以及两者的位置
 * @param emailAddr 输入的email地址
 * @return true/false。
 */
function emailCheck(emailAddr) {
    if ((emailAddr == null) || (emailAddr.length < 2))
        return false;
    var aPos = emailAddr.indexOf("@", 1);

    if (aPos < 0) {
        return false;
    }
    if (emailAddr.indexOf(".", aPos + 2) < 0) {
        return false;
    }
    return true;
}
