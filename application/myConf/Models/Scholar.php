<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/15
 * Time: 17:32
 */

namespace myConf\Models;


class Scholar extends \myConf\BaseModel
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'scholars';
    }

    /**
     * 获取某一个Scholar的信息
     * @param $email
     * @return array
     */
    public function get_scholar_info(string $email): array
    {
        return $this->_fetch_first(array('scholar_email' => $email));
    }

    /**
     * 修改scholar信息
     * @param $email
     * @param $data
     * @deprecated
     */
    public function update_scholar_info(string $email, array $data): void
    {
        $this->db->where('scholar_email', $email);
        $this->db->update($this->_table(), $data);
    }

    /**
     * 更新scholar信息
     * @param string $email
     * @param string $first_name
     * @param string $last_name
     * @param string $institution
     * @param string $department
     * @param string $address
     * @param string $prefix
     * @param string $chn_full_name
     */
    public function update_scholar(string $email, string $first_name, string $last_name, string $institution, string $department, string $address, string $prefix = '', string $chn_full_name = ''): void
    {
        $data_to_update = array(
            'scholar_first_name' => $first_name,
            'scholar_last_name' => $last_name,
            'scholar_institution' => $institution,
            'scholar_department' => $department,
            'scholar_address' => $address,
            'scholar_prefix' => $prefix,
            'scholar_chn_full_name' => $chn_full_name
        );
        $this->db->where('scholar_email', $email);
        $this->db->update($this->_table(), $data_to_update);
        return;
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
    public function add_scholar_info(string $email, string $first_name, string $last_name, string $address, string $prefix, string $institution, string $department, string $chn_full_name = ''): int
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
     * 判断scholar是否存在
     * @param string $email
     * @return bool
     */
    public function scholar_exists(string $email): bool
    {
        return $this->_exists(array('scholar_email' => $email));
    }
}