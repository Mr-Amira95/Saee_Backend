<?php

namespace App\Http\Controllers\Client;

use App\Models\ClientProfile;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function getClientProfile(): ClientProfile
    {
        $user = Auth::user();

        if ($user->isClientMaster()) {
            return $user->clientProfile;
        }

        return $user->clientEmployee->clientProfile;
    }
}
