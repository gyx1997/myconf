<?php
    /**
     * Created by PhpStorm.
     * User: 52297
     * Date: 2019/2/1
     * Time: 10:16
     */

    namespace myConf\Libraries;

    class Email {

        private static $mail_service = 'SendCloud';

        private static $send_cloud_path = APPPATH . DIRECTORY_SEPARATOR . 'myConf' . DIRECTORY_SEPARATOR . 'ThirdParty' . DIRECTORY_SEPARATOR . 'SendCloud' . DIRECTORY_SEPARATOR;

        /**
         * @var string SendCloud用户ID号
         */
        private static $send_cloud_user_id;

        /**
         * @var string SendCloud API Key
         */
        private static $send_cloud_api_key;

        /**
         * 初始化
         */
        public static function init() : void {
            if (ENVIRONMENT === 'production' || 1) {
                self::$mail_service = 'SendCloud';
                self::$send_cloud_user_id = 'myconf_app_trigger';
                self::$send_cloud_api_key = 'E8G9wHzTAmrtBHPn';
            } else {
                self::$mail_service = 'CodeIgniterSMTP';
            }
        }

        /**
         * 通过合适的渠道投递一封电子邮件
         * @param string $from
         * @param string $to
         * @param string $subject
         * @param string $content
         * @param string $from_name
         */
        public static function send_mail(string $from, string $from_name, string $to, string $subject, string $content) : void {
            if (self::$mail_service === 'SendCloud') {
                require_once self::$send_cloud_path . 'SendCloud.php';
                require_once self::$send_cloud_path . 'util/HttpClient.php';
                require_once self::$send_cloud_path . 'util/Mail.php';
                require_once self::$send_cloud_path . 'util/Mimetypes.php';
                var_dump(self::$send_cloud_user_id);
                $sc = new \SendCloud(self::$send_cloud_user_id, self::$send_cloud_api_key, 'v2');
                $mail = new \Mail();
                $mail->setFrom($from);
                $mail->addTo($to);
                $mail->setFromName($from_name);
                $mail->setSubject($subject);
                $mail->setContent($content);
                $result = json_decode($sc->sendCommon($mail));
                var_dump($result);
            } else if(self::$mail_service === 'CodeIgniterSMTP') {
                $CI = &get_instance();
                $CI->email->from($from, $from_name);
                $CI->email->to($to);
                $CI->email->subject($subject);
                $CI->email->message($content);
                $CI->email->send();
            }
        }
    }