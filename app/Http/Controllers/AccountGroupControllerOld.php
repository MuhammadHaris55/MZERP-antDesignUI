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

class AccountGroupController extends Controller
{
    public function index(Req $req)
    {
        if (request()->has(
            // ['select', 'search']
            'search'
        )) {
            $obj_data = AccountGroup::where(
                // $req->select
                'name',
                'LIKE',
                '%' . $req->search . '%'
            )
                ->where('company_id', session('company_id'))
                ->get();
            $mapped_data = $obj_data->map(function ($acc_group, $key) {
                return [
                    'id' => $acc_group->id,
                    'name' => $acc_group->name,
                    'type_id' => $acc_group->type_id,
                    'type_name' => $acc_group->accountType->name,
                    'company_id' => $acc_group->company_id,
                    'company_name' => $acc_group->company->name,
                    'delete' => Account::where('group_id', $acc_group->id)->first() || AccountGroup::where('company_id', session('company_id'))->where('parent_id', $acc_group->id)->first() ? false : true,
                ];
            });
        } else {
            $obj_data = AccountGroup::where('company_id', session('company_id'))->get();

            $mapped_data = $obj_data->map(function ($acc_group, $key) {
                return [
                    'id' => $acc_group->id,
                    'name' => $acc_group->name,
                    'type_id' => $acc_group->type_id,
                    'type_name' => $acc_group->accountType->name,
                    'company_id' => $acc_group->company_id,
                    'company_name' => $acc_group->company->name,
                    // 'delete' => Account::where('group_id', $acc_group->id)->first() ? false : true,
                    'delete' => Account::where('group_id', $acc_group->id)->first() || AccountGroup::where('company_id', session('company_id'))->where('parent_id', $acc_group->id)->first() ? false : true,
                ];
            });
        }

        return Inertia::render('AccountGroups/Index', [
            'mapped_data' => $mapped_data,
            'filters' => request()->all(['search', 'field', 'direction']),
            'can' => [
                'edit' => auth()->user()->can('edit'),
                'create' => auth()->user()->can('create'),
                'delete' => auth()->user()->can('delete'),
                'read' => auth()->user()->can('read'),
            ],
            'exists' => AccountGroup::where('company_id', session('company_id'))->first() ? false : true,
            'company' => Company::find(session('company_id')),
            'companies' => Auth::user()->companies,
        ]);
    }

    // public function generate()
    // {
    //     // $exitCode = Artisan::call('db:seed', [
    //     //     '--class' => 'TypeSeeder'
    //     // ]);
    //     // print_r("hi");
    //     // die();
    //     // $this->call([
    //     //     // UserSeeder::class,
    //     //     // PostSeeder::class,
    //     //     // CommentSeeder::class,
    //     //     // AccountSeeder::class,
    //     //     GroupSeeder::class,
    //     // ]);
    //     return GroupSeeder::class;
    //     return Redirect::back()->with('success', 'Account Group deleted.');
    // }

    public function create(Req $request)
    {
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
        Request::validate([
            'type_id' => ['required'],
            'name' => ['required'],
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
        $accountgroup->delete();
        return Redirect::back()->with('success', 'Account Group deleted.');
    }
}
