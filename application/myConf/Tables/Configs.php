<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/15
     * Time: 18:34
     */

    namespace myConf\Tables;

    class Configs extends \myConf\BaseTable {
        /**
         * Configs constructor.
         */
        private $_config_data = array();

        private static $fields = array('k', 'v');

        public function __construct() {
            parent::__construct();
            $tmp = $this->fetch_all();
            foreach ($tmp as $t) {
                $this->_config_data[$t['k']] = $t;
            }
        }

        /**
         * 得到当前的主键名
         * @return string
         */
        public function primary_key() : string {
            return 'k';
        }

        /**
         * 得到当前表名
         * @return string
         */
        public function table() : string {
            return $this->make_table('configs');
        }

        /**
         * 得到当前表的所有字段
         * @return array
         */
        public function fields() : array {
            return self::$fields;
        }

        /**
         * 重写父类方法，只从数据库读一次config，其余从临时变量读取
         * @param string $key
         * @return array
         */
        public function get(string $key) : array {
            return isset($this->_config_data[$key]) ? $this->_config_data[$key] : array();
        }

        /**
         * 重写父类方法，想config表写入值
         * @param string $key
         * @param array $value
         */
        public function set(string $key, array $data = array()) : void {
            parent::set($key, array('v' => $data['v']));
            $this->_config_data[$key] = $data['v'];
        }
    }