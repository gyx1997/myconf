<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/11/11
 * Time: 10:15
 */

class mConference extends CF_Model
{

    public $conference_status_mapping = array(
        'moderated' => 0,
        'normal' => 1
    );

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'conferences';
    }

    public function has_conference_by_url($url)
    {
        return $this->_exists(array('conference_url' => $url));
    }

    public function has_conference_by_id($conference_id)
    {
        return $this->_exists(array('conference_id' => $conference_id));
    }

    public function get_conference_creator($conference_id)
    {
        return $this->get_conference_by_id($conference_id)['conference_creator'];
    }

    /**
     * @param $conference_id
     * @return array
     */
    public function get_conference_by_id($conference_id)
    {
        $result_array = $this->_fetch_first(array('conference_id' => $conference_id));
        if (empty($result_array)) {
            return array();
        }
        return $this->_pack_conference_data($result_array);
    }

    private function _pack_conference_data($original_data)
    {
        $result = $original_data;
        $extra_unserialized = unserialize($original_data['conference_extra']);
        $result['conference_extra'] = NULL;

        $result['banner_image'] = isset($extra_unserialized['banner_image']) ? $extra_unserialized['banner_image'] : '';
        $result['host'] = isset($extra_unserialized['host']) ? $extra_unserialized['host'] : '';
        $result['qr_code'] = isset($extra_unserialized['qr_code']) ? $extra_unserialized['qr_code'] : '';
        return $result;
    }

    /**
     * @param int $conference_id
     * @param string $conference_name
     * @param string $conference_start_time
     * @param string $conference_banner
     * @param string $conference_qr_code
     * @param string $conference_host
     */
    public function update_conference($conference_id,
                                      $conference_name,
                                      $conference_start_time,
                                      $conference_banner = '',
                                      $conference_qr_code = '',
                                      $conference_host = '')
    {
        $this->db->where('conference_id', $conference_id);
        $this->db->update(
            $this->_table(),
            array(
                'conference_name' => $conference_name,
                'conference_start_time' => $conference_start_time,
                'conference_extra' => serialize(
                    array(
                        'banner_image' => $conference_banner,
                        'qr_code' => $conference_qr_code,
                        'host' => $conference_host
                    )
                )
            )
        );
    }

    public function get_conference_by_url($url)
    {
        $result_array = $this->_fetch_first(array('conference_url' => $url));
        if (empty($result_array)) {
            return array();
        }
        return $this->_pack_conference_data($result_array);
    }
}