<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/17
 * Time: 10:33
 */

namespace myConf\Controllers;

/**
 * 管理面板控制器
 * Class Admin
 * @package myConf\Controllers
 */
class Admin extends \myConf\BaseController
{
    public function __construct()
    {
        parent::__construct();
        //throw new \myConf\Exceptions\HttpStatusException(404, 'NOT_FOUND', 'The page you are requesting is not found on this server.');
    }

    public function index(): void
    {

    }

    public function sys(): void
    {
        if ($this->_action === 'opcache' && $this->_do === 'reset') {
            opcache_reset();
        } else if ($this->_action === 'dcache' && $this->_do === 'reset') {
            \myConf\Cache::clear();
        } else if ($this->_action === 'template' && $this->_do === 'clear') {
            \myConf\Libraries\NativeTpl::clear_compiled_template();
        }
    }
}