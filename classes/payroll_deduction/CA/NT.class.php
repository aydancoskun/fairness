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
 * @package PayrollDeduction\CA
 */
class PayrollDeduction_CA_NT extends PayrollDeduction_CA {
	var $provincial_income_tax_rate_options = array(
			20190101 => array(
					array('income' => 43137, 'rate' => 5.9, 'constant' => 0),
					array('income' => 86277, 'rate' => 8.6, 'constant' => 1165),
					array('income' => 140267, 'rate' => 12.2, 'constant' => 4271),
					array('income' => 140267, 'rate' => 14.05, 'constant' => 6866),
			),
			20180101 => array(
					array('income' => 42209, 'rate' => 5.9, 'constant' => 0),
					array('income' => 84420, 'rate' => 8.6, 'constant' => 1140),
					array('income' => 137248, 'rate' => 12.2, 'constant' => 4179),
					array('income' => 137248, 'rate' => 14.05, 'constant' => 6718),
			),
			20170101 => array(
					array('income' => 41585, 'rate' => 5.9, 'constant' => 0),
					array('income' => 83172, 'rate' => 8.6, 'constant' => 1123),
					array('income' => 135219, 'rate' => 12.2, 'constant' => 4117),
					array('income' => 135219, 'rate' => 14.05, 'constant' => 6619),
			),
			20160101 => array(
					array('income' => 41011, 'rate' => 5.9, 'constant' => 0),
					array('income' => 82024, 'rate' => 8.6, 'constant' => 1107),
					array('income' => 133353, 'rate' => 12.2, 'constant' => 4060),
					array('income' => 133353, 'rate' => 14.05, 'constant' => 6527),
			),
			20150101 => array(
					array('income' => 40484, 'rate' => 5.9, 'constant' => 0),
					array('income' => 80971, 'rate' => 8.6, 'constant' => 1093),
					array('income' => 131641, 'rate' => 12.2, 'constant' => 4008),
					array('income' => 131641, 'rate' => 14.05, 'constant' => 6443),
			),
			20140101 => array(
					array('income' => 39808, 'rate' => 5.9, 'constant' => 0),
					array('income' => 79618, 'rate' => 8.6, 'constant' => 1075),
					array('income' => 129441, 'rate' => 12.2, 'constant' => 3941),
					array('income' => 129441, 'rate' => 14.05, 'constant' => 6336),
			),
			20130101 => array(
					array('income' => 39453, 'rate' => 5.9, 'constant' => 0),
					array('income' => 78908, 'rate' => 8.6, 'constant' => 1065),
					array('income' => 128286, 'rate' => 12.2, 'constant' => 3906),
					array('income' => 128286, 'rate' => 14.05, 'constant' => 6279),
			),
			20120101 => array(
					array('income' => 38679, 'rate' => 5.9, 'constant' => 0),
					array('income' => 77360, 'rate' => 8.6, 'constant' => 1044),
					array('income' => 125771, 'rate' => 12.2, 'constant' => 3829),
					array('income' => 125771, 'rate' => 14.05, 'constant' => 6156),
			),
			20110101 => array(
					array('income' => 37626, 'rate' => 5.9, 'constant' => 0),
					array('income' => 75253, 'rate' => 8.6, 'constant' => 1016),
					array('income' => 122345, 'rate' => 12.2, 'constant' => 3725),
					array('income' => 122345, 'rate' => 14.05, 'constant' => 5988),
			),
			20100101 => array(
					array('income' => 37106, 'rate' => 5.9, 'constant' => 0),
					array('income' => 74214, 'rate' => 8.6, 'constant' => 1002),
					array('income' => 120656, 'rate' => 12.2, 'constant' => 3674),
					array('income' => 120656, 'rate' => 14.05, 'constant' => 5906),
			),
			20090101 => array(
					array('income' => 36885, 'rate' => 5.9, 'constant' => 0),
					array('income' => 73772, 'rate' => 8.6, 'constant' => 996),
					array('income' => 119936, 'rate' => 12.2, 'constant' => 3652),
					array('income' => 119936, 'rate' => 14.05, 'constant' => 5871),
			),
			20080101 => array(
					array('income' => 35986, 'rate' => 5.90, 'constant' => 0),
					array('income' => 71973, 'rate' => 8.60, 'constant' => 972),
					array('income' => 117011, 'rate' => 12.20, 'constant' => 3563),
					array('income' => 117011, 'rate' => 14.05, 'constant' => 5727),
			),
			20070101 => array(
					array('income' => 35315, 'rate' => 5.90, 'constant' => 0),
					array('income' => 70631, 'rate' => 8.60, 'constant' => 954),
					array('income' => 114830, 'rate' => 12.20, 'constant' => 3496),
					array('income' => 114830, 'rate' => 14.05, 'constant' => 5621),
			),
	);
}

?>
