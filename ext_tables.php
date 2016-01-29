<?php
if (! defined ( 'TYPO3_MODE' )) {
	die ( 'Access denied.' );
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin ( 'MAB.' . $_EXTKEY, 'Pi1', 'Öffnungszeiten: Heute' );

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin ( 'MAB.' . $_EXTKEY, 'Pi2', 'Öffnungszeiten: Woche' );

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile ( $_EXTKEY, 'Configuration/TypoScript', 'Opening times' );