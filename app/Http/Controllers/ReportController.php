<?php

namespace App\Http\Controllers;


use App;
use App\Models\Account;
use App\Models\AccountGroup;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request as Req;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Year;
use App\Models\Setting;
use Dompdf\Dompdf;
use App\Models\Company;
use App\Models\Document;
use App\Models\Entry;
use App\Models\DocumentType;
use App\Models\User;
use Inertia\Inertia;
use Carbon\Carbon;
use \PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use \PhpOffice\PhpSpreadsheet\Style\Style;
use \PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Crypt;
use PDF;
use NumberFormatter;
use DateTimeZone;

class ReportController extends Controller
{
    public function index()
    {
         $a = [
                'id' => 0,
                'name' => 'All',
            ];
        $accounts = \App\Models\Account::where('company_id', session('company_id'))->get()->map(function($row){
            return [
                'id' => $row->id,
                'name' => $row->name,
            ];
        })->toArray();
        if(count($accounts) > 0){
            array_unshift($accounts, $a);
        }
        // $account_first = \App\Models\Account::all()->where('company_id', session('company_id'))->map->only('id', 'name')->first();
        $date_range = Year::where('id', session('year_id'))->first();

        return Inertia::render('Reports/Index', [
            'min_start' => $date_range->begin,
            'max_end' => $date_range->end,
            // 'account_first' => $account_first,
            'accounts' => $accounts,
            'company' => Company::where('id', session('company_id'))->first(),
            'companies' => Auth::user()->companies,
            // 'years' => Year::all()
            //     ->where('company_id', session('company_id'))
            //     ->map(function ($year) {
            //         $begin = new Carbon($year->begin);
            //         $end = new Carbon($year->end);

            //         return [
            //             'id' => $year->id,
            //             'name' => $begin->format('M d, Y') . ' - ' . $end->format('M d, Y'),
            //         ];
            //     }),
                'years' => Year::where('company_id', session('company_id'))->get(),
                'year' => Year::all()
                    ->where('company_id', session('company_id'))
                    ->where('id', session('year_id'))
                    ->map(function ($year) {
                        $begin = new Carbon($year->begin);
                        $end = new Carbon($year->end);

                        return [
                            'id' => $year->id,
                            'name' => $begin->format('M d, Y') . ' - ' . $end->format('M d, Y'),
                        ];
                    },
                )->first(),

        ]);
    }


    // FOR LEDGER GENERATION -------------------------- START --------
    public function ledger($id)
    {
        $year = Year::where('company_id', session('company_id'))->where('enabled', 1)->first();
        $acc = Account::where('company_id', session('company_id'))->where('id', Crypt::decrypt($id))->first();

        $entries = DB::table('documents')
            ->join('entries', 'documents.id', '=', 'entries.document_id')
            ->whereDate('documents.date', '>=', $year->begin)
            ->whereDate('documents.date', '<=', $year->end)
            ->where('documents.company_id', session('company_id'))
            ->select('entries.account_id', 'entries.debit', 'entries.credit', 'documents.ref', 'documents.date', 'documents.description')
            ->where('entries.account_id', '=', Crypt::decrypt($id))
            ->get();

        $previous = DB::table('documents')
            ->join('entries', 'documents.id', '=', 'entries.document_id')
            ->whereDate('documents.date', '<', $year->begin)
            ->where('documents.company_id', session('company_id'))
            ->select('entries.debit', 'entries.credit')
            ->where('entries.account_id', '=', Crypt::decrypt($id))
            ->get();

        //$entries = Entry::where('account_id',Crypt::decrypt($id))->where('company_id',session('company_id'))->get();
        $period = "From " . strval($year->begin) . " to " . strval($year->end);
        $pdf = PDF::loadView('led', compact('entries', 'previous', 'year', 'period', 'acc'));
        return $pdf->stream($acc->name . ' - ' . $acc->accountGroup->name . '.pdf');
    }


    // public function rangeLedger($account_id, $date_start, $date_end)
    public function rangeLedger(Req $request)
    {


        $start = new Carbon($request->input('date_start'));
        $end = new Carbon($request->input('date_end'));
        $account = $request->input('account_id');

        $start = $start->format('Y-m-d');
        $end = $end->format('Y-m-d');

        $entries = DB::table('documents')
            ->join('entries', 'documents.id', '=', 'entries.document_id')
            ->whereDate('documents.date', '>=', $start)
            ->whereDate('documents.date', '<=', $end)
            ->where('documents.company_id', session('company_id'))
            ->select('entries.account_id', 'entries.debit', 'entries.credit', 'documents.ref', 'documents.date', 'documents.description')
            ->where('entries.account_id', '=', $account)
            ->get();

        $previous = DB::table('documents')
            ->join('entries', 'documents.id', '=', 'entries.document_id')
            ->whereDate('documents.date', '<', $start)
            ->where('documents.company_id', session('company_id'))
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
            ->select('entries.account_id', 'entries.debit', 'entries.credit', 'documents.ref', 'documents.date', 'documents.description')
            ->where('entries.account_id', '=', $account)
            ->get();

        $data['previous'] = DB::table('documents')
            ->join('entries', 'documents.id', '=', 'entries.document_id')
            ->whereDate('documents.date', '<', $start)
            ->where('documents.company_id', session('company_id'))
            ->select('entries.debit', 'entries.credit')
            ->where('entries.account_id', '=', $account)
            ->get();

        // dd($account);
        $data['acc'] = Account::where('id', '=', $account)->where('company_id', session('company_id'))->first();
        $data['period'] = "From " . strval($start) . " to " . strval($end);

        $a = "hello world";
        $pdf = App::make('dompdf.wrapper');


        $pdf = PDF::loadView('range', $data);


        return $pdf->stream($acc->name . ' - ' . $acc->accountGroup->name . '.pdf');
        return $pdf->stream('hi.pdf');
    }

    // FOR LEDGER GENERATION -------------------------- END --------


    // FOR PDF GENERATION -------------------------- --------
    public function pd($id)
    {
        // $data['entry_obj'] = Entry::all()->where('company_id', session('company_id'))->where('year_id', session('year_id'));
        $data['entry_obj'] = Entry::where('company_id', session('company_id'))->where('year_id', session('year_id'))->where('document_id', $id)->get();

        $data['entries'] = [];
        $i = 0;
        foreach ($data['entry_obj'] as $entry) {
            if ($entry) {
                $data['entries'][$i] = $entry;
                $i++;
            }
        }
        if ($data['entries'] != []) {
            $data['doc'] = Document::all()->where('id', $data['entries'][0]->document_id)->first();
            $data['doc_type'] = DocumentType::all()->where('id', $data['doc']->type_id)->first();
        }
        // $a = Company::where('id', session('company_id'))->first();
        $pdf = App::make('dompdf.wrapper');
        // $pdf->loadView('pdf', compact('a'));
        $pdf->loadView('pdf', $data);
        return $pdf->stream('v.pdf');
    }


    // ----------------------- FOR trialbalance GENERATION -------------------------- --------
    //Accordign to date
    public function trialbalance_accToDate(Req $request)
    {
        $data['date'] = $request->date;
        $data['account_groups'] = AccountGroup::where('company_id', session('company_id'))
            ->orderBy('type_id')
            ->orderBy('parent_id')
            ->get();
        // dd($data['account_groups']);
        $data['entry_obj'] = Entry::where('company_id', session('company_id'))
            ->get();
        $tb = App::make('dompdf.wrapper');
        $tb->loadView('trial', $data);
        return $tb->stream('v.pdf');
    }
    // ----------------------- Trialbalance GENERATION ends -------------------------- --------


    public function bs()
    {

        $bs = App::make('dompdf.wrapper');
        $bs->loadView('balanceSheet');
        return $bs->stream('bs.pdf');
    }
    //Accordign to date
    public function bs_accToDate(Req $request)
    {
        $data['date'] = $request->date;
        $bs = App::make('dompdf.wrapper');
        $bs->loadView('balanceSheet', $data);
        return $bs->stream('bs.pdf');
    }

    public function pl()
    {
        $pl = App::make('dompdf.wrapper');
        $pl->loadView('profitOrLoss');
        return $pl->stream('pl.pdf');
    }
    //Accordign to date
    public function pl_accToDate(Req $request)
    {
        $data['date'] = $request->date;
        $pl = App::make('dompdf.wrapper');
        $pl->loadView('profitOrLoss', $data);
        return $pl->stream('pl.pdf');
    }



    public function multi_ledger_pdf($data){

        $request = json_decode($data, true);
        if(count($request['account']) > 0){
             if (in_array("0", $request['account']))
            {
                $accounts = Account::where('company_id', session('company_id'))->get();
            }
            else
            {
                 $accounts = Account::where('company_id', session('company_id'))->whereIn('id',$request['account'])->get();
            }

             if ($accounts) {
                $year = Year::where('id', session('year_id'))->first();
                $start = new Carbon($year->begin);
                $start = $start->format('Y-m-d');
                $end = new Carbon($request['date']);
                $end = $end->format('Y-m-d');
                $spreadsheet = new Spreadsheet();

                foreach ($accounts as $key => $account) {

                $this->multi_ledger($spreadsheet , $key, $account, $start, $end );

                }

                $writer1 = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf($spreadsheet);
                $writer1->writeAllSheets();
                $writer1->save(storage_path('app/public/multiLedger.pdf'));
                return response()->download(storage_path('app/public/multiLedger.pdf'))->deleteFileAfterSend(true);

            } else {
                $comp = Company::find(session('company_id'));
                return redirect()->back()->with('warning', 'Account not found in ' . $comp->name . ' company, Upload trial to generate accounts');
            }

        }else{
            return redirect()->back()->with('warning', 'Please Select Account Fields is Required');
        }



    }

    public function multi_ledger_ex($data){

        $request = json_decode($data, true);
        if(count($request['account']) > 0){
            if (in_array("0", $request['account']))
            {
                $accounts = Account::where('company_id', session('company_id'))->get();
            }
            else
            {
                 $accounts = Account::where('company_id', session('company_id'))->whereIn('id',$request['account'])->get();
            }

            if ($accounts) {
                $year = Year::where('id', session('year_id'))->first();
                $start = new Carbon($year->begin);
                $start = $start->format('Y-m-d');
                $end = new Carbon($request['date']);
                $end = $end->format('Y-m-d');
                $spreadsheet = new Spreadsheet();

                foreach ($accounts as $key => $account) {
                    $this->multi_ledger($spreadsheet , $key, $account, $start, $end );
                }

                $writer = new Xlsx($spreadsheet);
                $writer->save(storage_path('app/public/' . 'multi-ledger.xlsx'));
                return response()->download(storage_path('app/public/' . 'multi-ledger.xlsx'))->deleteFileAfterSend(true);


            } else {
                $comp = Company::find(session('company_id'));
                return redirect()->back()->with('warning', 'Account not found in ' . $comp->name . ' company, Upload trial to generate accounts');
            }

        }else{
            return redirect()->back()->with('warning', 'Please Select Account Fields is Required');
        }


    }

    public function multi_ledger($spreadsheet , $key, $account , $start, $end ){


        $worksheet1 = $spreadsheet->createSheet($key);

        $entries = DB::table('documents')
            ->join('entries', 'documents.id', '=', 'entries.document_id')
            ->whereDate('documents.date', '>=', $start)
            ->whereDate('documents.date', '<=', $end)
            ->where('documents.company_id', session('company_id'))
            ->orderBy('documents.date', 'Asc')
            ->select('entries.account_id', 'entries.debit', 'entries.credit', 'documents.ref', 'documents.date', 'documents.description')
            ->where('entries.account_id', '=', $account->id)
            ->get();
        $previous = DB::table('documents')
            ->join('entries', 'documents.id', '=', 'entries.document_id')
            ->whereDate('documents.date', '<', $start)
            ->where('documents.company_id', session('company_id'))
            ->orderBy('documents.date', 'Asc')
            ->select('entries.debit', 'entries.credit')
            ->where('entries.account_id', '=', $account->id)
            ->get();

        $acc = Account::where('id', '=', $account->id)->where('company_id', session('company_id'))->first();
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
            ->where('entries.account_id', '=', $account->id)
            ->get();

        $data['previous'] = DB::table('documents')
            ->join('entries', 'documents.id', '=', 'entries.document_id')
            ->whereDate('documents.date', '<', $start)
            ->where('documents.company_id', session('company_id'))
            ->orderBy('documents.date', 'Asc')
            ->select('entries.debit', 'entries.credit')
            ->where('entries.account_id', '=', $account->id)
            ->get();

        $data['acc'] = Account::where('id', '=',$account->id)->where('company_id', session('company_id'))->first();
        $data['period'] = "From " . strval($start) . " to " . strval($end);
    $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);
        foreach (range('A', 'F') as $k => $col) {
            if($col == 'C'){
                $spreadsheet->getSheet($key)->getColumnDimension('C')->setWidth(40);
            }else{
                $spreadsheet->getSheet($key)->getColumnDimension($col)->setAutoSize(true);
            }


        }

            $spreadsheet->getSheet($key)->getStyle('A:F')->getAlignment()->setHorizontal('center');
            $spreadsheet->getSheet($key)->getStyle('A3')->getAlignment()->setHorizontal('left');
            $spreadsheet->getSheet($key)->getStyle('C')->getAlignment()->setHorizontal('left');
            $spreadsheet->getSheet($key)->getStyle('C7')->getAlignment()->setHorizontal('center');

             $fmt = new NumberFormatter( 'en_GB', NumberFormatter::CURRENCY );
            $amt = new NumberFormatter( 'en_GB', NumberFormatter::SPELLOUT );
            $fmt->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 0);
            $fmt->setSymbol(NumberFormatter::CURRENCY_SYMBOL, '');
        //Default Variables
        $prebal = 0;
        $lastbalance = 0;
        $ite = 0;
        $debits = 0;
        $credits = 0;

         if ($previous->count()) {
            foreach ($previous as $value) {
                $prebal= $lastbalance + floatval($value->debit) - floatval($value->credit);
                $lastbalance = $prebal;
                $ite++;
            }
        }
        $balance = [];
        $ite = 0;
        foreach ($entries as $value) {
            $balance[$ite]= $lastbalance + floatval($value->debit) - floatval($value->credit);
            $lastbalance = $balance[$ite];
            $ite++;
        }
        $dt = \Carbon\Carbon::now(new DateTimeZone('Asia/Karachi'))->format('M d, Y - h:m a');

        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);



        $spreadsheet->getSheet($key)->mergeCells('A3:C3');
        $spreadsheet->getSheet($key)->mergeCells('D3:F3');
        $spreadsheet->getSheet($key)->mergeCells('D4:F4');


        $spreadsheet->getSheet($key)->fromArray(['Ledger :' . $acc->name . ' - '. $acc->accountGroup->name], NULL, 'A3');
        $spreadsheet->getSheet($key)->fromArray([$data['period']], NULL, 'D3');
        $spreadsheet->getSheet($key)->fromArray(['Generated on :'. $dt], NULL, 'D4');



        $spreadsheet->getSheet($key)->fromArray(['Ref'], NULL, 'A7');
        $spreadsheet->getSheet($key)->fromArray(['Date'], NULL, 'B7');
        $spreadsheet->getSheet($key)->fromArray(['Description'], NULL, 'C7');
        $spreadsheet->getSheet($key)->fromArray(['Debit'], NULL, 'D7');
        $spreadsheet->getSheet($key)->fromArray(['Credit'], NULL, 'E7');
        $spreadsheet->getSheet($key)->fromArray(['Balance'], NULL, 'F7');

        $spreadsheet->getSheet($key)->fromArray([$start], NULL, 'B8');
        $spreadsheet->getSheet($key)->fromArray(['Opening Balance'], NULL, 'C8');
        $spreadsheet->getSheet($key)->fromArray([str_replace(['Rs.','.00'],'',$fmt->formatCurrency($prebal,'Rs.'))], NULL, 'F8');




        $e = 9;
        foreach ($entries as $k => $entry){

        $spreadsheet->getSheet($key)->fromArray([$entry->ref], NULL, 'A'.$e);
        $spreadsheet->getSheet($key)->fromArray([$entry->date], NULL, 'B'.$e);
        $spreadsheet->getSheet($key)->fromArray([$entry->description], NULL, 'C'.$e);
        $spreadsheet->getSheet($key)->fromArray([str_replace(['Rs.','.00'],'',$fmt->formatCurrency($entry->debit,'Rs.'))], NULL, 'D'.$e);
        $spreadsheet->getSheet($key)->fromArray([str_replace(['Rs.','.00'],'',$fmt->formatCurrency($entry->credit,'Rs.'))], NULL, 'E'.$e);
        $spreadsheet->getSheet($key)->fromArray([str_replace(['Rs.','.00'],'',$fmt->formatCurrency($balance[$k],'Rs.'))], NULL, 'F'.$e);
        $debits = $debits + $entry->debit;
        $credits = $credits + $entry->credit;
        $e++;
        }



        $spreadsheet->getSheet($key)->fromArray([str_replace(['Rs.','.00'],'',$fmt->formatCurrency($debits,'Rs.'))], NULL, 'D'.$e);
        $spreadsheet->getSheet($key)->fromArray([str_replace(['Rs.','.00'],'',$fmt->formatCurrency($credits,'Rs.'))], NULL, 'E'.$e);

         $spreadsheet->getSheet($key)->getStyle('A7:'.'F'.$e)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_DOUBLE);



    }

    public function create()
    {
        return Inertia::render('Years/Create');
    }

    public function store(Req $request)
    {
        Request::validate([
            'begin' => ['required', 'date'],
            'end' => ['required', 'date'],
        ]);

        $year = Year::create([
            'begin' => $request->begin,
            'end' => $request->end,
            'company_id' => session('company_id'),
        ]);

        Setting::create([
            'key' => 'active_year',
            'value' => $year->id,
            'user_id' => Auth::user()->id,
        ]);

        session(['year_id' => $year->id]);
        return Redirect::route('years')->with('success', 'Year created.');
    }

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
        $year->delete();
        if (Year::where('company_id', session('company_id'))->first()) {
            return Redirect::back()->with('success', 'Year deleted.');
        } else {
            session(['year_id' => null]);
            return Redirect::route('years.create')->with('success', 'YEAR NOT FOUND. Please create an Year for selected Company.');
        }
        // }
    }

    public function yrch($id)
    {

        $active_yr = Setting::where('user_id', Auth::user()->id)->where('key', 'active_year')->first();

        $active_yr->value = $id;
        $active_yr->save();
        session(['year_id' => $id]);

        return Redirect::back();
    }
}
