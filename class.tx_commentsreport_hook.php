<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Dmitry Dulepov <dmitry@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(t3lib_extMgm::extPath('lang', 'lang.php'));

class tx_commentsreport_hook {
	/**
	 * Adds extra marker to commented item
	 *
	 * @param	array	$params	Array of parameters
	 * @param	tx_comments_pi1	$pObj	Reference to parent object
	 * @return	array	Updated markers
	 */
	function extraMarkerHook($params, &$pObj) {
		/* @var $pObj tx_comments_pi1 */
		$markers = &$params['markers'];
		$conf = &$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_commentsreport_pi1.'];
		$templateCode = $pObj->cObj->fileResource($conf['templateFile']);
		$template = $pObj->cObj->getSubpart($templateCode, '###REPORT_LINK###');
		$lang = t3lib_div::makeInstance('language');
		/* @var $lang language */
		$lang->init($GLOBALS['TSFE']->lang);
		$markers['###TX_COMMENTSREPORT###'] = $pObj->cObj->substituteMarkerArray($template, array(
				'###LINK###' => $pObj->pi_getPageLink($conf['reportPid'], '', array(
						'tx_commentsreport_pi1[info]' => base64_encode(serialize(array(
							'url' => t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'),
							'uid' => $params['row']['uid'],
						))),
					)),
				'###TITLE###' => $lang->sL('LLL:EXT:comments_report/locallang.xml:report_link_title'),
			));
		return $markers;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments_report/class.tx_commentsreport_hook.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments_report/class.tx_commentsreport_hook.php']);
}

?>