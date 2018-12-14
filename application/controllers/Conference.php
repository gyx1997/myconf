<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Conference extends CF_Controller
{
    /**
     * @var array 权限表
     */

    // TODO 使用数据库构造完整的 Role-Based Access Control 权限系统
    private $_privilege_table = array(
        'guest' => array(
            array(
                'method' => '',
                'action' => '',
            ),
            array(
                'method' => 'download',
                'action' => '',
            ),
        ),
        'scholar' => array(
            array(
                'method' => ''
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
     * Conference constructor. 初始化的构造函数
     */
    public function __construct()
    {
        parent::__construct();
        //处理URL段
        $this->_conference_url = $this->uri->segment(2, '');
        $this->_method = $this->uri->segment(3, '');
        $this->_action = $this->uri->segment(4, '');
        //验证conference_url合法性
        if ($this->_conference_url == '' ||
            $this->mConference->has_conference_by_url($this->_conference_url) === FALSE) {
            if ($this->_ajax == TRUE) {
                $this->_exit_with_json(array('status' => 'CONF_NOT_FOUND'));
            }
            //TODO: 显示404页面，即未找到该会议。
            exit();
        }
        //处理会议信息
        $this->_conference_data = $this->mConference->get_conference_by_url($this->_conference_url);
        $this->_conference_id = $this->_conference_data['conference_id'];
        //处理权限信息
        $this->_check_privilege();
        $this->_auth_conf_manage =
            $this->_check_login() && (
                $this->mConfMember->user_joint_in_conference($this->_user_id, $this->_conference_id) &&
                $this->mConfMember->member_is_role($this->_user_id, $this->_conference_id, 'admin') ||
                $this->_current_user['user_role'] == 'admin'
            );
    }

    /**
     * @return bool
     */
    private function _check_privilege()
    {
        //将游客权限合并进基础权限
        $this->_privilege_table['scholar'] = array_merge($this->_privilege_table['guest'], $this->_privilege_table['scholar']);
        if ($this->_has_login() && $this->mConfMember->user_joint_in_conference($this->_user_id, $this->_conference_id)) {
            $roles = $this->mConfMember->get_member_roles($this->_user_id, $this->_conference_id);
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
            return TRUE;
        }
        //否则逐个检查权限
        foreach ($roles as $role) {
            foreach ($this->_privilege_table[$role] as $privilege) {
                if (isset($privilege['method']) && isset($privilege['action'])) {
                    if ($privilege['method'] == $this->_method && ($privilege['action'] == '' || $privilege['action'] == $this->_action)) {
                        return TRUE;
                    }
                }
            }
        }
        $this->_login_redirect();
        show_error('You do not have the permission to do this action.', 403);
        return FALSE;
    }

    /**
     * Conference Controller 入口函数。同时做权限控制。
     */
    public function index()
    {
        try {
            switch ($this->_method) {
                case 'paper-submit':
                    {
                        $this->_paper_submit();
                        break;
                    }
                case 'management':
                    {
                        $this->_management();
                        break;
                    }
                case 'member':
                    {
                        $this->_member();
                        break;
                    }
                case 'paper-review':
                    {
                        $this->_paper_review();
                        break;
                    }
                default:
                    {
                        $this->_default();
                    }
            }
        } catch (Exception $e) {
            show_error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 论文提交处理
     */
    private function _paper_submit()
    {
        switch ($this->_action) {
            case 'new':
                {
                    if ($this->_do == 'submit' || $this->_do == 'save') {
                        //处理作者
                        $authors = json_decode($this->input->post('authors'), TRUE);
                        foreach ($authors as $author) {
                            if ($this->mScholarInfo->scholar_exists($author['email']) === FALSE) {
                                $this->mScholarInfo->add_scholar_info(
                                    $author['email'],
                                    $author['first_name'],
                                    $author['last_name'],
                                    $author['address'],
                                    $author['prefix'],
                                    $author['institution'],
                                    $author['department']
                                );
                            }
                        }
                        //处理简单字段
                        $abstract = $this->input->get('abstract');
                        $title = $this->input->get('title');
                        $type = $this->input->get('type');
                        $suggested_session = $this->input->get('suggested_session');

                        //处理上传文件 Paper和Copyright
                        $f_param['upload_path'] = TMP_PATH;
                        $f_param['allowed_types'] = 'pdf';
                        $f_param['max_size'] = strval(1048576 * 5);
                        /*
                        $this->load->library('upload', $f_param);
                        if ($this->upload->do_upload('banner_image')) {
                            $file_data = $this->upload->data();
                            $new_image_name = $this->_conference_url . '_banner_' . strval(rand(1000,9999)) .  $image_data['file_ext'];
                            rename($file_data['full_path'], STATIC_IMG_DIR . $new_image_name);
                            $banner_image = $new_image_name;
                        }
                        */

                        $this->_exit_with_json(array('status' => 'SUCCESS'));
                    } else {
                        $this->_render('conference/paper_submit/paper_new.php', '', array());
                    }
                    break;
                }
            case 'edit':
                {
                    $this->_render('conference/paper_submit/paper_edit.php', '', array());
                    break;
                }
            case 'author':
                {
                    $data = $this->mScholarInfo->get_scholar_info(base64_decode($this->input->get('email')));
                    if ($this->_do == 'get') {
                        $this->_exit_with_json(
                            array(
                                'status' => 'SUCCESS',
                                'found' => !empty($data),
                                'data' => $data
                            )
                        );
                    }
                    break;
                }
            case '':
            default:
                {
                    $joint = $this->mConfMember->user_joint_in_conference($this->_user_id, $this->_conference_id);
                    $this->_render(
                        'conference/paper_submit/index',
                        'Paper Submission',
                        array(
                            'has_joint' => $joint
                        )
                    );
                }
        }
    }

    /**
     * 渲染页面函数。重写父类。
     * @param string $body_view
     * @param string $title
     * @param array $arguments
     */
    protected function _render($body_view, $title = '', $arguments = array())
    {
        parent::_render(
            $body_view,
            $title,
            array_merge(
                $arguments,
                array(
                    'tab_page' => $this->_method,
                    'auth_management' => $this->_auth_conf_manage,
                    'auth_review' => $this->_auth_conf_review_paper,
                    'auth_arrange_review' => $this->_auth_conf_arrange_review,
                    'conference' => $this->_conference_data
                )
            )
        );
    }

    private function _management()
    {
        switch ($this->_action) {
            case '':
                {
                    header('location:/conference/' . $this->_conference_url . '/management/overview/');
                    break;
                }
            case 'overview':
                {
                    if ($this->_do == 'submit') {
                        if (!$this->mConference->has_conference_by_id($this->input->post('conference_id'))) {
                            $this->_exit_with_json(array('status' => 'ACCESS_DENIED'));
                        }
                        $date_ymd = explode('-', $this->input->post('conference_date_text'));
                        if (count($date_ymd) != 3) {
                            $this->_exit_with_json(array('status' => 'WRONG_DATE'));
                        }
                        $conf_date = mktime(0, 0, 0, $date_ymd[1], $date_ymd[2], $date_ymd[0]);
                        if ($conf_date == FALSE) {
                            $this->_exit_with_json(array('status' => 'WRONG_DATE'));
                        }
                        $conf_title = trim($this->input->post('conference_name_text'));
                        $host = $this->input->post('conference_host_text');
                        if ($conf_title == '') {
                            $this->_exit_with_json(array('status' => 'TITLE_NULL'));
                        }
                        $conf_old_data = $this->mConference->get_conference_by_id($this->input->post('conference_id'));
                        $banner_image = $conf_old_data['banner_image'];
                        $qrcode_image = $conf_old_data['qr_code'];
                        //上传图片
                        $image_param['upload_path'] = TMP_PATH;
                        $image_param['allowed_types'] = 'jpeg|jpg|png';
                        $image_param['max_size'] = '1048576';
                        $this->load->library('upload', $image_param);
                        if ($this->upload->do_upload('banner_image')) {
                            $image_data = $this->upload->data();
                            $new_image_name = $this->_conference_url . '_banner_' . strval(rand(1000, 9999)) . $image_data['file_ext'];
                            rename($image_data['full_path'], STATIC_IMG_DIR . $new_image_name);
                            $banner_image = $new_image_name;
                        }

                        if ($this->upload->do_upload('qr_code')) {
                            $image_data = $this->upload->data();
                            $new_image_name = $this->_conference_url . '_qrcode_' . strval(rand(1000, 9999)) . $image_data['file_ext'];
                            rename($image_data['full_path'], STATIC_IMG_DIR . $new_image_name);
                            $qrcode_image = $new_image_name;
                        }
                        $this->mConference->update_conference(
                            $this->input->post('conference_id'),
                            $conf_title,
                            $conf_date,
                            $banner_image,
                            $qrcode_image,
                            $host
                        );
                        $this->_exit_with_json(
                            array(
                                'status' => 'SUCCESS',
                                'banner' => $banner_image == '' ? 'FAILED' : 'SUCCESS',
                                'qr_code' => $banner_image == '' ? 'FAILED' : 'SUCCESS',
                            )
                        );
                    } else {
                        $conf_data = $this->mConference->get_conference_by_url($this->_conference_url);
                        $this->_render(
                            'conference/management/general',
                            'Management',
                            array(
                                'conference' => $this->_conference_data
                            )
                        );
                    }
                    break;
                }
            case 'category':
                {
                    switch ($this->_do) {
                        case'add':
                            {
                                if (!$this->mConference->has_conference_by_id($this->input->post('conference_id'))) {
                                    $this->_exit_with_json(array('status' => 'ACCESS_DENIED'));
                                }
                                $this->mCategory->begin_transaction();
                                $category_id = $this->mCategory->add_category(
                                    $this->input->post('conference_id'),
                                    $this->input->post('category_name_text'),
                                    intval($this->input->post('category_type_id'))
                                );
                                $this->mDocument->add_document($category_id, '', '');
                                $this->mCategory->end_transaction();
                                $this->_exit_with_json(array('status' => 'SUCCESS'));
                                break;
                            }
                        case 'rename':
                            {
                                $category_id = intval($this->input->post('category_id'));
                                $category_id = $this->mCategory->has_category($category_id) ? $category_id : 0;
                                $result = array();
                                if ($category_id > 0) {
                                    $this->mCategory->rename_category(
                                        $category_id,
                                        $this->input->post('category_name_text')
                                    );
                                    $result['status'] = 'SUCCESS';
                                } else {
                                    $result['status'] = 'CAT_NOT_FOUND';
                                }
                                $this->_exit_with_json($result);
                            }
                        case 'remove':
                            {
                                $category_id = intval($this->input->get('cid'));
                                $category_id = $this->mCategory->has_category($category_id) ? $category_id : 0;
                                $this->mCategory->delete_category($category_id);
                                header('location:/conference/' . $this->_conference_url . '/management/category/');
                                return;
                            }
                        case 'up':
                            {
                                $category_id = intval($this->input->get('cid'));
                                $category_id = $this->mCategory->has_category($category_id) ? $category_id : 0;
                                if ($category_id > 0) {
                                    $categories = $this->mCategory->get_all_categories(
                                        $this->_conference_id,
                                        TRUE
                                    );
                                    $i = 0;
                                    foreach ($categories as $category) {
                                        if ($category['category_id'] == $category_id) {
                                            break;
                                        }
                                        $i++;
                                    }
                                    if ($i != 0) {
                                        $j = 0;
                                        foreach ($categories as $category) {
                                            $this->mCategory->set_category_display_order(
                                                $category['category_id'],
                                                $j == $i - 1 ? $i : ($j == $i ? $i - 1 : $j)
                                            );
                                            $j++;
                                        }
                                    }
                                }
                                header('location:/conference/' . $this->_conference_url . '/management/category/');
                                break;
                            }
                        case 'down':
                            {
                                //获取要操作的category_id
                                $category_id = intval($this->input->get('cid'));
                                $category_id = $this->mCategory->has_category($category_id) ? $category_id : 0;
                                if ($category_id > 0) {
                                    $categories = $this->mCategory->get_all_categories(
                                        $this->_conference_id,
                                        TRUE
                                    );
                                    $i = 0;
                                    $category_count = count($categories);
                                    foreach ($categories as $category) {
                                        if ($category['category_id'] == $category_id) {
                                            break;
                                        }
                                        $i++;
                                    }
                                    if ($i < $category_count - 1) {
                                        $j = 0;
                                        foreach ($categories as $category) {
                                            $this->mCategory->set_category_display_order(
                                                $category['category_id'],
                                                $j == $i + 1 ? $i : ($j == $i ? $i + 1 : $j)
                                            );
                                            $j++;
                                        }
                                    }
                                }
                                header('location:/conference/' . $this->_conference_url . '/management/category/');
                                break;
                            }
                        default:
                            {
                                $cat_data = $this->mCategory->get_all_categories($this->_conference_id);
                                foreach ($cat_data as &$cat) {
                                    $first_doc = $this->mDocument->get_first_document_from_category($cat['category_id']);
                                    $cat['first_document_id'] = $first_doc['document_id'];
                                }
                                $this->_render('conference/management/category', 'Categories', array('category_list' => $cat_data));
                            }
                    }
                    break;
                }
            case 'participant':
                {
                    switch ($this->_do) {
                        case 'get':
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
                                $count_per_page = 10;
                                $participants_data_set = array();
                                $count = 0;
                                $participants = $this->mConfMember->get_conference_members($this->_conference_id, $page, $count_per_page);
                                foreach ($participants as $participant) {
                                    //过滤信息
                                    $user_info = $this->mUser->get_user_by_user_id($participant['user_id']);
                                    if ($user_name_restrict != '' && strpos($user_info['user_name'], $user_name_restrict) === FALSE) {
                                        continue;
                                    }
                                    if ($user_email_restrict != '' && $user_info['user_email'] != $user_email_restrict) {
                                        continue;
                                    }
                                    $continue = FALSE;
                                    foreach ($user_role_restrict as $role) {
                                        if (strpos($participant['user_role'], $role) === FALSE) {
                                            $continue = TRUE;
                                            break;
                                        }
                                    }
                                    if ($continue == TRUE) {
                                        continue;
                                    }
                                    //添加信息
                                    $participant['user_name'] = $user_info['user_name'];
                                    $participant['user_roles'] = explode(',', $participant['user_role']);
                                    $participant['user_role'] = NULL;
                                    $participants_data_set [] = $participant;
                                    $count++;
                                }
                                $this->_exit_with_json(
                                    array(
                                        'status' => 'SUCCESS',
                                        'data' => $participants_data_set,
                                        'page_count' => ceil($count / $count_per_page)
                                    )
                                );
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
                                $this->_exit_with_json(array('status' => 'SUCCESS'));
                                break;
                            }
                        default:
                            {
                                $page = $this->input->get('page');
                                $page == '' ? $page = 0 : $page = intval($page) - 1;
                                $participants = $this->mConfMember->get_conference_members($this->_conference_id, $page);
                                foreach ($participants as &$participant) {
                                    $user_info = $this->mUser->get_user_by_user_id($participant['user_id']);
                                    $participant['user_name'] = $user_info['user_name'];
                                    $participant['user_roles'] = explode(',', $participant['user_role']);
                                }
                                $this->_render(
                                    'conference/management/participant',
                                    'Participants',
                                    array(
                                        'participants' => $participants
                                    )
                                );
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
     * 处理会议参与者
     */
    private function _member()
    {
        $this->_login_redirect();
        switch ($this->_do) {
            case 'register':
                {
                    if ($this->mConfMember->user_joint_in_conference($this->_user_id, $this->_conference_id)) {
                        $this->_exit_with_json(array('status' => 'ALREADY_JOIN'));
                    }
                    $this->mConfMember->add_member_to_conference($this->_user_id, $this->_conference_id);
                    $this->_do_redirect();
                    break;
                }
        }
    }

    /**
     * 论文评审处理
     */
    private function _paper_review()
    {

    }

    /**
     * 会议基本信息展示处理
     */
    private function _default()
    {
        //处理输入
        $category_id = $this->input->get('cid');
        $category_id = $category_id == '' ? 0 : intval($category_id);
        //进入业务逻辑
        $data = $this->sConference->homepage($this->_conference_id, $category_id);
        $this->_render(
            'conference/show',
            $this->_conference_data['conference_name'],
            array(
                'conference' => $this->_conference_data,
                'category_list' => $data->category_list,
                'active_category_id' => $category_id,
                'active_category_document_id' => $data->document_id,
                'active_document_content' => $data->document_html,
            )
        );
    }
}
