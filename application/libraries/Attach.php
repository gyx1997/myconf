<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/13
 * Time: 10:24
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Attach
{
    /**
     * @param $file_field_name
     * @return array
     */
    public function parse_attach($file_field_name)
    {
        $result = array('error' => '', 'status' => '');
        $file = $_FILES[$file_field_name];
        if (!$file || $file['error'] != 0) {
            $result['error'] = 'ERROR_FILE_NOT_FOUND';
        } else {
            if (!file_exists($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                $result['error'] = 'ERROR_TMP_FILE_NOT_FOUND';
            } else {
                $stored_data = $this->_get_stored_file_data($file['name']);
                $file_size = filesize($file['tmp_name']);
                if ($this->_move_file_to($file['tmp_name'], $stored_data['directory'], $stored_data['short_name']) === FALSE) {
                    $result['error'] = 'ERROR_WRITE_CONTENT';
                } else {
                    $is_image = ($stored_data['extension'] == '.jpg' || $stored_data['extension'] == '.jpeg'
                        || $stored_data['extension'] == '.png' || $stored_data['extension'] == '.gif') ? TRUE : FALSE;
                    if ($is_image) {
                        $image_info = getimagesize(FCPATH . $stored_data['directory'] . $stored_data['short_name']);
                    }
                    $result['original_name'] = $file['name'];
                    $result['is_image'] = $is_image;
                    $result['size'] = $file_size;
                    $result = array_merge($result, $stored_data);
                }
            }
        }
        if ($result['error'] === '') {
            $result['status'] = 'SUCCESS';
        }
        return $result;
    }

    /**
     * @param $file_name
     * @return array
     */
    private function _get_stored_file_data($file_name)
    {
        $file_ext = $this->_get_file_extension($file_name);
        $t = time();
        $d = explode('-', date("Y-y-m-d-H-i-s"));
        $format = RELATIVE_ATTACHMENT_DIR . '{yy}/{mm}/{dd}/';
        $format = str_replace("{yyyy}", $d[0], $format);
        $format = str_replace("{yy}", $d[1], $format);
        $format = str_replace("{mm}", $d[2], $format);
        $format = str_replace("{dd}", $d[3], $format);
        $stored_dir = $format;
        $short_name = strval($d[4]) . strval($d[5]) . strval($d[6]) . uniqid() . $file_ext;
        $full_path = $stored_dir . $short_name;
        return array(
            'full_name' => $full_path,
            'short_name' => $short_name,
            'directory' => $stored_dir,
            'extension' => $file_ext,
        );
    }

    /**
     * @param $file_name
     * @return string
     */
    private function _get_file_extension($file_name)
    {
        return strtolower(strrchr($file_name, '.'));
    }

    /**
     * @param $source_file
     * @param $relative_directory
     * @param $file_name
     * @return bool
     */
    private function _move_file_to($source_file, $relative_directory, $file_name)
    {
        $destination_dir = FCPATH . $relative_directory;
        if (!is_dir($destination_dir) && !mkdir($destination_dir, 0777, true)) {
            return FALSE;
        }
        if (!move_uploaded_file($source_file, $destination_dir . $file_name) ||
            !file_exists($destination_dir . $file_name)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * @param $attach_relative_path
     * @param $file_name_original
     * @param $file_size
     */
    public function download_attach($attach_relative_path, $file_name_original, $file_size, $redirect_source_file = FALSE, $download_speed = 80000)
    {
        $file_absolute_path = FCPATH . $attach_relative_path;
        if (!file_exists($file_absolute_path)) {
            show_404();
        }
        if ($redirect_source_file) {
            header('location:' . $attach_relative_path, true, 301);
            return;
        }
        set_time_limit(900);
        ob_clean();
        header("Content-type:application/octet-stream");
        header("Content-Disposition:attachment;filename=" . $file_name_original);
        header("Accept-ranges:bytes");
        header("Accept-length:" . $file_size);
        $fp = @fopen($file_absolute_path, 'r');
        if ($fp === FALSE) {
            show_error('Trying to read attachment file but failed.', 500);
        }
        $buffer = $download_speed;
        $buffer_count = 0;
        while (!@feof($fp) && $file_size - $buffer_count > 0) {
            $data = @fread($fp, $buffer);
            $buffer_count += $buffer;
            echo $data;
            flush();
            ob_flush();
            sleep(1);
        }
        @fclose($fp);
    }
}