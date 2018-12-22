<?php

namespace myConf\Libraries;

/**
 * Class Attach 附件文件相关的静态方法类
 * @package myConf\Libraries
 * @see \myConf\Libraries\Upload
 */
class Attach
{
    /**
     * 处理附件。
     * @param string $file_field_name
     * @return array
     * @throws \myConf\Exceptions\FileUploadException
     */
    public static function parse_attach(string $file_field_name): array
    {
        $result = array('error' => '', 'status' => '');
        $stored_data = self::_get_stored_file_data(Upload::get_original_name($file_field_name));
        $file_data = Upload::parse_upload_file($file_field_name, $stored_data['short_name'], ATTACHMENT_DIR . $stored_data['directory'], true);
        $is_image = ($stored_data['extension'] == '.jpg' || $stored_data['extension'] == '.jpeg'
            || $stored_data['extension'] == '.png' || $stored_data['extension'] == '.gif') ? TRUE : FALSE;
        if ($is_image === true) {
            $image_info = @getimagesize(FCPATH . RELATIVE_ATTACHMENT_DIR . $stored_data['directory'] . $stored_data['short_name']);
        }
        $result['mime_type'] = $file_data['mime_type'];
        $result['original_name'] = $file_data['original_name'];
        $result['is_image'] = $is_image;
        $result['size'] = $file_data['file_size'];
        $result = array_merge($result, $stored_data);
        return $result;
    }

    /**
     * @param $file_name
     * @return array
     */
    private static function _get_stored_file_data($file_name)
    {
        $file_ext = \myConf\Libraries\File::filename_extension($file_name);
        $t = time();
        $d = explode('-', date("Y-y-m-d-H-i-s"));
        $format = '{yy}/{mm}/{dd}/';
        $format = str_replace("{yyyy}", $d[0], $format);
        $format = str_replace("{yy}", $d[1], $format);
        $format = str_replace("{mm}", $d[2], $format);
        $format = str_replace("{dd}", $d[3], $format);
        $stored_dir = $format;
        $short_name = strval($d[4]) . strval($d[5]) . strval($d[6]) . uniqid() . '.' . $file_ext;
        $full_path = $stored_dir . $short_name;
        return array(
            'full_name' => $full_path,
            'short_name' => $short_name,
            'directory' => $stored_dir,
            'extension' => $file_ext
        );
    }
}