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

        /**
         * @param int $document_id
         * @param string $title
         * @param string $content
         * @throws \myConf\Exceptions\CacheDriverException
         * @throws \myConf\Exceptions\DbTransactionException
         */
        public function submit_content(int $document_id, string $title, string $content) : void {
            //找到文章中的附件
            $files = $this->_content_split_attachments($content);
            $images = $this->_content_split_images($content);
            $image_aids = array();
            foreach ($images as $image) {
                $id = $this->Models->Attachment->get_id_from_filename($image);
                $id !== 0 && $image_aids [] = $id;
            }
            $aids = array_merge($files, $image_aids);
            //更新文章内容（含附件）
            $this->Models->Document->update_content_by_id($document_id, $content, $title, $aids);
        }

        /**
         * @param string $content
         * @return array
         */
        private function _content_split_attachments(string $content) : array {
            $aids = array();
            //以下载的特征url进行正则匹配
            $regex_a = "/\"\/attachment\/get\/.*?\/\?aid=.*?\"/";
            $array_links = array();
            if (preg_match_all($regex_a, $content, $array_links)) {
                //将本次新加入的附件标记位使用的附件
                foreach ($array_links[0] as $link) {
                    $link = substr($link, 0, strlen($link) - 1);
                    $aid = intval(substr($link, strpos($link, '?aid=') + 5));
                    $aids [] = $aid;
                }
            }
            return $aids;
        }

        /**
         * @param string $content
         * @return array
         */
        private function _content_split_images(string $content) : array {
            $images = array();
            //以图片的特征url进行正则匹配
            $regex_a = "/src=\"\/data\/attachment\/.*?\"/";
            $array_links = array();
            if (preg_match_all($regex_a, $content, $array_links)) {
                //将本次新加入的附件标记位使用的附件
                foreach ($array_links[0] as $link) {
                    $link = substr($link, 22, strlen($link) - 23);
                    $images [] = $link;
                }

            }
            return $images;
        }
    }