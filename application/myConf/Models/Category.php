<?php

namespace myConf\Models;

/**
 * Class Category
 * @package myConf\Models
 * @author _g63<522975334@qq.com>
 * @version 2019.1
 */
class Category extends \myConf\BaseModel
{
    /**
     * Category constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'categories';
        $this->_pk = 'category_id';
    }

    /**
     * 判断指定的栏目是否存在
     * @param int $category_id
     * @return bool
     */
    public function exist(int $category_id) : bool {
        return $this->Tables->Categories->exist(strval($category_id));
    }

    /**
     * @param int $category_id
     * @return array
     */
    public function first_document(int $category_id) : array {
        return $this->Tables->Documents->fetch_first(array('document_category_id' => $category_id));
    }

    /**
     * @param int $conference_id
     * @param string $category_name
     * @param int $category_type
     * @return int
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbTransactionException
     */
    public function create_new(int $conference_id, string $category_name, int $category_type) : int
    {
        \myConf\Libraries\DbHelper::begin_trans();
        $category_id = $this->Tables->Categories->insert(
            array(
                'conference_id' => $conference_id,
                'category_title' => $category_name,
                'category_type' => $category_type
            )
        );
        $this->Tables->Documents->insert(array(
            'document_category_id' => $category_id,
            'document_title' => 'Untitled Document',
            'document_html' => '',
        ));
        \myConf\Libraries\DbHelper::end_trans();
        //刷新缓存
        $this->Tables->Categories->get_ids_by_conference($conference_id, true);
        return $category_id;
    }

    /**
     * @param int $conference_id
     * @param int $category_id
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function delete(int $conference_id, int $category_id) : void
    {
        $this->Tables->Categories->delete(strval($category_id));
        //刷新非主键缓存
        $this->Tables->Categories->get_ids_by_conference($conference_id, true);
    }

    /**
     * 修改某个category的名称（标题）
     * @param int $category_id
     * @param string $new_category_name
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function rename(int $category_id, string $new_category_name) : void
    {
        $this->Tables->Categories->set(strval($category_id), array('category_title' => $new_category_name));
    }

    /**
     * 修改某个category的display_order。
     * @param int $category_id
     * @param int $display_order
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function set_category_display_order(int $category_id, int $display_order): void
    {
        $this->Tables->Categories->set($category_id, array('category_display_order' => $display_order));
    }

    /**
     * 将指定的category上移一位
     * @param int $conference_id
     * @param int $category_id
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbTransactionException
     */
    public function move_up(int $conference_id, int $category_id) : void {
        $categories = $this->Tables->Categories->get_ids_by_conference($conference_id);
        //找到当前记录的id号
        $i = 0;
        foreach ($categories as $cid) {
            if ($cid == $category_id) {
                break;
            }
            $i++;
        }
        if ($i != 0) {
            $j = 0;
            //不是第一个，需要更新
            //因为多条UPDATE，需要使用事务
            \myConf\Libraries\DbHelper::begin_trans();
            foreach ($categories as $cid) {
                $this->Tables->Categories->set($cid, array('category_display_order' => $j == $i - 1 ? $i : ($j == $i ? $i - 1 : $j)));
                $j++;
            }
            \myConf\Libraries\DbHelper::end_trans();
        }
        //删除旧缓存
        $this->Tables->Categories->delete_conference_categories_cache($conference_id);
    }

    /**
     * 将指定的category下移一位
     * @param int $conference_id
     * @param int $category_id
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbTransactionException
     */
    public function move_down(int $conference_id, int $category_id) : void {
        $categories = $this->Tables->Categories->get_ids_by_conference($conference_id, true);
        $i = 0;
        $category_count = count($categories);
        foreach ($categories as $cid) {
            if ($cid == $category_id) {
                break;
            }
            $i++;
        }
        if ($i < $category_count - 1) {
            $j = 0;
            \myConf\Libraries\DbHelper::begin_trans();
            foreach ($categories as $cid) {
                $this->Tables->Categories->set($cid, array('category_display_order' => $j === $i + 1 ? $i : ($j === $i ? $i + 1 : $j)));
                $j++;
            }
            \myConf\Libraries\DbHelper::end_trans();
        }
        //删除旧缓存
        $this->Tables->Categories->delete_conference_categories_cache($conference_id);
    }
}