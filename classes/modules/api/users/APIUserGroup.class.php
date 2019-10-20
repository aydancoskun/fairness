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
 * @package API\Users
 */
class APIUserGroup extends APIFactory {
	protected $main_class = 'UserGroupFactory';

	/**
	 * APIUserGroup constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return TRUE;
	}

	/**
	 * Get options for dropdown boxes.
	 * @param bool|string $name Name of options to return, ie: 'columns', 'type', 'status'
	 * @param mixed $parent Parent name/ID of options to return if data is in hierarchical format. (ie: Province)
	 * @return bool|array
	 */
	function getOptions( $name = FALSE, $parent = NULL ) {
		if ( $name == 'columns'
				AND ( !$this->getPermissionObject()->Check('user', 'enabled')
					OR !( $this->getPermissionObject()->Check('user', 'view') OR $this->getPermissionObject()->Check('user', 'view_own') OR $this->getPermissionObject()->Check('user', 'view_child') ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default UserGroup data for creating new UserGroupes.
	 * @return array
	 */
	function getUserGroupDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		Debug::Text('Getting UserGroup default data...', __FILE__, __LINE__, __METHOD__, 10);

		$data = array(
						'company_id' => $company_obj->getId(),
						'parent_id' => 0,
						'name' => NULL,
					);

		return $this->returnHandler( $data );
	}

	/**
	 * Get UserGroup data for one or more UserGroupes.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @param string $mode
	 * @return array
	 */
	function getUserGroup( $data = NULL, $disable_paging = FALSE, $mode = 'flat' ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !$this->getPermissionObject()->Check('user', 'enabled')
				OR !( $this->getPermissionObject()->Check('user', 'view') OR $this->getPermissionObject()->Check('user', 'view_own') OR $this->getPermissionObject()->Check('user', 'view_child') ) ) {
			//return $this->getPermissionObject()->PermissionDenied();
			$data['filter_columns'] = $this->handlePermissionFilterColumns( (isset($data['filter_columns'])) ? $data['filter_columns'] : NULL, Misc::trimSortPrefix( $this->getOptions('list_columns') ) );
		}

		//Allow supervisor (subordinates only) to see all groups.
		//$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'user', 'view' );

		//Allow getting users from other companies, so we can change admin contacts when using the master company.
		if ( isset($data['filter_data']['company_id'])
				AND TTUUID::isUUID( $data['filter_data']['company_id'] ) AND $data['filter_data']['company_id'] != TTUUID::getZeroID() AND $data['filter_data']['company_id'] != TTUUID::getNotExistID()
				AND ( $this->getPermissionObject()->Check('company', 'enabled') AND $this->getPermissionObject()->Check('company', 'view') ) ) {
			$company_id = $data['filter_data']['company_id'];
		} else {
			$company_id = $this->getCurrentCompanyObject()->getId();
		}

		$uglf = TTnew( 'UserGroupListFactory' ); /** @var UserGroupListFactory $uglf */
		if ( $mode == 'flat' ) {
			$uglf->getAPISearchByCompanyIdAndArrayCriteria( $company_id, $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], NULL, $data['filter_sort'] );
			Debug::Text('Record Count: '. $uglf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $uglf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->start( $this->getAMFMessageID(), $uglf->getRecordCount() );

				$this->setPagerObject( $uglf );

				$retarr = array();
				foreach( $uglf as $ug_obj ) {
					$retarr[] = $ug_obj->getObjectAsArray( $data['filter_columns'] );

					$this->getProgressBarObject()->set( $this->getAMFMessageID(), $uglf->getCurrentRow() );
				}

				$this->getProgressBarObject()->stop( $this->getAMFMessageID() );

				return $this->returnHandler( $retarr );
			}
		} else {
			$nodes = $uglf->getByCompanyIdArray( $company_id );
			//Debug::Arr($nodes, ' Nodes: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Text('Record Count: '. count($nodes), __FILE__, __LINE__, __METHOD__, 10);
			if ( isset($nodes) ) {
				$retarr = TTTree::FormatArray( $nodes );
				//Debug::Arr($retarr, ' Data: ', __FILE__, __LINE__, __METHOD__, 10);

				return $this->returnHandler( $retarr );
			}
		}

		return $this->returnHandler( TRUE ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonUserGroupData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getUserGroup( $data, TRUE ) ) );
	}

	/**
	 * Validate UserGroup data for one or more UserGroupes.
	 * @param array $data UserGroup data
	 * @return array
	 */
	function validateUserGroup( $data ) {
		return $this->setUserGroup( $data, TRUE );
	}

	/**
	 * Set UserGroup data for one or more UserGroupes.
	 * @param array $data UserGroup data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setUserGroup( $data, $validate_only = FALSE, $ignore_warning = TRUE ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('user', 'enabled')
				OR !( $this->getPermissionObject()->Check('user', 'edit') OR $this->getPermissionObject()->Check('user', 'edit_own') OR $this->getPermissionObject()->Check('user', 'edit_child') OR $this->getPermissionObject()->Check('user', 'add') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == TRUE ) {
			Debug::Text('Validating Only!', __FILE__, __LINE__, __METHOD__, 10);
		}

		list( $data, $total_records ) = $this->convertToMultipleRecords( $data );
		Debug::Text('Received data for: '. $total_records .' UserGroups', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		$validator = $save_result = $key = FALSE;
		if ( is_array($data) AND $total_records > 0 ) {
			foreach( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'UserGroupListFactory' ); /** @var UserGroupListFactory $lf */
				$lf->StartTransaction();
				if ( isset($row['id']) AND $row['id'] != '' ) {
					//Modifying existing object.
					//Get UserGroup object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
							$validate_only == TRUE
							OR
								(
								$this->getPermissionObject()->Check('user', 'edit')
									OR ( $this->getPermissionObject()->Check('user', 'edit_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE )
								) ) {

							Debug::Text('Row Exists, getting current data for ID: '. $row['id'], __FILE__, __LINE__, __METHOD__, 10);
							$lf = $lf->getCurrent();
							$row = array_merge( $lf->getObjectAsArray(), $row );
						} else {
							$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Edit permission denied') );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Edit permission denied, record does not exist') );
					}
				} else {
					//Adding new object, check ADD permissions.
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check('user', 'add'), TTi18n::gettext('Add permission denied') );
				}
				Debug::Arr($row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid( $ignore_warning );
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Setting object data...', __FILE__, __LINE__, __METHOD__, 10);

					//Force Company ID to current company.
					$row['company_id'] = $this->getCurrentCompanyObject()->getId();

					$lf->setObjectFromArray( $row );

					$is_valid = $lf->isValid( $ignore_warning );
					if ( $is_valid == TRUE ) {
						Debug::Text('Saving data...', __FILE__, __LINE__, __METHOD__, 10);
						if ( $validate_only == TRUE ) {
							$save_result[$key] = TRUE;
						} else {
							$save_result[$key] = $lf->Save();
						}
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == FALSE ) {
					Debug::Text('Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					$validator[$key] = $this->setValidationArray( $primary_validator, $lf );
				} elseif ( $validate_only == TRUE ) {
					$lf->FailTransaction();
				}


				$lf->CommitTransaction();
			}

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Delete one or more UserGroups.
	 * @param array $data UserGroup data
	 * @return array|bool
	 */
	function deleteUserGroup( $data ) {
		if ( !is_array($data) ) {
			$data = array($data);
		}

		if ( !is_array($data) ) {
			return $this->returnHandler( FALSE );
		}

		if ( !$this->getPermissionObject()->Check('user', 'enabled')
				OR !( $this->getPermissionObject()->Check('user', 'delete') OR $this->getPermissionObject()->Check('user', 'delete_own') OR $this->getPermissionObject()->Check('user', 'delete_child') ) ) {
			return	$this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text('Received data for: '. count($data) .' UserGroups', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$total_records = count($data);
		$validator = $save_result = $key = FALSE;
		$validator_stats = array('total_records' => $total_records, 'valid_records' => 0 );
		if ( is_array($data) AND $total_records > 0 ) {
			foreach( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'UserGroupListFactory' ); /** @var UserGroupListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get UserGroup object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check('user', 'delete')
								OR ( $this->getPermissionObject()->Check('user', 'delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE ) ) {
							Debug::Text('Record Exists, deleting record ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
							$lf = $lf->getCurrent();
						} else {
							$primary_validator->isTrue( 'permission', FALSE, TTi18n::gettext('Delete permission denied') );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Delete permission denied, record does not exist') );
					}
				} else {
					$primary_validator->isTrue( 'id', FALSE, TTi18n::gettext('Delete permission denied, record does not exist') );
				}

				//Debug::Arr($lf, 'AData: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == TRUE ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text('Attempting to delete record...', __FILE__, __LINE__, __METHOD__, 10);
					$lf->setDeleted(TRUE);

					$is_valid = $lf->isValid();
					if ( $is_valid == TRUE ) {
						Debug::Text('Record Deleted...', __FILE__, __LINE__, __METHOD__, 10);
						$save_result[$key] = $lf->Save();
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == FALSE ) {
					Debug::Text('Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10);

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					$validator[$key] = $this->setValidationArray( $primary_validator, $lf );
				}

				$lf->CommitTransaction();
			}

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( FALSE );
	}

	/**
	 * Change parent of one or more groups to another group.
	 * @param array $src_id source Group ID
	 * @param int $dst_id destination Group ID
	 * @return array
	 */
	function dragNdropUserGroup( $src_id, $dst_id ) {
		if ( !is_array($src_id) ) {
			$src_id = array($src_id);
		}

		if ( is_array($dst_id) ) {
			return $this->returnHandler( FALSE );
		}

		Debug::Arr($src_id, 'Src ID: Data: ', __FILE__, __LINE__, __METHOD__, 10);
		Debug::Arr($dst_id, 'Dst ID: Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$src_rows = $this->stripReturnHandler( $this->getUserGroup( array('filter_data' => array('id' => $src_id ) ), TRUE, 'flat' ) );
		if ( is_array( $src_rows ) AND count($src_rows) > 0 ) {
			Debug::Arr($src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);
			foreach( $src_rows as $key => $row ) {
				$src_rows[$key]['parent_id'] = $dst_id;
			}
			unset($row); //code standards
			Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setUserGroup( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( FALSE );
	}
}
?>
