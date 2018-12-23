<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2018/12/23
     * Time: 18:34
     */

    namespace myConf\Services;

    class Document extends \myConf\BaseService {
        public function __construct() {
            parent::__construct();
        }

        /**
         * 得到指定document_id的document的内容
         * @param int $document_id
         * @return array
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DocumentNotExistsException
         */
        public function get_content(int $document_id) : array {
            $document_content = $this->Models->Document->get_by_id($document_id);
            if (empty($document_content)) {
                throw new \myConf\Exceptions\DocumentNotExistsException('DOC_NOT_FOUND', 'The document with id"' . strval($document_id) . '" does not exist.');
            }
            return [
                'document_html' => $document_content['document_html'],
                'title' => $document_content['document_title'],
            ];
        }
    }