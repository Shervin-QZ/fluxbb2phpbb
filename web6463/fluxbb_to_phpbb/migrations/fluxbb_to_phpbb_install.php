<?php
/**
 *
 * FluxBB to PHPbb for the phpBB Forum Software package.
 *
 * @copyright (c) 2023 - web6463
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
*/

namespace web6463\fluxbb_to_phpbb\migrations;

class fluxbb_to_phpbb_install extends \phpbb\db\migration\migration
{

	public function update_data()
	{
		// Add config
		return [
			['config.add', ['fluxbb_to_phpbb_tb_prefix', 'fluxbb_']],
			['config.add', ['fluxbb_to_phpbb_tb_limit', 20]],
			['config.add', ['fluxbb_to_phpbb_avatars_dir', '']],

			// Add ACP modules
			['module.add', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_FLUXBB_TO_PHPBB']],
			['module.add', ['acp', 'ACP_FLUXBB_TO_PHPBB', [
				'module_basename'		=> '\web6463\fluxbb_to_phpbb\acp\fluxbb_to_phpbb_module',
				'module_langname'		=> 'ACP_FLUXBB_TO_PHPBB_CONF',
				'module_mode'			=> 'overview',
				'module_auth'			=> 'ext_web6463/fluxbb_to_phpbb && acl_a_board',
			]]],
			['module.add', ['acp', 'ACP_FLUXBB_TO_PHPBB', [
				'module_basename'		=> '\web6463\fluxbb_to_phpbb\acp\fluxbb_to_phpbb_converter_module',
				'module_langname'		=> 'ACP_FLUXBB_TO_PHPBB_CONVERTER',
				'module_mode'			=> 'converter',
				'module_auth'			=> 'ext_web6463/fluxbb_to_phpbb && acl_a_board',
			]]]			
		];
	}
}
