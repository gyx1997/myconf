<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/21
 * Time: 18:54
 */

/**
 * Class CF_Model
 */
class CF_Model extends CI_Model
{
    protected $_table_name = '';
    protected $_table_prefix = 'myconf';
    protected $_pk = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function begin_transaction()
    {
        $this->db->trans_start();
    }

    public function end_transaction()
    {
        $this->db->trans_complete();
    }

    /**
     * @param $where_segment_array
     * @return bool
     */
    protected function _exists($where_segment_array)
    {
        foreach ($where_segment_array as $field => $value) {
            $this->db->where($field, $value);
        }
        $this->db->select('COUNT(*)');
        $query = $this->db->get($this->_table());
        $result = $query->row_array();
        return intval($result['COUNT(*)']) === 1;
    }

    /**
     * @return string
     */
    protected function _table()
    {
        return $this->_table_prefix . '_' . $this->_table_name;
    }

    /**
     * @param array $where_segment_array
     * @param string $order_field
     * @param string $order_direction
     * @param int $start
     * @param int $limit
     * @return array
     */
    protected function _fetch_all($where_segment_array = array(), $order_field = '', $order_direction = '', $start = 0, $limit = 0)
    {
        $this->_pack_query_args(
            $where_segment_array,
            $order_field,
            $order_direction,
            $start,
            $limit
        );
        $query_result = $this->db->get($this->_table());
        $result = $query_result->result_array();
        return empty($result) ? array() : $result;
    }

    /**
     * @param array $where_segment_array
     * @param string $order_field
     * @param string $order_direction
     * @param int $start
     * @param int $limit
     */
    private function _pack_query_args($where_segment_array = array(), $order_field = '', $order_direction = '', $start = 0, $limit = 0)
    {
        foreach ($where_segment_array as $field => $value) {
            $this->db->where($field, $value);
        }
        $order_field !== '' && $order_direction !== '' && $this->db->order_by($order_field, $order_direction);
        $limit !== 0 && $this->db->limit($limit, $start);
    }

    /**
     * @param array $where_segment_array
     * @param string $order_field
     * @param string $order_direction
     * @return array
     */
    protected function _fetch_first($where_segment_array = array(), $order_field = '', $order_direction = '')
    {
        $this->_pack_query_args(
            $where_segment_array,
            $order_field,
            $order_direction,
            0,
            1
        );
        $query_result = $this->db->get($this->_table());
        $result = $query_result->row_array();
        return empty($result) ? array() : $result;
    }

    /**
     * @param $query_str
     * @param array $parameters
     * @return array
     */
    protected function _fetch_all_raw($query_str, $parameters = array())
    {
        $qr = $this->db->query($query_str, $parameters);
        $result = $qr->result_array();
        return empty($result) ? array() : $result;
    }

    /**
     * @param $query_str
     * @param array $parameters
     * @return array
     */
    protected function _fetch_first_raw($query_str, $parameters = array())
    {
        $qr = $this->db->query($query_str, $parameters);
        $result = $qr->row_array();
        return empty($result) ? array() : $result;
    }

    /**
     * @param int $pk_val
     * @return array
     * @throws DbNotFoundException
     */
    public function get(int $pk_val): array
    {
        $this->db->where($this->_pk, $pk_val);
        $query_result = $this->db->get($this->_table(), 1);
        if (empty($query_result->row_array())) {
            throw new DbNotFoundException(sprintf('No record found in table %s with primary key %s valued %d', $this->_table(), $this->_pk, $pk_val), 1001);
        }
        return $query_result->row_array();
    }
}

/**
 * Class CF_Service
 */
class CF_Service extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
}