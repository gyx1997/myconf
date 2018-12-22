<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/22
     * Time: 17:53
     */

    namespace myConf\Libraries;

    class DbHelper {

        /**
         * 得到CI的数据库对象
         * @return \CI_DB_active_record
         */
        public static function db() {
            $CI = &get_instance();
            return $CI->db;
        }

        /**
         * 开始事务
         */
        public static function begin_trans() : void {
            $CI = &get_instance();
            $CI->db->trans_strict(false);
            $CI->db->trans_begin();
        }

        /**
         * 结束事务
         * @throws \myConf\Exceptions\DbTransactionException
         */
        public static function end_trans() : void {
            $CI = &get_instance();
            if ($CI->db->trans_status() === true) {
                $CI->db->trans_commit();
                return;
            }
            $CI->db->trans_rollback();
            throw new \myConf\Exceptions\DbTransactionException('DB_TRANS_ERROR', 'Tables transaction exception. Closest SQL: ' . $CI->db->last_query() . '  .', 10002);
        }
    }