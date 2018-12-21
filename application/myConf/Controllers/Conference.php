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
        'scholar' => array(
            array(
                'method' => 'index',
                'action' => '',
            ),
            array(
                'method' => 'paper-submit',
                'action' => '',
            ),
            array(
                'method' => 'download',
                'action' => '',
            ),
            array(
                'method' => 'member',
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
            $this->_conference_data = $this->services()->Conference->init_load_conference($this->_conference_url);
            $this->_conference_id = $this->_conference_data['conference_id'];
            //处理权限信息
            $this->_check_privilege();
        } catch (\myConf\Exceptions\ConferenceNotFoundException $e) {
            throw new \myConf\Exceptions\HttpStatusException(404, 'CONF_NOT_FOUND', 'The conference you requested was not found, or have been deleted before.');
        }
    }

    /**
     * @throws \myConf\Exceptions\HttpStatusException
     * @throws \myConf\Exceptions\SendRedirectInstructionException
     */
    private function _check_privilege(): void
    {
        //将游客权限合并进基础权限
        $this->_privilege_table['scholar'] = array_merge($this->_privilege_table['guest'], $this->_privilege_table['scholar']);
        if ($this->_has_login() && $this->services()->Conference->user_joint_in($this->_user_id, $this->_conference_id)) {
            $roles = $this->services()->Conference->get_member_roles($this->_user_id, $this->_conference_id);
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
            $data = $this->services()->Conference->homepage($this->_conference_id, $category_id);
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
                        try {
                            $this->services()->Conference->update_conference_info($this->_conference_id, $this->input->post('conference_name_text'), $this->input->post('conference_host_text'), $this->input->post('conference_date_text'), 'banner_image', 'qr_code');
                            $this->add_output_variables(array('status' => 'SUCCESS'), OUTPUT_VAR_JSON_ONLY);
                        } catch (\myConf\Exceptions\UpdateConferenceException $e) {
                            $this->add_output_variables(array('status' => 'ERROR', 'err_flag' => $e->getErrorFlags()), OUTPUT_VAR_JSON_ONLY);
                        }
                    } else {
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
                                    $this->services()->Conference->add_category($this->_conference_id, $this->input->post('category_name_text'), intval($this->input->post('category_type_id')));
                                    $this->add_output_variables(array('status' => 'SUCCESS'), OUTPUT_VAR_JSON_ONLY);
                                } catch (\myConf\Exceptions\ConferenceNotFoundException $e) {
                                    throw new HttpStatusException(404, 'CONF_NOT_FOUND', $e->getMessage());
                                }
                                break;
                            }
                        case 'rename':
                            {
                                try {
                                    $this->services()->Conference->rename_category($this->_conference_id, intval($this->input->post('category_id')), $this->input->post('category_name_text'));
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
                                    $this->services()->Conference->delete_category($this->_conference_id, $category_id);
                                } catch (\myConf\Exceptions\CategoryNotFoundException $e) {
                                    throw new HttpStatusException(404, 'CAT_NOT_FOUND', 'The category you want to delete does not exists. It may have been deleted by administrator before you do this action.');
                                }
                                $this->_redirect_to('/conference/' . $this->_conference_url . '/management/category/');
                                return;
                            }
                        case 'up':
                            {
                                try {
                                    $this->services()->Conference->move_up_category($this->_conference_id, intval($this->input->get('cid')));
                                    $this->_redirect_to('/conference/' . $this->_conference_url . '/management/category/');
                                } catch (\myConf\Exceptions\CategoryNotFoundException $e) {
                                    throw new HttpStatusException(404, 'CAT_NOT_FOUND', 'The category you want to move down does not exists. It may have been deleted by administrator before you do this action.');
                                }
                                break;
                            }
                        case 'down':
                            {
                                try {
                                    $this->services()->Conference->move_down_category($this->_conference_id, intval($this->input->get('cid')));
                                    $this->_redirect_to('/conference/' . $this->_conference_url . '/management/category/');
                                } catch (\myConf\Exceptions\CategoryNotFoundException $e) {
                                    throw new HttpStatusException(404, 'CAT_NOT_FOUND', 'The category you want to move down does not exists. It may have been deleted by administrator before you do this action.');
                                }
                                break;
                            }
                        default:
                            {
                                $cat_data = $this->services()->Conference->get_category_list($this->_conference_id);
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
                                //处理输入
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
                                $members = $this->services()->ConferenceMember->get_conference_members($this->_conference_id, $user_role_restrict, $user_name_restrict, $user_email_restrict);
                                $this->add_output_variables(array('status' => 'SUCCESS', 'data' => $members, 'count' => count($members)), OUTPUT_VAR_JSON_ONLY);
                                break;
                            }
                        case 'toggleRole':
                            {
                                $user_id = $this->input->get('uid');
                                $role = $this->input->get('role');
                                if (array_key_exists($role, $this->_privilege_table)) {
                                    if ($this->mConfMember->member_is_role($user_id, $this->_conference_id, $role)) {
                                        $this->mConfMember->delete_role_from_member($user_id, $this->_conference_id, $role);
                                    } else {
                                        $this->mConfMember->add_role_to_member($user_id, $this->_conference_id, $role);
                                    }
                                }
                                $this->add_output_variables(array('status' => 'SUCCESS'), OUTPUT_VAR_JSON_ONLY);
                                break;
                            }
                        case 'remove':
                            {
                                $user_id = $this->input->get('uid');
                                $this->services()->ConferenceMember;
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
                                $old_attachments = $this->mAttachment->get_used_attachments('document', $document_id);
                                $old_attachment_ids = array();
                                foreach ($old_attachments as $old_attachment) {
                                    $old_attachment_ids [$old_attachment['attachment_id']] = TRUE;
                                }
                                //以下载的特征url进行正则匹配
                                $regex_a = "/\"\/attachment\/get\/.*?\/\?aid=.*?\"/";
                                $array_links = array();
                                if (preg_match_all($regex_a, $document_html, $array_links)) {
                                    //将本次新加入的附件标记位使用的附件
                                    foreach ($array_links[0] as $link) {
                                        $link = substr($link, 0, strlen($link) - 1);
                                        $aid = intval(substr($link, strpos($link, '?aid=') + 5));
                                        if (!isset($old_attachment_ids[$aid])) {
                                            $this->mAttachment->set_attachment_used($aid);
                                        }
                                        $old_attachment_ids[$aid] = FALSE;
                                    }
                                    //标记为没有使用的附件
                                    foreach ($old_attachment_ids as $old_attachment_id => $is_unused) {
                                        if ($is_unused) {
                                            $this->mAttachment->set_attachment_used($old_attachment_id, FALSE);
                                        }
                                    }
                                }
                                //修改文章信息
                                $this->mDocument->modify_document(
                                    $document_id,
                                    $this->input->post('document_title'),
                                    str_replace(
                                        'href=',
                                        'target="_blank" href=',
                                        str_replace('target="_blank"',
                                            '',
                                            $this->input->post('document_html')
                                        )
                                    )
                                );
                                header('location:/conference/' . $this->_conference_url . '/management/category/');
                                break;
                            }
                        case 'get':
                            {
                                $document_id = intval($this->input->get('id'));
                                if (empty($document_id) || $document_id == NULL || $document_id == 0) {
                                    echo '';
                                }
                                $document = $this->mDocument->get_document($document_id);
                                if (empty($document)) {
                                    echo '';
                                }
                                echo $document['document_html'];
                                break;
                            }
                        case 'edit':
                            {
                                $document_id = intval($this->input->get('id'));
                                $this->_render(
                                    'conference/management/edit_document',
                                    'Edit Document',
                                    array(
                                        'document_id' => $document_id
                                    )
                                );
                                break;
                            }
                        default:
                            {
                                show_404();
                            }
                    }
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