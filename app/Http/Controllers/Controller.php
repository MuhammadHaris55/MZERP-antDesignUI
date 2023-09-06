<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use app\Models\Account;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

     function snum($account)
    {
        $ty = $account->accountGroup->accountType;
        $grs = $ty->accountGroups->where('company_id', session('company_id'));
        $grindex = 1;
        $grselindex = 0;
        $grsel = null;
        $number = 0;
        foreach ($grs as $gr) {
            if ($gr->name == $account->accountGroup->name) {
                $grselindex = $grindex;
                $grsel = $gr;
            }
            ++$grindex;
        }
        if (count($grsel->accounts) == 1) {
            $number = $ty->id . sprintf("%'.03d", $grselindex) . sprintf("%'.03d", 1);
        } else {
            $lastac = Account::orderBy('id', 'desc')->where('company_id', session('company_id'))->where('group_id', $grsel->id)->skip(1)->first()->number;
            $lastst = Str::substr($lastac, 4, 3);
            $number = $ty->id . sprintf("%'.03d", $grselindex) . sprintf("%'.03d", ++$lastst);
        }
        return $number;
    }

}
