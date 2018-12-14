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
 * @property ConferenceDocument $document
 */
class sConferenceHomepageRet extends MyConfBaseClass
{
    public function __construct(array $category_list, ConferenceDocument $document)
    {
        $this->document = $document;
        $this->category_list = $category_list;
    }
}