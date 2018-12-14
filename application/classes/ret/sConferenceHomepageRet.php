<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/14
 * Time: 0:03
 */

/**
 * Class sConferenceHomepageRet
 * @property array $category_list
 * @property string $document_html
 * @property string $document_title
 * @property int $document_id
 */
class sConferenceHomepageRet
{
    public function __construct(array $category_list, array $document)
    {
        $this->document_id = $document['document_id'];
        $this->document_html = $document['document_html'];
        $this->document_title = $document['document_title'];
        $this->category_list = $category_list;
    }
}