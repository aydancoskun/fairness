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


/**
 * @package Module_Install
 */
class InstallSchema_1063A extends InstallSchema_Base {

	/**
	 * @return bool
	 */
	function preInstall() {
		Debug::text('preInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		$plf = new PunchListFactory();
		$punch_control_ids = $plf->db->GetCol( 'SELECT distinct punch_control_id FROM punch WHERE deleted = 0 GROUP BY punch_control_id,status_id HAVING count(*) > 1' );

		if ( is_array($punch_control_ids) ) {
			Debug::text('Duplicate Punch Records: '. count($punch_control_ids), __FILE__, __LINE__, __METHOD__, 9);
			foreach( $punch_control_ids as $punch_control_id ) {
				Debug::text('  Punch Control ID: '. $punch_control_id, __FILE__, __LINE__, __METHOD__, 9);

				//Handle duplicate punch timestamps...
				$plf->getByPunchControlId( $punch_control_id, NULL, array( 'time_stamp' => 'asc') );
				if ( $plf->getRecordCount() > 2 ) {
					Debug::text('    Found Punches: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9);

					//If there are more than two duplicate punches, delete ones with identical timestamps.
					$prev_time_stamp = FALSE;
					$i = 0;
					foreach( $plf as $p_obj ) {
						if ( $prev_time_stamp !== FALSE AND $prev_time_stamp == $p_obj->getTimeStamp() ) {
							Debug::text('    Found Duplicate TimeStamp: '. $p_obj->getTimeStamp() .'('. $p_obj->getID() .') Deleting...', __FILE__, __LINE__, __METHOD__, 9);
							$plf->db->Execute('UPDATE punch SET deleted = 1 WHERE id = \''. TTUUID::castUUID($p_obj->getID()) .'\'' );
							$i++;
						}

						$prev_time_stamp = $p_obj->getTimeStamp();
					}

					//If there are more than two duplicate punches and no duplicate timestamps, delete ones with duplicate status_ids in timestamp order.
					//Check for duplicate statuses not in a row too, ie: 10, 20, 10.
					if ( $i == 0 ) {
						$prev_status_id = FALSE;
						foreach( $plf as $p_obj ) {
							if ( $prev_status_id !== FALSE AND in_array( $p_obj->getStatus(), $prev_status_id ) ) {
								Debug::text('    Found Duplicate Status: '. $p_obj->getStatus() .'('. $p_obj->getID() .') Deleting...', __FILE__, __LINE__, __METHOD__, 9);
								$plf->db->Execute('UPDATE punch SET deleted = 1 WHERE id = \''. TTUUID::castUUID($p_obj->getID()).'\'' );
								$i++;
							}

							$prev_status_id[] = $p_obj->getStatus();
						}
					}
				}

				//Handle punches with the same status_id
				$plf->getByPunchControlId( $punch_control_id, NULL, array( 'time_stamp' => 'asc') );
				if ( $plf->getRecordCount() == 2 ) {
					Debug::text('    Checking Duplicate Status Punches: '. $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9);
					$x = 0;
					foreach( $plf as $p_obj ) {
						if ( $x == 0 AND $p_obj->getStatus() != 10 ) {
							Debug::text('    Found Duplicate IN punches: '. $p_obj->getID() .' Correcting...', __FILE__, __LINE__, __METHOD__, 9);
							$plf->db->Execute('UPDATE punch SET status_id = 10 WHERE id = \''. TTUUID::castUUID($p_obj->getID()).'\'' );
						} elseif ( $x == 1 AND $p_obj->getStatus() != 20 ) {
							Debug::text('    Found Duplicate OUT punches: '. $p_obj->getID() .' Correcting...', __FILE__, __LINE__, __METHOD__, 9);
							$plf->db->Execute('UPDATE punch SET status_id = 20 WHERE id = \''. TTUUID::castUUID($p_obj->getID()).'\'' );
						}
						$x++;
					}
				}

			}
		}

		Debug::text('preInstall Done: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		return TRUE;
	}

	/**
	 * @return bool
	 */
	function postInstall() {
		Debug::text('postInstall: '. $this->getVersion(), __FILE__, __LINE__, __METHOD__, 9);

		$clf = TTNew('CompanyListFactory'); /** @var CompanyListFactory $clf */
		$clf->getAll();
		if ( $clf->getRecordCount() > 0 ) {
			$x = 0;
			foreach( $clf as $company_obj ) {
				//Go through each permission group, and enable schedule, view_open for for anyone who has schedule, view
				Debug::text('Company: '. $company_obj->getName() .' X: '. $x .' of :'. $clf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9);

				//Populate currency rate table.
				CurrencyFactory::updateCurrencyRates( $company_obj->getId() );

				$x++;
			}
		}

		return TRUE;
	}
}
?>
