<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/15
 * Time: 16:01
 */

namespace myConf\Controllers;

use myConf\Exceptions\HttpStatusException;

class Conference extends \myConf\BaseController
{

    /**
     * @var array 权限表
     */

    // TODO 使用数据库构造完整的 Role-Based Access Control 权限系统
    private $_privilege_table = array(
        'guest' => array(
            array(
                'method' => 'index',
                'action' => '',
            ),
            array(
                'method' => 'download',
                'action' => '',
            ),
        ),
        'user' => [
            [
                'method' => 'paper-submit',
                'action' => '',
            ],
            [
                'method' => 'member',
                'action' => '',
            ],
        ],
        'scholar' => array(
            array(
                'method' => 'index',
                'action' => '',
            ),
            array(
                'method' => 'download',
                'action' => '',
            ),
        ),
        'reviewer' => array(
            array(
                'method' => 'paper-review',
                'action' => 'review',
            ),
            array(
                'method' => 'paper-review',
                'action' => '',
            ),
        ),
        'editor' => array(
            array(
                'method' => 'paper-review',
                'action' => 'arrange',
            ),
            array(
                'method' => 'paper-review',
                'action' => '',
            ),
        ),
        'admin' => array(
            array(
                'method' => 'management',
                'action' => ''
            )
        ),
    );

    /**
     * @var array 当前会议信息
     */
    private $_conference_data = array();
    /**
     * @var mixed|string 当前会议的二级URL
     */
    private $_conference_url = '';
    /**
     * @var int|mixed 当前会议的ID号
     */
    private $_conference_id = 0;
    /**
     * @var bool 是否有权限进行会议管理操作
     */
    private $_auth_conf_manage = FALSE;
    /**
     * @var bool 是否具有分配文章审核的权限
     */
    private $_auth_conf_arrange_review = FALSE;
    /**
     * @var bool 是否具有审核文章的权限
     */
    private $_auth_conf_review_paper = FALSE;
    /**
     * @var bool 是否为会议创始人
     */
    private $_auth_conf_creator = FALSE;

    /**
     * Conference constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        try {
            //处理会议信息
            $this->_conference_url = $this->uri->segment(2, '');
            $this->_conference_data = $this->Services->Conference->init_load_conference($this->_conference_url);
            $this->_conference_id = $this->_conference_data['conference_id'];
            //处理权限信息
            $this->_check_privilege();
        } catch (\myConf\Exceptions\ConferenceNotFoundException $e) {
            throw new \myConf\Exceptions\HttpStatusException(404, 'CONF_NOT_FOUND', 'The conference you requested was not found, or have been deleted before.');
        }
    }

    /**
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbCompositeKeysException
     * @throws \myConf\Exceptions\HttpStatusException
     * @throws \myConf\Exceptions\SendRedirectInstructionException
     */
    private function _check_privilege(): void
    {
        //将游客、未加入会议的注册用户的角色权限合并进基础权限
        $this->_privilege_table['user'] = array_merge($this->_privilege_table['user'], $this->_privilege_table['guest']);
        $this->_privilege_table['scholar'] = array_merge($this->_privilege_table['scholar'], $this->_privilege_table['user']);
        if ($this->_has_login()) {
            if ($this->Services->Conference->user_joint_in($this->_user_id, $this->_conference_id)) {
                $roles = $this->Services->Conference->get_member_roles($this->_user_id, $this->_conference_id);
            } else {
                $roles = ['user'];
            }
        } else {
            $roles = array('guest');
        }
        //view的显示权限变量
        $this->_auth_conf_review_paper = in_array('reviewer', $roles);
        $this->_auth_conf_arrange_review = in_array('editor', $roles);
        $this->_auth_conf_creator = in_array('creator', $roles);
        $this->_auth_conf_manage = $this->_auth_conf_creator || in_array('admin', $roles);
        //检查是否符合权限
        //创始人拥有所有权限
        if ($this->_auth_conf_creator) {
            return;
        }
        //否则逐个检查权限
        foreach ($roles as $role) {
            foreach ($this->_privilege_table[$role] as $privilege) {
                if (isset($privilege['method']) && isset($privilege['action'])) {
                    if ($privilege['method'] == $this->_method && ($privilege['action'] == '' || $privilege['action'] == $this->_action)) {
                        return;
                    }
                }
            }
        }
        $this->_login_redirect();
        throw new \myConf\Exceptions\HttpStatusException(403, 'ACCESS_DENIED', 'You do not have the permission to do this action');
    }

    /**
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\HttpStatusException
     */
    public function index(): void
    {
        //处理输入
        $category_id = $this->input->get('cid');
        $category_id = $category_id == '' ? 0 : intval($category_id);
        //进入业务逻辑
        try {
            $data = $this->Services->Conference->homepage($this->_conference_id, $category_id);
            $this->add_output_variables(
                array(
                    'conference' => $this->_conference_data,
                    'category_list' => $data['category_list'],
                    'active_category_id' => $category_id,
                    'active_category_document_id' => $data['document']['document_id'],
                    'active_document_content' => $data['document']['document_html'],
                )
            );
        } catch (\myConf\Exceptions\CategoryNotFoundException $e) {
            throw new \myConf\Exceptions\HttpStatusException(404, 'CAT_NOT_FOUND', 'The page you requested was not found. It might have been deleted, or the page identifier you provided is wrong.');
        }
    }


    public function management()
    {
        switch ($this->_action) {
            case 'default':
                {
                    $this->_redirect_to('/conference/' . $this->_conference_url . '/management/overview/');
                    break;
                }
            case 'overview':
                {
                    if ($this->_do == 'submit') {
                        //修改会议信息提交
                        try {
                            $this->Services->Conference->update_conference($this->_conference_id, $this->input->post('conference_name_text'), $this->input->post('conference_host_text'), $this->input->post('conference_date_text'), true, $this->input->post('conference_paper_submit_end'), 'banner_image', 'qr_code');
                            $this->add_output_variables(array('status' => 'SUCCESS'), OUTPUT_VAR_JSON_ONLY);
                        } catch (\myConf\Exceptions\UpdateConferenceException $e) {
                            $this->add_output_variables(array('status' => 'ERROR', 'err_flag' => $e->getErrorFlags()), OUTPUT_VAR_JSON_ONLY);
                        }
                    } else {
                        //返回普通页面
                        $this->add_output_variables(array('conference' => $this->_conference_data), OUTPUT_VAR_HTML_ONLY);
                    }
                    break;
                }
            case 'category':
                {
                    switch ($this->_do) {
                        case'add':
                            {
                                try {
                                    $this->Services->Conference->add_category($this->_conference_id, $this->input->post('category_name_text'), intval($this->input->post('category_type_id')));
                                    $this->add_output_variables(array('status' => 'SUCCESS'), OUTPUT_VAR_JSON_ONLY);
                                } catch (\myConf\Exceptions\ConferenceNotFoundException $e) {
                                    throw new HttpStatusException(404, 'CONF_NOT_FOUND', $e->getMessage());
                                }
                                break;
                            }
                        case 'rename':
                            {
                                try {
                                    $this->Services->Conference->rename_category($this->_conference_id, intval($this->input->post('category_id')), $this->input->post('category_name_text'));
                                } catch (\myConf\Exceptions\CategoryNotFoundException $e) {
                                    throw new HttpStatusException(404, 'CAT_NOT_FOUND', 'The category you want to delete does not exists. It may have been deleted by administrator before you do this action.');
                                }
                                $this->_redirect_to('/conference/' . $this->_conference_url . '/management/category/');
                                break;
                            }
                        case 'remove':
                            {
                                $category_id = intval($this->input->get('cid'));
                                try {
                                    $this->Services->Conference->delete_category($this->_conference_id, $category_id);
                                } catch (\myConf\Exceptions\CategoryNotFoundException $e) {
                                    throw new HttpStatusException(404, 'CAT_NOT_FOUND', 'The category you want to delete does not exists. It may have been deleted by administrator before you do this action.');
                                }
                                $this->_redirect_to('/conference/' . $this->_conference_url . '/management/category/');
                                return;
                            }
                        case 'up':
                            {
                                try {
                                    $this->Services->Conference->move_up_category($this->_conference_id, intval($this->input->get('cid')));
                                    $this->_redirect_to('/conference/' . $this->_conference_url . '/management/category/');
                                } catch (\myConf\Exceptions\CategoryNotFoundException $e) {
                                    throw new HttpStatusException(404, 'CAT_NOT_FOUND', 'The category you want to move down does not exists. It may have been deleted by administrator before you do this action.');
                                }
                                break;
                            }
                        case 'down':
                            {
                                try {
                                    $this->Services->Conference->move_down_category($this->_conference_id, intval($this->input->get('cid')));
                                    $this->_redirect_to('/conference/' . $this->_conference_url . '/management/category/');
                                } catch (\myConf\Exceptions\CategoryNotFoundException $e) {
                                    throw new HttpStatusException(404, 'CAT_NOT_FOUND', 'The category you want to move down does not exists. It may have been deleted by administrator before you do this action.');
                                }
                                break;
                            }
                        default:
                            {
                                $cat_data = $this->Services->Conference->get_category_list($this->_conference_id);
                                $this->add_output_variables(array('category_list' => $cat_data));
                            }
                    }
                    break;
                }
            case 'participant':
                {
                    switch ($this->_do) {
                        case 'getAll':
                            {
                                $page = $this->input->get('page');
                                $page == '' ? $page = 0 : $page = intval($page) - 1;
                                $user_name_restrict = $this->input->get('username');
                                $user_email_restrict = $this->input->get('email');
                                $user_role_restrict = array();
                                foreach ($this->_privilege_table as $key => $value) {
                                    if ($this->input->get($key) === 'yes') {
                                        $user_role_restrict [] = $key;
                                    }
                                }
                                $members = $this->Services->Conference->get_members($this->_conference_id, $user_role_restrict, $user_name_restrict, $user_email_restrict);
                                $this->add_output_variables([
                                    'status' => 'SUCCESS',
                                    'data' => $members,
                                    'count' => count($members),
                                ], OUTPUT_VAR_JSON_ONLY);
                                break;
                            }
                        case 'toggleRole':
                            {
                                $user_id = $this->input->get('uid');
                                $role = $this->input->get('role');
                                if (array_key_exists($role, $this->_privilege_table)) {
                                    if ($this->Services->Conference->member_is_role($this->_conference_id, $user_id, $role)) {
                                        $this->Services->Conference->member_remove_role($this->_conference_id, $user_id, $role);
                                    } else {
                                        $this->Services->Conference->member_add_role($this->_conference_id, $user_id, $role);
                                    }
                                }
                                $this->add_output_variables(array('status' => 'SUCCESS'), OUTPUT_VAR_JSON_ONLY);
                                break;
                            }
                        case 'remove':
                            {
                                $user_id = $this->input->get('uid');
                                $this->Services->Conference->remove_member($this->_conference_id, $user_id);
                                $this->add_output_variables(array('status' => 'SUCCESS'), OUTPUT_VAR_JSON_ONLY);
                            }
                        case '':
                            {
                                //显示页面
                                break;
                            }
                        default:
                            {
                                throw new HttpStatusException(400, 'UNKNOWN_DO_PARAM', 'The request parameters are invalid.');
                            }
                    }

                    break;
                }
            case 'document':
                {
                    switch ($this->_do) {
                        case 'submit':
                            {
                                //TODO 添加判断DOCUMENT_ID是否存在
                                $document_id = intval($this->input->post('document_id'));
                                //检查附件表是否有多余的附件、没有添加的附件，并进行相关操作
                                $document_html = $this->input->post('document_html');
                                $this->Services->Document->submit_content($document_id, '', $document_html);
                                $this->add_output_variables(['status' => 'SUCCESS']);
                                usleep(500000);
                                break;
                            }
                        case 'get':
                            {
                                $document_id = intval($this->input->get('id'));
                                $data = ['status' => 'DOC_NOT_FOUND'];
                                if ($document_id !== 0) {
                                    try {
                                        $document = $this->Services->Document->get_content($document_id);
                                        $data['status'] = 'SUCCESS';
                                        $data['doc_html'] = $document['document_html'];
                                        $data['doc_title'] = $document['title'];
                                    } catch (\myConf\Exceptions\DocumentNotExistsException $e) {
                                        //do nothing.
                                    }
                                }
                                $this->add_output_variables($data);
                                break;
                            }
                        case 'edit':
                            {
                                $document_id = intval($this->input->get('id'));
                                $this->add_output_variables(['document_id' => $document_id]);
                                break;
                            }
                        default:
                            {
                                throw new HttpStatusException(400, 'UNKNOWN_DO_PARAM', 'The request parameters are invalid.');
                            }
                    }
                    break;
                }
        }
    }

    public function paper_submit() {
        switch ($this->_action) {
            case 'new':
                {
                    if ($this->_do == 'submit') {
                        //处理作者
                        $authors = json_decode($this->input->post('authors'), true);
                        if (!isset($authors) || empty($authors)) {
                            $this->exit_promptly(['status' => 'AUTHOR_EMPTY']);
                        }
                        //如果作者在scholar表中不存在，则添加进去
                        foreach ($authors as &$author) {
                            !isset($author['chn_full_name']) && $author['chn_full_name'] = '';  //临时fix，暂时不用中文名这个字段
                            if ($this->Services->Scholar->exist_by_email($author['email']) === false) {
                                $this->Services->Scholar->add_new($author['email'], $author['first_name'], $author['last_name'], $author['chn_full_name'], $author['address'], $author['prefix'], $author['institution'], $author['department']);
                            }
                        }
                        //提交文章
                        try {
                            $this->Services->Paper->submit_new($this->_user_id, $this->_conference_id, $this->input->post('paper_title_text'), $this->input->post('paper_abstract_text'), $authors, 'paper_pdf', 'paper_copyright_pdf', $this->input->post('paper_type_text'), $this->input->post('paper_suggested_session'));
                            $this->add_output_variables(array('status' => 'SUCCESS'));
                        } catch (\myConf\Exceptions\FileUploadException $e) {
                            $this->add_output_variables(['status' => 'FILE_ERROR']);
                        }

                    } else if ($this->_do === 'save') {

                    }
                    break;
                }
            case 'edit':
                {
                    $id = intval($this->input->get('id'));
                    if ($this->_do === 'submit') {

                        //处理作者
                        $authors = json_decode($this->input->post('authors'), true);
                        if (!isset($authors) || empty($authors)) {
                            $this->exit_promptly(['status' => 'AUTHOR_EMPTY']);
                        }
                        //如果作者在scholar表中不存在，则添加进去
                        foreach ($authors as &$author) {
                            !isset($author['chn_full_name']) && $author['chn_full_name'] = '';  //临时fix，暂时不用中文名这个字段
                            if ($this->Services->Scholar->exist_by_email($author['email']) === false) {
                                $this->Services->Scholar->add_new($author['email'], $author['first_name'], $author['last_name'], $author['chn_full_name'], $author['address'], $author['prefix'], $author['institution'], $author['department']);
                            }
                        }
                        try {
                            $this->Services->Paper->update_paper($id, 'paper_pdf', 'paper_copyright_pdf', $authors, $this->input->post('paper_type_text'), $this->input->post('paper_title_text'), $this->input->post('paper_abstract_text'), $this->input->post('paper_suggested_session'));
                        } catch (\myConf\Exceptions\FileUploadException $e) {
                            $this->add_output_variables(['status' => 'FILE_ERROR']);
                        }
                    } else {
                        $paper = $this->Services->Paper->get_paper($id);
                        $this->add_output_variables(['paper' => $paper]);
                    }
                    break;
                }
            case 'author':
                {
                    $data = $this->Services->Scholar->get_by_email(base64_decode($this->input->get('email')));
                    if ($this->_do == 'get') {
                        $this->add_output_variables([
                            'status' => 'SUCCESS',
                            'found' => !empty($data),
                            'data' => $data,
                        ]);
                    }
                    break;
                }
            case 'default':
                {
                    $joint = $this->Services->Conference->user_joint_in($this->_user_id, $this->_conference_id);
                    if ($joint === true) {
                        $papers = $this->Services->Paper->get_user_conference_papers($this->_user_id, $this->_conference_id);
                    }
                    $this->add_output_variables([
                        'has_joint' => $joint,
                        'papers' => isset($papers) ? $papers : [],
                    ]);
                    break;
                }
            default:
                {
                    throw new HttpStatusException(400, 'UNKNOWN_DO_PARAM', 'The request parameters are invalid.');
                }

        }
    }

    /**
     * @throws \myConf\Exceptions\SendExitInstructionException
     * @throws \myConf\Exceptions\SendRedirectInstructionException
     */
    public function member() {
        switch ($this->_do) {
            case 'register':
                {
                    if ($this->Services->Conference->user_joint_in($this->_user_id, $this->_conference_id)) {
                        $this->exit_promptly(array('status' => 'ALREADY_JOIN'));
                    }
                    $this->Services->Conference->add_member($this->_conference_id, $this->_user_id);
                    $this->_self_redirect();
                    break;
                }
        }
    }

    /**
     * 汇总conference 控制器下的html模板公共数据
     */
    protected function _collect_output_variables(): void
    {
        parent::_collect_output_variables();
        $this->add_output_variables(array(
            'tab_page' => $this->_method,
            'auth_management' => $this->_auth_conf_manage,
            'auth_review' => $this->_auth_conf_review_paper,
            'auth_arrange_review' => $this->_auth_conf_arrange_review,
            'conference' => $this->_conference_data
        ), OUTPUT_VAR_HTML_ONLY);
    }
}