<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Year;
use App\Models\Setting;
use App\Models\Company;
use App\Models\Document;
use Inertia\Inertia;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Models\AccountGroup;
use App\Models\AccountType;
use App\Models\Account;
use App\Models\Entry;

class YearController extends Controller
{
    public function index()
    {
        if(request()->has(
            // ['select', 'search']
            'search'
            )){
            $obj_data = Year::where(
                // $req->select
                'name'
                ,'LIKE', '%'.$req->search.'%')
            ->where('company_id', session('company_id'))
            ->get();
            $mapped_data = $obj_data->map(function($year, $key) {
            return [
                    $begin = new Carbon($year->begin),
                    $end = new Carbon($year->end),
                    'id' => $year->id,
                    'closed' => $year->closed == 0 || Document::where('year_id', $year->id)->first() ? true : false,
                    'begin' => $begin->format('F,j Y'),
                    'end' => $end->format('F,j Y'),
                    'company_name' => $year->company->name,
                    'company_id' => $year->company_id,
                    'delete' =>
                    Document::where('year_id', $year->id)->first()
                        ||
                        $year->id != Year::where('company_id', session('company_id'))->orderBy('id','desc')->first()->id
                        ? false : true,
                ];
            });
        }
        else{
            $obj_data = Year::where('company_id', session('company_id'))->get();
            $mapped_data = $obj_data->map(function($year, $key) {
            return [
                    $begin = new Carbon($year->begin),
                    $end = new Carbon($year->end),
                    'id' => $year->id,
                     'closed' => $year->closed == 0 && Document::where('year_id', $year->id)->first() ? true : false,
                    'begin' => $begin->format('F,j Y'),
                    'end' => $end->format('F,j Y'),
                    'company_name' => $year->company->name,
                    'company_id' => $year->company_id,
                    'delete' =>
                    Document::where('year_id', $year->id)->first()
                        ||
                        $year->id != Year::where('company_id', session('company_id'))->orderBy('id','desc')->first()->id
                        ? false : true,
                ];
            });
        }
        return Inertia::render('Years/Index', [
            'mapped_data' => $mapped_data,
            'can' => [
                'edit' => auth()->user()->can('edit'),
                'create' => auth()->user()->can('create'),
                'delete' => auth()->user()->can('delete'),
                'read' => auth()->user()->can('read'),
            ],
            'company' => Company::where('id', session('company_id'))->first(),
            // 'companies' => Company::all()
            //     ->map(function ($com) {
            //         return [
            //             'id' => $com->id,
            //             'name' => $com->name,
            //         ];
            //     }),
            'companies' => Auth::user()->companies,

        ]);
    }

    public function create()
    {
        $year = Year::where('company_id', session('company_id'))->latest()->first();
        $begin = Carbon::createFromFormat('Y-m-d H:i:s', $year->end  . "0:00:00")->addDay(1);
        $end = Carbon::createFromFormat('Y-m-d H:i:s', $begin)->addYear(1)->addDay(-1);

        $newBegin = $begin->format('Y-m-d');
        $newEnd = $end->format('Y-m-d');
        // dd($newBegin);
        Year::create([
            'begin' => $newBegin,
            'end' => $newEnd,
            'company_id' => session('company_id'),

        ]);

        $year = Year::where('company_id', session('company_id'))->latest()->first();
        // Storage::makedirectory('/public/' . $year->company->id . '/' . $year->id);


        return Redirect::back()->with('success', 'Year created.');
    }

    // public function store(Req $request)
    // {
    //     Request::validate([
    //         'begin' => ['required', 'date'],
    //         'end' => ['required', 'date'],
    //     ]);

    //     $year = Year::create([
    //         'begin' => $request->begin,
    //         'end' => $request->end,
    //         'company_id' => session('company_id'),
    //     ]);

    //     Setting::create([
    //         'key' => 'active_year',
    //         'value' => $year->id,
    //         'user_id' => Auth::user()->id,
    //     ]);

    //     session(['year_id' => $year->id]);
    //     return Redirect::route('years')->with('success', 'Year created.');
    // }

    public function edit(Year $year)
    {
        return Inertia::render('Years/Edit', [
            'year' => [
                'id' => $year->id,
                'begin' => $year->begin,
                'end' => $year->end,
                'company_id' => session('company_id'),
            ],
        ]);
    }

    public function update(Year $year)
    {
        Request::validate([
            'begin' => ['required', 'date'],
            'end' => ['required', 'date'],
        ]);

        $begin = new carbon(Request::input('begin'));
        $end = new carbon(Request::input('end'));

        $year->begin = $begin->format('Y-m-d');
        $year->end = $end->format('Y-m-d');
        $year->company_id = session('company_id');
        $year->save();

        return Redirect::route('years')->with('success', 'Year updated.');
    }

    public function destroy(Year $year)
    {
        // if (Document::where('year_id', $year->id)->first()) {
        //     return Redirect::back()->with('success', 'Can\'t DELETE this Year.');
        // } else {
        $comp_year = Year::where('company_id', session('company_id'))->get();
        if(count($comp_year) >> 1)
        {
            $year->delete();
           $y = Year::where('company_id', session('company_id'))->first();
            if ($y) {
                $active_yr = Setting::where('user_id', Auth::user()->id)->where('key', 'active_year')->first();
                $active_yr->value = $y->id;
                $active_yr->save();
                session(['year_id' => $y->id]);
                return Redirect::back()->with('success', 'Year deleted.');
            }
            // else {
            //     session(['year_id' => null]);
            //     return Redirect::route('years.create')->with('success', 'YEAR NOT FOUND. Please create an Year for selected Company.');
            // }
        } else {
            return Redirect::back()->with('error', 'Can\'t delete! Company have only Year.');
        }
    }

    public function yrch($id)
    {
        $active_yr = Setting::where('user_id', Auth::user()->id)->where('key', 'active_year')->first();

        $active_yr->value = $id;
        $active_yr->save();
        session(['year_id' => $id]);

        return Redirect::back();
    }

    public function close($id)
    {

        $claccount = null;
        $cls_acc_id = Setting::where('company_id', session('company_id'))->where('key', 'retain_earning')->first();

        if($cls_acc_id){
            $claccount = Account::find($cls_acc_id->value);
        }else{
            return Redirect::route('years')->with('warning', "Retain Earnings Account Not Found" );
        }
        DB::transaction(function () use ($id , $claccount) {
            $year =  \App\Models\Year::where('company_id', session('company_id'))->where('id', $id)->first();
            $yearef = Carbon::parse($year->end);

            // $clgroup = null;
            // if (!Account::where('company_id', session('company_id'))->where('name', 'Accumulated Profit')->first()) {
                // if (!AccountGroup::where('company_id', session('company_id'))->where('name', 'Reserves')->first()) {
                //     $clgroup = AccountGroup::create([
                //         'name' => 'Reserves',
                //         'type_id' => AccountType::where('name', 'Capital')->first()->id,
                //         'company_id' => session('company_id'),
                //     ]);
                // } else {
                //     $clgroup = AccountGroup::where('company_id', session('company_id'))->where('name', 'Reserves')->first();
                // }
            //     $claccount = Account::create([
            //         'name' => 'Accumulated Profit',
            //         'group_id' => $clgroup->id,
            //         'company_id' => session('company_id'),
            //     ]);
            // } else {

            // }



            $doc = \App\Models\Document::create([
                'ref' => 'CL/' . $yearef->year . '/' . $yearef->month . '/00',
                'date' => $year->end,
                'description' => 'To the record the closing entry.',
                'type_id' => \App\Models\DocumentType::where('company_id', session('company_id'))->first()->id,
                'company_id' => session('company_id'),
                'year_id' => $year->id,
            ]);

            $id4 = \App\Models\AccountType::where('name', 'Revenue')->first()->id;
            $id5 = \App\Models\AccountType::where('name', 'Expenses')->first()->id;
            $grps4 = AccountGroup::where('company_id', session('company_id'))
                ->where(function (Builder $query)  use ($id4, $id5) {
                    return $query->where('type_id', $id4)
                        ->orWhere('type_id', $id5);
                })->get();

            $balance4 = [];
            $ite4 = 0;
            foreach ($grps4 as $group) {
                foreach ($group->accounts as $account) {
                    $balance = 0;
                    $lastbalance = 0;

                    $entries = DB::table('documents')
                        ->join('entries', 'documents.id', '=', 'entries.document_id')
                        ->whereDate('documents.date', '>=', $year->begin)
                        ->whereDate('documents.date', '<=', $year->end)
                        ->where('documents.company_id', session('company_id'))
                        ->where('entries.account_id', '=', $account->id)
                        ->select('entries.debit', 'entries.credit')
                        ->get();

                    foreach ($entries as $entry) {
                        $balance = $lastbalance + floatval($entry->debit) - floatval($entry->credit);
                        $lastbalance = $balance;
                    }

                    $balance4[$ite4++] = $balance;
                    $balance = -1 * $balance;
                    Entry::create([
                        'document_id' => $doc->id,
                        'account_id' => $account->id,
                        'debit' => ($balance < 0) ? '0' : $balance,
                        'credit' => ($balance > 0) ? '0' : abs($balance),
                        'company_id' => session('company_id'),
                        'year_id' => $year->id,

                    ]);
                }
            }


            //            $profit = abs(array_sum($gbalance4)) - array_sum($gbalance5);

            Entry::create([
                'document_id' => $doc->id,
                'account_id' => $claccount->id,
                'debit' => (array_sum($balance4) < 0) ? '0' : array_sum($balance4),
                'credit' => (array_sum($balance4) > 0) ? '0' : abs(array_sum($balance4)),
                'company_id' => session('company_id'),
                'year_id' => $year->id,
            ]);

            $year->update(['closed' => 1]);
        });
        return Redirect::route('reports')->with('success', 'Fiscal Year closed successfully.');
    }
}
