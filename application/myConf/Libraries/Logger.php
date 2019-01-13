<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/26
     * Time: 15:55
     */

    namespace myConf\Libraries;

    use myConf\Libraries\DbHelper;

    class Logger {

        const operation_login = 'login';
        const operation_logout = 'logout';
        const operation_reset_password = 'reset-pwd';
        const operation_login_admin = 'login-a';
        const operation_create_conference = 'new-conf';
        const operation_delete_conference = 'del-conf';
        const operation_new_admin_role = 'new-admin';

        /**
         * @param string $operation
         * @param string $ip
         * @param string $description
         */
        public static function log_sensitive_operation(string $operation, string $ip, string $description = '') {
            DbHelper::insert(DbHelper::make_table('logs'), [
                    'log_ip_addr' => ip2long($ip),
                    'log_type' => 'app',
                    'log_action' => $operation,
                    'log_desc' => $description,
                ]);
        }
    }