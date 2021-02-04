<?php

namespace App\Http\Controllers\MockService;

use App\Http\Controllers\BaseController;

class ThirdPartyController extends BaseController
{
    public function changeStatus()
    {
        return $this->jsonResponse(
            [
                'result' => 'Success',
            ]
        );
    }
}