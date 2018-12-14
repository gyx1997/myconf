<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/11/30
 * Time: 14:09
 */

class mPaper extends CF_Model
{
    private $num_per_page = 10;

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'papers';
    }

    /**
     * @param $conference_id
     * @param $user_id
     * @param array $data
     * @param bool $is_draft
     * @return int
     */
    public function add_paper($conference_id, $user_id, $data = array(), $is_draft = FALSE)
    {
        $this->db->insert(
            $this->_table(),
            array(
                'paper_conference_id' => $conference_id,
                'paper_user_id' => $user_id,
                'paper_pdf' => $data['paper_pdf'],
                'paper_copyright' => $data['paper_copyright'],
                'paper_title' => $data['paper_title'],
                'paper_abstract' => $data['paper_abstract'],
                'paper_type' => $data['paper_type'],
                'paper_submit_time' => $data['paper_submit_time'],
                'paper_status' => $is_draft ? -1 : 0                     //默认上传的paper是待审核的，不是草稿。
            )
        );
        return $this->db->insert_id();
    }

    /**
     * 修改论文的信息
     * @param $paper_id
     * @param $data
     */
    public function update_paper($paper_id, $data)
    {
        $this->db->where('paper_id', $paper_id);
        $arguments = array();
        isset($data['paper_title']) && $arguments['paper_title'] = $data['paper_title'];
        isset($data['paper_pdf']) && $arguments['paper_pdf'] = $data['paper_pdf'];
        isset($data['paper_abstract']) && $arguments['paper_abstract'] = $data['paper_abstract'];
        isset($data['paper_type']) && $arguments['paper_type'] = $data['paper_type'];
        !empty($arguments) && $this->db->update($this->_table(), $arguments);
    }

    /**
     * 修改论文的评审状态
     * @param $paper_id
     * @param $paper_status
     */
    public function review_paper($paper_id, $paper_status)
    {
        $this->db->where('paper_id', $paper_id);
        $this->db->update($this->_table(), array('paper_status' => $paper_status));
    }

    /**
     * @param $conference_id
     * @param int $page
     * @return array
     */
    public function get_conference_papers($conference_id, $page = 1)
    {
        return $this->_fetch_all(
            array('paper_conference_id' => $conference_id),
            '',
            '',
            ($page - 1) * $this->num_per_page,
            $this->num_per_page
        );
    }

    /**
     * @param $conference_id
     * @param $user_id
     * @param int $page
     * @return array
     */
    public function get_conference_user_papers($conference_id, $user_id, $page = 1)
    {
        return $this->_fetch_all(
            array(
                'paper_conference_id' => $conference_id,
                'paper_user_id' => $user_id
            ),
            '',
            '',
            ($page - 1) * $this->num_per_page,
            $this->num_per_page
        );
    }
}