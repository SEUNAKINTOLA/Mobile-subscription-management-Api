<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class OSMockController extends Controller
{
    public function verifygooglereceipt(Request $request)
    {
        $now = new Carbon('now', new \DateTimeZone("UTC"));

        $params = $request->all();
        $lastchar = ((int)substr($params["receipt"], -1));

        if (($lastchar != 0) && ($lastchar % 2 != 0)) {
            $result = array(
                "status" => "invalid",
                "expire_date" => Carbon::parse($now)->setTimezone('UTC')
            );
        } else {
            $result = array(
                "status" => "valid",
                "expire_date" => Carbon::parse($now)->setTimezone('UTC')
            );
        }
        return response()->json($result, 200);
    }

    public function verifyiosreceipt(Request $request)
    {
        $now = new Carbon('now', new \DateTimeZone("UTC"));

        $params = $request->all();
        $lastchar = ((int)substr($params["receipt"], -1));

        if (($lastchar != 0) && ($lastchar % 2 != 0)) {
            $result = array(
                "status" => "invalid",
                "expire_date" => Carbon::parse($now)->setTimezone('UTC')
            );
        } else {
            $result = array(
                "status" => "valid",
                "expire_date" => Carbon::parse($now)->setTimezone('UTC')
            );
        }
        return response()->json($result, 200);
    }
}
