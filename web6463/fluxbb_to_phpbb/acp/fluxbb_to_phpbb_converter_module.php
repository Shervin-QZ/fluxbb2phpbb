<?php
namespace web6463\fluxbb_to_phpbb\acp;

class fluxbb_to_phpbb_converter_module
{
    /** @var string */
    public $u_action;

    /** @var string */
    public $tpl_name;

    /** @var string */
    public $page_title;

    public function main($id, $mode)
    {
        global $template, $request, $config, $cache, $db, $user, $phpbb_log, $language, $phpbb_container, $phpbb_root_path;
        $template->assign_vars(array(
            'U_ACTION'   => $this->u_action)
        );
        $action = $request->variable('action', '');
        $check_reset_users = $this->get_config($config, 'fluxbb_to_phpbb_reset_users', false );
        $check_reset_data = $this->get_config($config, 'fluxbb_to_phpbb_reset_data', false );
        $check_import_users = $this->get_config($config, 'fluxbb_to_phpbb_import_users', false );
        $check_import_forums = $this->get_config($config, 'fluxbb_to_phpbb_import_forums', false );
        $check_import_categories = $this->get_config($config, 'fluxbb_to_phpbb_import_categories', false );
        $check_import_topics = $this->get_config($config, 'fluxbb_to_phpbb_import_topics', false );
        $check_import_posts = $this->get_config($config, 'fluxbb_to_phpbb_import_posts', false );
        $check_import_bots = $this->get_config($config, 'fluxbb_to_phpbb_import_bots', false );
        $fluxbb_db_prefix = $this->get_config($config, 'fluxbb_to_phpbb_tb_prefix', 'fluxbb_' );
        $fluxbb_db_limit = $this->get_config($config, 'fluxbb_to_phpbb_tb_limit', 20 );
        $default_lang = $this->get_config($config, 'default_lang', 'en' );
        $passwords_manager = $phpbb_container->get('passwords.manager');
        switch ($mode)
        {
            case 'converter':
                if($action){
                    if ($request->is_ajax())
                    {
                        switch ($action){
                            case 'reset_users':
                                $sql = 'DELETE FROM ' . USERS_TABLE . '
                                    WHERE user_id > 2';
                                $result = $db->sql_query($sql);
                                $db->sql_freeresult($result);
                                $sql = 'DELETE FROM ' . USER_GROUP_TABLE . '
                                    WHERE user_id > 2';
                                $result = $db->sql_query($sql);
                                $db->sql_freeresult($result);
                                $sql = 'DELETE FROM ' . PROFILE_FIELDS_DATA_TABLE . '
                                    WHERE user_id > 2';
                                $result = $db->sql_query($sql);
                                $db->sql_freeresult($result);
                                $sql = 'DELETE FROM ' . BOTS_TABLE . '';
                                $result = $db->sql_query($sql);
                                $db->sql_freeresult($result);
                                $config->set('fluxbb_to_phpbb_reset_users', true );
                                $config->set('fluxbb_to_phpbb_import_users', false );
                                $config->set('fluxbb_to_phpbb_import_bots', false );
                                $config->set('fluxbb_to_phpbb_offset_users', 0 );
                                trigger_error('ACP_FLUXBB_TO_PHPBB_CONVERTER_USERS_RESETS');
                            break;
                            case 'reset_data':
                                switch ($db->get_sql_layer())
                                {
                                    case 'sqlite3':
                                        $db->sql_query('DELETE FROM ' . FORUMS_TABLE);
                                        $db->sql_query('DELETE FROM ' . POSTS_TABLE);
                                        $db->sql_query('DELETE FROM ' . TOPICS_TABLE);
                                        $db->sql_query('DELETE FROM ' . TOPICS_POSTED_TABLE);
                                        $db->sql_query('DELETE FROM ' . TOPICS_TRACK_TABLE);
                                        $db->sql_query('DELETE FROM ' . ACL_GROUPS_TABLE);
                                    break;

                                    default:
                                        $db->sql_query('TRUNCATE TABLE ' . FORUMS_TABLE);
                                        $db->sql_query('TRUNCATE TABLE ' . POSTS_TABLE);
                                        $db->sql_query('TRUNCATE TABLE ' . TOPICS_TABLE);
                                        $db->sql_query('TRUNCATE TABLE ' . TOPICS_POSTED_TABLE);
                                        $db->sql_query('TRUNCATE TABLE ' . TOPICS_TRACK_TABLE);
                                        $db->sql_query('TRUNCATE TABLE ' . ACL_GROUPS_TABLE);
                                    break;
                                }
                                $config->set('fluxbb_to_phpbb_offset_forums', 0 );
                                $config->set('fluxbb_to_phpbb_offset_categories', 0 );
                                $config->set('fluxbb_to_phpbb_offset_topics', 0 );
                                $config->set('fluxbb_to_phpbb_offset_posts', 0 );
                                $config->set('fluxbb_to_phpbb_reset_data', true );
                                $config->set('fluxbb_to_phpbb_import_forums', false );
                                $config->set('fluxbb_to_phpbb_import_categories', false );
                                $config->set('fluxbb_to_phpbb_import_topics', false );
                                $config->set('fluxbb_to_phpbb_import_posts', false );
                                trigger_error('ACP_FLUXBB_TO_PHPBB_CONVERTER_DATA_RESETS');
                            break;
                            case 'import_users':
                                if(!$check_import_users AND $check_reset_users){
                                    $sql_ary = array();
                                    $sql_user_group = array();
                                    $sql_profile_fields_data = array();
                                    $offset = (int)$this->get_config($config,'fluxbb_to_phpbb_offset_users', 0 );
                                    $result = $db->sql_query('SELECT * FROM '.$fluxbb_db_prefix.'users WHERE id > 2 ORDER BY id ASC LIMIT '.$offset.','.$fluxbb_db_limit);
                                    while ($row = $db->sql_fetchrow($result)){
                                        if($this->check_user_exist($row['id'], $row['username'], $row['email'])){
                                            continue;
                                        }
                                        $user_avatar = $this->generate_avatar_markup($row['id']);
                                        $last_visit = ($row['last_visit'])?:$row['registered'];
                                        $sql_ary[] = array(
                                            'user_id'           => (int) $row['id'],
                                            'username'          => $row['username'],
                                            'username_clean'    => $row['username'],
                                            'user_email'        => $row['email'],
                                            'user_password'     => $passwords_manager->hash($row['password']),
                                            'user_passchg'      => time(),
                                            'user_form_salt'    => unique_id(),
                                            'user_regdate'      => $row['registered'],
                                            'user_ip'           => $row['registration_ip'],
                                            'user_posts'        => ($row['num_posts'])?:0,
                                            'user_lastvisit'    => $last_visit,
                                            'user_last_active'  => $last_visit,
                                            'user_lastpost_time'=> ($row['last_post'])?:0,
                                            'user_permissions'  => '',
                                            'user_avatar'       => $user_avatar,
                                            'user_avatar_type'  => ($user_avatar)?'avatar.driver.upload':'',
                                            'user_avatar_height'=> ($user_avatar)?60:0,
                                            'user_avatar_width' => ($user_avatar)?60:0,
                                            'user_sig'          => ($row['signature'])?:'',
                                            'user_type'         => 3,
                                            'user_new'          => 0,
                                            'user_lang'         => ($this->getLanguagesList($row['language']))?:$default_lang,
                                            'group_id'          => ($this->get_group_id('REGISTERED'))?:2,
                                        );
                                        $sql_user_group[] = array(
                                            'user_id'           => (int) $row['id'],
                                            'group_id'          => ($this->get_group_id('REGISTERED'))?:2,
                                            'group_leader'      => 0,
                                            'user_pending'      => 0,
                                        );                                  
                                        $sql_profile_fields_data[] = array(
                                            'user_id'               => (int) $row['id'],
                                            'pf_phpbb_website'      => ($row['url'])?:'',
                                            'pf_phpbb_icq'          => ($row['icq'])?:'',
                                            'pf_phpbb_skype'        => ($row['msn'])?:'',
                                            'pf_phpbb_yahoo'        => ($row['yahoo'])?:'',
                                            'pf_phpbb_location'     => ($row['location'])?:'',
                                            'pf_phpbb_interests'    => '',
                                            'pf_phpbb_facebook'     => '',
                                            'pf_phpbb_twitter'      => '',
                                            'pf_phpbb_youtube'      => '',
                                            'pf_phpbb_occupation'   => '',
                                        );                                  
                                    }                                
                                    $db->sql_freeresult($result);
                                    if (count($sql_ary))
                                    {
                                        $db->sql_multi_insert(USERS_TABLE, $sql_ary);
                                    }
                                    if (count($sql_user_group))
                                    {
                                        $db->sql_multi_insert(USER_GROUP_TABLE, $sql_user_group);
                                    }
                                    if (count($sql_profile_fields_data))
                                    {
                                        $db->sql_multi_insert(PROFILE_FIELDS_DATA_TABLE, $sql_profile_fields_data);
                                    }
                                    $result_count = $db->sql_query('SELECT COUNT(id) AS all_count FROM '.$fluxbb_db_prefix.'users WHERE id > 2 ');
                                    $all_count = $db->sql_fetchfield('all_count');
                                    $db->sql_freeresult($result_count);
                                    if($all_count > $offset + count($sql_ary)){
                                        $continue = true;
                                        $config->set('fluxbb_to_phpbb_offset_users', $offset + $fluxbb_db_limit );
                                    } else {
                                        $continue = false;
                                        $config->set('fluxbb_to_phpbb_import_users', true );
                                        $config->set('fluxbb_to_phpbb_reset_users', false );
                                    }
                                    $json = new \phpbb\json_response();
                                    $json->send(array(
                                        'success'   => true,
                                        'count'     => $offset + count($sql_ary).$language->lang('ACP_FLUXBB_TO_PHPBB_CONVERTER_FROM').$all_count,
                                        'title'     => $language->lang('ACP_FLUXBB_TO_PHPBB_CONVERTER_SUCCESS_TITLE'),
                                        'message'   => $language->lang('ACP_FLUXBB_TO_PHPBB_USERS_SUCCESS_IMPORT'),
                                        'continue'  => $continue,
                                    ));  

                                } else {
                                    if($check_reset_users){
                                        trigger_error('ACP_FLUXBB_TO_PHPBB_CONVERT_WHAS_DONE');
                                    } else {
                                        trigger_error('ACP_FLUXBB_TO_PHPBB_USERS_NOT_RESET');
                                    }
                                    
                                }
                            break;
                            case 'import_forums':
                                if(!$check_import_forums AND $check_import_users){
                                    $sql_ary = array();
                                    $offset = (int)$this->get_config($config,'fluxbb_to_phpbb_offset_forums', 0 );                                
                                    $result = $db->sql_query('SELECT * FROM '.$fluxbb_db_prefix.'forums ORDER BY id ASC LIMIT '.$offset.','.$fluxbb_db_limit);
                                    while ($row = $db->sql_fetchrow($result)){
                                        $sql_ary[$row['id']]['insert'] = array(
                                            'forum_id'                  => (int) $row['id'],
                                            'forum_desc'                => $row['forum_desc'],
                                            'forum_name'                => $row['forum_name'],
                                            'forum_topics_approved'     => $row['num_topics'],
                                            'forum_posts_approved'      => $row['num_posts'],
                                            'forum_last_post_id'        => $row['last_post_id'],
                                            'forum_last_post_time'      => $row['last_post'],
                                            'forum_last_poster_id'      => $this->get_user($row['last_poster']),
                                            'forum_last_post_subject'   => $this->get_fluxbb_post($row['last_post_id'], $fluxbb_db_prefix) ?? '',
                                            'forum_last_poster_name'    => $row['last_poster'],
                                            'forum_parents'             => '',
                                            'forum_rules'               => '',
                                            'forum_type'                => FORUM_POST,

                                        );
                                        $sql_ary[$row['id']]['update'] = array(
                                            'parent_id'                 => 0,
                                            'type_action'               => '',
                                            'forum_status'              => ITEM_UNLOCKED,
                                            'forum_link'                => '',
                                            'forum_link_track'          => false,
                                            'forum_desc'                => '',
                                            'forum_desc_uid'            => '',
                                            'forum_desc_options'        => 7,
                                            'forum_desc_bitfield'       => '',
                                            'forum_rules_uid'           => '',
                                            'forum_rules_options'       => 7,
                                            'forum_rules_bitfield'      => '',
                                            'forum_rules_link'          => '',
                                            'forum_image'               => '',
                                            'forum_style'               => 0,
                                            'display_subforum_list'     => true,
                                            'display_subforum_limit'    => false,
                                            'display_on_index'          => true,
                                            'forum_topics_per_page'     => 0,
                                            'enable_indexing'           => true,
                                            'enable_icons'              => true,
                                            'enable_prune'              => false,
                                            'enable_post_review'        => true,
                                            'enable_quick_reply'        => false,
                                            'enable_shadow_prune'       => false,
                                            'prune_days'                => 7,
                                            'prune_viewed'              => 7,
                                            'prune_freq'                => 1,
                                            'prune_old_polls'           => false,
                                            'prune_announce'            => false,
                                            'prune_sticky'              => false,
                                            'prune_shadow_days'         => 7,
                                            'prune_shadow_freq'         => 1,
                                            'forum_password'            => '',
                                            'forum_password_confirm'    => '',
                                            'forum_password_unset'      => false,
                                            'show_active'               => true,
                                            
                                        );                                        
                                    }                                
                                    if (count($sql_ary))
                                    {
                                        include_once($phpbb_root_path . 'includes/acp/acp_forums.php');
                                        $acp_forums = new \acp_forums();
                                        foreach ($sql_ary as $forum_data) {
                                            $get_left_right = $this->get_left_right();
                                            $forum_data['insert']['left_id'] = $get_left_right['left_id'];
                                            $forum_data['insert']['right_id'] = $get_left_right['right_id'];
                                            $sql = 'INSERT INTO ' . FORUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $forum_data['insert']);
                                            $db->sql_query($sql);
                                            $forum_id = $db->sql_nextid();
                                            $acp_forums->update_forum_data(array_merge($forum_data['insert'], $forum_data['update']));
                                            $this->acl_groups($forum_id);
                                        }
                                    }
                                    $db->sql_freeresult($result);
                                    $result_count = $db->sql_query('SELECT COUNT(id) AS all_count FROM '.$fluxbb_db_prefix.'forums');
                                    $all_count = $db->sql_fetchfield('all_count');
                                    $db->sql_freeresult($result_count);
                                    if($all_count > $offset + count($sql_ary)){
                                        $continue = true;
                                        $config->set('fluxbb_to_phpbb_offset_forums', $offset + $fluxbb_db_limit );
                                    } else {
                                        $continue = false;
                                        $config->set('fluxbb_to_phpbb_import_forums', true );
                                    }

                                    $json = new \phpbb\json_response();
                                    $json->send(array(
                                        'success'   => true,
                                        'count'     => $offset + count($sql_ary).$language->lang('ACP_FLUXBB_TO_PHPBB_CONVERTER_FROM').$all_count,
                                        'title'     => $language->lang('ACP_FLUXBB_TO_PHPBB_CONVERTER_SUCCESS_TITLE'),
                                        'message'   => $language->lang('ACP_FLUXBB_TO_PHPBB_FORUMS_SUCCESS_IMPORT'),
                                        'continue'  => $continue,
                                    ));  
                                } else {
                                    if($check_import_users){
                                        trigger_error('ACP_FLUXBB_TO_PHPBB_CONVERT_WHAS_DONE');
                                    } else {
                                        trigger_error('ACP_FLUXBB_TO_PHPBB_USERS_NOT_IMPORT');
                                    }
                                    
                                }
                            break;
                            case 'import_categories':
                                include_once($phpbb_root_path . 'includes/acp/acp_forums.php');
                                $acp_forums = new \acp_forums();
                                if(!$check_import_categories AND $check_import_forums){
                                    $sql_ary = array();
                                    $offset = (int)$this->get_config($config,'fluxbb_to_phpbb_offset_categories', 0 );                                
                                    $result = $db->sql_query('SELECT * FROM '.$fluxbb_db_prefix.'categories ORDER BY id ASC LIMIT '.$offset.',1');
                                    $category = $db->sql_fetchrow($result);
                                    if($category){
                                        $category_data = array(
                                            'forum_desc'            => (int) $category['id'],
                                            'forum_name'            => $category['cat_name'],
                                            'parent_id'             => 0,
                                            'forum_type'            => FORUM_CAT, 
                                            'type_action'           => '',
                                            'forum_status'          => ITEM_UNLOCKED,
                                            'forum_parents'         => '',
                                            'forum_link'            => '',
                                            'forum_link_track'      => false,
                                            'forum_desc'            => '',
                                            'forum_desc_uid'        => '',
                                            'forum_desc_options'    => 7,
                                            'forum_desc_bitfield'   => '',
                                            'forum_rules'           => '',
                                            'forum_rules_uid'       => '',
                                            'forum_rules_options'   => 7,
                                            'forum_rules_bitfield'  => '',
                                            'forum_rules_link'      => '',
                                            'forum_image'           => '',
                                            'forum_style'           => 0,
                                            'display_subforum_list' => true,
                                            'display_subforum_limit'=> false,
                                            'display_on_index'      => true,
                                            'forum_topics_per_page' => 0,
                                            'enable_indexing'       => true,
                                            'enable_icons'          => true,
                                            'enable_prune'          => false,
                                            'enable_post_review'    => true,
                                            'enable_quick_reply'    => false,
                                            'enable_shadow_prune'   => false,
                                            'prune_days'            => 7,
                                            'prune_viewed'          => 7,
                                            'prune_freq'            => 1,
                                            'prune_old_polls'       => false,
                                            'prune_announce'        => false,
                                            'prune_sticky'          => false,
                                            'prune_shadow_days'     => 7,
                                            'prune_shadow_freq'     => 1,
                                            'forum_password'        => '',
                                            'forum_password_confirm'=> '',
                                            'forum_password_unset'  => false,
                                            'show_active'           => true,
                                        );                                    
                                        if ($category_data){
                                           // $get_left_right = $this->get_left_right();
                                           // $category_data['left_id'] = $get_left_right['left_id'];
                                           // $category_data['right_id'] = $get_left_right['right_id'];                                            
                                            $acp_forums->update_forum_data($category_data);
                                            $parent_id = $this->get_parent_id();
                                            $this->acl_groups($parent_id);
                                            $result_forums = $db->sql_query('SELECT * FROM '.$fluxbb_db_prefix.'forums WHERE cat_id = "'.$category['id'].'" ORDER BY id ASC');
                                            while ($forum_data_sql = $db->sql_fetchrow($result_forums)){
                                                $forum_data = [];
                                                $forum_data['parent_id'] = $parent_id;
                                                $acp_forums->move_forum($forum_data_sql['id'], $parent_id);
                                                //$forum_data_sql['parent_id'] = $parent_id;
                                                $sql = 'UPDATE ' . FORUMS_TABLE . '
                                                    SET ' . $db->sql_build_array('UPDATE', $forum_data) . '
                                                    WHERE forum_id = ' . $forum_data_sql['id'];
                                                $db->sql_query($sql);
                                            }
                                            $db->sql_freeresult($result_forums);                                            
                                        }

                                    }
                                    $db->sql_freeresult($result);
                                    $result_count = $db->sql_query('SELECT COUNT(id) AS all_count FROM '.$fluxbb_db_prefix.'categories');
                                    $all_count = $db->sql_fetchfield('all_count');
                                    $db->sql_freeresult($result_count);
                                    if($all_count > $offset + count($sql_ary)){
                                        $continue = true;
                                        $config->set('fluxbb_to_phpbb_offset_categories', $offset + 1 );
                                    } else {
                                        $continue = false;
                                        $config->set('fluxbb_to_phpbb_import_categories', true );
                                    }

                                    $json = new \phpbb\json_response();
                                    $json->send(array(
                                        'success'   => true,
                                        'count'     => $offset + count($sql_ary).$language->lang('ACP_FLUXBB_TO_PHPBB_CONVERTER_FROM').$all_count,
                                        'title'     => $language->lang('ACP_FLUXBB_TO_PHPBB_CONVERTER_SUCCESS_TITLE'),
                                        'message'   => $language->lang('ACP_FLUXBB_TO_PHPBB_CATEGORIES_SUCCESS_IMPORT'),
                                        'continue'  => $continue,
                                    ));                                     
                                } else {
                                    if($check_import_forums){
                                        trigger_error('ACP_FLUXBB_TO_PHPBB_CONVERT_WHAS_DONE');
                                    } else {
                                        trigger_error('ACP_FLUXBB_TO_PHPBB_FORUMS_NOT_IMPORT');
                                    }
                                }
                            break;

                            case 'import_topics':
                                if(!$check_import_topics AND $check_import_forums){
                                    $sql_ary = array();
                                    $sql_topics_track = array();
                                    $offset = (int)$this->get_config($config,'fluxbb_to_phpbb_offset_topics', 0 );
                                    $result = $db->sql_query('SELECT * FROM '.$fluxbb_db_prefix.'topics ORDER BY id ASC LIMIT '.$offset.','.$fluxbb_db_limit);
                                    while ($row = $db->sql_fetchrow($result))
                                    {
                                        $sql_ary[] = array(
                                            'topic_id'                  => (int) $row['id'],
                                            'forum_id'                  => (int) $row['forum_id'],
                                            'topic_title'               => $row['subject'],
                                            'topic_poster'              => $this->get_user($row['poster']),
                                            'topic_first_poster_name'   => $row['poster'],
                                            'topic_time'                => $row['posted'],
                                            'topic_first_post_id'       => $row['first_post_id'],
                                            'topic_last_post_time'      => $row['last_post'],
                                            'topic_last_post_id'        => $row['last_post_id'],
                                            'topic_last_poster_id'      => $this->get_user($row['last_poster']),
                                            'topic_last_poster_name'    => $row['last_poster'] ?? '',
                                            'topic_posts_approved'      => $row['num_replies'],
                                            'topic_views'               => $row['num_views'],
                                            'topic_posts_approved'      => $row['num_replies'],
                                            'topic_last_post_subject'   => $row['subject'],
                                            'topic_visibility'          => 1,
                                        );
                                        $sql_topics_track[] =  array(
                                            'topic_id'          => (int) $row['id'],
                                            'forum_id'          => (int) $row['forum_id'],
                                            'user_id'           => $this->get_user($row['poster']),
                                            'mark_time'         => $row['posted'],
                                        );
                                    }                                
                                    $db->sql_freeresult($result);
                                    if (count($sql_ary))
                                    {
                                        $db->sql_multi_insert(TOPICS_TABLE, $sql_ary);
                                    }
                                    if (count($sql_topics_track))
                                    {
                                        $db->sql_multi_insert(TOPICS_TRACK_TABLE, $sql_topics_track);
                                    }
                                    $result_count = $db->sql_query('SELECT COUNT(id) AS all_count FROM '.$fluxbb_db_prefix.'topics');
                                    $all_count = $db->sql_fetchfield('all_count');
                                    $db->sql_freeresult($result_count);
                                    if($all_count > $offset + count($sql_ary)){
                                        $continue = true;
                                        $config->set('fluxbb_to_phpbb_offset_topics', $offset + $fluxbb_db_limit );
                                    } else {
                                        $continue = false;
                                        $config->set('fluxbb_to_phpbb_import_topics', true );
                                    }

                                    $json = new \phpbb\json_response();
                                    $json->send(array(
                                        'success'   => true,
                                        'count'     => $offset + count($sql_ary).$language->lang('ACP_FLUXBB_TO_PHPBB_CONVERTER_FROM').$all_count,
                                        'title'     => $language->lang('ACP_FLUXBB_TO_PHPBB_CONVERTER_SUCCESS_TITLE'),
                                        'message'   => $language->lang('ACP_FLUXBB_TO_PHPBB_TOPICS_SUCCESS_IMPORT'),
                                        'continue'  => $continue,
                                    ));
                                } else {
                                    if($check_import_forums){
                                        trigger_error('ACP_FLUXBB_TO_PHPBB_CONVERT_WHAS_DONE');
                                    } else {
                                        trigger_error('ACP_FLUXBB_TO_PHPBB_FORUMS_NOT_IMPORT');
                                    }
                                }
                            break;
                            case 'import_posts':
                                if(!$check_import_posts AND $check_import_topics){
                                    $sql_ary = array();
                                    $offset = (int)$this->get_config($config, 'fluxbb_to_phpbb_offset_posts', 0 );
                                    $result = $db->sql_query('SELECT * FROM '.$fluxbb_db_prefix.'posts ORDER BY id ASC LIMIT '.$offset.','.$fluxbb_db_limit);
                                    while ($row = $db->sql_fetchrow($result))
                                    {
                                        $topic = $this->get_topic($row['topic_id']);
                                        if($row['id'] == $topic['topic_first_post_id']){
                                            $subject = $topic['topic_title'];
                                        } else {
                                            $subject = 'Re: '.$topic['topic_title'];
                                        }
                                        $sql_ary[] = array(
                                            'post_id'           => (int) $row['id'],
                                            'topic_id'          => (int) $row['topic_id'],
                                            'forum_id'          => (int) $topic['forum_id'],
                                            'post_subject'      => $subject,
                                            'poster_id'         => $this->get_user($row['poster']),
                                            'poster_ip'         => $row['poster_ip'],
                                            'post_time'         => $row['posted'],
                                            'post_text'         => $row['message'],
                                            'post_visibility'   => 1,
                                        );                                    
                                    }                                
                                    $db->sql_freeresult($result);
                                    if (count($sql_ary))
                                    {
                                        $db->sql_multi_insert(POSTS_TABLE, $sql_ary);
                                    }
                                    $result_count = $db->sql_query('SELECT COUNT(id) AS all_count FROM '.$fluxbb_db_prefix.'posts');
                                    $all_count = $db->sql_fetchfield('all_count');
                                    $db->sql_freeresult($result_count);
                                    if($all_count > $offset + count($sql_ary)){
                                        $continue = true;
                                        $config->set('fluxbb_to_phpbb_offset_posts', $offset + $fluxbb_db_limit );
                                    } else {
                                        $continue = false;
                                        $config->set('fluxbb_to_phpbb_import_posts', true );
                                        $config->set('fluxbb_to_phpbb_reset_data', false );
                                    }

                                    $json = new \phpbb\json_response();
                                    $json->send(array(
                                        'success'   => true,
                                        'count'     => $offset + count($sql_ary).$language->lang('ACP_FLUXBB_TO_PHPBB_CONVERTER_FROM').$all_count,
                                        'title'     => $language->lang('ACP_FLUXBB_TO_PHPBB_CONVERTER_SUCCESS_TITLE'),
                                        'message'   => $language->lang('ACP_FLUXBB_TO_PHPBB_POSTS_SUCCESS_IMPORT'),
                                        'continue'  => $continue,
                                   ));
                                } else {
                                    if($check_import_topics){
                                        trigger_error('ACP_FLUXBB_TO_PHPBB_WHAS_DONE');
                                    } else {
                                        trigger_error('ACP_FLUXBB_TO_PHPBB_TOPICS_NOT_IMPORT');
                                    }
                                }
                            break;
                            case 'insert_bots':
                                if(!$check_import_bots AND $check_import_users){
                                    $this->inser_bots();
                                    $json = new \phpbb\json_response();
                                    $json->send(array(
                                        'success'   => true,
                                        'count'     => 50,
                                        'title'     => $language->lang('ACP_FLUXBB_TO_PHPBB_CONVERTER_SUCCESS_TITLE'),
                                        'message'   => $language->lang('ACP_FLUXBB_TO_PHPBB_BOTS_SUCCESS_IMPORT'),
                                        'continue'  => false,
                                    ));
                                } else {
                                    if($check_import_users){
                                        trigger_error('ACP_FLUXBB_TO_PHPBB_CONVERT_WHAS_DONE');
                                    } else {
                                        trigger_error('ACP_FLUXBB_TO_PHPBB_USERS_NOT_IMPORT');
                                    }
                                    
                                }
                            break;
                        }
                    }
                } else {
                    $this->tpl_name = 'acp_fluxbb_to_phpbb_converter';
                    $language->add_lang('acp_fluxbb_to_phpbb', 'web6463/fluxbb_to_phpbb');
                    $this->page_title = $language->lang('ACP_FLUXBB_TO_PHPBB_CONVERTER');
                }
                break;

        }
    }
    private function acl_groups($forum_id) {
        global $db;
        $sql_values = [
            [5, $forum_id, 0, 14, 0],
            [2, $forum_id, 0, 21, 0],
            [6, $forum_id, 0, 17, 0],
            [4, $forum_id, 0, 14, 0],
            [1, $forum_id, 0, 17, 0],
            [7, $forum_id, 0, 15, 0],
        ];

        // نام فیلدها
        $fields = ['group_id', 'forum_id', 'auth_option_id', 'auth_role_id', 'auth_setting'];

        // آرایه نهایی
        $result = [];
        foreach ($sql_values as $row) {
            $result[] = array_combine($fields, $row);
        } 
        $db->sql_multi_insert(ACL_GROUPS_TABLE, $result);   
    }
    private function get_left_right(){
        global $db;
        $forum_data_sql = [];
        $sql = 'SELECT MAX(right_id) AS right_id
            FROM ' . FORUMS_TABLE;
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        $forum_data_sql['left_id'] = $row['right_id'] + 1;
        $forum_data_sql['right_id'] = $row['right_id'] + 2;
        return $forum_data_sql;       
    }
    private function get_parent_id()
    {
        global $db;
        $sql = 'SELECT *
            FROM ' . FORUMS_TABLE . "
            WHERE forum_type = ".FORUM_CAT." ORDER BY forum_id DESC ";
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        if (!$row)
        {
            return 0;
        }
        return $row['forum_id'];
    }    
    private function get_forum_info($forum_id)
    {
        global $db;

        $sql = 'SELECT *
            FROM ' . FORUMS_TABLE . "
            WHERE forum_id = $forum_id";
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        if (!$row)
        {
            trigger_error("Forum #$forum_id does not exist", E_USER_ERROR);
        }

        return $row;
    }    
	private function generate_avatar_markup($user_id){
		global $config, $phpbb_root_path;
		$filetypes = array('jpg', 'gif', 'png');
		$avatar_markup = '';
		$o_avatars_dir =  $this->get_config($config, 'fluxbb_to_phpbb_avatars_dir');
		
		foreach ($filetypes as $cur_type) {
			$path = $o_avatars_dir . '/' . $user_id . '.' . $cur_type;
			
			// Check if the file exists and if the image size can be retrieved
			if (@file_exists($path) && @getimagesize($path)) {
				$avatar_markup = $user_id . '.' . $cur_type;
				$prefix = $config['avatar_salt'] . '_';
				
				// Try to copy the file and suppress errors if the copy fails
				if (@copy($path, $phpbb_root_path . 'images/avatars/upload/' . $prefix . $user_id . '.' . $cur_type)) {
					$avatar_markup = $user_id . '.' . $cur_type;
					break; // If successful, break the loop
				}
			}
		}

		return $avatar_markup;
	}
    
    private function get_config($config, $key, $default = ''){
        if(isset($config[$key])){
            return $config[$key];
        } else {
            return $default;
        }
    }
    private function get_user($username){
        global $db;
        $sql = 'SELECT user_id, username
                FROM ' . USERS_TABLE . '
                WHERE username_clean = "' . $username.'" OR username = "' . $username.'"';
            $result = $db->sql_query($sql);
            $user_row = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);
            if(isset($user_row['user_id'])){
                return $user_row['user_id'];
            } else {
                return 2;
            }
        
    }
    private function check_user_exist($id, $username, $email){
        global $db;
        $sql = 'SELECT  COUNT(user_id) AS all_count
                FROM ' . USERS_TABLE . '
                WHERE user_id = ' . $id.' OR user_email = "' . $email.'" OR username_clean = "' . $username.'" OR username = "' . $username.'"';
            $result = $db->sql_query($sql);
            $all_count = $db->sql_fetchfield('all_count');
            $db->sql_freeresult($result);
            if($all_count > 0){
                return true;
            } else {
                return false;
            }
        
    }
    private function get_topic($topic_id){
        global $db;
        $sql = 'SELECT *
                FROM ' . TOPICS_TABLE . '
                WHERE topic_id = "' . $topic_id.'"';
            $result = $db->sql_query($sql);
            $topic_row = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);
            if($topic_row){
                return $topic_row;
            } 
        
    }
    private function get_fluxbb_post($post_id, $prefix){
        global $db;
        $sql = 'SELECT t.subject as subject
                FROM ' . $prefix . 'posts AS p
                LEFT JOIN ' . $prefix . 'topics AS t ON p.topic_id = t.id
                WHERE p.id = "' . $post_id.'"';
            $result = $db->sql_query($sql);
            $fluxbb_post_row = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);
            if($fluxbb_post_row){
                return $fluxbb_post_row['subject'];
            } 
        
    }
    private function get_group_id($group_name){
        global $db;
        $sql = 'SELECT group_id
            FROM ' . GROUPS_TABLE . "
            WHERE group_name = '".$group_name."'";
        $result = $db->sql_query($sql);
        $group_id = (int) $db->sql_fetchfield('group_id');
        $db->sql_freeresult($result);
        return $group_id;
    }
    private function inser_bots(){
        global $db, $config, $language, $phpbb_root_path;
        $group_id = $this->get_group_id('BOTS');
        if (!$group_id)
        {
            return;
        }
        $bot_list = array(
                'AdsBot [Google]'           => array('AdsBot-Google', ''),
                'Ahrefs [Bot]'              => array('AhrefsBot/', ''),
                'Alexa [Bot]'               => array('ia_archiver', ''),
                'Alta Vista [Bot]'          => array('Scooter/', ''),
                'Amazon [Bot]'              => array('Amazonbot/', ''),
                'Ask Jeeves [Bot]'          => array('Ask Jeeves', ''),
                'Baidu [Spider]'            => array('Baiduspider', ''),
                'Bing [Bot]'                => array('bingbot/', ''),
                'DuckDuckGo [Bot]'          => array('DuckDuckBot/', ''),
                'Exabot [Bot]'              => array('Exabot/', ''),
                'FAST Enterprise [Crawler]' => array('FAST Enterprise Crawler', ''),
                'FAST WebCrawler [Crawler]' => array('FAST-WebCrawler/', ''),
                'Francis [Bot]'             => array('http://www.neomo.de/', ''),
                'Gigabot [Bot]'             => array('Gigabot/', ''),
                'Google Adsense [Bot]'      => array('Mediapartners-Google', ''),
                'Google Desktop'            => array('Google Desktop', ''),
                'Google Feedfetcher'        => array('Feedfetcher-Google', ''),
                'Google [Bot]'              => array('Googlebot', ''),
                'Heise IT-Markt [Crawler]'  => array('heise-IT-Markt-Crawler', ''),
                'Heritrix [Crawler]'        => array('heritrix/1.', ''),
                'IBM Research [Bot]'        => array('ibm.com/cs/crawler', ''),
                'ICCrawler - ICjobs'        => array('ICCrawler - ICjobs', ''),
                'ichiro [Crawler]'          => array('ichiro/', ''),
                'Majestic-12 [Bot]'         => array('MJ12bot/', ''),
                'Metager [Bot]'             => array('MetagerBot/', ''),
                'MSN NewsBlogs'             => array('msnbot-NewsBlogs/', ''),
                'MSN [Bot]'                 => array('msnbot/', ''),
                'MSNbot Media'              => array('msnbot-media/', ''),
                'NG-Search [Bot]'           => array('NG-Search/', ''),
                'Nutch [Bot]'               => array('http://lucene.apache.org/nutch/', ''),
                'Nutch/CVS [Bot]'           => array('NutchCVS/', ''),
                'OmniExplorer [Bot]'        => array('OmniExplorer_Bot/', ''),
                'Online link [Validator]'   => array('online link validator', ''),
                'psbot [Picsearch]'         => array('psbot/0', ''),
                'Seekport [Bot]'            => array('Seekbot/', ''),
                'Semrush [Bot]'             => array('SemrushBot/', ''),
                'Sensis [Crawler]'          => array('Sensis Web Crawler', ''),
                'SEO Crawler'               => array('SEO search Crawler/', ''),
                'Seoma [Crawler]'           => array('Seoma [SEO Crawler]', ''),
                'SEOSearch [Crawler]'       => array('SEOsearch/', ''),
                'Snappy [Bot]'              => array('Snappy/1.1 ( http://www.urltrends.com/ )', ''),
                'Steeler [Crawler]'         => array('http://www.tkl.iis.u-tokyo.ac.jp/~crawler/', ''),
                'Synoo [Bot]'               => array('SynooBot/', ''),
                'Telekom [Bot]'             => array('crawleradmin.t-info@telekom.de', ''),
                'TurnitinBot [Bot]'         => array('TurnitinBot/', ''),
                'Voyager [Bot]'             => array('voyager/', ''),
                'W3 [Sitesearch]'           => array('W3 SiteSearch Crawler', ''),
                'W3C [Linkcheck]'           => array('W3C-checklink/', ''),
                'W3C [Validator]'           => array('W3C_*Validator', ''),
                'WiseNut [Bot]'             => array('http://www.WISEnutbot.com', ''),
                'YaCy [Bot]'                => array('yacybot', ''),
                'Yahoo MMCrawler [Bot]'     => array('Yahoo-MMCrawler/', ''),
                'Yahoo Slurp [Bot]'         => array('Yahoo! DE Slurp', ''),
                'Yahoo [Bot]'               => array('Yahoo! Slurp', ''),
                'YahooSeeker [Bot]'         => array('YahooSeeker/', ''),
            );
        foreach ($bot_list as $bot_name => $bot_ary)
        {
            $user_row = array(
                'user_type'             => USER_IGNORE,
                'group_id'              => $group_id,
                'username'              => $bot_name,
                'user_regdate'          => time(),
                'user_password'         => '',
                'user_colour'           => '9E8DA7',
                'user_email'            => '',
                'user_lang'             => $this->get_config($config, 'default_lang', 'en' ),
                'user_style'            => 1,
                'user_timezone'         => 'UTC',
                'user_dateformat'       => $language->lang('default_dateformat'),
                'user_allow_massemail'  => 0,
                'user_allow_pm'         => 0,
            );

            if (!function_exists('user_add'))
            {
                include($phpbb_root_path . 'includes/functions_user.php');
            }

            $user_id = user_add($user_row);

            if (!$user_id)
            {
                continue;
            }

            $sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
                'bot_active'    => 1,
                'bot_name'      => (string) $bot_name,
                'user_id'       => (int) $user_id,
                'bot_agent'     => (string) $bot_ary[0],
                'bot_ip'        => (string) $bot_ary[1],
            ));

            $db->sql_query($sql);
        }        
    }
    private function getLanguagesList($lan) {
        $all = [
            'Afrikaans' => 'af',
            'Albanian' => 'sq',
            'Amharic' => 'am',
            'Arabic' => 'ar',
            'Armenian' => 'hy',
            'Azerbaijani' => 'az',
            'Basque' => 'eu',
            'Belarusian' => 'be',
            'Bengali' => 'bn',
            'Bosnian' => 'bs',
            'Bulgarian' => 'bg',
            'Catalan' => 'ca',
            'Cebuano' => 'ceb',
            'Chinese (Simplified)' => 'zh-CN',
            'Chinese (Traditional)' => 'zh-TW',
            'Corsican' => 'co',
            'Croatian' => 'hr',
            'Czech' => 'cs',
            'Danish' => 'da',
            'Dutch' => 'nl',
            'English' => 'en',
            'Esperanto' => 'eo',
            'Estonian' => 'et',
            'Finnish' => 'fi',
            'French' => 'fr',
            'Frisian' => 'fy',
            'Galician' => 'gl',
            'Georgian' => 'ka',
            'German' => 'de',
            'Greek' => 'el',
            'Gujarati' => 'gu',
            'Haitian Creole' => 'ht',
            'Hausa' => 'ha',
            'Hawaiian' => 'haw',
            'Hebrew' => 'he',
            'Hindi' => 'hi',
            'Hmong' => 'hmn',
            'Hungarian' => 'hu',
            'Icelandic' => 'is',
            'Igbo' => 'ig',
            'Indonesian' => 'id',
            'Irish' => 'ga',
            'Italian' => 'it',
            'Japanese' => 'ja',
            'Javanese' => 'jv',
            'Kannada' => 'kn',
            'Kazakh' => 'kk',
            'Khmer' => 'km',
            'Korean' => 'ko',
            'Kurdish' => 'ku',
            'Kyrgyz' => 'ky',
            'Lao' => 'lo',
            'Latin' => 'la',
            'Latvian' => 'lv',
            'Lithuanian' => 'lt',
            'Luxembourgish' => 'lb',
            'Macedonian' => 'mk',
            'Malagasy' => 'mg',
            'Malay' => 'ms',
            'Malayalam' => 'ml',
            'Maltese' => 'mt',
            'Maori' => 'mi',
            'Marathi' => 'mr',
            'Mongolian' => 'mn',
            'Myanmar (Burmese)' => 'my',
            'Nepali' => 'ne',
            'Norwegian' => 'no',
            'Nyanja (Chichewa)' => 'ny',
            'Odia (Oriya)' => 'or',
            'Pashto' => 'ps',
            'Persian' => 'fa',
            'Polish' => 'pl',
            'Portuguese' => 'pt',
            'Punjabi' => 'pa',
            'Romanian' => 'ro',
            'Russian' => 'ru',
            'Samoan' => 'sm',
            'Scots Gaelic' => 'gd',
            'Serbian' => 'sr',
            'Sesotho' => 'st',
            'Shona' => 'sn',
            'Sindhi' => 'sd',
            'Sinhala (Sinhalese)' => 'si',
            'Slovak' => 'sk',
            'Slovenian' => 'sl',
            'Somali' => 'so',
            'Spanish' => 'es',
            'Sundanese' => 'su',
            'Swahili' => 'sw',
            'Swedish' => 'sv',
            'Tagalog (Filipino)' => 'tl',
            'Tajik' => 'tg',
            'Tamil' => 'ta',
            'Tatar' => 'tt',
            'Telugu' => 'te',
            'Thai' => 'th',
            'Turkish' => 'tr',
            'Turkmen' => 'tk',
            'Ukrainian' => 'uk',
            'Urdu' => 'ur',
            'Uyghur' => 'ug',
            'Uzbek' => 'uz',
            'Vietnamese' => 'vi',
            'Welsh' => 'cy',
            'Xhosa' => 'xh',
            'Yiddish' => 'yi',
            'Yoruba' => 'yo',
            'Zulu' => 'zu'
        ];
        if(isset($all[$lan])){
            return $all[$lan];
        }
    }
}
