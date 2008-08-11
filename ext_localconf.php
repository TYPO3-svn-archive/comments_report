<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_commentsreport_pi1.php', '_pi1', 'list_type', 0);

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['comments_getComments'][$_EXTKEY] = 'EXT:' . $_EXTKEY . '/class.tx_commentsreport_hook.php:&tx_commentsreport_hook->extraMarkerHook';

?>