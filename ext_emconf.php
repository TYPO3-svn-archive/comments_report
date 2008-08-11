<?php

########################################################################
# Extension Manager/Repository config file for ext: "comments_report"
#
# Auto generated 24-06-2008 18:43
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Comments: report bad comment',
	'description' => 'Provides a custom marker to link to a page where reporting form is placed. Reporting form plugin is also provided.',
	'category' => 'plugin',
	'author' => 'Dmitry Dulepov [netcreators]',
	'author_email' => 'dmitry@typo3.org',
	'shy' => '',
	'dependencies' => 'comments',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'Netcreators BV',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.1.0',
	'constraints' => array(
		'depends' => array(
			'comments' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:16:{s:9:"ChangeLog";s:4:"2e80";s:32:"class.tx_commentsreport_hook.php";s:4:"6ed4";s:12:"ext_icon.gif";s:4:"7c67";s:17:"ext_localconf.php";s:4:"7109";s:14:"ext_tables.php";s:4:"f860";s:13:"locallang.xml";s:4:"fe25";s:16:"locallang_db.xml";s:4:"33f8";s:19:"doc/wizard_form.dat";s:4:"bbd4";s:20:"doc/wizard_form.html";s:4:"b780";s:35:"pi1/class.tx_commentsreport_pi1.php";s:4:"3187";s:17:"pi1/locallang.xml";s:4:"7eff";s:14:"res/styles.css";s:4:"6a92";s:17:"res/template.html";s:4:"f7ad";s:22:"res/template_email.txt";s:4:"7a01";s:36:"static/comments_report/constants.txt";s:4:"d26c";s:32:"static/comments_report/setup.txt";s:4:"2b42";}',
	'suggests' => array(
	),
);

?>