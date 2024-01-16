<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountGroup;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Year;
use App\Models\Setting;
use Egulias\EmailValidator\Warning\Warning;
use Inertia\Inertia;
use Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;use App;
use App\Models\AccountType;
use App\Models\Document;
use App\Models\Entry;
use App\Models\DocumentType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Http\Request as Req;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{


    public function index()
    {
        if(request()->has(
            // ['select', 'search']
            'search'
            )){
            $obj_data = auth()->user()->companies()->where(
                // $req->select
                'name'
                ,'LIKE', '%'.$req->search.'%')
            ->get();
            $mapped_data = $obj_data->map(function($comp, $key) {
            return [
                    'id' => $comp->id,
                    'name' => $comp->name,
                    'address' => $comp->address,
                    'email' => $comp->email,
                    'web' => $comp->web,
                    'phone' => $comp->phone,
                    'fiscal' => $comp->fiscal,
                    'incorp' => $comp->incorp,
                    'delete' => Year::where('company_id', $comp->company_id)->first() != null ? false : true,
                ];
            });
        }
        else{
            $obj_data = auth()->user()->companies()->get();
            $mapped_data = $obj_data->map(function($comp, $key) {
            return [
                    'id' => $comp->id,
                    'name' => $comp->name,
                    'address' => $comp->address,
                    'email' => $comp->email,
                    'web' => $comp->web,
                    'phone' => $comp->phone,
                    'fiscal' => $comp->fiscal,
                    'incorp' => $comp->incorp,
                    'delete' => Year::where('company_id', $comp->company_id)->first() != null ? false : true,
                ];
            });
        }

        return Inertia::render('Company/Index', [
            'can' => [
                'edit' => auth()->user()->can('edit'),
                'create' => auth()->user()->can('create'),
                'delete' => auth()->user()->can('delete'),
                'read' => auth()->user()->can('read'),
            ],
            'mapped_data' => $mapped_data,
            'filters' => request()->all(['search', 'field', 'direction'])
        ]);
    }

    public function create()
    {
        if (auth()->user()->roles->first()->name == 'user') {
            abort(403, 'You don\'t have access this page');
        }


        $fiscals = ['June', 'March', 'September', 'December'];
        $fiscal_first = 'June';

        return Inertia::render('Company/Create', [
            'fiscals' => $fiscals, 'fiscal_first' => $fiscal_first
        ]);
    }

    public function store()
    {
        Request::validate([
            'name' => ['required', 'unique:companies'],
            'fiscal' => ['required'],
        ]);
        DB::transaction(function () {
            $company = Company::create([
                'name' => strtoupper(Request::input('name')),
                'address' => Request::input('address'),
                'email' => Request::input('email'),
                'web' => Request::input('web'),
                'phone' => Request::input('phone'),
                'fiscal' => Request::input('fiscal'),
                'incorp' => Request::input('incorp'),
            ]);
            $company->users()->attach(auth()->user()->id);

            //Start Month & End Month
            $startMonth = Carbon::parse($company->fiscal)->month + 1;
            $endMonth = Carbon::parse($company->fiscal)->month;
            if ($startMonth == 13) {
                $startMonth = 1;
            }

            //Start Month Day & End Month Day
            $startMonthDays = 1;
            $endMonthDays = Carbon::create()->month($endMonth)->daysInMonth;

            // Year Get
            $today = Carbon::today();
            $startYear = 0;
            $endYear = 0;
            if ($startMonth == 1) {
                $startYear = $today->year;
                $endYear = $today->year;
            } else {
                $endYear = ($today->month >= $startMonth) ? $today->year + 1 : $today->year;
                $startYear = $endYear - 1;
            }

            $startDate = $startYear . '-' . '0' . $startMonth . '-' . $startMonthDays;
            $endDate = $endYear . '-' . '0' . $endMonth . '-' . $endMonthDays;

            $year = Year::create([
                'begin' => $startDate,
                'end' => $endDate,
                'company_id' => $company->id,
            ]);



            $set_comp = Setting::where('user_id', Auth::user()->id)->where('key', 'active_company')->first();
            $set_year = Setting::where('user_id', Auth::user()->id)->where('key', 'active_year')->first();

            if ($set_comp) {
                $set_comp->value = $company->id;
                $set_comp->save();
            } else {
                // Create Active Company Setting
                Setting::create([
                    'key' => 'active_company',
                    'value' => $company->id,
                    'user_id' => Auth::user()->id,
                ]);
            }
            if ($set_year) {
                $set_year->value = $year->id;
                $set_year->save();
            } else {
                // Create Active Year Setting
                Setting::create([
                    'key' => 'active_year',
                    'value' => $year->id,
                    'user_id' => Auth::user()->id,
                ]);
            }

            session(['company_id' => $company->id]);
            session(['year_id' => $year->id]);


            //create default Account Group
            $acc_grp =  AccountGroup::create([
                'name' => 'Reserves',
                'type_id' => 3,
                'company_id' =>  $company->id
            ]);

            //create default Account
            $account = Account::create([
            'name' => 'Retained Eearnings',
            'group_id' => $acc_grp->id,
            'company_id' => $company->id
            ]);
            $account->update(['number' => $this->snum($account)]);

             $retain_earning = Setting::where('company_id' ,  $company->id)->where('key', 'retain_earning')->first();

            if ($retain_earning) {
                $retain_earning->company_id = $company->id;
                $retain_earning->value = $account->id;
                $set_comp->save();
            } else {
                // Create Active Company Setting
                Setting::create([
                    'key' => 'retain_earning',
                    'value' => $account->id,
                    'user_id' => Auth::user()->id,
                    'company_id' => $company->id,
                ]);
            }

            //TO run the seeders class
            // Artisan::call('db:seed', array('--class' => "GroupSeeder"));

            // Storage::makeDirectory('/public/' . $company->id);
            // Storage::makeDirectory('/public/' . $company->id . '/' . $year->id);
        });
        return Redirect::route('companies')->with('success', 'Company created');
    }

    public function edit(Company $company)
    {

        if (auth()->user()->roles->first()->name == 'user') {
            abort(403, 'You don\'t have access this page');
        }


        return Inertia::render('Company/Edit', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'address' => $company->address,
                'email' => $company->email,
                'web' => $company->web,
                'phone' => $company->phone,
                'fiscal' => $company->fiscal,
                'incorp' => $company->incorp,
            ],
        ]);
    }

    public function update(Company $company)
    {


        Request::validate([
            'name' => ['required'],
            'address' => ['nullable'],
            'email' => ['nullable'],
            'web' => ['nullable'],
            'phone' => ['nullable'],
            'fiscal' => ['required'],
            'incorp' => ['nullable'],
        ]);

        $company->name = Request::input('name');
        $company->address = Request::input('address');
        $company->email = Request::input('email');
        $company->web = Request::input('web');
        $company->phone = Request::input('phone');
        $company->fiscal = Request::input('fiscal');

        $incorp = new carbon(Request::input('incorp'));
        $company->incorp = $incorp->format('Y-m-d');

        $company->save();

        return Redirect::route('companies')->with('success', 'Company updated.');
    }

    public function destroy(Company $company)
    {
        if (auth()->user()->roles->first()->name == 'user') {
            abort(403, 'You don\'t have access this page');
        }

        $company->users()->detach(auth()->user()->id);
        $company->delete();
        return Redirect::back()->with('success', 'Company deleted.');
    }

    //    <inertia-link
    //                     v-if="canRegister"
    //                     :href="route('register')"
    //                     class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150 ml-4"
    //                 >
    //                     Register
    //                 </inertia-link>

    //TO CHANGE THE COMPANY IN SESSION FROM DROPDOWN
    public function coch($id)
    {
        $active_co = Setting::where('user_id', Auth::user()->id)->where('key', 'active_company')->first();
        // $coch_hold = Company::where('id', $active_co->value)->first();
        // $active_co = Setting::all();
        // where('user_id', Auth::user()->id)->where('key', 'active_company')->first();
        // dd($active_co);

        $active_co->value = $id;

        $active_co->save();
        session(['company_id' => $id]);

        if (Year::where('company_id', $id)->latest()->first()) {
            $active_yr = Setting::where('user_id', Auth::user()->id)->where('key', 'active_year')->first();
            $active_yr->value = Year::where('company_id', $id)->latest()->first()->id;
            $active_yr->save();

            $active_yr = Year::where('company_id', $id)->latest()->first()->id;

            session(['year_id' => $active_yr]);
            // session(['year_id' => $active_yr->value]);
            // $active_co->save();
            // session(['company_id' => $id]);
            return Redirect::back();
        } else {
            session(['year_id' => null]);
            return Redirect::route('years.create')->with('success', 'YEAR NOT FOUND. Please create an Year for selected Company.');
        }
    }


    // FOR PDF FROM MZAUDIT --------
    public function pd()
    {
        // $a = "hello world";
        // dd(AccountType::where('company_id', session('company_id'))->first());
        $voucher = Entry::all()
            ->where('id', 2)
            // ->where('company_id', session('company_id'))
            //     ->where('year_id', session('year_id'))

            ->map(function ($comp) {
                return [
                    'id' => $comp->id,
                    'debit' => $comp->debit,
                    'credit' => $comp->credit,
                    'description' => 'description',
                    'ref' => 'ref',
                    'name' => 'name',
                    // 'ref' => $comp->document->ref,
                    // 'description' => $comp->document->description,
                    // 'name' => $comp->document->documentType->name,
                ];
            })
            ->first();

        $data['entry_obj'] = Entry::all()->where('company_id', session('company_id'))->where('year_id', session('year_id'));

        $i = 0;
        foreach ($data['entry_obj'] as $entry) {
            if ($entry) {
                $data['entries'][$i] = $entry;
                $i++;
            }
        }
        $data['doc'] = Document::all()->where('id', $data['entries'][0]->document_id)->first();
        $data['doc_type'] = DocumentType::all()->where('id', $data['doc']->type_id)->first();
        $a = Company::where('id', session('company_id'))->first();
        $pdf = App::make('dompdf.wrapper');
        // $pdf->loadView('pdf', compact('a'));
        $pdf->loadView('pdf', $data);
        return $pdf->stream('v.pdf');
    }
    // FOR PDF FROM MZAUDIT --------

}
