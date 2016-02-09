<?php

namespace MAB\MabOpeningtimes\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Marcel Briefs <t3@lbrmedia.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 * OpeningtimesController
 */
class OpeningtimesController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	
	/**
	 * action today
	 *
	 * @return void
	 */
	public function todayAction() {
		$configuration = $this->getConfiguration ();
		if (is_array ( $configuration[ 'today' ] ) === FALSE) {
			return "";
		}
		$this->view->assign ( "today", $configuration[ 'today' ] );
	}
	
	/**
	 * action show
	 *
	 * @return void
	 */
	public function showAction() {
		$configuration = $this->getConfiguration ();
		$this->view->assign ( "weekdays", $configuration[ 'weekdays' ] );
		$this->view->assign ( "future", $configuration[ 'future' ] );
		$this->view->assign ( "summary", $configuration[ 'summary' ] );
	}
	
	/**
	 * action summary
	 *
	 * @return void
	 */
	public function summaryAction() {
		$configuration = $this->getConfiguration ();
		$this->view->assign ( "weekdays", $configuration[ 'weekdays' ] );
		$this->view->assign ( "future", $configuration[ 'future' ] );
		$this->view->assign ( "summary", $configuration[ 'summary' ] );
	}
	
	/**
	 * 
	 * @throws \Exception
	 */
	protected function getConfiguration() {
		if (isset ( $this->settings[ 'configurationFile' ] ) === FALSE || trim ( $this->settings[ 'configurationFile' ] ) === "") {
			throw new \Exception ( "You have to define plugin.tx_mabopeningtimes.settings.configurationFile!" );
		}
		
		if (is_file ( PATH_site . $this->settings[ 'configurationFile' ] ) === FALSE) {
			throw new \Exception ( "Could not find file '" . $this->settings[ 'configurationFile' ] . "' defined in plugin.tx_mabopeningtimes.settings.configurationFile!" );
		}
		
		// read the file into an array
		$lines = file ( PATH_site . $this->settings[ 'configurationFile' ] );
		
		// iterate the lines and build configuration
		$configuration = [ 
				'today' => '',
				'weekdays' => [ 
						'monday' => [ ],
						'tuesday' => [ ],
						'wednesday' => [ ],
						'thursday' => [ ],
						'friday' => [ ],
						'saturday' => [ ],
						'sunday' => [ ] 
				],
				'future' => [ ],
				'summary' => [ ] 
		];
		$todayObj = new \DateTime ();
		$todayObj->setTime ( 12, 0, 0 );
		$weekday = strtolower ( $todayObj->format ( "l" ) );
		$datekey = strtolower ( $todayObj->format ( "Y-m-d" ) );
		
		foreach ( $lines as $line ) {
			// skip empty lines and configuration lines
			if (! trim ( $line ) || substr ( trim ( $line ), 0, 1 ) == "#") {
				continue;
			}
			
			// get the key and the value (divided by =)
			$equalPos = strpos ( $line, "=" );
			$key = strtolower ( trim ( substr ( $line, 0, $equalPos ) ) );
			$value = trim ( substr ( $line, $equalPos + 1 ) );
			
			if (! ($key && $value)) {
				continue;
			}
			
			if ($key === $datekey) {
				$configuration[ 'today' ] = $this->determineValue ( $value );
			} else if ($key === $weekday) {
				$configuration[ 'today' ] = $this->determineValue ( $value );
			} else if ($key === "summary") {
				$configuration[ 'summary' ] = $value;
			} else if (preg_match ( '/^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$/', $key )) {
				$dtObj = new \DateTime ( $key );
				$dtObj->setTime ( 12, 0, 0 );
				if ($dtObj && $dtObj > $todayObj) {
					if (!isset($configuration[ 'future' ][ $key ])) {
						$configuration[ 'future' ][ $key ] = [];
						$configuration[ 'future' ][ $key ][ 'values' ] = [];
					}
					$configuration[ 'future' ][ $key ][ 'values' ][] = $this->determineValue ( $value );
					$configuration[ 'future' ][ $key ][ 'DateTime' ] = $dtObj;
				}
			} 
			
			if (in_array ( $key, array (
					'monday',
					'tuesday',
					'wednesday',
					'thursday',
					'friday',
					'saturday',
					'sunday' 
			) )) {
				$configuration[ 'weekdays' ][ $key ] = $this->determineValue ( $value );
			}
		}
		
		ksort ( $configuration[ 'future' ] );
		
		return $configuration;
	}
	
	/**
	 * @param string $value
	 * @return array
	 */
	protected function determineValue($value) {
		$retArr = [ ];
		$parts = \TYPO3\CMS\Extbase\Utility\ArrayUtility::trimExplode ( "|", $value );
		foreach ( $parts as $num => $part ) {
			if (preg_match ( '/^([0-9]|[0-1][0-9]|2[0-3]):([0-5][0-9])$/', $part )) {
				$retArr[ $num ] = array (
						'type' => "singletime",
						'value' => $part 
				);
			} else if (preg_match ( "/^([0-9]|[0-1][0-9]|2[0-3]):([0-5][0-9])-([0-9]|[0-1][0-9]|2[0-3]):([0-5][0-9])$/", $part )) {
				$timeParts = explode ( "-", $part );
				$retArr[ $num ] = array (
						'type' => "doubletime",
						'value1' => $timeParts[ 0 ],
						'value2' => $timeParts[ 1 ] 
				);
			} else if ($part) {
				$retArr[ $num ] = array (
						'type' => "string",
						'value' => $part 
				);
			}
		}
		
		return $retArr;
	}
}