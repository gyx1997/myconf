<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/14
 * Time: 0:05
 */

/**
 * Class ConferenceDocument
 * @property int $id
 * @property string $title
 * @property string $html
 */
class ConferenceDocument extends MyConfBaseClass
{
    public function __construct(int $document_id, string $document_title, string $document_html)
    {
        $this->title = $document_title;
        $this->html = $document_html;
        $this->id = $document_id;
    }
}