<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use App\Models\Account;
use App\Models\Company;
use App\Models\Entry;
use App\Models\AccountGroup;
use App\Models\Setting;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request as Req;
use Illuminate\Validation\Rule;


class AccountController extends Controller
{
    public function index(Req $req)
    {
        if (AccountGroup::where('company_id', session('company_id'))->first()) {

            //Validating request
            request()->validate([
                'direction' => ['in:asc,desc'],
                'field' => ['in:name,email']
            ]);

            //Searching request
            $query = Account::query();
            if (request('search')) {
                $query->where('name', 'LIKE', '%' . request('search') . '%');
            }
            // // Ordering request
            // if (request()->has(['field', 'direction'])) {
            //     $query->orderBy(
            //         request('field'),
            //         request('direction')
            //     );
            // }
            $retain =   Setting::where('company_id', session('company_id'))->where('key' , 'retain_earning')->first();
            if($retain){
               $retain =  $retain->value;
            }else{
                $retain = 0;
            }

            $balances = $query
                ->where('company_id', session('company_id'))
                ->paginate(10)
                ->withQueryString()
                ->through(
                    function ($account) use ($retain) {
                        return
                            [
                                'id' => $account->id,
                                'name' => $account->name,
                                // 'group_id' => $account->parent_id,
                                'group_name' => $account->accountGroup->name,
                                'delete' => $retain == $account->id || Entry::where('account_id', $account->id)->first() ? false : true,
                            ];
                    }
                );

            if(request()->has(
                // ['select', 'search']
                'search'
                )){
                $obj_data = Account::where(
                    // $req->select
                    'name'
                    ,'LIKE', '%'.$req->search.'%')
                ->where('company_id', session('company_id'))
                ->get();
                $mapped_data = $obj_data->map(function($account, $key) use ($retain) {
                return [
                        'id' => $account->id,
                        'name' => $account->name,
                        // 'group_id' => $account->parent_id,
                        'group_name' => $account->accountGroup->name,
                        'delete' => $retain == $account->id || Entry::where('account_id', $account->id)->first() ? false : true,
                    ];
                });
            }
            else{
                $obj_data = Account::where('company_id', session('company_id'))->get();
                $mapped_data = $obj_data->map(function($account, $key) use ($retain) {
                return [
                        'id' => $account->id,
                        'name' => $account->name,
                        // 'group_id' => $account->parent_id,
                        'group_name' => $account->accountGroup->name,
                        'delete' => $retain == $account->id || Entry::where('account_id', $account->id)->first() ? false : true,
                    ];
                });
            }
            return Inertia::render('Accounts/Index', [
                'filters' => request()->all(['search', 'field', 'direction']),
                'balances' => $balances,
                'mapped_data' => $mapped_data,
                'company' => Company::where('id', session('company_id'))->first(),
                'companies' => auth()->user()->companies,
                'can' => [
                    'edit' => auth()->user()->can('edit'),
                    'create' => auth()->user()->can('create'),
                    'delete' => auth()->user()->can('delete'),
                    'read' => auth()->user()->can('read'),
                ],
            ]);
        } else {
            return Redirect::route('accountgroups')->with('warning', 'ACCOUNTGROUP NOT FOUND, Please create account group first.');
        }
    }

    public function create()
    {
        // $groups = \App\Models\AccountGroup::where('company_id', session('company_id'))->map->only('id', 'name')->get();
        $groups = AccountGroup::where('company_id', session('company_id'))->tree()->get()->toTree();

        $group_first = AccountGroup::all()->where('company_id', session('company_id'))->map->only('id', 'name')->first();

        if ($group_first) {
            return Inertia::render('Accounts/Create', [
                'groups' => $groups,
                'group_first' => $group_first,
            ]);
        } else {
            return Redirect::route('accountgroups.create')->with('success', 'ACCOUNTGROUP NOT FOUND, Please create account group first.');
        }
    }

    public function store(Req $request)
    {

        $company_id = session('company_id');
        $group_id = Request::input('group');
        Request::validate([
            'name' => ['required', Rule::unique('accounts')->where(function ($query) use ($company_id, $group_id) {
            return $query->where('company_id', $company_id)
                        ->where('group_id', $group_id);
            })],
            'group' => ['required'],
        ]);

        $account = Account::create([
            'name' => Request::input('name'),
            // 'number' => Request::input('number'),
            'group_id' =>  $group_id,
            'company_id' => $company_id,
        ]);
        $account->update(['number' => snum($account)]);

        return Redirect::route('accounts')->with('success', 'Account created.');
    }

    public function edit(Account $account)
    {
        // $groups = AccountGroup::all()->where('company_id', session('company_id'))->map->only('id', 'name');
        $groups = AccountGroup::where('company_id', session('company_id'))->tree()->get()->toTree();
        $group_first = AccountGroup::where('id', $account->group_id)->first();

        return Inertia::render('Accounts/Edit', [
            'account' => [
                'id' => $account->id,
                'company_id' => $account->company_id,
                'group_id' => $account->accountGroup->name,
                'name' => $account->name,
                'number' => $account->number,
            ],
            'groups' => $groups,
            'group_first' => $group_first,
        ]);
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
        $account->delete();
        return Redirect::back()->with('success', 'Account deleted.');
    }



}
