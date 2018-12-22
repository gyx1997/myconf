<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/15
 * Time: 17:22
 */

namespace myConf;

/**
 * Class Models 模型管理器
 * @package myConf
 * @property-read \myConf\Models\User $User
 * @property-read \myConf\Models\Scholar $Scholar
 * @property-read \myConf\Models\Config $Config
 * @property-read \myConf\Models\Document $Document
 * @property-read \myConf\Models\Conference $Conference
 * @property-read \myConf\Models\Category $Category
 * @property-read \myConf\Models\ConfMember $ConfMember
 * @property-read \myConf\Models\Attachment $Attachment
 */
class Models
{
    /**
     * @var array 模型数组
     */
    private $_models = array();
    /**
     * @var \CI_DB_active_record CI 数据库对象，用来操作事务
     */
    private $_db;

    /**
     * Models constructor.
     */
    public function __construct()
    {
        $CI = &get_instance();
        $this->_db = $CI->db;
    }

    /**
     * 返回指定的模型（大小写敏感）
     * @param string $model_name
     * @return mixed
     */
    public function __get(string $model_name): \myConf\BaseModel
    {
        if (!isset($this->_models[$model_name])) {
            $class_name = '\\myConf\\Models\\' . $model_name;
            $this->_models[$model_name] = new $class_name();
        }
        return $this->_models[$model_name];
    }

    /**
     * 事务开始
     */
    public function trans_block_begin(): void
    {
        $this->_db->trans_strict(false);
        $this->_db->trans_begin();
    }

    /**
     * 事务结束扫尾工作
     * @throws Exceptions\DbTransactionException
     */
    public function trans_block_end(): void
    {
        if ($this->_db->trans_status() === TRUE) {
            $this->_db->trans_commit();
            return;
        }
        $this->_db->trans_rollback();
        throw new \myConf\Exceptions\DbTransactionException('DB_TRANS_ERROR', 'Tables transaction exception. Closest SQL: ' . $this->_db->last_query() . '  .', 10002);
    }

    /**
     * 事务的当前状态（正常或者错误）
     * @return bool
     */
    public function trans_status(): bool
    {
        return $this->_db->trans_status();
    }
}