<?php

namespace myConf\Libraries;

/**
 * Class Attach 附件文件相关的静态方法类
 * @package myConf\Libraries
 * @see \myConf\Libraries\Upload
 */
class Attach
{
    public const file_type_pdf = 1;
    public const file_type_jpeg = 2;
    public const file_type_png = 3;

    /**
     * 处理附件。
     * @param string $file_field_name
     * @return array
     * @throws \myConf\Exceptions\FileUploadException
     */
    public static function parse_attach(string $file_field_name): array
    {
        $result = ['error' => '', 'status' => ''];
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
        //生成文件名
        $short_name = md5(strval($d[4]) . strval($d[5]) . strval($d[6]) . $file_name) . '.' . $file_ext;
        $full_path = $stored_dir . $short_name;
        return array(
            'full_name' => $full_path,
            'short_name' => $short_name,
            'directory' => $stored_dir,
            'extension' => $file_ext
        );
    }

    /**
     * @param string $attach_relative_path
     * @param string $file_name_original
     * @param int $file_size
     * @param int $download_speed
     * @throws \myConf\Exceptions\AttachFileCorruptedException
     */
    public static function download_attach(string $attach_relative_path, string $file_name_original, int $file_size, int $download_speed = 80000) : void {
        $file_absolute_path = ATTACHMENT_DIR . $attach_relative_path;
        if (!file_exists($file_absolute_path)) {
            throw new \myConf\Exceptions\AttachFileCorruptedException('ATTACH_NOT_EXIST', 'File "' . $file_absolute_path . '" does not exist.');
        }
        set_time_limit(1800);
        ob_clean();
        header("Content-type:application/octet-stream");
        header("Content-Disposition:attachment;filename=" . $file_name_original);
        header("Accept-ranges:bytes");
        header("Accept-length:" . $file_size);
        $fp = fopen($file_absolute_path, 'r');
        if ($fp === false) {
            throw new \myConf\Exceptions\AttachFileCorruptedException('ATTACH_CANNOT_READ', 'File "' . $file_absolute_path . '" cannot be read.');
        }
        $buffer = $download_speed / 10;
        $buffer_count = 0;
        while (!feof($fp) && $file_size - $buffer_count > 0) {
            $data = fread($fp, $buffer);
            $buffer_count += $buffer;
            echo $data;
            flush();
            ob_flush();
            usleep(100000);
        }
        fclose($fp);
    }

    /**
     * @param string $attach_relative_path
     * @param string $file_name_original
     * @param int $file_size
     * @param int $file_type
     * @throws \myConf\Exceptions\AttachFileCorruptedException
     * @throws \myConf\Exceptions\SendExitInstructionException
     */
    public static function preview_attach(string $attach_relative_path, string $file_name_original, int $file_size, int $file_type) : void {
        $file_absolute_path = ATTACHMENT_DIR . $attach_relative_path;
        if (!file_exists($file_absolute_path)) {
            throw new \myConf\Exceptions\AttachFileCorruptedException('ATTACH_NOT_EXIST', 'File "' . $file_absolute_path . '" does not exist.');
        }
        set_time_limit(1800);
        ob_clean();
        if ($file_type === self::file_type_pdf) {
            header("Content-type:application/pdf");
            header("Content-Disposition:inline;filename=" . $file_name_original);
        }
        header("Accept-ranges:bytes");
        header("Accept-length:" . $file_size);
        $fp = fopen($file_absolute_path, 'r');
        if ($fp === false) {
            throw new \myConf\Exceptions\AttachFileCorruptedException('ATTACH_CANNOT_READ', 'File "' . $file_absolute_path . '" cannot be read.');
        }
        $buffer = 1048576;
        $buffer_count = 0;
        while (!feof($fp) && $file_size - $buffer_count > 0) {
            $data = fread($fp, $buffer);
            $buffer_count += $buffer;
            echo $data;
            flush();
            ob_flush();
            usleep(100000);
        }
        fclose($fp);
        throw new \myConf\Exceptions\SendExitInstructionException();
    }
}