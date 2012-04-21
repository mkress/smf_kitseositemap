<?php
/*******************************************************************************
* SEO Sitemap © 2012, Markus Kress - Kress.IT							       *
********************************************************************************
* Subs-KitSitemap.php														   *
********************************************************************************
* License http://creativecommons.org/licenses/by-sa/3.0/deed.de CC BY-SA 	   *
* Support for this software  http://kress.it and							   *
* http://custom.simplemachines.org/mods/index.php?mod=3393					   *
*******************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');
	
function kit_sitemap_load_theme()
{
	global $context, $sourcedir, $boards, $boardList, $cat_tree, $scripturl, $board_info, $modSettings, $user_info, $smcFunc, $txt;
	
	// load template
	loadTemplate('KitSitemap');
	
	// Retrieve the categories and boards.
	require_once($sourcedir . '/Subs-BoardIndex.php');
	$boardIndexOptions = array(
		'include_categories' => true,
		'base_level' => 0,
		'parent_id' => 0,
		'set_latest_post' => false,
		'countChildPosts' => false,
	);
	$context['categories'] = getBoardIndex($boardIndexOptions);
	
	if ($context['current_action'] == 'kitsitemap')
	{	
		loadLanguage('KitSitemap');
		
		$xmlView = false;
		if ( isset($_REQUEST['xml']) )
		{
			$context['template_layers'] = array('kitsitemap_xml');
			$xmlView = true;
		}
		else
		{
			$context['template_layers'] = array('kitsitemap');
		}
		// current url
		$context['last_linktree'] = end($context['linktree']);
	
		// list board topics
		if ( !empty($board_info['id']) )
		{
			$start = (int) $_REQUEST['start'];
			if ( $xmlView )
			{
				$context['sub_template'] = 'kitsitemap_xml_board';
				// maximum entries per sitemap
				$context['topics_per_page'] = 50000;
				$query= $smcFunc['db_query']('', "
					SELECT
						t.ID_TOPIC
					FROM {db_prefix}messages AS m, {db_prefix}topics AS t
					WHERE m.ID_BOARD = $board_info[id] AND m.ID_MSG = t.ID_FIRST_MSG
					ORDER BY t.ID_LAST_MSG DESC
					LIMIT $start,$context[topics_per_page]");
				
				while($row = $smcFunc['db_fetch_assoc']($query))
				{
					// fill topic array
					$context['topics'][$row['ID_TOPIC']] = $row;
					$context['topics'][$row['ID_TOPIC']]['href'] = $scripturl.'?topic='.$row['ID_TOPIC'].'.0';
				}
			}
			else
			{
				$context['kit_sitemap_title'] = $board_info['name'].' '.$txt['kitsitemap_archive'].' ';
				$context['topics_per_page'] = 50;
				$context['sub_template'] = 'kitsitemap_board';
				
				// show all pages
				$modSettings['compactTopicPagesEnable'] = false;
				$context['page_index'] = constructPageIndex($scripturl . '?action=kitsitemap&board=' . $board_info['id'] . '.%1$d', $start, $board_info['num_topics'], $context['topics_per_page'], true);
				
				$context['page_info'] = array(
					'current_page' => ($start / $context['topics_per_page'] + 1),
					'num_pages' => floor(($board_info['num_topics'] - 1) / $context['topics_per_page']) + 1,
					'element_start' => $start+1
				);
				
				if ($context['page_info']['current_page'] > 1)
				{
					$context['kit_sitemap_title'] .= $context['page_info']['current_page'].' ';
				}
				
				// get topics
				$context['topics'] = array();
				$query= $smcFunc['db_query']('', "
					SELECT
						m.subject, t.ID_TOPIC, t.num_replies
					FROM {db_prefix}messages AS m, {db_prefix}topics AS t
					WHERE m.ID_BOARD = $board_info[id] AND m.ID_MSG = t.ID_FIRST_MSG
					ORDER BY t.ID_LAST_MSG DESC
					LIMIT $start,$context[topics_per_page]");
				
				while($row = $smcFunc['db_fetch_assoc']($query))
				{
					// fill topic array
					$context['topics'][$row['ID_TOPIC']] = $row;
					$context['topics'][$row['ID_TOPIC']]['link'] = '<a href="'.$scripturl.'?topic='.$row['ID_TOPIC'].'.0">'.$row['subject'].'</a>';
				}
			}
		}
		// show board list
		else
		{
			$context['kit_sitemap_title'] = $txt['kitsitemap_archive'].' ';
			if ( $xmlView )
			{
				$context['sub_template'] = 'kitsitemap_xml_main';
			}
			else
			{
				$context['sub_template'] = 'kitsitemap_main';
			}
		}
	}
	elseif ( !isset($_REQUEST['xml']) )
	{
		// add footer before body-layer
		$context['template_layers'][] = 'kitsitemap_footer';
	}
}


function kit_sitemap_actions(&$actionArray)
{
	$actionArray['kitsitemap'] = array('Subs-KitSitemap.php');
}
?>