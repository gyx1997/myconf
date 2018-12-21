<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/16
     * Time: 0:16
     */

    namespace myConf\Tables;

    class ConferenceMembers extends \myConf\BaseTable {

        /**
         * 表的字段
         * @var array
         */
        private static $fields = array('id', 'conference_id', 'user_id', 'user_role',);

        /**
         * ConfMember constructor.
         */
        public function __construct() {
            parent::__construct();
        }

        /**
         * 返回主键名。relation table this function for dummy use.
         * @return string
         */
        public static function primary_key() : string {
            return 'id';
        }

        /**
         * 返回当前表名
         * @return string
         */
        public static function table() : string {
            return self::make_table('conference_members');
        }

        /**
         * 返回当前表的所有字段
         * @return array
         */
        public static function fields() : array {
            return self::$fields;
        }

        /**
         * 获取会议的成员。获取数据库所有满足条件的记录
         * @param $conference_id
         * @return array
         */
        public function get_conference_members(int $conference_id) : array {
            //NOTE:使用连表查询，写死了表明，后面需要注意
            //NOTE:过滤角色信息，请在上一层处理，因为返回的是数组展开的字符串
            $scholar_table = self::make_table('scholars');
            $user_table = self::make_table('users');
            $this_table = self::table();
            $sql = "SELECT $scholar_table.scholar_first_name AS first_name, $scholar_table.scholar_last_name AS last_name, $this_table.user_id, $this_table.conference_id, $this_table.user_role, $user_table.user_name, $user_table.user_email FROM $this_table INNER JOIN $user_table ON $user_table.user_id = $this_table.user_id INNER JOIN $scholar_table ON $scholar_table.scholar_email = $user_table.user_email WHERE $user_table.user_id = $this_table.user_id AND $this_table.conference_id = " . strval($conference_id) . " ORDER BY $this_table.id ASC";
            $data = $this->fetch_all_raw($sql);
            foreach ($data as &$item) {
                $item['user_roles'] = explode(',', $item['user_role']);
                unset($item['user_role']);
            }
            return $data;
        }

        /**
         * 得到某个会议的参与人数
         * @param int $conference_id
         * @return int
         */
        public function get_conference_members_count(int $conference_id) : int {
            $sql_result = $this->fetch_first_raw('SELECT COUNT(1) FROM ' . self::table() . ' WHERE conference_id = ' . strval($conference_id));
            return intval($sql_result['COUNT(1)']);
        }

        /**
         * 得到用户参与的会议列表
         * @param int $user_id
         * @param int $start
         * @param int $limit
         * @return array
         */
        public function get_conferences_from_user(int $user_id, int $start = 0, int $limit = 10) : array {
            return $this->fetch_all(array('user_id' => $user_id), '', '', $start, $limit);
        }

        /**
         * 判断一个用户是否加入了这个会议。
         * @param $user_id
         * @param $conference_id
         * @return bool
         */
        public function user_joint_in_conference(int $user_id, int $conference_id) : bool {
            return $this->exist_using_where(array('user_id' => $user_id, 'conference_id' => $conference_id));
        }

        /**
         * 将用户加入会议
         * @param int $user_id
         * @param int $conference_id
         * @return int
         */
        public function user_join_in_conference(int $user_id, int $conference_id) : int {
            $this->db->insert(self::table(), array('user_id' => $user_id, 'conference_id' => $conference_id, 'user_role' => 'scholar'));
            return $this->db->insert_id();
        }

        /**
         * 将用户移出会议
         * @param int $user_id
         * @param int $conference_id
         */
        public function user_remove_from_conference(int $user_id, int $conference_id) : void {
            $this->db->where('user_id', $user_id);
            $this->db->where('conference_id', $conference_id);
            $this->db->delete(self::table());
        }

        /**
         * 得到用户角色
         * @param int $user_id
         * @param int $conference_id
         * @return array
         */
        public function get_user_roles_in_conference(int $user_id, int $conference_id) : array {
            $user = $this->fetch_first(array('user_id' => $user_id, 'conference_id' => $conference_id));
            return explode(',', $user['user_role']);
        }

        /**
         * 从参会者身上移除角色
         * @param int $user_id
         * @param int $conference_id
         * @param array $roles
         */
        public function set_user_roles_in_conference(int $user_id, int $conference_id, array $roles) : void {
            $this->db->where('user_id', $user_id);
            $this->db->where('conference_id', $conference_id);
            $this->db->update(self::table(), array('user_role' => implode(',', $roles)));
        }

        /**
         * dummy method
         * @deprecated
         * @param string $pk_val
         * @return array
         */
        public function get(string $pk_val) : array {
            return parent::get($pk_val);
        }

        /**
         * dummy method
         * @deprecated
         * @param string $pk_val
         * @param array $data
         */
        public function set(string $pk_val, array $data = array()) : void {
            parent::set($pk_val, $data);
        }
    }