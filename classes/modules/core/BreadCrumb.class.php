<?php
/*********************************************************************************
 * This file is part of "Fairness", a Payroll and Time Management program.
 * Fairness is Copyright 2013 Aydan Coscun (aydan.ayfer.coskun@gmail.com)
 * Portions of this software are Copyright (C) 2003 - 2013 TimeTrex Software Inc.
 * because Fairness is a fork of "TimeTrex Workforce Management" Software.
 *
 * Fairness is free software; you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation, either version 3 of the License, or (at you option )
 * any later version.
 *
 * Fairness is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
  ********************************************************************************/
/*
 * $Revision: 2095 $
 * $Id: BreadCrumb.class.php 2095 2008-09-01 07:04:25Z ipso $
 * $Date: 2008-09-01 00:04:25 -0700 (Mon, 01 Sep 2008) $
 */

/**
 * @package Core
 */
class BreadCrumb {
	static $home_name = 'Home';
	static $seperator = ' > ';

	static function setCrumb($name, $url = NULL) {
		global $db, $current_user;

		//
		// If bread crumbs "seem" like they are getting overwritten, make sure the
		// setCrumb function is being called ONLY in the default section of the switch statement. NOT THE TOP.
		//

		if ( $url == '' ) {
			$url = $_SERVER['REQUEST_URI'];
		}

		if ( !is_object( $current_user ) ) {
			return FALSE;
		}

		Debug::text('Dropping Bread Crumb: '. $name .' URL: '. $url, __FILE__, __LINE__, __METHOD__, 10);

		$ph = array(
					'user_id' => $current_user->getId(),
					'name' => $name,
					);

		//Determine if we should update or insert bread crumb.
		$query = 'select name
					FROM bread_crumb
					WHERE user_id = ?
						AND name = ?
					LIMIT 1';
		try {
			$rs = $db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		if ( $rs->RecordCount() == 1 ) {
			$ph = array(
						'url' => $url,
						'created_date' => TTDate::getTime(),
						'user_id' => $current_user->getId(),
						'name' => $name,
						);

			$query = 'UPDATE bread_crumb
						SET		url = ?,
								created_date = ?
						WHERE	user_id = ?
							AND name = ?';
		} else {
			$ph = array(
						'user_id' => $current_user->getId(),
						'name' => $name,
						'url' => $url,
						'created_date' => TTDate::getTime(),
						);

			$query = 'insert into bread_crumb (user_id,name,url,created_date)
							VALUES(
									?,
									?,
									?,
									?
								)';
		}
		try {
			$db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	static function getCrumbs() {
		global $db, $current_user;

		$ph = array(
					'user_id' => $current_user->getId(),
					);

		$query = 'SELECT name,url
					FROM bread_crumb
					WHERE user_id = ?
					ORDER BY created_date DESC
					LIMIT 5';

		//Debug::text('Query: '. $query, __FILE__, __LINE__, __METHOD__, 10);

		try {
			$rs = $db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		$result = $rs->GetRows();

		foreach ($result as $row) {
			$retarr[] = array(
								'name' => $row['name'],
								'url' => $row['url']);
			//Debug::text('Picking up Bread Crumb: '. $row['name'] .' URL: '. $row['url'], __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( isset($retarr) ) {
			return $retarr;
		}

		return FALSE;
	}

	static function Delete($user_id = NULL) {
		global $db, $current_user;

		if ( empty($user_id) ) {
			if ( is_object($current_user) ) {
				$user_id = $current_user->getId();
			} else {
				return FALSE;
			}
		}

		$ph = array(
					'user_id' => $user_id,
					);

		$query = 'DELETE FROM bread_crumb where user_id = ?';

		try {
			$rs = $db->Execute($query, $ph);
		} catch (Exception $e) {
			throw new DBError($e);
		}

		return TRUE;
	}

	//Used to return to the last URL the user visited.
	static function getReturnCrumb($num = 1) {
		$crumbs = self::getCrumbs();

		return $crumbs[$num]['url'];
	}

	static function Display() {
		$crumbs = self::getCrumbs();

		if ( is_array($crumbs) ) {
			$crumbs = array_reverse($crumbs);
		}

		//var_dump($crumbs);
		$links[] = '<a href="'. Environment::getBaseURL() .'">'. TTi18n::gettext(self::$home_name) .'</a>';

		if ( $crumbs != FALSE) {
			$total_crumbs = count($crumbs);
			$i=1;
			foreach ($crumbs as $crumb) {
				if ($i == 1 AND $crumb['name'] == 'Home') {

				} else {
					if ($i == $total_crumbs) {
							$links[] = TTi18n::gettext($crumb['name']);
					} else {
						if ( $crumb['name'] != 'Home' ) {
							$links[] = '<a href="'.$crumb['url'].'">'.TTi18n::gettext($crumb['name']).'</a>';
						}
					}
				}
				$i++;
			}
		}
		return implode(self::$seperator, $links);
	}
}
?>
