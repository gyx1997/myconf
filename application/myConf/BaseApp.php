<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/22
     * Time: 10:43
     */

    namespace myConf;

    class BaseApp {
        private $_loaded_classes = array();

        public function __construct() {

        }

        /**
         * 魔术方法，加载类
         * @param $key
         * @return mixed
         */
        public function __get($key) {
            if (!isset($this->_loaded_classes[$key])) {
                $class_name = '\\myConf\\' . $key;
                $this->_loaded_classes[$key] = new $class_name();
            }
            return $this->_loaded_classes[$key];
        }
    }