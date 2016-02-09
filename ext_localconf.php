<?php
if (! defined ( 'TYPO3_MODE' )) {
	die ( 'Access denied.' );
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin ( 'MAB.' . $_EXTKEY, 'Pi1', array (
		'Openingtimes' => 'today' 
), array (
		'Openingtimes' => '' 
) );

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin ( 'MAB.' . $_EXTKEY, 'Pi2', array (
		'Openingtimes' => 'show'
), array (
		'Openingtimes' => ''
) );

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin ( 'MAB.' . $_EXTKEY, 'Pi3', array (
		'Openingtimes' => 'summary'
), array (
		'Openingtimes' => ''
) );