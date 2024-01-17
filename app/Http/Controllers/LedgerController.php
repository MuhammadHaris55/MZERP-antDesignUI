<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Entry;
use FontLib\Table\Type\name;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

use App;
use App\Models\Account;
use Illuminate\Http\Request as Req;
use Illuminate\Support\Facades\Auth;
use App\Models\Year;
use App\Models\Setting;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use PDF;
use Illuminate\Support\Arr;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {

        $acc_exists = Account::where('company_id', session('company_id'))->first();
        if ($acc_exists) {

            $account_first = ['id' => 0];
            $account_first = Account::where('company_id', session('company_id'))->first();
            $accounts = Account::where('company_id', session('company_id'))->get()->map(function($row){
                return [
                     "id" => $row->id,
                    "number" => $row->number,
                    "name" =>  $row->name .' - '. $row->accountGroup->name,
                    "enabled" => $row->enabled,
                    "company_id" => $row->company_id,
                    "group_id" => $row->group_id,
                    "created_at" => $row->created_at,
                    "updated_at" => $row->updated_at,
                ];
           });
            if ($request->account_id) {
                $account_first = Account::all()->where('company_id', session('company_id'))->where('id', $request->account_id)->map->only('id', 'name')->first();
            }
            //------------------------ date acc to date -----------
            if (count($request->all()) > 0) {
                $start = new Carbon($request->input('date_start'));
                $end = new Carbon($request->input('date_end'));
                $account = $request->input('account_id');
            } else {
                $year = Year::find(session("year_id"));
                $start = new Carbon($year->begin);
                $end = new Carbon($year->end);
                $account = $request->input('account_id');
            }
            //------------------------ date acc to date -----------
            if ($request) {

                $start = $start->format('Y-m-d');
                $end = $end->format('Y-m-d');

                $data['start'] = $start;

                $data['entries'] = DB::table('documents')
                    ->join('entries', 'documents.id', '=', 'entries.document_id')
                    ->whereDate('documents.date', '>=', $start)
                    ->whereDate('documents.date', '<=', $end)
                    ->orderBy('documents.date', 'Asc')
                    ->where('documents.company_id', session('company_id'))

                    ->select('entries.account_id', 'entries.debit', 'entries.credit', 'documents.ref', 'documents.date', 'documents.description')
                    ->where('entries.account_id', '=', $account)
                    ->get();

                $data['previous'] = DB::table('documents')
                    ->join('entries', 'documents.id', '=', 'entries.document_id')
                    ->whereDate('documents.date', '<', $start)

                    ->where('documents.company_id', session('company_id'))
                    ->orderBy('documents.date', 'Asc')
                    ->select('entries.debit', 'entries.credit')
                    ->where('entries.account_id', '=', $account)
                    ->get();

                $data['acc'] = Account::where('id', '=', $account)->where('company_id', session('company_id'))->first();
                // $data['period'] = "From " . strval($start) . " to " . strval($end);


                //----------------------------------------------- WORKING OF VUE ---------------------------------------------
                // $fmt = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);
                // $amt = new NumberFormatter('en_GB', NumberFormatter::SPELLOUT);
                // $fmt->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 0);
                // $fmt->setSymbol(NumberFormatter::CURRENCY_SYMBOL, '');
                $prebal = 0;
                $data['lastbalance'] = 0;
                $ite = 0;
                $data['debits'] = 0;
                $data['credits'] = 0;
                if ($data['previous']->count()) {
                    foreach ($data['previous'] as $value) {
                        $prebal = $data['lastbalance'] + floatval($value->debit) - floatval($value->credit);
                        $data['lastbalance'] = $prebal;
                        $ite++;
                    }
                }
                $balance = [];
                $ite = 0;
                foreach ($data['entries'] as $value) {
                    $balance[$ite] = $data['lastbalance'] + floatval($value->debit) - floatval($value->credit);
                    $data['lastbalance'] = $balance[$ite];
                    $ite++;
                }
                // $dt = \Carbon\Carbon::now(new DateTimeZone('Asia/Karachi'))->format('M d, Y - h:m a');
            }


            // @foreach ($entries as $entry)
            foreach ($data['entries'] as $entry) {
                //     {{$entry->ref}}
                //     {{$entry->date}}
                //     {{$entry->description}}
                //     {{str_replace(['Rs.','.00'],'',$fmt->formatCurrency($entry->debit,'Rs.'))}}
                //     {{str_replace(['Rs.','.00'],'',$fmt->formatCurrency($entry->credit,'Rs.'))}}
                //     {{str_replace(['Rs.','.00'],'',$fmt->formatCurrency($balance[$loop->index],'Rs.'))}}

                $data['debits'] = $data['debits'] + $entry->debit;
                $data['credits'] = $data['credits'] + $entry->credit;
            }
            $date_range = Year::where('id', session('year_id'))->first();

            // ---------- mapping data for ant-design
            $mapped_data_incomp = $data['entries']->map(function ($entry, $key) use ($balance) {
                return [
                    'account_id' => $entry->account_id,
                    'ref' => $entry->ref,
                    'date' => $entry->date,
                    'description' => $entry->description,
                    'debit' => number_format($entry->debit,2),
                    'credit' => number_format($entry->credit,2),
                    'balance' => number_format($balance[$key] , 2),
                    // 'delete' => Account::where('group_id', $entry->id)->first() ? false : true,
                ];
            });

            //first and last line which we have to show in table
            $first_arr = [
                'account_id' => '',
                'ref' => '',
                'date' => '',
                'description' => 'Opening Balance',
                'debit' => '',
                'credit' => '',
                'balance' => number_format($prebal,2),
            ];
            $last_arr = [
                'account_id' => '',
                'ref' => '',
                'date' => '',
                'description' => 'Totals',
                'debit' => number_format($data['debits'],2),
                'credit' => number_format($data['credits'],2),
                'balance' => '',
            ];
            //main data to send on frontend VUE
            $mapped_data[0] = $first_arr;
            $a = 1;
            foreach ($mapped_data_incomp as $map_incomp_data) {
                $mapped_data[$a++] = $map_incomp_data;
            }
            $mapped_data[$a] = $last_arr;
            // ---------- mapping data for ant-design
            // dd(number_format($data['debits'] , 2));
            return Inertia::render('Ledgers/Index', [

                'account_first' => $account_first,
                'accounts' => $accounts,
                'date_start' => $start,
                'date_end' => $end,
                'entries' => $data['entries'],
                'mapped_data' => $mapped_data,
                'debits' => number_format($data['debits'] , 2),
                'credits' => number_format($data['credits'],2),
                'balance' => $balance,
                'prebal' => $prebal,
                'min_start' => $date_range->begin,
                'max_end' => $date_range->end,
                'company' => companies_first(),
                'companies' => companies_get(),
                'years' => years_get(),
                'year' => years_first(),

            ]);
        } else {
            return Redirect::route('accounts')->with('warning', 'Transaction NOT FOUND, Please create an transaction first.');
        }
    }


    // To generate ledger report in pdf --------------------------- LEDGER ---------------
    public function rangeLedger(Req $request, $id)
    {
        $start = new Carbon($request->input('date_start'));
        $end = new Carbon($request->input('date_end'));
        // $account = $request->input('account_id');
        $account = $id;

        $start = $start->format('Y-m-d');
        $end = $end->format('Y-m-d');

        $entries = DB::table('documents')
            ->join('entries', 'documents.id', '=', 'entries.document_id')
            ->whereDate('documents.date', '>=', $start)
            ->whereDate('documents.date', '<=', $end)
            ->where('documents.company_id', session('company_id'))
            ->orderBy('documents.date', 'Asc')
            ->select('entries.account_id', 'entries.debit', 'entries.credit', 'documents.ref', 'documents.date', 'documents.description')
            ->where('entries.account_id', '=', $account)
            ->get();

        $previous = DB::table('documents')
            ->join('entries', 'documents.id', '=', 'entries.document_id')
            ->whereDate('documents.date', '<', $start)
            ->where('documents.company_id', session('company_id'))
            ->orderBy('documents.date', 'Asc')
            ->select('entries.debit', 'entries.credit')
            ->where('entries.account_id', '=', $account)
            ->get();

        $acc = Account::where('id', '=', $account)->where('company_id', session('company_id'))->first();
        $period = "From " . strval($start) . " to " . strval($end);


        // $data['start'] = $start;
        $data['start'] = $start;



        $data['entries'] = DB::table('documents')
            ->join('entries', 'documents.id', '=', 'entries.document_id')
            ->whereDate('documents.date', '>=', $start)
            ->whereDate('documents.date', '<=', $end)
            ->where('documents.company_id', session('company_id'))
            ->orderBy('documents.date', 'Asc')
            ->select('entries.account_id', 'entries.debit', 'entries.credit', 'documents.ref', 'documents.date', 'documents.description')
            ->where('entries.account_id', '=', $account)
            ->get();

        $data['previous'] = DB::table('documents')
            ->join('entries', 'documents.id', '=', 'entries.document_id')
            ->whereDate('documents.date', '<', $start)
            ->where('documents.company_id', session('company_id'))
            ->orderBy('documents.date', 'Asc')
            ->select('entries.debit', 'entries.credit')
            ->where('entries.account_id', '=', $account)
            ->get();

        // dd(session('company_id'));
        // dd($account);
        $data['acc'] = Account::where('id', '=', $account)->where('company_id', session('company_id'))->first();
        $data['period'] = "From " . strval($start) . " to " . strval($end);
        // $pdf = PDF::loadView('range', compact('entries', 'previous', 'acc', 'period', 'start'));
        // dd($data['entries']);
        // dd($acc->accountGroup->name);

        // dd($data['acc']);
        $a = "hello world";
        $pdf = App::make('dompdf.wrapper');
        // $pdf->loadView('ledger', compact('a'));
        // return $pdf->stream('v.pdf');

        $pdf = PDF::loadView('range', $data);
        // $pdf = PDF::loadView('ledger', compact('entries', 'previous', 'acc', 'period', 'start'));

        // $pdf = PDF::loadView('range', $data);


        // $pdf = PDF::loadView('range', compact('entries', 'previous', 'acc', 'period', 'start'));


        return $pdf->stream($acc->name . ' - ' . $acc->accountGroup->name . '.pdf');
        return $pdf->stream('ledger.pdf');
    }



    public function getledger($id)
    {

        if ($id) {
            $account_first = \App\Models\Account::all()->where('account_id', request('account_id'))->map->only('id', 'name')->first();
        } else {
            $account_first = \App\Models\Account::all()->where('company_id', session('company_id'))->map->only('id', 'name')->first();
        }

        $accounts = \App\Models\Account::all()->where('company_id', session('company_id'))->map->only('id', 'name');
        $entries = Entry::all()->where('company_id', session('company_id'))->where('account_id', $id)
            ->map(function ($entry) {
                return [
                    'ref' => $entry->document->ref,
                    'date' => $entry->document->date,
                    'description' => $entry->document->description,
                    'debit' => 'debit',
                    'credit' => 'credit',
                ];
            });

        return Inertia::render('Ledgers/Index', [
            'enteries' => $entries,
            'companies' => Company::all(),
            'accounts' => $accounts,
        ]);
    }


    public function __invoke(Request $request)
    {
    }
}
