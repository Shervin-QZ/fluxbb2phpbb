<?php
/**
 *
 * FluxBB to PHPbb for the phpBB Forum Software package.
 *
 * @copyright (c) 2023 - web6463
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
*/

namespace web6463\fluxbb_to_phpbb\acp;

/**
 * @package acp
*/

class fluxbb_to_phpbb_module
{
	/** @var string */
	public $u_action;

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $page_title;

	function main($id, $mode)
	{
		global $template, $request, $config, $user, $phpbb_log, $language;

		$this->tpl_name = 'acp_fluxbb_to_phpbb';
		$language->add_lang('acp_fluxbb_to_phpbb', 'web6463/fluxbb_to_phpbb');
		$this->page_title = $language->lang('ACP_FLUXBB_TO_PHPBB');

		add_form_key('fluxbb_to_phpbb/acp_fluxbb_to_phpbb');

		$submit = $request->is_set_post('submit');
		if ($submit)
		{
			if (!check_form_key('fluxbb_to_phpbb/acp_fluxbb_to_phpbb'))
			{
				trigger_error('FORM_INVALID');
			}

			$config->set('fluxbb_to_phpbb_tb_prefix', $request->variable('fluxbb_to_phpbb_tb_prefix', ''));
			$config->set('fluxbb_to_phpbb_tb_limit', $request->variable('fluxbb_to_phpbb_tb_limit', ''));
			$config->set('fluxbb_to_phpbb_avatars_dir', $request->variable('fluxbb_to_phpbb_avatars_dir', ''));

			$user_id = $user->data['user_id'];
			$user_ip = $user->ip;

			$phpbb_log->add('admin', $user_id, $user_ip, 'ACP_FLUXBB_TO_PHPBB_SAVE');
			trigger_error($language->lang('ACP_FLUXBB_TO_PHPBB_SAVE') . adm_back_link($this->u_action));
		}

		$template->assign_vars([
			'FLUXBB_TO_PHPBB_AVATARS_DIR'	=> $config['fluxbb_to_phpbb_avatars_dir'],
			'FLUXBB_TO_PHPBB_TB_LIMIT'		=> $config['fluxbb_to_phpbb_tb_limit'],
			'FLUXBB_TO_PHPBB_TB_PREFIX'		=> $config['fluxbb_to_phpbb_tb_prefix'],
		]);
	}
}
