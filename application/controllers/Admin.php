<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/18
 * Time: 22:13
 */

defined('BASEPATH') OR exit('Access Denied.');

class admin extends CF_Controller
{
    /**
     * admin constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * index page of administrator panel.
     */
    public function index()
    {
        $this->_admin_login_redirect();
        $this->_render_admin('admin/center');
    }

    private function _admin_login_redirect()
    {
        $this->_admin_has_login() == FALSE && header('location:/admin/login/');

    }

    /**
     * determine whether the administrator has logged in.
     * @return bool
     */
    private function _admin_has_login()
    {
        $login_flag = $this->session->userdata('admin_login');
        $login_timestamp = $this->session->userdata('login_timestamp');
        //var_dump(empty($login_flag));
        return (
            !empty($login_flag) &&
            !empty($login_timestamp) &&
            $login_flag == TRUE &&
            time() - $login_timestamp <= 3600    //1小时不在管理面板操作，默认为取消登录
        );
    }

    /**
     * category management.
     */
    public function category()
    {
        $this->_admin_login_redirect();
        $category_id = $this->uri->segment(4, 0);
        $category_id = $this->mCategory->has_category($category_id) ? intval($category_id) : 0;
        switch ($this->_action) {
            case 'add-cat':
                {
                    if ($category_id == 0) {
                        $this->mCategory->begin_transaction();
                        $category_id = $this->mCategory->add_category(
                            $this->input->post('conference_id'),
                            $this->input->post('category_name_text'),
                            intval($this->input->post('category_type_id'))
                        );
                        $this->mDocument->add_document($category_id, '', '');
                        $this->mCategory->end_transaction();
                    }
                    header('location:/admin/category/');
                    return;
                }
            case 'del-cat':
                {
                    if ($category_id > 0) {
                        $this->mCategory->delete_category(
                            $this->uri->segment(4)
                        );
                    }
                    header('location:/admin/category/');
                    return;
                }
            case 'mod-cat':
                {
                    if ($category_id > 0) {
                        $this->mCategory->rename_category(
                            $this->uri->segment(4),
                            $this->input->post('category_name_text')
                        );
                    }
                    header('location:/admin/category/');
                    return;
                }
            case 'mod-doc':
                {
                    if ($category_id > 0) {

                        $document = $this->mDocument->get_first_document_from_category($category_id);
                        $this->_render_admin(
                            'admin/edit_document',
                            '编辑文章',
                            array(
                                'category_id' => $category_id,
                                'document_id' => $document['document_id'],
                                'document_title' => empty($document) ? '' : $document['document_title'],
                                'document_html' => empty($document) ? '' : html_entity_decode($document['document_html'])
                            )
                        );
                    }
                    return;
                }
            case 'up-cat':
                {
                    $categories = $this->mCategory->get_all_categories(TRUE);
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
                    header('location:/admin/category/');
                    return;
                }
            case 'down-cat':
                {
                    $categories = $this->mCategory->get_all_categories(TRUE);
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
                    header('location:/admin/category/');
                    return;
                }
            case 'submit-doc':
                {
                    if ($category_id > 0) {
                        $document = $this->mDocument->get_first_document_from_category($category_id);
                        if (!empty($document)) {
                            $this->mDocument->modify_document(
                                $document['document_id'],
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
                        }
                    }
                    header('location:/admin/category/');
                    return;
                }
            default:
                {
                    $category_list = $this->mCategory->get_all_categories();
                    $this->_render_admin(
                        'admin/category',
                        '栏目设置',
                        array(
                            'category_list' => $category_list,
                        )
                    );
                }
        }
    }

    public function attachment()
    {
        $this->_admin_login_redirect();
    }

    public function user()
    {
        $role_mapping = array('0' => 'scholar', '1' => 'reviewer', '2' => 'editor', '3' => 'none');
        $this->_admin_login_redirect();
        switch ($this->_action) {
            case 'assign-role':
                {
                    $user_id = $this->input->post('user_id_text');

                    break;
                }

            case 'search':
            default:
                {
                    $username_pattern = $this->input->post('username_text');
                    $role_id = $this->input->post('user_role_id');
                    if ($role_id === NULL) $role_id = '3';

                    $users = $this->mUser->find_user_by_name_and_role(
                        $username_pattern,
                        $role_mapping[$role_id]
                    );
                    $this->_render_admin(
                        'admin/user',
                        '用户管理',
                        array(
                            'users' => $users,
                            'user_count' => count($users),
                            'target_user_name' => $username_pattern,
                            'target_role_id' => $role_id
                        )
                    );
                    break;
                }
        }
    }

    public function general()
    {
        $this->_admin_login_redirect();
        $banner_failed = FALSE;
        $qrcode_failed = FALSE;
        switch ($this->_action) {
            case  'submit':
                {
                    $this->mConfig->set_mitbeian($this->input->post('icp_text'));
                    $this->mConfig->set_footer1($this->input->post('footer1_text'));
                    $this->mConfig->set_footer2($this->input->post('footer2_text'));
                    $this->mConfig->set_title($this->input->post('title_text'));
                    //上传图片
                    $config['upload_path'] = STATIC_IMG_DIR;
                    $config['allowed_types'] = 'jpeg|jpg|png';
                    $config['max_size'] = '1048576';
                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('banner_image')) {
                        $image_data = $this->upload->data();
                        $new_image_name = uniqid() . $image_data['file_ext'];
                        rename($image_data['full_path'], STATIC_IMG_DIR . $new_image_name);
                        $this->mConfig->set_banner($new_image_name);
                    } else {
                        $banner_failed = TRUE;
                    }

                    if ($this->upload->do_upload('qr_code')) {
                        $image_data = $this->upload->data();
                        $new_image_name = uniqid() . $image_data['file_ext'];
                        rename($image_data['full_path'], STATIC_IMG_DIR . $new_image_name);
                        $this->mConfig->set_qrcode($new_image_name);
                    } else {
                        $qrcode_failed = TRUE;
                    }
                }
            default:
                {
                    $banner = $this->mConfig->get_banner();
                    $qrcode = $this->mConfig->get_qrcode();
                    $this->_render_admin(
                        'admin/general',
                        '基本设置',
                        array(
                            'banner_failed' => $banner_failed,
                            'qrcode_failed' => $qrcode_failed,
                            'mitbeian' => $this->mConfig->get_mitbeian(),
                            'footer1' => $this->mConfig->get_footer1(),
                            'footer2' => $this->mConfig->get_footer2(),
                            'banner' => $banner == '' ? '' : RELATIVE_STATIC_IMG_DIR . $banner,
                            'qrcode' => $qrcode == '' ? '' : RELATIVE_STATIC_IMG_DIR . $qrcode,
                            'title' => $this->mConfig->get_title(),
                        )
                    );
                }
        }
    }

    public function login()
    {
        if ($this->_admin_has_login()) {
            header('location:/admin/');
            return;
        }
        $this->_render(
            'admin/login',
            '登录管理面板',
            array(
                'err_code' => $this->input->get('err_code')
            )
        );
    }

    public function login_submit()
    {
        if ($this->_admin_has_login()) {
            header('location:/admin/');
            return;
        }
        $password = $this->mConfig->get_administrator_password();
        $password_received = $this->input->post('password_text');
        $salt = $this->mConfig->get_salt();
        $password_md5 = md5($password_received . $salt);
        if ($password_md5 == $password) {
            $this->_set_login_state();
            header('location:/admin/');
            return;
        } else {
            header('location:/admin/login/?err_code=wrong_password');
            return;
        }
    }

    /**
     * set the administrator state logged in.
     */
    private function _set_login_state()
    {
        $this->session->set_userdata('admin_login', TRUE);
        $this->session->set_userdata('login_timestamp', time());
    }

    public function logout()
    {
        if ($this->_admin_has_login()) {
            $this->_set_logout_state();
            header('location:/admin/login/');
            return;
        } else {
            header('location:/error/');
        }
    }

    private function _set_logout_state()
    {
        $this->session->unset_userdata('admin_login');
        $this->session->unset_userdata('login_timestamp');
    }

    public function reset_password()
    {
        if ($this->_admin_has_login()) {
            $this->_show_reset_password();

        }
        header('location:/admin/login/');
    }

    private function _show_reset_password()
    {
        $this->load->view('common/header');
        $this->load->view('admin/reset_password');
        $this->load->view('common/footer');
    }

    private function _show_login_page()
    {
        $this->load->view('common/header');
        $this->load->view
        (
            'admin/login',
            array(
                'csrf_name' => $this->security->get_csrf_token_name(),
                'csrf_hash' => $this->security->get_csrf_hash(),
                'err_code' => $this->input->get('err_code'),
            )
        );
        $this->load->view('common/footer');
    }

    private function _show_admin_center()
    {

    }


}
