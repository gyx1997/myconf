<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/17
 * Time: 13:38
 */

namespace myConf;

use myConf\Exceptions\CacheDriverException;

/**
 * Class Cache
 * @package myConf
 */
class Cache
{
    const CACHE_DIR = ENVIRONMENT === 'production' ? '/server/cache/data/' : APPPATH . 'cache' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;

    private $_prefix;
    private $_redis;

    private $_driver;

    /**
     * Cache constructor.
     * @param string $prefix
     * @param string $driver
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function __construct(string $prefix = 'def', string $driver = 'redis')
    {
        if ($driver === 'redis') {
            $this->_driver = \myConf\Caches\RedisDriver::instance();
        } else {
            throw new \myConf\Exceptions\CacheDriverException('UNKNOWN_DRIVER', 'Unknown Cache Driver "' . $driver . '"');
        }
        $this->_prefix = $prefix;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws CacheDriverException
     * @throws Exceptions\CacheMissException
     */
    public function get(string $key)
    {
        return $this->_driver->get($this->_prefix, $key);
    }

    /**
     * @param string $key
     * @param $data
     * @param int $ttl
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function set(string $key, $data, int $ttl = 0): void
    {
        $this->_driver->set($this->_prefix, $key, $data, $ttl);
    }

    /**
     * @param string $key
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function delete(string $key): void
    {
        $this->_driver->delete($this->_prefix, $key);
    }
}