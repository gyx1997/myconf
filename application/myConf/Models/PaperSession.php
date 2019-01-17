<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019/1/16
 * Time: 14:50
 */

namespace myConf\Models;


use myConf\Exceptions\PaperSessionAlreadyUsedException;

/**
 * Class Session
 * @package myConf\Models
 * @author _g63<522975334@qq.com>
 * @version 2019.1
 */
class PaperSession extends \myConf\BaseModel
{
    /**
     * Session constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 添加一个session
     * @param int $conference_id
     * @param string $session_text
     * @param int $session_type
     * @return int
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function add_session(int $conference_id, string $session_text, int $session_type) : int {
        $display_order = $this->Tables->PaperSessions->get_conference_sessions_max_display_order($conference_id) + 1;
        $id = $this->Tables->PaperSessions->insert([
            'session_conference_id' => $conference_id,
            'session_type' => $session_type,
            'session_text' => $session_text,
            'session_display_order' => $display_order,
        ]);
        $this->Tables->PaperSessions->delete_conference_sessions_cache($conference_id);
        return $id;
    }

    /**
     * @param int $session_id
     * @throws PaperSessionAlreadyUsedException
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function delete_session(int $session_id) : void {
        if ($this->Tables->Papers->exist_using_where(['paper_suggested_session' => strval($session_id)])) {
            throw new PaperSessionAlreadyUsedException('PAPER_SESSION_ALREADY_USED', 'Session with id "' . $session_id . '" has already used by some papers.');
        }
        $session = $this->Tables->PaperSessions->get(strval($session_id));
        if (empty($session)) {
            return;
        }
        $this->Tables->PaperSessions->delete($session_id);
        return;
    }

    /**
     * @param int $session_id
     * @param int $session_type
     * @param string $session_text
     * @param int $session_display_order
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function update_session(int $session_id, int $session_type, string $session_text, int $session_display_order) : void {
        $data = [
            'session_text' => $session_text,
            'session_type' => $session_type,
        ];
        $session_display_order > 0 && $data['session_display_order'] = $session_display_order;
        $this->Tables->PaperSessions->set($session_id, $data);
    }

    /**
     * 将session上移一位
     * @param int $session_id
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbTransactionException
     */
    public function move_up(int $session_id) : void {
        $conference_id = $this->Tables->PaperSessions->get(strval($session_id))['session_conference_id'];
        $sessions = $this->Tables->PaperSessions->get_conference_sessions($conference_id);
        $i = 1;
        foreach($sessions as $sess_id) {
            if (intval($sess_id) === $session_id) {
                break;
            }
            $i++;
        }
        //如果不是第一个，那么需要更新
        if ($i != 1) {
            $j = 1;
            \myConf\Libraries\DbHelper::begin_trans();
            foreach ($sessions as $sess_id) {
                $this->Tables->PaperSessions->set($sess_id, array('session_display_order' => $j == $i - 1 ? $i : ($j == $i ? $i - 1 : $j)));
                $j++;
            }
            \myConf\Libraries\DbHelper::end_trans();
        }
    }

    /**
     * @param int $session_id
     * @throws \myConf\Exceptions\CacheDriverException
     * @throws \myConf\Exceptions\DbTransactionException
     */
    public function move_down(int $session_id) : void {
        $conference_id = $this->Tables->PaperSessions->get(strval($session_id))['session_conference_id'];
        $sessions = $this->Tables->PaperSessions->get_conference_sessions($conference_id);
        $i = 1;
        $session_count = count($sessions);
        foreach($sessions as $sess_id) {
            if (intval($sess_id) === $session_id) {
                break;
            }
            $i++;
        }
        //如果不是最后一个，也需要更新
        if($i < $session_count){
            $j = 1;
            \myConf\Libraries\DbHelper::begin_trans();
            foreach ($sessions as $sess_id) {
                $this->Tables->PaperSessions->set($sess_id, array('session_display_order' => $j === $i + 1 ? $i : ($j === $i ? $i + 1 : $j)));
                $j++;
            }
            \myConf\Libraries\DbHelper::end_trans();
        }
    }

    /**
     * @param int $session_id
     * @return bool
     */
    public function exist(int $session_id) : bool{
        return $this->Tables->PaperSessions->exist(strval($session_id));
    }

    /**
     * @param int $conference_id
     * @param int $session_id
     * @return bool
     * @throws \myConf\Exceptions\CacheDriverException
     */
    public function exist_in_conference(int $conference_id, int $session_id) : bool {
        $sess = $this->Tables->PaperSessions->get($session_id);
        return !empty($sess) && $sess['session_conference_id'] === $conference_id;
    }

}