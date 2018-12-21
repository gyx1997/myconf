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
        if ($category_id === 0) {
            $category_id = $this->models()->Category->get_first_category_id($conference_id);
        } else {
            if (!$this->models()->Category->exist($category_id)) {
                throw new \myConf\Exceptions\CategoryNotFoundException('CAT_NOT_FOUND', 'This category does not exists or has been deleted.');
            }
        }
        //使用了缓存，因为在会议管理页面更新列表顺序可能存在锁问题，因此使用缓存，当更新完成刷新缓存即可。
        $categories = $this->models()->Category->get_categories_from_conference($conference_id, TRUE);
        $category_document = $this->models()->Document->get_first_document_from_category($category_id);
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
        $conf = $this->models()->Conference->get_conference_by_url($conference_url);
        if (empty($conf)) {
            throw $this->_exception_conf_not_found();
        }
        return $conf;
    }

    /**
     * 根据会议的ID号得到会议信息
     * @param int $conference_id
     * @return array
     * @throws \myConf\Exceptions\ConferenceNotFoundException
     */
    public function get_conference_by_id(int $conference_id): array
    {
        $conf = $this->models()->Conference->get(strval($conference_id));
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
        return $this->models()->ConfMember->user_joint_in_conference($user_id, $conference_id);
    }

    /**
     * 获取会议成员的角色
     * @param int $user_id
     * @param int $conference_id
     * @return array
     */
    public function get_member_roles(int $user_id, int $conference_id): array
    {
        return $this->models()->ConfMember->get_member_roles($user_id, $conference_id);
    }

    /**
     * 更新会议信息
     * @param int $id
     * @param string $title
     * @param string $host
     * @param string $date
     * @param string $banner_field
     * @param string $qr_field
     * @throws ConferenceNotFoundException
     * @throws \myConf\Exceptions\DbTransactionException
     * @throws \myConf\Exceptions\UpdateConferenceException
     */
    public function update_conference_info(int $id, string $title, string $host, string $date, string $banner_field = '', string $qr_field = ''): void
    {
        $data_old = $this->models()->Conference->get($id);
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
        //验证会议名称（标题）是否为空
        $conf_title = trim($title);
        if ($conf_title === '') {
            $error['title'] = true;
        }
        //上传头图
        try {
            $banner_img_data = \myConf\Libraries\Attach::parse_attach($banner_field);
        } catch (\myConf\Exceptions\FileUploadException $e) {
            $error['banner'] = true;
        }
        //上传二维码
        try {
            $qr_img_data = \myConf\Libraries\Attach::parse_attach($qr_field);
        } catch (\myConf\Exceptions\FileUploadException $e) {
            $error['qr'] = true;
        }

        //多表修改/插入操作，开启数据库事务
        $this->models()->trans_block_begin();
        if (isset($banner_img_data)) {
            $banner_image = $banner_img_data['full_name'];
            $this->models()->Attachment->add_attachment_from_conference($banner_img_data['full_name'], $banner_img_data['size'], $banner_img_data['original_name'], $id, $banner_img_data['is_image'], 0, 0);
        }
        if (isset($qr_img_data)) {
            $qrcode_image = $qr_img_data['full_name'];
            $this->models()->Attachment->add_attachment_from_conference($qr_img_data['full_name'], $qr_img_data['size'], $qr_img_data['original_name'], $id, $qr_img_data['is_image'], 0, 0);
        }
        $this->models()->Conference->update_conference(
            $id,
            isset($error['title']) ? '' : $conf_title,
            isset($error['date']) ? '' : $conf_date,
            isset($banner_image) ? $banner_image : $data_old['banner_image'],      //是否错误已经判断过了
            isset($qrcode_image) ? $qrcode_image : $data_old['qr_code'],
            $host
        );
        $this->models()->trans_block_end();
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
        $cat_data = $this->models()->Category->get_categories_from_conference($conference_id);
        foreach ($cat_data as &$cat) {
            $first_doc = $this->models()->Document->get_first_document_from_category($cat['category_id']);
            $cat['first_document_id'] = $first_doc['document_id'];
        }
        return $cat_data;
    }

    /**
     * 添加一个category条目
     * @param int $conference_id
     * @param string $category_text
     * @param int $category_id
     * @throws ConferenceNotFoundException
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbTransactionException
     */
    public function add_category(int $conference_id, string $category_text, int $category_id): void
    {
        if (!$this->models()->Conference->exist(strval($conference_id))) {
            throw self::_exception_conf_not_found();
        }
        //使用事务插入一个列表条目
        $this->models()->trans_block_begin();
        $category_id = $this->models()->Category->add_category(
            $conference_id,
            $category_text,
            $category_id
        );
        $this->models()->Document->add_document($category_id, '', '');
        $this->models()->trans_block_end();
        $this->_refresh_category_list_cache($conference_id);
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
        $category_id = $this->models()->Category->exist(strval($category_id)) ? $category_id : 0;
        if ($category_id === 0) {
            throw self::_exception_cat_not_found();
        }
        $this->models()->Category->delete_category($category_id);
        $this->_refresh_category_list_cache($conference_id);
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
        $category_id = $this->models()->Category->exist(strval($category_id)) ? $category_id : 0;
        if ($category_id === 0) {
            throw self::_exception_cat_not_found();
        }
        $this->models()->Category->rename_category($category_id, $category_new_name);
        $this->_refresh_category_list_cache($conference_id);
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
        $category_id = $this->models()->Category->exist(strval($category_id)) ? $category_id : 0;
        if ($category_id === 0) {
            throw self::_exception_cat_not_found();
        }
        $categories = $this->models()->Category->get_categories_from_conference($conference_id, TRUE);
        //找到当前记录的id号
        $i = 0;
        foreach ($categories as $category) {
            if ($category['category_id'] == $category_id) {
                break;
            }
            $i++;
        }
        if ($i != 0) {
            $j = 0;
            //不是第一个，需要更新
            //因为多条UPDATE，需要使用事务
            $this->models()->trans_block_begin();
            foreach ($categories as $category) {
                $this->models()->Category->set_category_display_order(
                    $category['category_id'],
                    $j == $i - 1 ? $i : ($j == $i ? $i - 1 : $j)
                );
                $j++;
                //var_dump($this->models()->trans_status());
            }
            $this->models()->trans_block_end();
        }
        $this->_refresh_category_list_cache($conference_id);
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
        $category_id = $this->models()->Category->exist(strval($category_id)) ? $category_id : 0;
        if ($category_id === 0) {
            throw self::_exception_cat_not_found();
        }
        $categories = $this->models()->Category->get_categories_from_conference($conference_id, TRUE);
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
            $this->models()->trans_block_begin();
            foreach ($categories as $category) {
                $this->models()->Category->set_category_display_order(
                    $category['category_id'],
                    $j === $i + 1 ? $i : ($j === $i ? $i + 1 : $j)
                );
                $j++;
            }
            $this->models()->trans_block_end();
        }
        $this->_refresh_category_list_cache($conference_id);
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

    /**
     * 刷新条目列表缓存
     * @param int $conference_id
     * @throws \myConf\Exceptions\CacheDriverException
     */
    private function _refresh_category_list_cache(int $conference_id): void
    {
        $this->models()->Category->get_categories_from_conference($conference_id, TRUE, true);
    }
}