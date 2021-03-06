<?php
/*********************************************************************************
 * FairnessTNA is a Workforce Management program forked from TimeTrex in 2013,
 * copyright Aydan Coskun. Original code base is copyright TimeTrex Software Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact Aydan Coskun via issue tracker on github.com/aydancoskun
 ********************************************************************************/

require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'global.inc.php');
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'includes'. DIRECTORY_SEPARATOR .'CLI.inc.php');

if ( isset($argv[1]) AND in_array($argv[1], array('--help', '-help', '-h', '-?') ) ) {
	$help_output = "Usage: fix_client_balance.php -company_id [company_id] -client_id [client_id]\n";
	echo $help_output;
} else {
	if ( in_array('-company_id', $argv) ) {
		$company_id = trim($argv[array_search('-company_id', $argv)+1]);
	}

	if ( in_array('-client_id', $argv) ) {
		$client_id = trim($argv[array_search('-client_id', $argv)+1]);
	}

	//Force flush after each output line.
	ob_implicit_flush( TRUE );
	ob_end_flush();

	$clf = new CompanyListFactory();
	if ( isset($company_id) AND $company_id != '' ) {
		$clf->getByCompanyId( $company_id );
	} else {
		$clf->getAll();
	}
	if ( $clf->getRecordCount() > 0 ) {
		foreach ( $clf as $c_obj ) {
			echo 'Company: '. $c_obj->getName() ."...\n";

			$cbf = new ClientBalanceFactory();
			$cbf->StartTransaction();

			$tmp_clf = new ClientListFactory();
			if ( isset($client_id) AND $client_id > 0 ) {
				$tmp_clf->getByIdAndCompanyId( $client_id, $c_obj->getId() );
			} else {
				$tmp_clf->getByCompanyId( $c_obj->getId() );
			}

			$max = $tmp_clf->getRecordCount();
			$i = 0;
			foreach( $tmp_clf as $tmp_c_obj ) {
				//if ( !in_array( $tmp_c_obj->getId(), array(195,1249,1800) ) ) {
				//	continue;
				//}

				echo '  '. $i .'/'. $max .' Recalculating: '. $tmp_c_obj->getCompanyName() ."...\n";
				$cbf->reCalculateBalance( $tmp_c_obj->getId(), $tmp_c_obj->getCompany() );

				$i++;
			}

			//$cbf->FailTransaction();
			$cbf->CommitTransaction();
		}
	}

}
//Debug::Display();
?>
