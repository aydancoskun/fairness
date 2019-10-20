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
 * @package Core
 */
class UserDateListFactory extends UserDateFactory implements IteratorAggregate {

	/**
	 * @param int $limit Limit the number of records returned
	 * @param int $page Page number of records to return for pagination
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return $this
	 */
	function getAll( $limit = NULL, $page = NULL, $where = NULL, $order = NULL) {
		$query = '
					select	*
					from	'. $this->getTable() .'
					WHERE deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, NULL, $limit, $page );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDateListFactory
	 */
	function getById( $id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array(
					'id' => TTUUID::castUUID($id),
					);


		$this->rs = $this->getCache($id);
		if ( $this->rs === FALSE ) {

			$query = '
						select	*
						from	'. $this->getTable() .'
						where	id = ?
							AND deleted = 0';
			$query .= $this->getWhereSQL( $where );
			$query .= $this->getSortSQL( $order );

			$this->rs = $this->ExecuteSQL( $query, $ph );

			$this->saveCache($this->rs, $id);
		}

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDateListFactory
	 */
	function getByIds( $id, $where = NULL, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		$ph = array();

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	id in ('. $this->getListSQL( $id, $ph, 'uuid' ) .')
						AND deleted = 0';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDateListFactory
	 */
	function getByCompanyId( $company_id, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => TTUUID::castUUID($company_id),
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $id UUID
	 * @param string $company_id UUID
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDateListFactory
	 */
	function getByIdAndCompanyId( $id, $company_id, $order = NULL) {
		if ( $id == '') {
			return FALSE;
		}

		if ( $company_id == '') {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => TTUUID::castUUID($company_id),
					'id' => TTUUID::castUUID($id),
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where	a.user_id = b.id
						AND	b.company_id = ?
						AND	a.id = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date EPOCH
	 * @param $status
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDateListFactory
	 */
	function getByCompanyIdAndStartDateAndEndDateAndPayPeriodStatus( $company_id, $start_date, $end_date, $status, $where = NULL, $order = NULL) {
		if ( $company_id == '') {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'a.user_id' => 'asc', 'a.date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();
		$ppf = new PayPeriodFactory();

		$ph = array(
					'company_id' => TTUUID::castUUID($company_id),
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
						LEFT JOIN '. $ppf->getTable() .' as c ON a.pay_period_id = c.id
					where	b.company_id = ?
						AND a.date_stamp >= ?
						AND a.date_stamp <= ?
						AND c.status_id in ('. $this->getListSQL($status, $ph) .')
						AND ( a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0 )';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDateListFactory
	 */
	function getByUserId( $user_id, $order = NULL) {
		if ( $user_id == '') {
			return FALSE;
		}

		$ph = array(
					'user_id' => TTUUID::castUUID($user_id),
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	user_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $pay_period_id UUID
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDateListFactory
	 */
	function getByPayPeriodId( $pay_period_id, $order = NULL) {
		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		$ph = array(
					'pay_period_id' => TTUUID::castUUID($pay_period_id),
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where	pay_period_id = ?
						AND deleted = 0';
		$query .= $this->getSortSQL( $order );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param int $date EPOCH
	 * @return bool|UserDateListFactory
	 */
	function getByDate( $date) {
		if ( $date == '' ) {
			return FALSE;
		}

		$uf = new UserFactory();

		$ph = array(
					'date' => $this->db->BindDate( $date ),
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a
						LEFT JOIN '. $uf->getTable() .' as b ON a.user_id = b.id
					where
						a.date_stamp = ?
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $date EPOCH
	 * @return bool|UserDateListFactory
	 */
	function getByUserIdAndDate( $user_id, $date) {
		if ( $user_id === '' ) {
			return FALSE;
		}

		if ( $date == '' OR $date <= 0 ) {
			return FALSE;
		}

		$ph = array(
					'user_id' => TTUUID::castUUID($user_id),
					'date' => $this->db->BindDate( $date ),
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						user_id = ?
						AND date_stamp = ?
						AND deleted = 0
					ORDER BY id ASC
					';

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_ids UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date EPOCH
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDateListFactory
	 */
	function getByUserIdAndStartDateAndEndDate( $user_ids, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_ids == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						date_stamp >= ?
						AND date_stamp <= ?
						AND user_id in ('. $this->getListSQL( $user_ids, $ph, 'uuid') .')
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_ids UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date EPOCH
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDateListFactory
	 */
	function getByUserIdAndStartDateAndEndDateAndEmptyPayPeriod( $user_ids, $start_date, $end_date, $where = NULL, $order = NULL) {
		if ( $user_ids == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array(
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						date_stamp >= ?
						AND date_stamp <= ?
						AND user_id in ('. $this->getListSQL( $user_ids, $ph, 'uuid') .')
						AND ( pay_period_id = \''. TTUUID::getZeroID() .'\' OR pay_period_id IS NULL )
						AND deleted = 0
					';
		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param string $pay_period_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDateListFactory
	 */
	function getByUserIdAndPayPeriodID( $user_id, $pay_period_id, $where = NULL, $order = NULL) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		//Order matters here, as this is mainly used for recalculating timesheets.
		//The days must be returned in order.
		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$ph = array();

		$query = '
					select	*
					from	'. $this->getTable() .'
					where
						user_id in ('. $this->getListSQL( $user_id, $ph, 'uuid' ) .')
						AND pay_period_id in ('. $this->getListSQL( $pay_period_id, $ph, 'uuid' ) .')
						AND deleted = 0
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $pay_period_id UUID
	 * @param array $where Additional SQL WHERE clause in format of array( $column => $filter, ... ). ie: array( 'id' => 1, ... )
	 * @param array $order Sort order passed to SQL in format of array( $column => 'asc', 'name' => 'desc', ... ). ie: array( 'id' => 'asc', 'name' => 'desc', ... )
	 * @return bool|UserDateListFactory
	 */
	function getByCompanyIdAndPayPeriodID( $company_id, $pay_period_id, $where = NULL, $order = NULL) {
		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $pay_period_id == '' ) {
			return FALSE;
		}

		//Order matters here, as this is mainly used for recalculating timesheets.
		//The days must be returned in order.
		if ( $order == NULL ) {
			$order = array( 'a.date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}

		$uf = new UserFactory();

		$ph = array(
					'company_id' => TTUUID::castUUID($company_id),
					//'pay_period_id' => TTUUID::castUUID($pay_period_id),
					);

		$query = '
					select	a.*
					from	'. $this->getTable() .' as a,
							'. $uf->getTable() .' as b
					where
						a.user_id = b.id
						AND b.company_id = ?
						AND a.pay_period_id in ('. $this->getListSQL( $pay_period_id, $ph, 'uuid' ) .')
						AND ( a.deleted = 0 AND b.deleted = 0 )
					';

		$query .= $this->getWhereSQL( $where );
		$query .= $this->getSortSQL( $order, $strict );

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * Used by calcQuickExceptions maintenance job to speed up finding days that need to have exceptions calculated throughout the day.
	 * @param int $start_date EPOCH
	 * @param int $end_date EPOCH
	 * @param int $pay_period_status_id
	 * @return bool|UserDateListFactory
	 */
	function getMidDayExceptionsByStartDateAndEndDateAndPayPeriodStatus( $start_date, $end_date, $pay_period_status_id ) {
		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		if ( $pay_period_status_id == '' ) {
			return FALSE;
		}

		$epf = new ExceptionPolicyFactory();
		$ef = new ExceptionFactory();
		$epcf = new ExceptionPolicyControlFactory();
		$pgf = new PolicyGroupFactory();
		$pguf = new PolicyGroupUserFactory();
		$uf = new UserFactory();
		$cf = new CompanyFactory();
		$udf = new UserDateFactory();
		$sf = new ScheduleFactory();
		$pcf = new PunchControlFactory();
		$pf = new PunchFactory();
		$ppf = new PayPeriodFactory();

		$current_epoch = time();

		$ph = array(
					'current_time1' => $this->db->BindTimeStamp( $current_epoch ),
					'current_time2' => $this->db->BindTimeStamp( $current_epoch ),
					'current_epoch1' => $current_epoch,
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		//Exceptions that need to be calculated in the middle of the day:
		//Definitely: In Late, Out Late, Missed CheckIn
		//Possible: Over Daily Scheduled Time, Over Weekly Scheduled Time, Over Daily Time, Over Weekly Time, Long Lunch (can't run this fast enough), Long Break (can't run this fast enough),
		//Optimize calcQuickExceptions:
		// Loop through exception policies where In Late/Out Late/Missed CheckIn are enabled.
		// Loop through ACTIVE users assigned to these exceptions policies.
		// Only find days that are scheduled AND ( NO punch after schedule start time OR NO punch after schedule end time )
		//		For Missed CheckIn they do not need to be scheduled.
		// Exclude days that already have the exceptions triggered on them (?) (What about split shifts?)
		//	- Just exclude exceptions not assigned to punch/punch_control_id, if there is more than one in the day I don't think it helps much anyways.
		//
		//Currently Over Weekly/Daily time exceptions are only triggered on a Out punch.
		$query = '	select distinct udf.*
					FROM '. $epf->getTable() .' as epf
					LEFT JOIN '. $epcf->getTable() .' as epcf ON ( epf.exception_policy_control_id = epcf.id )
					LEFT JOIN '. $pgf->getTable() .' as pgf ON ( epcf.id = pgf.exception_policy_control_id )
					LEFT JOIN '. $pguf->getTable() .' as pguf ON ( pgf.id = pguf.policy_group_id )
					LEFT JOIN '. $uf->getTable() .' as uf ON ( pguf.user_id = uf.id )
					LEFT JOIN '. $cf->getTable() .' as cf ON ( uf.company_id = cf.id )
					LEFT JOIN '. $udf->getTable() .' as udf ON ( uf.id = udf.user_id )
					LEFT JOIN '. $ppf->getTable() .' as ppf ON ( ppf.id = udf.pay_period_id )
					LEFT JOIN '. $ef->getTable() .' as ef ON ( udf.id = ef.user_date_id AND ef.exception_policy_id = epf.id AND ef.type_id != 5 )
					LEFT JOIN '. $sf->getTable() .' as sf ON ( udf.id = sf.user_date_id AND ( sf.start_time <= ? OR sf.end_time <= ? ) )
					LEFT JOIN '. $pcf->getTable() .' as pcf ON ( udf.id = pcf.user_date_id AND pcf.deleted = 0 )
					LEFT JOIN '. $pf->getTable() .' as pf ON	(
																pcf.id = pf.punch_control_id AND pf.deleted = 0
																AND (
																		( epf.type_id = \'S4\' AND ( pf.time_stamp >= sf.start_time OR pf.time_stamp <= sf.end_time ) )
																		OR
																		( epf.type_id = \'S6\' AND ( pf.time_stamp >= sf.end_time ) )
																		OR
																		( epf.type_id = \'C1\' AND ( pf.status_id = 10 AND pf.time_stamp <= '. $this->getSQLToTimeStampFunction() .'(?-epf.grace) ) )
																	)
																)
					WHERE ( epf.type_id in (\'S4\', \'S6\', \'C1\') AND epf.active = 1 )
						AND ( uf.status_id = 10 AND cf.status_id != 30 )
						AND ( udf.date_stamp >= ? AND udf.date_stamp <= ? )
						AND ppf.status_id in ('. $this->getListSQL( $pay_period_status_id, $ph, 'int' ) .')
						AND ( ( ( epf.type_id in (\'S4\', \'S6\') AND ( sf.id IS NOT NULL AND sf.deleted = 0 ) AND pf.id IS NULL ) OR epf.type_id = \'C1\' ) AND ef.id IS NULL	)
						AND ( epf.deleted = 0 AND epcf.deleted = 0 AND pgf.deleted = 0 AND uf.deleted = 0 AND cf.deleted = 0 AND udf.deleted = 0 )
				';
				//Don't check deleted = 0 on PCF/PF tables, as we need to check IS NULL on them instead.

		//Debug::Arr($ph, 'Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/*

		Report functions

	*/

	/**
	 * @param $time_period
	 * @param string $user_ids UUID
	 * @param string $company_id UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date EPOCH
	 * @return bool|UserDateListFactory
	 */
	function getDaysWorkedByTimePeriodAndUserIdAndCompanyIdAndStartDateAndEndDate( $time_period, $user_ids, $company_id, $start_date, $end_date ) {
		if ( $time_period == '' ) {
			return FALSE;
		}

		if ( $user_ids == '' ) {
			return FALSE;
		}

		if ( $company_id == '' ) {
			return FALSE;
		}

		if ( $start_date == '' ) {
			return FALSE;
		}

		if ( $end_date == '' ) {
			return FALSE;
		}

		/*
		if ( $order == NULL ) {
			$order = array( 'date_stamp' => 'asc' );
			$strict = FALSE;
		} else {
			$strict = TRUE;
		}
		*/

		$uf = new UserFactory();
		$pcf = new PunchControlFactory();

		$ph = array(
					'company_id' => TTUUID::castUUID($company_id),
					'start_date' => $this->db->BindDate( $start_date ),
					'end_date' => $this->db->BindDate( $end_date ),
					);

		$query = '
					select	user_id,
							avg(total) as avg,
							min(total) as min,
							max(total) as max
					from (

						select	a.user_id,
								(EXTRACT('.$time_period.' FROM a.date_stamp) || \'-\' || EXTRACT(year FROM a.date_stamp) ) as date,
								count(*) as total
						from	'. $this->getTable() .' as a,
								'. $uf->getTable() .' as b
						where	a.user_id = b.id
							AND b.company_id = ?
							AND a.date_stamp >= ?
							AND a.date_stamp <= ?
							AND a.user_id in ('. $this->getListSQL( $user_ids, $ph, 'uuid') .')
							AND exists(
										select id
										from '. $pcf->getTable() .' as z
										where z.user_date_id = a.id
										AND z.deleted=0
										)
							AND ( a.deleted = 0 AND b.deleted=0 )
							GROUP BY user_id, (EXTRACT('.$time_period.' FROM a.date_stamp) || \'-\' || EXTRACT(year FROM a.date_stamp) )
						) tmp
					GROUP BY user_id
					';

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $date EPOCH
	 * @param $deleted
	 * @return bool|UserDateListFactory
	 */
	function deleteByUserIdAndDateAndDeleted( $user_id, $date, $deleted ) {
		if ( $user_id == '' ) {
			return FALSE;
		}

		if ( $date == '' OR $date <= 0 ) {
			return FALSE;
		}

		if ( $deleted == '' ) {
			return FALSE;
		}

		$ph = array(
					'user_id' => TTUUID::castUUID($user_id),
					'date' => $this->db->BindDate( $date ),
					'deleted' => (int)$deleted
					);

		$query = '
					delete
					from	'. $this->getTable() .'
					where
						user_id = ?
						AND date_stamp = ?
						AND deleted = ?
					';

		$this->rs = $this->ExecuteSQL( $query, $ph );

		return $this;
	}

	/**
	 * @param $lf
	 * @return array|bool
	 */
	function getArrayByListFactory( $lf) {
		if ( !is_object($lf) ) {
			return FALSE;
		}

		$list = array();
		foreach ($lf as $obj) {
			$list[] = $obj->getID();
		}

		if ( empty($list) == FALSE ) {
			return $list;
		}

		return FALSE;
	}

}
?>
