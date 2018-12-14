<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/14
 * Time: 12:44
 */

namespace sAccount;
/**
 * Class sAccountUserFullInfoRet
 * @package sAccount
 * @property int $user_id
 * @property string $user_name
 * @property string $user_email
 * @property string $user_phone
 * @property string $user_avatar
 * @property string $user_organization
 * @property array $assigned_scholar_info
 */
class sAccountUserFullInfoRet
{
    public function __construct(int $user_id, string $user_name, string $user_email, string $user_phone, string $user_avatar, string $user_organization, array $assigned_scholar_info = array())
    {
        $this->user_id = $user_id;
        $this->user_name = $user_name;
        $this->user_email = $user_email;
        $this->user_phone = $user_phone;
        $this->user_avatar = $user_avatar;
        $this->user_organization = $user_organization;
        $this->assigned_scholar_info = $assigned_scholar_info;
    }
}