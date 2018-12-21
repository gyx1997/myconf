<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/13
 * Time: 23:09
 */

class sConference extends CF_Service
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $conference_id
     * @param $category_id
     * @return \sConference\sConferenceHomepageRet
     * @throws \sConference\CategoryNotFoundException
     */
    public function homepage($conference_id, $category_id)
    {
        if ($category_id === 0) {
            $category_id = $this->mCategory->get_first_category_id($conference_id);
        } else {
            if (!$this->mCategory->has_category($category_id)) {
                throw new \sConference\CategoryNotFoundException('This category does not exists or has been deleted.');
            }
        }
        $categories = $this->mCategory->get_all_categories($conference_id, TRUE);
        $category_document = $this->mDocument->get_first_document_from_category($category_id);
        return new sConference\sConferenceHomepageRet($categories, $category_document);
    }

    public function management_overview_submit()
    {

    }

    public function management_overview_display()
    {

    }
}