<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/22
     * Time: 15:44
     */

    namespace myConf\Caches;

    class RedisDriver implements ICacheDriver {

        private $_redis_object;

        private static $_instance = null;

        public static function instance() : RedisDriver {
            if (self::$_instance === null) {
                self::$_instance = new RedisDriver();
            }
            return self::$_instance;
        }

        /**
         * 禁止克隆
         */
        private function __clone() {

        }

        /**
         * RedisDriver constructor.
         * 禁止外部调用构造函数。
         */
        private function __construct() {
            $this->_redis_object = new \Redis();
            //要求安装本地redis服务器
            $this->_redis_object->connect('127.0.0.1');
        }

        /**
         * @param string $prefix
         * @param string $key
         * @param $value
         * @param int $ttl
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function set(string $prefix, string $key, $value, int $ttl = 0) : void {
            $data = serialize(array('expires' => ($ttl === 0 ? 0 : time() + $ttl), 'data' => serialize($value)));
            if ($this->_redis_object->hSet($prefix, $key, $data) === false) {
                throw $this->_exceptions_driver($key, 'set');
            }
        }

        /**
         * @param string $prefix
         * @param string $key
         * @return mixed
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\CacheMissException
         */
        public function get(string $prefix, string $key) {
            if ($this->_redis_object->hExists($prefix, $key) === false) {
                throw $this->_exceptions_miss($key);
            }
            $result = $this->_redis_object->hGet($prefix, $key);
            if ($result === false) {
                throw $this->_exceptions_driver($key, 'get');
            }
            $cache_obj = unserialize($result);
            if (time() > $cache_obj['expires']) {
                if ($this->_redis_object->hDel($prefix, $key) === false) {
                    throw $this->_exceptions_driver($key, 'delete');
                }
                throw $this->_exceptions_miss($key);
            }
            return unserialize($cache_obj['data']);
        }

        /**
         * @param string $prefix
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function clear(string $prefix) : void {
            if ($this->_redis_object->flushAll() === false) {
                throw $this->_exceptions_driver('', 'clear');
            }
        }

        /**
         *
         */
        public function optimize() : void {
            // TODO: Implement optimize() method.
            // dummy method
        }

        /**
         * @param string $prefix
         * @param string $key
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function increase(string $prefix, string $key) : void {
            if ($this->_redis_object->incr($key) === false) {
                throw $this->_exceptions_driver($key, 'increase');
            }
        }

        /**
         * @param string $prefix
         * @param string $key
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function decrease(string $prefix, string $key) : void {
            if ($this->_redis_object->decr($key) === false) {
                throw $this->_exceptions_driver($key, 'decrease');
            }
        }

        /**
         * @param string $prefix
         * @param string $key
         * @throws \myConf\Exceptions\CacheDriverException
         */
        public function delete(string $prefix, string $key) : void {
            if ($this->_redis_object->delete($key) === false) {
                throw $this->_exceptions_driver($key, 'delete');
            }
        }

        /**
         * @return array
         */
        public function info() : array {
            // TODO: Implement info() method.
            return array('redis' => $this->_redis_object->info());
        }

        private function _exceptions_miss(string $key) : \myConf\Exceptions\CacheMissException {
            return new \myConf\Exceptions\CacheMissException('CACHE_MISS', 'Key "' . $key . '" does not in the cache.');
        }

        private function _exceptions_driver(string $key, string $operation) : \myConf\Exceptions\CacheDriverException {
            return new \myConf\Exceptions\CacheDriverException('REDIS_DRIVER_ERROR', 'An error occurred when trying to ' . $operation . ' the key "' . $key . '". Message : ' . $this->_redis_object->getLastError());
        }
    }