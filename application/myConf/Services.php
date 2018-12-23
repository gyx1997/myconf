<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/15
 * Time: 18:24
 */

namespace myConf;


/**
 * Class Services 微服务管理器
 * @package myConf
 * @property-read \myConf\Services\Account $Account
 * @property-read \myConf\Services\Config $Config
 * @property-read \myConf\Services\Conference $Conference
 * @property-read \myConf\Services\Document $Document
 */
class Services
{
    /**
     * @var array 当前加载的服务
     */
    private $_services = array();

    /**
     * 返回指定的微服务实例对象（类名大小写敏感）
     * @param string $service_name
     * @return BaseService
     */
    public function __get(string $service_name): \myConf\BaseService
    {
        if (!isset($this->_services[$service_name])) {
            $class_name = '\\myConf\\Services\\' . $service_name;
            $this->_services[$service_name] = new $class_name();
        }
        return $this->_services[$service_name];
    }
}