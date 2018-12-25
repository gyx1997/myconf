<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/15
 * Time: 22:58
 */

namespace myConf\Services;


use myConf\Exceptions\ConferenceNotFoundException;

class Conference extends \myConf\BaseService
{
    /**
     * @param int $conference_id
     * @param int $category_id
     * @return array 返回键值对数组，其中'category_list'为category的集合，'document'为文章的键值对数组
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\CategoryNotFoundException
     */
    public function homepage(int $conference_id, int $category_id): array
    {
        $categories = $this->Models->Conference->categories($conference_id);
        //检查该会议内部的category的id
        $cat_ids = array();
        foreach ($categories as $cid) {
            $cat_ids [] = $cid['category_id'];
        }
        if ($category_id === 0) {
            $category_id = reset($categories)['category_id'];
        } else {
            if (!in_array($category_id, $cat_ids)) {
                throw new \myConf\Exceptions\CategoryNotFoundException('CAT_NOT_FOUND', 'This category does not exists or has been deleted.');
            }
        }
        $category_document = $this->Models->Category->first_document($category_id);
        return array('category_list' => $categories, 'document' => $category_document);
    }

    /**
     * 初始化Conference控制器时得到Conference信息
     * @param string $conference_url
     * @return array
     * @throws \myConf\Exceptions\ConferenceNotFoundException
     */
    public function init_load_conference(string $conference_url): array
    {
        $conf = $this->Models->Conference->get_by_url($conference_url);
        if (empty($conf)) {
            throw $this->_exception_conf_not_found();
        }
        return $conf;
    }

    /**
     * 根据会议的ID号得到会议信息
     * @param int $conference_id
     * @return array
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\ConferenceNotFoundException
     */
    public function get_conference_by_id(int $conference_id): array
    {
        $conf = $this->Models->Conference->get_by_id((strval($conference_id)));
        if (empty($conf)) {
            throw $this->_exception_conf_not_found();
        }
        return $conf;
    }

    /**
     * 判断指定用户是否加入了会议
     * @param int $user_id
     * @param int $conference_id
     * @return bool
     */
    public function user_joint_in(int $user_id, int $conference_id): bool
    {
        return $this->Models->Conference->user_joint_in($conference_id, $user_id);
    }

    /**
     * 获取用户成员角色
     * @param int $user_id
     * @param int $conference_id
     * @return array
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbCompositeKeysException
     */
    public function get_member_roles(int $user_id, int $conference_id): array
    {
        return $this->Models->Conference->get_user_roles($conference_id, $user_id);
    }

    /**
     * 更新会议基本信息
     * @param int $id
     * @param string $title
     * @param string $host
     * @param string $date
     * @param bool $paper_submission
     * @param string $submit_end_date
     * @param string $banner_field
     * @param string $qr_field
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\ConferenceNotFoundException
     * @throws \myConf\Exceptions\UpdateConferenceException
     */
    public function update_conference(int $id, string $title, string $host, string $date, bool $paper_submission, string $submit_end_date, string $banner_field = '', string $qr_field = '') : void
    {
        $data_old = $this->Models->Conference->get_by_id($id);
        if (empty($data_old)) {
            throw self::_exception_conf_not_found();
        }
        $error = array();
        //验证日期是否正确
        $date_ymd = explode('-', $date);
        if (count($date_ymd) !== 3) {
            $error['date'] = true;
            return;
        }
        $conf_date = mktime(0, 0, 0, $date_ymd[1], $date_ymd[2], $date_ymd[0]);
        if ($conf_date === FALSE) {
            $error['date'] = true;
            return;
        }
        //验证提交文章截止日期是否正确
        $date_ymd = explode('-', $submit_end_date);
        if (count($date_ymd) !== 3) {
            $error['submit_date'] = true;
            return;
        }
        $submit_end = mktime(0, 0, 0, $date_ymd[1], $date_ymd[2], $date_ymd[0]);
        if ($submit_end === false) {
            $error['submit_date'] = true;
            return;
        }
        //验证会议名称（标题）是否为空
        $conf_title = trim($title);
        if ($conf_title === '') {
            $error['title'] = true;
        }
        //上传头图，如果成功删除原文件
        try {
            $banner_img_data = \myConf\Libraries\Attach::parse_attach($banner_field);
            $banner_image = $banner_img_data['full_name'];
            unlink(ATTACHMENT_DIR . $data_old['conference_banner_image']);
        } catch (\myConf\Exceptions\FileUploadException $e) {
            $error['banner'] = true;
        }
        //上传二维码,如果成功删除原文件
        try {
            $qr_img_data = \myConf\Libraries\Attach::parse_attach($qr_field);
            $qrcode_image = $qr_img_data['full_name'];
            unlink(ATTACHMENT_DIR . $data_old['conference_qr_code']);
        } catch (\myConf\Exceptions\FileUploadException $e) {
            $error['qr'] = true;
        }
        //更新模型信息
        $this->Models->Conference->update_conference(
            $id,
            isset($error['title']) ? '' : $conf_title,
            isset($error['date']) ? '' : $conf_date, isset($banner_image) ? $banner_image : $data_old['conference_banner_image'],      //如果没有上传，使用旧的
            isset($qrcode_image) ? $qrcode_image : $data_old['conference_qr_code'], $host, $paper_submission, $submit_end
        );
        //返回操作状态
        if (!empty($error)) {
            throw new \myConf\Exceptions\UpdateConferenceException($error, 'UPDATE_CONF_ERROR', 'An error occurred when trying to update the information of conference which id is " ' . strval($id) . ' ".');
        }
        return;
    }

    /**
     * @param $conference_id
     * @return array
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function get_category_list($conference_id)
    {
        $cat_data = $this->Models->Conference->categories($conference_id);
        foreach ($cat_data as &$cat) {
            //var_dump($cat);
            $first_doc = $this->Models->Category->first_document($cat['category_id']);
            $cat['first_document_id'] = $first_doc['document_id'];
        }
        return $cat_data;
    }

    /**
     * 添加一个category条目
     * @param int $conference_id
     * @param string $category_text
     * @param int $category_type
     * @throws ConferenceNotFoundException
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbTransactionException
     */
    public function add_category(int $conference_id, string $category_text, int $category_type) : void
    {
        if (!$this->Models->Conference->exist(strval($conference_id))) {
            throw self::_exception_conf_not_found();
        }
        //使用事务插入一个列表条目
        $this->Models->Category->create_new($conference_id, $category_text, $category_type);
    }

    /**
     * 删除一个条目
     * @param int $conference_id
     * @param int $category_id
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\CategoryNotFoundException
     */
    public function delete_category(int $conference_id, int $category_id): void
    {
        $category_id = $this->Models->Category->exist(strval($category_id)) ? $category_id : 0;
        if ($category_id === 0) {
            throw self::_exception_cat_not_found();
        }
        $this->Models->Category->delete($conference_id, $category_id);
    }

    /**
     * 重命名一个条目
     * @param int $conference_id
     * @param int $category_id
     * @param string $category_new_name
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\CategoryNotFoundException
     */
    public function rename_category(int $conference_id, int $category_id, string $category_new_name): void
    {
        //需要检查是否存在，因为管理员可能随时删除了这个category，但是前台用户并不知道
        $category_id = $this->Models->Category->exist(strval($category_id)) ? $category_id : 0;
        if ($category_id === 0) {
            throw self::_exception_cat_not_found();
        }
        $this->Models->Category->rename($category_id, $category_new_name);
    }

    /**
     * 将当前会议当前条目显示顺序向上移动一位。
     * @param int $conference_id
     * @param int $category_id
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\CategoryNotFoundException
     * @throws \myConf\Exceptions\DbTransactionException
     */
    public function move_up_category(int $conference_id, int $category_id): void
    {
        $category_id = $this->Models->Category->exist(strval($category_id)) ? $category_id : 0;
        if ($category_id === 0) {
            throw self::_exception_cat_not_found();
        }
        $this->Models->Category->move_up($conference_id, $category_id);
    }

    /**
     * 将当前会议条目向下移动一位。
     * @param int $conference_id
     * @param int $category_id
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\CategoryNotFoundException
     * @throws \myConf\Exceptions\DbTransactionException
     */
    public function move_down_category(int $conference_id, int $category_id): void
    {
        $category_id = $this->Models->Category->exist(strval($category_id)) ? $category_id : 0;
        if ($category_id === 0) {
            throw self::_exception_cat_not_found();
        }
        $this->Models->Category->move_down($conference_id, $category_id);
    }

    /**
     * @param int $conference_id
     * @param array $roles_restrict
     * @param string $name_restrict
     * @param string $email_restrict
     * @return array
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function get_members(int $conference_id, array $roles_restrict = array(), string $name_restrict = '', string $email_restrict = '') : array {
        //先定义返回结果集
        $members_data_set = array();
        $members = $this->Models->Conference->get_members($conference_id);
        foreach ($members as $member) {
            //过滤信息
            if ($name_restrict != '' && strpos($member['user_name'], $name_restrict) === false) {
                continue;
            }
            if ($email_restrict != '' && $member['user_email'] !== $email_restrict) {
                continue;
            }
            $continue = false;
            foreach ($roles_restrict as $role) {
                if (in_array($role, $member['user_role']) === false) {
                    $continue = true;
                    break;
                }
            }
            if ($continue == true) {
                continue;
            }
            $members_data_set [] = $member;
        }
        return $members_data_set;
    }

    /**
     * 判断用户是否是某一个角色
     * @param int $conference_id
     * @param int $user_id
     * @param string $role
     * @return bool
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbCompositeKeysException
     */
    public function member_is_role(int $conference_id, int $user_id, string $role) {
        return in_array($role, $this->Models->Conference->get_user_roles($conference_id, $user_id));
    }

    /**
     * 将用户添加某个角色
     * @param int $conference_id
     * @param int $user_id
     * @param string $role
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbCompositeKeysException
     */
    public function member_add_role(int $conference_id, int $user_id, string $role) : void {
        $roles = $this->Models->Conference->get_user_roles($conference_id, $user_id);
        //先判断是否已经存在这个角色，如果存在就不用进行数据库操作了。
        //否则，增加额外的数据库操作，且缓存也失效了。
        //下面的member_remove_role同理
        if (!in_array($role, $roles)) {
            $roles [] = $role;
            $this->Models->Conference->set_user_roles($conference_id, $user_id, $roles);
        }
    }

    /**
     * 将用户移除某个角色
     * @param int $conference_id
     * @param int $user_id
     * @param string $role
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbCompositeKeysException
     */
    public function member_remove_role(int $conference_id, int $user_id, string $role) : void {
        $roles = $this->Models->Conference->get_user_roles($conference_id, $user_id);
        $roles_new = array();
        foreach ($roles as $r) {
            $r !== $role && $roles_new [] = $r;
        }
        if (!empty(array_diff($roles, $roles_new))) {
            $this->Models->Conference->set_user_roles($conference_id, $user_id, $roles_new);
        }
    }

    /**
     * @param int $conference_id
     * @param int $user_id
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbCompositeKeysException
     */
    public function remove_member(int $conference_id, int $user_id) : void {
        $this->Models->Conference->remove_member($conference_id, $user_id);
    }

    /**
     * @param int $conference_id
     * @param int $user_id
     */
    public function add_member(int $conference_id, int $user_id) : void {
        $this->Models->Conference->add_member($conference_id, $user_id);
    }

    /**
     * 会议未找到时的异常
     * @return ConferenceNotFoundException
     */
    private static function _exception_conf_not_found(): \myConf\Exceptions\ConferenceNotFoundException
    {
        return new \myConf\Exceptions\ConferenceNotFoundException('CONF_NOT_FOUND', 'The requested conference does not exists, or has been renamed or deleted.');
    }

    /**
     * 栏目没找到时的异常
     * @return \myConf\Exceptions\CategoryNotFoundException
     */
    private static function _exception_cat_not_found(): \myConf\Exceptions\CategoryNotFoundException
    {
        return new \myConf\Exceptions\CategoryNotFoundException('CAT_NOT_FOUND', 'The request category does not exists, or has been deleted.');
    }

}