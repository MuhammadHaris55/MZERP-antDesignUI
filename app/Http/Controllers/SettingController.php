<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use App\Models\Account;
use App\Models\Setting;
use Inertia\Inertia;
use Illuminate\Http\Request as Req;



class SettingController extends Controller
{
    public function index()
    {
        $accounts = Account::where('company_id', session('company_id'))->get();
        $setting = Setting::where('company_id',session('company_id'))
        ->where('key','retain_earning')->first();
        if($setting){
            $setting->value = $setting->account->name .' - '. $setting->account->accountGroup->name;
        }


        if (count($accounts) > 0) {
            // dd($accounts);
            $accounts = $accounts->map(function($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name .' - '. $row->accountGroup->name
                ];
            });


            return Inertia::render('Settings/Index', [
                'accounts' => $accounts,
                'setting' => $setting,
                 'company' => companies_first(),
                'companies' => companies_get(),
                'years' => years_get(),
                'year' => years_first(),
            ]);
        } else {
            return Redirect::route('account.create')->with('success', 'ACCOUNT NOT FOUND, Please create account first.');
        }
    }

    public function create()
    {
    }

    public function store(Req $request)
    {
        Request::validate([
            'account_id' => 'required',
        ]);

         $setting =  Setting::create([
                    'key' => 'retain_earning',
                    'value' => $request['account_id'],
                    'user_id' => auth()->user()->id,
                    'company_id' =>  session('company_id'),
                ]);

        return Redirect::route('settings')->with('success', 'Account Profite And Loss Succesfully.');
    }

    public function edit(Account $account)
    {
    }

    public function update(Account $account)
    {
        Request::validate([
            'name' => ['required'],
        ]);
        // $account->group_id = Request::input('group_id');
        $account->company_id = session('company_id');
        // $account->number = Request::input('number');
        $account->name = Request::input('name');
        $account->update();

        return Redirect::route('accounts')->with('success', 'Account updated.');
    }

    public function destroy(Account $account)
    {
    }
}
