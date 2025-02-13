<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use App\Models\AccountGroup;
use App\Models\AccountType;
use App\Models\Company;
use Database\Seeders\AccountSeeder;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request as Req;
use Illuminate\Validation\Rule;

class AccountGroupController extends Controller
{
    public function index(Req $req)
    {
        
        $query = AccountGroup::where('company_id', session('company_id'));

        if ($req->has('search') && !empty($req->search)) {
            $query->where('name', 'like', '%' . $req->search . '%');
        }


        $page = (int) $req->get('page', 1); // Ensure it's an integer
        $pageSize = (int) $req->get('pageSize', 10); // Ensure it's an integer

        $obj_data = $query->orderBy('id', 'Desc')->paginate($pageSize, ['*'], 'page', $page); // Force page

        $mapped_data = $obj_data->map(function ($acc_group) {
            return [
                'id' => $acc_group->id,
                'name' => $acc_group->name,
                'type_id' => $acc_group->type_id,
                'type_name' => $acc_group->accountType->name,
                'company_id' => $acc_group->company_id,
                'company_name' => $acc_group->company->name,
                'delete' => Account::where('group_id', $acc_group->id)->exists() || 
                            AccountGroup::where('company_id', session('company_id'))
                                ->where('parent_id', $acc_group->id)->exists() ? false : true,
            ];
        });
        return Inertia::render('AccountGroups/Index', [
            'mapped_data' => $mapped_data,
            'total' => $obj_data->total(),
            'current_page' => $obj_data->currentPage(),
            'per_page' => $obj_data->perPage(),
            'filters' => $req->only(['search', 'field', 'direction']),
            'can' => [
                'edit' => auth()->user()->can('edit'),
                'create' => auth()->user()->can('create'),
                'delete' => auth()->user()->can('delete'),
                'read' => auth()->user()->can('read'),
            ],
            'exists' => !AccountGroup::where('company_id', session('company_id'))->exists(),
            'company' => Company::find(session('company_id')),
            'companies' => Auth::user()->companies,
        ]);
    }


    public function create(Req $request)
    {
        if (auth()->user()->roles->first()->name == 'user') {
            abort(403, 'You don\'t have access this page');
        }

        if ($request->type_id) {
            $first = AccountType::where('id', $request->type_id)->first();
            $name = $request->name;
        } else {
            $name = null;
            $first = AccountType::all('id', 'name')->first();
        }
        $types = AccountType::all()->map->only('id', 'name');
        $data = AccountGroup::where('company_id', session('company_id'))->where('type_id', $first->id)->tree()->get()->toTree();

        return Inertia::render('AccountGroups/Create', [
            'types' => $types,
            'first' => $first,
            'data' => $data,
            'name' => $name,
        ]);
    }

    public function store(Req $request)
    {

        // Request::validate([
        //     'type_id' => ['required'],
        //     'name' => ['required', 'unique:account_groups'],
        //     'parent_id' => [],
        // ]);
        $company_id = session('company_id');
        Request::validate([
        'type_id' => ['required'],
        'name' => ['required', Rule::unique('account_groups')->where(function ($query) use ($company_id) {
            return $query->where('company_id', $company_id);
        })],
        'parent_id' => [],
        ]);
        AccountGroup::create([
            'type_id' => Request::input('type_id'),
            'parent_id' => Request::input('parent_id'),
            'name' => Request::input('name'),
            'company_id' => session('company_id'),
        ]);

        return Redirect::route('accountgroups')->with('success', 'Account Group created.');
    }

    public function edit(AccountGroup $accountgroup)
    {
        if (auth()->user()->roles->first()->name == 'user') {
            abort(403, 'You don\'t have access this page');
        }
        $accountgroup = AccountGroup::where('id', $accountgroup->id)->get()
            ->map(
                function ($accountgroup) {
                    return
                        [
                            'id' => $accountgroup->id,
                            'type_id' => $accountgroup->accountType->name,
                            // 'parent_id' => $accountgroup->parent_id,
                            'parent_id' => $accountgroup->parent_id ? $accountgroup->accountGroup->name : null,
                            'name' => $accountgroup->name,
                            'company_id' => session('company_id'),
                            'delete' => Account::where('group_id', $accountgroup->id)->first() ? false : true,
                        ];
                }
            );
        return Inertia::render('AccountGroups/Edit', [
            'accountgroup' => $accountgroup,
        ]);
    }

    public function update(AccountGroup $accountgroup)
    {
        Request::validate([
            // 'type' => ['required'],
            'name' => ['required'],
        ]);
        // $accountgroup->type_id = Request::input('type');
        // $accountgroup->company_id = session('company_id');
        $accountgroup->name = Request::input('name');
        $accountgroup->save();

        return Redirect::route('accountgroups')->with('success', 'Account Group updated.');
    }

    public function destroy(AccountGroup $accountgroup)
    {
        if (auth()->user()->roles->first()->name == 'user') {
            abort(403, 'You don\'t have access this page');
        }
        $accountgroup->delete();
        return Redirect::back()->with('success', 'Account Group deleted.');
    }
}
