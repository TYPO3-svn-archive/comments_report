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

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Comments: report bad comment' for the 'comments_report' extension.
 *
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 * @package	TYPO3
 * @subpackage	tx_commentsreport
 */
class tx_commentsreport_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_commentsreport_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_commentsreport_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'comments_report';	// The extension key.
	var $templateCode;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

		if (!isset($conf['templateFile'])) {
			return $this->pi_getLL('no_ts_template');
		}
		$this->init();

		$errors = array();
		if (!$this->processForm($errors)) {
			$content = $this->showForm($errors);
		}
		else {
			$content = $this->showThanks();
		}

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Initializes the plugin
	 *
	 * @return	void
	 */
	protected function init() {
		$this->templateCode = $this->cObj->fileResource($this->conf['templateFile']);
		$this->addHeaderParts();
	}

	/**
	 * Adds header parts to TSFE
	 *
	 * @return	void
	 */
	protected function addHeaderParts() {
		$key = 'EXT:' . $this->extKey . '_' . md5($this->templateCode);
		if (!isset($GLOBALS['TSFE']->additionalHeaderData[$key])) {
			$headerParts = $this->cObj->getSubpart($this->templateCode, '###HEADER_ADDITIONS###');
			if ($headerParts) {
				$headerParts = $this->cObj->substituteMarker($headerParts, '###SITE_REL_PATH###', t3lib_extMgm::siteRelPath($this->extKey));
				$GLOBALS['TSFE']->additionalHeaderData[$key] = $headerParts;
			}
		}
	}

	/**
	 * Processes the form and sebnds error message by e-mail
	 *
	 * @param	array	$errors
	 * @return	boolean	true if successful
	 */
	protected function processForm(array &$errors) {
		if ($this->piVars['submit']) {
			// Check captcha
			$captchaType = intval($this->conf['useCaptcha']);
			if ($captchaType == 1 && t3lib_extMgm::isLoaded('captcha')) {
				@session_start();	// As of PHP 4.3.3, calling session_start() while the session has already been started will result in an error of level E_NOTICE. Also, the second session start will simply be ignored.
				$captchaStr = $_SESSION['tx_captcha_string'];
				$_SESSION['tx_captcha_string'] = '';
				if (!$captchaStr || $this->piVars['captcha'] !== $captchaStr) {
					$errors['captcha'] = $this->pi_getLL('error_wrong_captcha');
				}
			}
			elseif ($captchaType == 2 && t3lib_extMgm::isLoaded('sr_freecap')) {
				require_once(t3lib_extMgm::extPath('sr_freecap') . 'pi2/class.tx_srfreecap_pi2.php');
				$freeCap = t3lib_div::makeInstance('tx_srfreecap_pi2');
				/* @var $freeCap tx_srfreecap_pi2 */
				if (!$freeCap->checkWord($this->piVars['captcha'])) {
					$errors['captcha'] = $this->pi_getLL('error_wrong_captcha');
				}
			}
			// Check required fields
			foreach (t3lib_div::trimExplode(',', $this->conf['requiredFields'], true) as $field) {
				if (trim($this->piVars[$field]) == '') {
					$errors[$field] = $this->pi_getLL('error_empty_field');
				}
			}
			if ($this->piVars['frommail'] != '' && !t3lib_div::validEmail($this->piVars['frommail'])) {
				$errors['frommail'] = $this->pi_getLL('error_bad_email');
			}

			// Decode info
			$info = @unserialize(base64_decode($this->piVars['info']));
			if (!is_array($info)) {
				$errors['text'] = $this->pi_getLL('error_cannot_get_info');
			}
			else {
				// Get comment
				t3lib_div::loadTCA('tx_comments_comments');
				$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',
					'tx_comments_comments',
					'uid=' . intval($info['uid']) . $this->cObj->enableFields('tx_comments_comments'));
				if (count($rows) == 0) {
					$errors['text'] = $this->pi_getLL('error_cannot_get_comment');
				}
				else {
					$comment = $rows[0];
				}
			}

			// Process form
			if (count($errors) == 0) {
				$template = $this->cObj->fileResource($this->conf['emailTemplateFile']);
				$email = $this->cObj->substituteMarkerArray($template, array(
								'###URL###' => $info['url'],
								'###UID###' => $comment['uid'],
								'###FROM###' => $this->piVars['from'],
								'###FROMMAIL###' => $this->piVars['frommail'],
								'###USER_TEXT###' => $this->piVars['text'],
								'###COMMENT_TEXT###' => $comment['content'],
							));
				t3lib_div::plainMailEncoded($this->conf['destinationEmail'], $this->pi_getLL('report_subject'),
					$email, 'From: <' . $this->conf['sourceEmail'] . '>' .chr(10));
				return true;
			}
		}
		return false;
	}

	/**
	 * Shows "thank you" message after text was submitted
	 *
	 * @return	string	HTML
	 */
	function showThanks() {
		$template = $this->cObj->getSubpart($this->templateCode, '###THANK_YOU###');
		return $this->cObj->substituteMarker($template, '###TEXT_THANKYOU###', $this->pi_getLL('text_thankyou'));
	}

	function showForm(array $errors) {
		$template = $this->cObj->getSubpart($this->templateCode, '###REPORT_FORM###');
		$req_template = $this->cObj->getSubpart($this->templateCode, '###REQUIRED###');
		$error_template = $this->cObj->getSubpart($this->templateCode, '###ERROR###');

		$required = $this->cObj->substituteMarker($req_template, '###TEXT_REQUIRED###', $this->pi_getLL('text_required'));

		$markers = array(
			'###ACTION###' => $this->pi_getPageLink($GLOBALS['TSFE']->id),
			'###INFO###' => $this->piVars['info'],
			'###FROM###' => htmlspecialchars($this->piVars['from']),
			'###FROMMAIL###' => htmlspecialchars($this->piVars['from']),
			'###TEXT###' => htmlspecialchars($this->piVars['text']),
			'###TEXT_FROM###' => $this->pi_getLL('text_from'),
			'###TEXT_FROMMAIL###' => $this->pi_getLL('text_frommail'),
			'###TEXT_TEXT###' => $this->pi_getLL('text_text'),
			'###TEXT_SUBMIT###' => $this->pi_getLL('text_submit'),
			'###ERROR_FROM###' => '',
			'###ERROR_FROMMAIL###' => '',
			'###ERROR_TEXT###' => '',
			'###REQUIRED_FROM###' => '',
			'###REQUIRED_FROMMAIL###' => '',
			'###REQUIRED_TEXT###' => '',
			'###CAPTCHA###' => '',
		);

		foreach ($errors as $field => $error) {
			if ($field != 'captcha') {
				$markers['###ERROR_' . strtoupper($field) . '###'] = $this->cObj->substituteMarker($error_template, '###TEXT###', htmlspecialchars($error));
			}
		}
		foreach (t3lib_div::trimExplode(',', $this->conf['requiredFields'], true) as $field) {
			$markers['###REQUIRED_' . strtoupper($field) . '###'] = $required;
		}

		// Captcha
		if ($this->conf['useCaptcha']) {
			$error = '';
			if ($errors['captcha']) {
				$error = $this->cObj->substituteMarker($error_template, '###TEXT###', htmlspecialchars($errors['captcha']));
			}
			$markers['###CAPTCHA###'] = $this->getCaptcha($required, $error);
		}

		return $this->cObj->substituteMarkerArray($template, $markers);
	}

	/**
	 * Adds captcha code if enabled.
	 *
	 * @param	string	Possible error text
	 * @return	string		Generated HTML
	 */
	protected function getCaptcha($required, $error) {
		$captchaType = intval($this->conf['useCaptcha']);
		if ($captchaType == 1 && t3lib_extMgm::isLoaded('captcha')) {
			$template = $this->cObj->getSubpart($this->templateCode, '###REPORT_CAPTCHA###');
			$code = $this->cObj->substituteMarkerArray($template, array(
							'###SR_FREECAP_IMAGE###' => '<img src="' . t3lib_extMgm::siteRelPath('captcha') . 'captcha/captcha.php" alt="" />',
							'###SR_FREECAP_CANT_READ###' => '',
							'###REQUIRED_CAPTCHA###' => $required,
							'###ERROR_CAPTCHA###' => $error,
							'###SITE_REL_PATH###' => t3lib_extMgm::siteRelPath('comments'),
							'###TEXT_CAPTCHA###' => $this->pi_getLL('text_captcha'),
						));
			return str_replace('<br /><br />', '<br />', $code);
		}
		elseif ($captchaType == 2 && t3lib_extMgm::isLoaded('sr_freecap')) {
			require_once(t3lib_extMgm::extPath('sr_freecap') . 'pi2/class.tx_srfreecap_pi2.php');
			$freeCap = t3lib_div::makeInstance('tx_srfreecap_pi2');
			/* @var $freeCap tx_srfreecap_pi2 */
			$template = $this->cObj->getSubpart($this->templateCode, '###REPORT_CAPTCHA###');
			return $this->cObj->substituteMarkerArray($template, array_merge($freeCap->makeCaptcha(), array(
							'###REQUIRED_CAPTCHA###' => $required,
							'###ERROR_CAPTCHA###' => $error,
							'###TEXT_CAPTCHA###' => $this->pi_getLL('text_captcha'),
						)));
		}
		return '';
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments_report/pi1/class.tx_commentsreport_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments_report/pi1/class.tx_commentsreport_pi1.php']);
}

?>