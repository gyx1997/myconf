<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/12
 * Time: 13:44
 */

class mScholarInfo extends CF_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'scholar_info';
    }

    /**
     * 从scholarInfo表中获取一个scholar的information
     * @param $email
     * @return array
     */
    public function get_scholar_info($email)
    {
        return $this->_fetch_first(array('scholar_email' => $email));
    }

    /**
     * 修改scholar信息
     * @param $email
     * @param $data
     */
    public function update_scholar_info($email, $data)
    {
        $this->db->where('scholar_email', $email);
        $this->db->update($this->_table(), $data);
    }

    /**
     * 添加一个scholar
     * @param $email
     * @param $first_name
     * @param $last_name
     * @param $address
     * @param $prefix
     * @param $institution
     * @param $department
     * @param string $chn_full_name
     * @return int
     */
    public function add_scholar_info($email, $first_name, $last_name, $address, $prefix, $institution, $department, $chn_full_name = '')
    {
        $this->db->insert(
            $this->_table(),
            array(
                'scholar_email' => $email,
                'scholar_first_name' => $first_name,
                'scholar_last_name' => $last_name,
                'scholar_address' => $address,
                'scholar_prefix' => $prefix,
                'scholar_institution' => $institution,
                'scholar_department' => $department,
                'scholar_chn_full_name' => $chn_full_name
            )
        );
        return $this->db->insert_id();
    }

    /**
     * @param string $email
     * @return bool
     */
    public function scholar_exists($email)
    {
        return $this->_exists(array('scholar_email' => $email));
    }
}