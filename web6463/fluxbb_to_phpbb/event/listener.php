<?php

namespace web6463\fluxbb_to_phpbb\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbb\request\request_interface;
use phpbb\user;
use phpbb\config\config;

class listener implements EventSubscriberInterface
{
    protected $request;
    protected $user;
    public function __construct(request_interface $request, user $user)
    {
        $this->request = $request;
        $this->user = $user;
    }

    public static function getSubscribedEvents()
    {
        return [
            'core.auth_login_session_create_before' => 'check_custom_login',
        ];
    }

    public function check_custom_login($event)
    {
        $login_data = $event->get_data();
        if(isset($login_data['login'])){
            $login_data = $login_data['login'];
            $username = $this->request->variable('username', '', true);
            $password = $this->request->variable('password', '', true);
            if ($login_data['status'] === LOGIN_ERROR_PASSWORD && $login_data['error_msg'] === 'LOGIN_ERROR_PASSWORD') {
                $user_row = $login_data['user_row'];
                if ($this->fluxx_user_login($username, $password)) {
                    // انجام عملیات خاص
                    $this->user->session_create($user_row['user_id'], false, false);
                    $this->change_password($user_row['user_id'], $password);
                    // هدایت به صفحه مورد نظر (مثلاً صفحه اصلی)
                    redirect(append_sid('index.php'));
                }
            }
        }
    }
    private function change_password($user_id, $password = ''){
        global $db, $phpbb_container;
        $passwords_manager = $phpbb_container->get('passwords.manager');
        $hash = $passwords_manager->hash($password);
        // Update the password in the users table to the new format
        $sql = 'UPDATE ' . USERS_TABLE . "
            SET user_password = '" . $db->sql_escape($hash) . "'
            WHERE user_id = {$user_id}";
        $db->sql_query($sql);        
    }
    private function get_flux_user($username){
        global $config, $db;
        $fluxbb_db_prefix = $config->offsetGet('fluxbb_to_phpbb_tb_prefix' );
        $sql = 'SELECT id, username, password
                FROM '.$fluxbb_db_prefix.'users
                WHERE username = "' . $username.'"';
            $result = $db->sql_query($sql);
            $user_row = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);
            if(isset($user_row['password'])){
                return $user_row['password'];
            } else {
                return 2;
            }
        
    }
    private function flux_password_verify($pass, $hash)
    {
        if ($hash[0] == '#')
        {
            // MD5 from 1.2
            if (substr($hash, 0, 5) == '#MD5#')
            {
                $pass = md5($pass);
                $hash = substr($hash, 5);
            }
            // SHA1-With-Salt from 1.3
            else if (substr($hash, 0, 8) == '#SHA1-S#')
            {
                preg_match('/^#SHA1-S#(.+)#(.+)$/', $hash, $matches);
                list(, $salt, $hash) = $matches;
                $pass = sha1($salt.sha1($pass));
            }
            // SHA1-Without-Salt from 1.4
            else if (substr($hash, 0, 6) == '#SHA1#')
            {
                $pass = sha1($pass);
                $hash = substr($hash, 6);
            }
        }

        // Support current password standard
        return password_verify($pass, $hash);
    }    
    private function fluxx_user_login($username, $password)
    {
        $has_password = password_hash($password, PASSWORD_DEFAULT, array('cost' => 10));
        $flux_user_pass =  $this->get_flux_user($username);
        $is_password_authorized = hash_equals($password, $flux_user_pass);
        $is_hash_authorized = $this->flux_password_verify($password, $flux_user_pass);
        return $is_hash_authorized;
    }
}
