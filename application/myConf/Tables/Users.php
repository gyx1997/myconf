<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/15
     * Time: 16:59
     */

    namespace myConf\Tables;

    use \myConf\Libraries\DbHelper;

    class Users extends \myConf\BaseEntityTable {
        private static $fields_extra = array('avatar', 'organization');

        public function __construct() {
            parent::__construct();
        }

        public function primary_key() : string {
            return 'user_id';
        }

        public function fields() : array {
            // TODO: Implement fields() method.
        }

        public function table() : string {
            return DbHelper::make_table('users');
        }

        /**
         * 用户注册时创建一个用户
         * @param $username
         * @param $password
         * @param $email
         * @param $salt
         * @return int
         */
        public function create($username, $password, $email, $salt) {
            return $this->insert([
                    'user_name' => $username,
                    'user_email' => $email,
                    'user_password' => $password,
                    'password_salt' => $salt,
                    'is_frozen' => 1,
                    'user_role' => 'user',
                    'user_avatar' => '',
                ]);
        }

        /**
         * 激活用户
         * @param $user_id
         */
        public function activate($user_id) {
            DbHelper::update($this->table(), ['is_frozen' => 0], ['user_id' => $user_id]);
        }

        /**
         * 根据用户名获取用户信息
         * @param $username
         * @return array
         */
        public function get_by_username(string $username) : array {
            return DbHelper::fetch_first($this->table(), ['user_name' => $username]);
        }

        /**
         * 更新密码
         * @param string $user_email
         * @param string $password
         * @param string $salt
         */
        public function update_user_password_by_email(string $user_email, string $password, string $salt) : void {
            DbHelper::update($this->table(), [
                    'user_password' => $password,
                    'password_salt' => $salt,
                ], ['user_email' => $user_email]);
        }

        /**
         * 根据用户email获取用户信息
         * @param $email
         * @return array
         */
        public function get_by_email(string $email) : array {
            return DbHelper::fetch_first($this->table(), ['user_email' => $email]);
        }

        /**
         * 根据username判断用户是否存在
         * @param string $username
         * @return bool
         */
        public function exist_by_username(string $username) : bool {
            return $this->exist_using_where(array('user_name' => $username));
        }

        /**
         * 根据电子邮件判断用户是否存在
         * @param string $email
         * @return bool
         */
        public function exist_by_email(string $email) {
            return $this->exist_using_where(array('user_email' => $email));
        }
    }