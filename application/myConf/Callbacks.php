<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2019/5/5
     * Time: 22:16
     */

    namespace myConf;

    use \myConf\Libraries\DbHelper;

    class Callbacks {

        private static $_instance = null;

        public static function get_instance() : Callbacks {
            if (!isset(self::$_instance)) {
                self::$_instance = new Callbacks();
            }
            return self::$_instance;
        }

        private function __construct() {
            $this->db_table = DbHelper::make_table('callbacks');
        }

        /**
         * @param string $callback_tag
         * @param string $callback_class
         * @param array $callback_args
         * @param bool $exec_only_once
         * @param bool $exec_async
         */
        public function add(string $callback_tag,
                            string $callback_class,
                            array $callback_args,
                            bool $exec_only_once = true,
                            bool $exec_async = false) : void {
            DbHelper::insert($this->db_table, [
                'callback_tag' => $callback_tag,
                'callback_class' => $callback_class,
                'callback_args' => serialize($callback_args),
                'callback_tstamp' => time(),
                'callback_once' => $exec_only_once === true ? 1 : 0,
                'callback_async' => $exec_async === true ? 1 : 0,
            ]);
        }

        /**
         * @param string $callback_tag
         * @param array|null $environment
         */
        public function exec(string $callback_tag,
                             array $environment = null) : void {
            $callbacks = DbHelper::fetch_all($this->db_table, ['callback_tag' => $callback_tag]);
            foreach ($callbacks as $callback) {
                if ($callback['callback_async'] === 1) {
                    //异步执行callback
                    //TODO 使用 Swoole 进行 异步处理
                    continue;
                } else {
                    //同步执行
                    $callback_class = '\\myConf\\Callbacks\\' . $callback['callback_class'];
                    if (class_exists($callback_class) === true) {
                        $class = new $callback_class(unserialize($callback['callback_args']), $environment);
                    } else {
                        log_message('info', sprintf('Callback which tag is \'%s\' could not be executed because of missing callback handler \'%s\'', $callback['callback_tag'], $callback['callback_class']));
                    }
                }

                if ($callback['callback_once'] === 1) {
                    DbHelper::delete($this->db_table, ['callback_id' =>  $callback['callback_id']]);
                }
            }
        }

    }