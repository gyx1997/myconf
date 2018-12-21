<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/15
 * Time: 18:34
 */

namespace myConf\Models;


class Config extends \myConf\BaseModel
{
    /**
     * Config constructor.
     */
    private $_config_data = array();

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'configs';
        $this->_pk = 'k';
        $tmp = $this->_fetch_all();
        foreach ($tmp as $t) {
            $this->_config_data[$t['k']] = $t['v'];
        }
    }

    /**
     * @param string $key
     * @return string
     */
    public function __get(string $key): string
    {
        return isset($this->_config_data[$key]) ? $this->_config_data[$key] : '';
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function __set(string $key, string $value): void
    {
        $this->set($key, $value);
        $this->_config_data[$key] = $value;
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
    private function _get_value(string $key): string
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
    private function _set_value(string $key, string $value): void
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

    /**
     * @deprecated
     * @return string
     */
    public function get_mitbeian(): string
    {
        return $this->_get_value('mitbeian');
    }

    /**
     * @deprecated
     * @return string
     */
    public function get_footer(): string
    {
        return $this->_get_value('footer1');
    }

    /**
     * @deprecated
     * @return string
     */
    public function get_banner(): string
    {
        return $this->_get_value('headimg');
    }

    /**
     * @deprecated
     * @return string
     */
    public function get_qrcode(): string
    {
        return $this->_get_value('qrcode');
    }

    public function get_title(): string
    {
        return $this->_get_value('title');
    }

    public function set_footer(string $footer): void
    {
        $this->_set_value('footer1', $footer);
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