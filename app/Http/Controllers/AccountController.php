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
        if (!AccountGroup::where('company_id', session('company_id'))->exists()) {
            return Redirect::route('accountgroups')->with('warning', 'ACCOUNTGROUP NOT FOUND, Please create account group first.');
        }
    
        // Validate request
        $req->validate([
            'direction' => ['in:asc,desc'],
            'field' => ['in:name,email']
        ]);
    
        $companyId = session('company_id');
    
        // Retrieve retain earnings setting
        $retain = Setting::where('company_id', $companyId)->where('key', 'retain_earning')->value('value') ?? 0;
    
        // Fetch accounts
        $query = Account::where('company_id', $companyId);
        
        if ($req->has('search') && !empty($req->search)) {
            $query->where('name', 'LIKE', "%$req->search%");
        }
        
        $page = (int) $req->get('page', 1); // Ensure it's an integer
        $pageSize = (int) $req->get('pageSize', 10);
        
        $accounts = $query->orderBy('id', 'DESC')->paginate($pageSize, ['*'], 'page', $page);
    
        // Map data
        $mappedData = $accounts->map(function ($account) use ($retain) {
            return [
                'id' => $account->id,
                'name' => $account->name,
                'group_name' => $account->accountGroup->name,
                'delete' => !($retain == $account->id || Entry::where('account_id', $account->id)->exists()),
            ];
        });
    
        return Inertia::render('Accounts/Index', [
            'filters' => $req->only(['search', 'field', 'direction']),
            'mapped_data' => $mappedData,
            'total' => $accounts->total(),
            'current_page' => $accounts->currentPage(),
            'per_page' => $accounts->perPage(),
            'company' => Company::find($companyId),
            'companies' => auth()->user()->companies,
            'can' => [
                'edit' => auth()->user()->can('edit'),
                'create' => auth()->user()->can('create'),
                'delete' => auth()->user()->can('delete'),
                'read' => auth()->user()->can('read'),
            ],
        ]);
    }
    

    public function create()
    {
        if (auth()->user()->roles->first()->name == 'user') {
            abort(403, 'You don\'t have access this page');
        }
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
        $account->update(['number' => $this->snum($account)]);

        return Redirect::route('accounts')->with('success', 'Account created.');
    }

    public function edit(Account $account)
    {
        if (auth()->user()->roles->first()->name == 'user') {
            abort(403, 'You don\'t have access this page');
        }

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
        if (auth()->user()->roles->first()->name == 'user') {
            abort(403, 'You don\'t have access this page');
        }
        $account->delete();
        return Redirect::back()->with('success', 'Account deleted.');
    }



}
