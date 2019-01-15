<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2019/1/8
     * Time: 0:12
     */

    namespace myConf\Controllers;

    class Home extends \myConf\BaseController {

        /**
         * @throws \myConf\Exceptions\SendRedirectInstructionException
         */
        public function index() : void {
            $this->_redirect_to('/conference/csqrwc2019/');
        }
    }