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
 * $Revision: 8371 $
 * $Id: GA.class.php 8371 2012-11-22 21:18:57Z ipso $
 * $Date: 2012-11-22 13:18:57 -0800 (Thu, 22 Nov 2012) $
 */

/**
 * @package PayrollDeduction\US
 */
class PayrollDeduction_US_GA extends PayrollDeduction_US {
/*
	protected $state_ga_filing_status_options = array(
														10 => 'Single',
														20 => 'Married - Filing Separately',
														30 => 'Married - Joint One Income',
														40 => 'Married - Joint Two Incomes',
														50 => 'Head of Household',
									);

*/

	var $state_options = array(
								1136102400 => array(
													'standard_deduction' => array(
																				'10' => 2300.00,
																				'20' => 1500.00,
																				'30' => 3000.00,
																				'40' => 1500.00,
																				'50' => 2300.00,
																				),
													'employee_allowance' => 2700,
													'dependant_allowance' => 3000
													)
								);

	function getStatePayPeriodDeductions() {
		return bcdiv($this->getStateTaxPayable(), $this->getAnnualPayPeriods() );
	}

	function getStateAnnualTaxableIncome() {
		$annual_income = $this->getAnnualTaxableIncome();
		$state_deductions = $this->getStateStandardDeduction();
		$state_employee_allowance = $this->getStateEmployeeAllowanceAmount();
		$state_dependant_allowance = $this->getStateDependantAllowanceAmount();

		$income = bcsub( bcsub( bcsub($annual_income, $state_deductions), $state_employee_allowance), $state_dependant_allowance );

		Debug::text('State Annual Taxable Income: '. $income, __FILE__, __LINE__, __METHOD__, 10);

		return $income;
	}

	function getStateStandardDeduction() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$deduction = $retarr['standard_deduction'][$this->getStateFilingStatus()];

		Debug::text('Standard Deduction: '. $deduction, __FILE__, __LINE__, __METHOD__, 10);

		return $deduction;
	}

	function getStateEmployeeAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$allowance_arr = $retarr['employee_allowance'];

		$retval = bcmul( $this->getUserValue2(), $allowance_arr );

		Debug::text('State Employee Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateDependantAllowanceAmount() {
		$retarr = $this->getDataFromRateArray($this->getDate(), $this->state_options);
		if ( $retarr == FALSE ) {
			return FALSE;

		}

		$allowance_arr = $retarr['dependant_allowance'];

		$retval = bcmul( $this->getUserValue3(), $allowance_arr );

		Debug::text('State Dependant Allowance Amount: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	function getStateTaxPayable() {
		$annual_income = $this->getStateAnnualTaxableIncome();

		$retval = 0;

		if ( $annual_income > 0 ) {
			$rate = $this->getData()->getStateRate($annual_income);
			$state_constant = $this->getData()->getStateConstant($annual_income);
			$state_rate_income = $this->getData()->getStateRatePreviousIncome($annual_income);

			$retval = bcadd( bcmul( bcsub( $annual_income, $state_rate_income ), $rate ), $state_constant );
		}

		if ( $retval < 0 ) {
			$retval = 0;
		}

		Debug::text('State Annual Tax Payable: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

}
?>
