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
    private $_privilege_table;

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
        //权限表
        $this->_privilege_table = [
            //游客的权限
            'guest' => [
                ['method' => 'index', 'actions' => ['default']],
            ],
            //普通注册用户的权限
            'user' => [
                ['method' => 'paper-submit', 'actions' => ['default']],
                ['method' => 'member', 'actions' => ['']],
            ],
            //注册进入会议的用户的权限
            'scholar' => array(
                ['method' => 'paper-submit', 'actions' => ['new', 'author']],
                //编辑和删除文章，需要满足是作者本人进行操作。
                ['method' => 'paper-submit', 'actions' => ['edit', 'delete', 'preview', 'revision'], 'special_check' => function() : bool {
                    $paper = $this->Services->Paper->get(intval($this->input->get('id')), intval($this->input->get('ver')));
                    if (empty($paper)) {
                        throw new HttpStatusException(404, 'PAPER_NOT_FOUND', 'The paper you requested was not found.');
                    }
                    return intval($paper['user_id']) === $this->_user_id;
                }],
            ),
            //paper审核人的权限
            'reviewer' => [
                ['method' => 'paper-review', 'actions' => ['show-review'], 'special_check' => function() : bool {
                    $paper_id = intval($this->input->get('id'));
                    $paper_ver = intval($this->input->get('ver'));
                    return $this->Services->PaperReview->reviewer_exists_in_paper($this->_current_user['user_email'], $paper_id, $paper_ver);
                }],
                ['method' => 'paper-review', 'actions' => ['reviewer-tasks', 'default'],],
            ],
            //编辑的权限
            'editor' => [
                ['method' => 'paper-review', 'actions' => ['']],
            ],
            //管理员的权限
            'admin' => [
                ['method' => 'management', 'actions' => ['']]
            ],
        ];
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
            $roles = ['guest'];
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
                if (isset($privilege['method']) && isset($privilege['actions'])) {
                    $check_func = isset($privilege['special_check']) ? $privilege['special_check'] : function() : bool {return true;};
                    if ($privilege['method'] === $this->_method) {
                        foreach($privilege['actions'] as $action) {
                            if ($action === '' || ($action === $this->_action && $check_func())) {
                                return;
                            }
                        }
                    }
                }
            }
        }
        $this->_login_redirect();
        throw new \myConf\Exceptions\HttpStatusException(403, 'ACCESS_DENIED', 'You do not have the permission to do this action.');
    }

    public function paper_review() : void {
        switch($this->_action) {
            case 'editor-list':
                //editor的操作（分配审稿、完成评审）
                {
                    switch($this->_do) {
                        case 'addReviewer':
                            {
                                $reviewer_email = base64_decode($this->input->get('email'));
                                $paper_id = intval($this->input->get('id'));
                                $paper_version = intval($this->input->get('ver'));
                                try {
                                    $found = $this->Services->Paper->add_reviewer($paper_id, $paper_version, $reviewer_email);
                                    $this->add_output_variables(['status' => 'SUCCESS', 'found' => $found === TRUE ? 'true' : 'false']);
                                } catch (\myConf\Exceptions\ReviewerAlreadyExistsException $e) {
                                    $this->add_output_variables(['status' => 'ALREADY_ADDED']);
                                }
                                break;
                            }
                        case 'endReview':
                            {
                                $paper_id = intval($this->input->get('id'));
                                $paper_ver = intval($this->input->get('ver'));
                                $result = $this->input->get('result');
                                $comment = '';//$this->input->get('review_comment');
                                $this->Services->PaperReview->editor_finish_review(
                                    $paper_id, $paper_ver, $result, $comment
                                );
                                $this->add_output_variables(['status' => 'SUCCESS']);
                                break;
                            }
                        default:
                            {
                                $papers = $this->Services->Paper->get_conference_papers($this->_conference_id);
                                $this->add_output_variables(['papers' => $papers]);
                                break;
                            }
                    }
                    break;
                }
            case 'reviewer-tasks':
                //reviewer的操作
                {
                    switch($this->_do) {
                        case 'enterReview':
                            {
                                $this->Services->PaperReview->reviewer_enter_review($this->_current_user['user_email'], intval($this->input->get('id')), intval($this->input->get('ver')));
                                $this->_redirect_to('/conference/' . $this->_conference_url . '/paper-review/reviewer-tasks/');
                                break;
                            }
                        default:
                            {
                                $this->add_output_variables(['papers' => $this->Services->Paper->get_user_review_tasks($this->_current_user['user_email'], $this->_conference_id)]);
                            }
                    }
                    break;
                }
            case 'show-review':
                {
                    switch($this->_do) {
                        //提交当前审稿人的评审结果
                        case 'save':
                        case 'submit':
                            {
                                $paper_id = intval($this->input->post('paper_id'));
                                $ver = intval($this->input->post('paper_version'));
                                $review_action = $this->input->post('review_action');
                                $comment = $this->input->post('review_comment');
                                if ($this->_do === 'save') {
                                    $this->Services->PaperReview->reviewer_save_review($this->_current_user['user_email'], $paper_id, $ver, $review_action, $comment);
                                } else {
                                    $this->Services->PaperReview->reviewer_submit_review($this->_current_user['user_email'], $paper_id, $ver, $review_action, $comment);
                                }
                                $this->add_output_variables(['status' => 'SUCCESS']);
                                break;
                            }
                        default:
                            //展示评审页面
                            {
                                $paper_id = intval($this->input->get('id'));
                                $ver = intval($this->input->get('ver'));
                                $reviewer_status =
                                    $this->Services->PaperReview->get_review_status($paper_id, $ver, $this->_current_user['user_email']);
                                $this->add_output_variables(['paper' => $this->Services->Paper->get
                                ($paper_id, $ver), 'review_status' => $reviewer_status]);
                            }
                    }
                    break;
                }
        }
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

    //
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
                                $document_id = intval($this->input->post('document_id'));
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
            case 'suggested-session':
                {
                    switch($this->_do){
                        case 'add':
                            {
                                try {
                                    $this->Services->Conference->add_new_session($this->_conference_id, $this->input->post('session_text'), intval($this->input->post('session_type')));
                                    $this->add_output_variables(array('status' => 'SUCCESS'), OUTPUT_VAR_JSON_ONLY);
                                } catch (\myConf\Exceptions\ConferenceNotFoundException $e) {
                                    throw new HttpStatusException(404, 'CONF_NOT_FOUND', 'The requested conference was not found.');
                                }
                                break;
                            }
                        case 'down':
                            {
                                $this->Services->Conference->move_down_session($this->input->get('id'));
                                $this->_redirect_to('/conference/' . $this->_conference_url . '/management/suggested-session/');
                                break;
                            }
                        case 'up':
                            {
                                $this->Services->Conference->move_up_session($this->input->get('id'));
                                $this->_redirect_to('/conference/' . $this->_conference_url . '/management/suggested-session/');
                                break;
                            }
                        case 'edit':
                            {
                                $sess_id = $this->input->post('session_id');
                                $sess_type = intval($this->input->post('session_type'));
                                $sess_text = $this->input->post('session_text');
                                $this->Services->Conference->edit_session($sess_id, $sess_type, $sess_text);
                                $this->_redirect_to('/conference/' . $this->_conference_url . '/management/suggested-session/');
                                break;
                            }
                        case 'delete':
                            {
                                try {
                                    $sess_id = intval($this->input->get('id'));
                                    $this->Services->Conference->delete_session($sess_id);
                                    $this->add_output_variables(['status' => 'SUCCESS']);
                                } catch (\myConf\Exceptions\PaperSessionAlreadyUsedException $e) {
                                    $this->add_output_variables(['status' => 'SESS_ALREADY_USED']);
                                }
                                break;
                            }
                        default:
                            {
                                $sessions = $this->Services->Conference->get_sessions($this->_conference_id);
                                $this->add_output_variables(['sessions' => $sessions]);
                            }
                    }
                    break;
                }
            case 'papers':
                {
                    switch($this->_do) {
                        default:
                            $papers = $this->Services->Paper->get_conference_papers($this->_conference_id);
                            $this->add_output_variables(['papers' => $papers]);
                    }
                    break;
                }
            default:
                {
                    throw new HttpStatusException(404, 'NOT_FOUND', 'The requested URL is not found on this server.');
                }
        }
    }

    private function paper_submit_get_authors(bool
                                              $do_submit =
                                              false) : array {
        //处理作者
        $authors = json_decode($this->input->post('authors'), true);
        if (!isset($authors) || empty($authors)) {
            if ($do_submit === true) {
                $this->exit_promptly(['status' => 'AUTHOR_EMPTY']);
            } else {
                $authors = [];
            }
        } else {
            //如果作者在scholar表中不存在，则添加进去
            foreach ($authors as &$author) {
                !isset($author['chn_full_name']) && $author['chn_full_name'] = '';  //临时fix，暂时不用中文名这个字段
                if ($this->Services->Scholar->exist_by_email($author['email']) === false) {
                    $this->Services->Scholar->add_new($author['email'], $author['first_name'], $author['last_name'], $author['chn_full_name'], $author['address'], $author['prefix'], $author['institution'], $author['department']);
                }
            }
        }
        return $authors;
    }

    private function paper_submit_check_date() : void {
        if (time() >= $this->_conference_data['conference_paper_submit_end'] + 24 * 3600) {
            $this->exit_promptly(['status' => 'OUT_OF_DATE']);
        }
    }

    public function paper_submit() {

        /**
         * 将session源数据转换为分类的数据供模板显示
         * @param array $sessions
         * @return array
         */
        function dispatch_sessions(array $sessions) : array {
            //获取所有的session
            $sessions_dispatched = [];
            foreach ($sessions as $session){
                $sessions_dispatched[intval($session['session_type'])] []= $session;
            }
            return $sessions_dispatched;
        }

        switch ($this->_action) {
            case 'new':
                {
                    if ($this->_do == 'submit' || $this->_do == 'save') {
                        //检查是否过期了
                        $this->paper_submit_check_date();
                        if ($this->_do == 'submit') {
                            //获取作者
                            $authors = $this->paper_submit_get_authors(true);
                            //直接submit文章
                            $this->Services->Paper->new_submit($this->_user_id, $this->_conference_id, $this->input->post('paper_title_text'), $this->input->post('paper_abstract_text'), $authors, intval($this->input->post('paper_pdf_aid')), intval($this->input->post('paper_copyright_aid')), $this->input->post('paper_type_text'), $this->input->post('paper_suggested_session'), $this->input->post('paper_suggested_session_custom'));
                        } else {
                            //获取作者
                            $authors = $this->paper_submit_get_authors(false);
                            //保存草稿
                            $this->Services->Paper->new_draft(
                                $this->_user_id, $this->_conference_id,
                                $this->input->post('paper_title_text'),
                                $this->input->post('paper_abstract_text'),
                                $authors,
                                intval($this->input->post('paper_pdf_aid')),
                                intval($this->input->post('paper_copyright_aid')),
                                $this->input->post('paper_type_text'),
                                $this->input->post('paper_suggested_session'),
                                $this->input->post('paper_suggested_session_custom')
                            );
                        }
                        $this->add_output_variables(array('status' => 'SUCCESS'));
                    } else {
                        //新建文章界面
                        $this->add_output_variables([
                            'sessions' => dispatch_sessions($this->Services->Conference->get_sessions($this->_conference_id))
                        ]);
                    }
                    break;
                }
            case 'edit':
                {
                    $id = intval($this->input->get('id'));
                    $version = intval($this->input->get('ver'));
                    $paper = $this->Services->Paper->get($id, $version);
                    if (empty($paper)) {
                        throw new HttpStatusException(404, 'PAPER_NOT_FOUND', 'The paper you want to edit or delete does not exist. It may have been deleted before.');
                    }
                    if ($this->_do === 'submit' || $this->_do === 'save') {
                        //编辑文章
                        //先处理作者
                        $authors = json_decode($this->input->post('authors'), true);

                        if (!isset($authors) || empty($authors)) {
                            if ($this->_do === 'submit') {
                                $this->exit_promptly(['status' => 'AUTHOR_EMPTY']);
                            } else {
                                $authors = [];
                            }
                        }
                        //如果作者在scholar表中不存在，则添加进去
                        foreach ($authors as &$author) {
                            !isset($author['chn_full_name']) && $author['chn_full_name'] = '';  //临时fix，暂时不用中文名这个字段
                            if ($this->Services->Scholar->exist_by_email($author['email']) === false) {
                                $this->Services->Scholar->add_new($author['email'], $author['first_name'], $author['last_name'], $author['chn_full_name'], $author['address'], $author['prefix'], $author['institution'], $author['department']);
                            }
                        }
                        if ($this->_do === 'submit') {
                            $this->Services->Paper->submit_paper(
                                $id,
                                $version,
                                intval($this->input->post('paper_pdf_aid')),
                                intval($this->input->post('paper_copyright_aid')),
                                $authors,
                                $this->input->post('paper_type_text'),
                                $this->input->post('paper_title_text'),
                                $this->input->post('paper_abstract_text'),
                                $this->input->post('paper_suggested_session'),
                                $this->input->post('paper_suggested_session_custom')
                            );
                        } else {
                            $this->Services->Paper->save_draft(
                                $id,
                                $version,
                                intval($this->input->post('paper_pdf_aid')),
                                intval($this->input->post('paper_copyright_aid')),
                                $authors,
                                $this->input->post('paper_type_text'),
                                $this->input->post('paper_title_text'),
                                $this->input->post('paper_abstract_text'),
                                $this->input->post('paper_suggested_session'),
                                $this->input->post('paper_suggested_session_custom')
                            );
                        }
                        $this->add_output_variables(['status' => 'SUCCESS']);
                    } else {
                        if (intval($paper['paper_status']) !== \myConf\Models\Paper::paper_status_saved) {
                            throw new HttpStatusException(403, 'PAPER_STATUS_CANNOT_EDIT', 'The paper cannot be edited since it has been submitted.');
                        }
                        $this->add_output_variables([
                            'paper' => $paper,
                            'sessions' => dispatch_sessions($this->Services->Conference->get_sessions($this->_conference_id)),
                        ]);
                    }
                    break;
                }
            case 'preview':
                {
                    $id = intval($this->input->get('id'));
                    $version = intval($this->input->get('ver'));
                    $paper = $this->Services->Paper->get($id, $version);
                    $this->add_output_variables([
                        'paper' => $paper,
                        'sessions' => dispatch_sessions(
                            $this->Services->Conference->get_sessions($this->_conference_id)
                        ),
                    ]);
                    break;
                }
            case 'delete':
                {
                    $id = intval($this->input->get('id'));
                    $version = intval($this->input->get('ver'));
                    $paper = $this->Services->Paper->get($id, $version);
                    if (empty($paper)) {
                        throw new HttpStatusException(404, 'PAPER_NOT_FOUND', 'The paper you want to edit or delete does not exist. It may have been deleted before.');
                    }
                    if (intval($paper['paper_status']) === \myConf\Models\Paper::paper_status_saved) {
                        $this->Services->Paper->delete_paper($id, $version);
                    }
                    $this->add_output_variables(['status' => 'SUCCESS']);
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
            case 'revision':
                {
                    $id = intval($this->input->get('id'));
                    $version = intval($this->input->get('ver'));
                    $paper = $this->Services->Paper->get($id, $version);
                    if (empty($paper)) {
                        throw new HttpStatusException(404, 'PAPER_NOT_FOUND', 'The paper you want to edit or delete does not exist. It may have been deleted before.');
                    }
                    if ($this->_do === 'submit' || $this->_do === 'save') {
                        //检查是否过期了
                        $this->paper_submit_check_date();
                        //再根据提交或者保存的选项选择操作
                        if ($this->_do == 'submit') {
                            //获取作者
                            $authors = $this->paper_submit_get_authors(true);
                            //直接submit文章
                            $this->Services->Paper->new_submit_version($id, $version, $this->_user_id, $this->_conference_id, $this->input->post('paper_title_text'), $this->input->post('paper_abstract_text'), $authors, intval($this->input->post('paper_pdf_aid')), intval($this->input->post('paper_copyright_aid')), $this->input->post('paper_type_text'), $this->input->post('paper_suggested_session'), $this->input->post('paper_suggested_session_custom'));
                        } else {
                            //获取作者
                            $authors = $this->paper_submit_get_authors(false);
                            //保存草稿
                            $this->Services->Paper->new_draft_version($id, $version,
                                $this->_user_id, $this->_conference_id,
                                $this->input->post('paper_title_text'),
                                $this->input->post('paper_abstract_text'),
                                $authors,
                                intval($this->input->post('paper_pdf_aid')),
                                intval($this->input->post('paper_copyright_aid')),
                                $this->input->post('paper_type_text'),
                                $this->input->post('paper_suggested_session'),
                                $this->input->post('paper_suggested_session_custom')
                            );
                        }
                        $this->add_output_variables(['status' => 'SUCCESS']);
                    } else {
                        if (intval($paper['paper_status']) !== \myConf\Models\Paper::paper_status_revision) {
                            throw new HttpStatusException(403, 'PAPER_STATUS_CANNOT_EDIT', 'Cannot submit the revision of current paper.');
                        }
                        $this->add_output_variables([
                            'paper' => $paper,
                            'sessions' => dispatch_sessions($this->Services->Conference->get_sessions($this->_conference_id)),
                        ]);
                    }
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
