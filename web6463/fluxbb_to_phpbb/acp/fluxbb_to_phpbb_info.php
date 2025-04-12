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

class fluxbb_to_phpbb_info
{
	function module()
	{
		return [
			'filename'	=> '\web6463\fluxbb_to_phpbb\acp\fluxbb_to_phpbb_module',
			'title'		=> 'ACP_FLUXBB_TO_PHPBB',
			'modes'		=> [
				'settings'	=> [
					'title' 	=> 'ACP_FLUXBB_TO_PHPBB_CONFIG',
					'auth' 		=> 'web6463/fluxbb_to_phpbb && acl_a_board',
					'cat'		=> ['ACP_FLUXBB_TO_PHPBB_CONFIG'],
				],
			],
		];
	}
}
