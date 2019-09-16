<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PreproController extends Controller
{
    // CALL VIEW HOLDER PAGE
    public function index() {
        return view('app');
    }

    private function exchangeRate($amount, $currency, $tocurrency) {
        // CURRENCY RATES
        $rates = [
            'EUR' => 1,
            'USD' => 1.1497,
            'JPY' => 129.53
        ];

        if(strtoupper($tocurrency) == 'EUR') {
            return ($amount / $rates[strtoupper($currency)]);
        }
        // RETURN EXCHANGE AMOUNT
        return ($amount * $rates[strtoupper($currency)]);
    }

    private function toPercentage($amount, $operation) {
        // PERCENTAGE PER OPERATION
        if(strtolower($operation) == 'cash_in') {
            return ($this->checkNegative($amount)*0.03)/100;
        } 
        if(strtolower($operation) == 'cash_out') {
            return ($this->checkNegative($amount)*0.3)/100;
        }
    }

    private function checkNegative($commission) {
        // REMOVE NEGATIVE
        if($commission < 0) {
            return 0;
        }
        return $commission;
    }

    private function commissionFormat($commission) {
        // REFORMAT COMMISSION
        if($commission > 0.90) {
            return ceil($commission);
        } else {
            return number_format($commission, 2);
        }
    }

    public function computeCSV(Request $request) {
        // 0=date 1=id 2=type 3=operation 4=amount 5=currency
        $data = [];
        $last_id = 0;
        $credit_amount = 1000;
        $last_date = "1993-0-0";
        $credit_count = 3;
        // SCAN DATA
        for($i = 0; $i < count($request[0]); ++$i) {
            if(isset($request[0][$i][3])) {
                // OPERATION CASHOUT
                if(strtolower($request[0][$i][3]) == 'cash_out') {
                    if(strtolower($request[0][$i][2]) == 'natural') {
                        // STATIC THE AMOUNT
                        $amount = $request[0][$i][4];
                        // GET BOOLEAN STATUS
                        $same_week = date('oW', strtotime($request[0][$i][0])) === date('oW', strtotime($last_date));
                        // LAST ID == CREDIT COUNT NOT ZERO == SAME WEEK TRUE
                        if($last_id == $request[0][$i][1] && $credit_count >= 0 && $same_week) {
                            // CREDIT COUNT
                            $credit_count -= 1;
                        } else {
                            // ALWAYS START CREDIT AMOUNT TO 1000 IF NOT SAME ID AND WEEK
                            $credit_amount = 1000;
                            // CREDIT COUNT
                            $credit_count = 2;
                        }
                        // DEDUCT LAST CREDIT LEFT
                        $amount -= $this->exchangeRate(round($this->checkNegative($credit_amount), 0, PHP_ROUND_HALF_EVEN), $request[0][$i][5], $request[0][$i][5]);
                        // DEDUCT CREDIT USED
                        $credit_amount -= $this->exchangeRate($request[0][$i][4], $request[0][$i][5], 'EUR');
                        // COMMISSION TO PERCENTAGE
                        $commission = $this->toPercentage($amount, $request[0][$i][3]);
                    }
                    if(strtolower($request[0][$i][2]) == 'legal') {
                        // COMMISSION TO PERCENTAGE
                        $commission = $this->toPercentage($request[0][$i][4], $request[0][$i][3]);
                        // IF COMMISSION NOT LESSTHAN
                        if($commission < $this->exchangeRate(0.50, 'EUR', $request[0][$i][5])) {
                            $commission = $this->exchangeRate(0.50, 'EUR', $request[0][$i][5]);
                        }
                    }
                }
                // OPERATION CASHIN
                if(strtolower($request[0][$i][3]) == 'cash_in') {
                    // COMMISSION TO PERCENTAGE
                    $commission = $this->toPercentage($request[0][$i][4], $request[0][$i][3]);
                    // IF COMMISSION NOT GREATERTHAN
                    if($commission >= $this->exchangeRate(5, 'EUR', $request[0][$i][5])) {
                        $commission = $this->exchangeRate(5, 'EUR', $request[0][$i][5]);
                    }
                }
                // STORE LAST DATA
                $last_id = $request[0][$i][1];
                $last_date = $request[0][$i][0];
                // STORED DATA
                $data[] = $this->commissionFormat($commission);       
            }
        }
        // RETURN DATA
        return response()->json($data);
    }
}

// $result = date('oW', strtotime('2016-12-30')) === date('oW', strtotime('2016-12-31'));
// var_dump($result); // true (same week, same year)

// 0.60 0 y Y
// 3.00 1 x Y
// 0.00 2 y Y
// 0.06 3 y Y
// 0.90 4 y Y
// 0    5 y Y
// 0.70 6 x X
// 0.30 7 x Y
// 0.30 8 x Y
// 5.00 9 y Y
// 0.00 10 y Y
// 0.00 11 y Y
// 8612 12 y Y