<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/21
 * Time: 1:21
 */

namespace myConf\Services;


class ConferenceMember extends \myConf\BaseService
{
    /**
     * ConferenceMember constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $conference_id
     * @param array $roles_restrict
     * @param string $name_restrict
     * @param string $email_restrict
     * @return array
     */
    public function get_conference_members(int $conference_id, array $roles_restrict = array(), string $name_restrict = '', string $email_restrict = ''): array
    {
        //先定义返回结果集
        $members_data_set = array();
        $members = $this->models()->ConfMember->get_conference_members($conference_id, $email_restrict);
        foreach ($members as $member) {
            //过滤信息
            if ($name_restrict != '' && strpos($member['user_name'], $name_restrict) === FALSE) {
                continue;
            }
            if ($email_restrict != '' && $member['user_email'] !== $email_restrict) {
                continue;
            }
            $continue = FALSE;
            foreach ($roles_restrict as $role) {
                if (in_array($role, $member['user_role']) === FALSE) {
                    $continue = TRUE;
                    break;
                }
            }
            if ($continue == TRUE) {
                continue;
            }
            $members_data_set [] = $member;
        }
        return $members_data_set;
    }
}