<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/18
 * Time: 22:24
 */

class mConfig extends CF_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'configs';
    }

    /**
     * @return string
     */
    public function get_administrator_password()
    {
        return $this->_get_value('password');
    }

    /**
     * @param $key
     * @return mixed
     */
    private function _get_value($key)
    {
        $query = $this->db->query('SELECT v FROM ' . $this->_table() . ' WHERE k="' . $key . '"');
        $row = $query->row_array();
        return $row['v'];
    }

    /**
     * @return string
     */
    public function generate_salt()
    {
        $salt = md5(uniqid() . time());
        $this->_set_value('salt', $salt);
        return $salt;
    }

    /**
     * @param $key
     * @param $value
     */
    private function _set_value($key, $value)
    {
        $this->db->where('k', $key);
        $this->db->update($this->_table(), array('v' => $value));
    }

    /**
     * @return string
     */
    public function get_salt()
    {
        return $this->_get_value('salt');
    }

    public function get_mitbeian()
    {
        return $this->_get_value('mitbeian');
    }

    public function get_footer1()
    {
        return $this->_get_value('footer1');
    }

    public function get_footer2()
    {
        return $this->_get_value('footer2');
    }

    public function get_banner()
    {
        return $this->_get_value('headimg');
    }

    public function get_qrcode()
    {
        return $this->_get_value('qrcode');
    }

    public function get_title()
    {
        return $this->_get_value('title');
    }

    public function set_footer1($footer)
    {
        $this->_set_value('footer1', $footer);
    }

    public function set_footer2($footer)
    {
        $this->_set_value('footer2', $footer);
    }

    public function set_qrcode($qrcode)
    {
        $this->_set_value('qrcode', $qrcode);
    }

    public function set_title($title)
    {
        $this->_set_value('title', $title);
    }

    /**
     * @param $password
     */
    public function set_administrator_password($password)
    {
        $this->_set_value('password', $password);
    }

    public function set_mitbeian($mitbeian)
    {
        $this->_set_value('mitbeian', $mitbeian);
    }

    public function set_banner($banner_image_filename)
    {
        $this->_set_value('headimg', $banner_image_filename);
    }
}
