<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/18
 * Time: 22:13
 */

defined('BASEPATH') OR exit('Access Denied.');

class setup extends CI_Controller
{

    /**
     * setup constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('mConfig');
    }

    public function index()
    {
        $this->load->view('common/header');
        $this->load->view(
            'setup',
            array
            (
                'salt' => $this->mConfig->generate_salt(),
                'csrf_name' => $this->security->get_csrf_token_name(),
                'csrf_hash' => $this->security->get_csrf_hash(),
            )
        );
        $this->load->view('common/footer');
    }

    public function submit()
    {
        $salt_received = trim($this->input->post('salt_text'));
        if ($salt_received != $this->mConfig->get_salt()) {
            header('location:/error/?code=403');
            return;
        }
        $password = md5(md5($this->input->post('password_text')) . $salt_received);
        $this->mConfig->set_administrator_password($password);
        $this->mConfig->set_mitbeian($this->input->post('icp_text'));
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
            echo('设置成功！<a href="/admin/login/">点此登录管理面板</a>。');
            return;
        }
        echo('图片上传失败，您可以稍后再管理面板中修改图片。<a href="/admin/login/">点此登录管理面板</a>。');
    }
}
