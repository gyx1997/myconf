<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/15
     * Time: 16:59
     */

    namespace myConf\Tables;

    class Users extends \myConf\BaseTable {
        private static $fields_extra = array('avatar', 'organization');

        public function __construct() {
            parent::__construct();
        }

        public static function primary_key() : string {
            return 'user_id';
        }

        public static function fields() : array {
            // TODO: Implement fields() method.
        }

        public static function table() : string {
            return self::make_table('users');
        }

        public function set(string $pk_val, array $data = array()) : void {

            //排除主键和序列化的键,因此要把其他键和值复制过来
            $basic_data = array_filter($data, function ($key) {
                return !($key === 'user_id' && $key === 'user_extra');
            }, ARRAY_FILTER_USE_KEY);
            //只选择需要序列化的键值对
            $new_data_extra = array();
            foreach (self::$fields_extra as $field) {
                isset($data[$field]) && $new_extra[$field] = $data[$field];
            }
            parent::set($pk_val, $this->pack_serialized_field($basic_data, 'conference_extra', $new_data_extra));
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
            return $this->insert(array('user_name' => $username, 'user_email' => $email, 'user_password' => $password, 'password_salt' => $salt, 'is_frozen' => 1, 'user_role' => 'user', 'user_extra' => serialize(array('avatar' => '', 'organization' => '',)),));
        }

        /**
         * 激活用户
         * @param $user_id
         */
        public function activate($user_id) {
            $this->db->where('user_id', $user_id);
            $this->db->update(self::table(), array('is_frozen' => 0));
        }

        /**
         * 重写父类方法，根据用户id获取用户信息
         * @param string $pk_val
         * @return array
         */
        public function get(string $pk_val) : array {
            $data = parent::get($pk_val);
            if (empty($data)) {
                return array();
            }
            $data['user_role'] = explode(',', $data['user_role']);
            $extra_unserialized = unserialize($data['user_extra']);
            foreach ($extra_unserialized as $key => $value) {
                $data[$key] = $value;
            }
            $data['user_extra'] = null;
            return $data;
        }

        /**
         * 根据用户名获取用户信息
         * @param $username
         * @return array
         */
        public function get_by_username($username) {
            return $this->fetch_first(array('user_name' => $username));
        }

        /**
         * 更新密码
         * @param string $user_email
         * @param string $password
         * @param string $salt
         */
        public function update_user_password_by_email(string $user_email, string $password, string $salt) : void {
            $this->db->where('user_email', $user_email);
            $this->db->update($this->_table(), array('user_password' => $password, 'password_salt' => $salt));
            return;
        }

        /**
         * 根据用户email获取用户信息
         * @param $email
         * @return array
         */
        public function get_by_email($email) {
            return $this->fetch_first(array('user_email' => $email));
        }

        /**
         * 根据username判断用户是否存在
         * @param $username
         * @return bool
         */
        public function exist_by_username($username) {
            return $this->exist_using_where(array('user_name' => $username));
        }

        /**
         * 根据电子邮件判断用户是否存在
         * @param $email
         * @return bool
         */
        public function exists_email($email) {
            return $this->exist_using_where(array('user_email' => $email));
        }
    }