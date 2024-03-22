<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Balance Sheet</title>

    <style type="text/css">
        @page {
            margin-right: 10px;
            margin-left: 45px;
            margin-top: -10px;
        }

        body {
            margin: 10px;
        }

        * {
            font-family: Verdana, Arial, sans-serif;
        }

        a {
            text-decoration: none;
        }

        table {
            font-size: x-small;
        }

        tfoot tr td {
            font-weight: bold;
            font-size: medium;
        }

        .invoice table {
            margin: 5px;
        }

        .invoice h3 {
            margin-left: 5px;
        }

        .information {
            background-color: #fff;
        }

        .information .logo {
            margin: 5px;
        }

        .information table {
            padding: 5px;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <?php
    $fmt = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);
    $amt = new NumberFormatter('en_GB', NumberFormatter::SPELLOUT);
    $fmt->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 0);
    $fmt->setSymbol(NumberFormatter::CURRENCY_SYMBOL, '');
    $dt = \Carbon\Carbon::now(new DateTimeZone('Asia/Karachi'))->format('M d, Y - h:m a');
    $year = \App\Models\Year::where('company_id', session('company_id'))
        ->where('enabled', 1)
        ->first();

    $id1 = \App\Models\AccountType::where('name', 'Assets')->first()->id;
    $grps1 = \App\Models\AccountGroup::where('company_id', session('company_id'))
        ->where('type_id', $id1)
        ->tree()
        ->get()
        ->ToTree();
    $gbalance1 = [];
    $gi = 0;
    foreach ($grps1 as $gr) {
        $gite1 = 0;
        $balance = 0;
        $lastbalance = 0;

        foreach ($gr->accounts as $account) {
            $entries = Illuminate\Support\Facades\DB::table('documents')
                ->join('entries', 'documents.id', '=', 'entries.document_id')
                // ->whereDate('documents.date', '<=', $year->end)
                //According to selected date
                //  ->whereDate('documents.date', '>=', $start_date)
                ->whereDate('documents.date', '<=', $date)
                ->where('documents.company_id', session('company_id'))
                ->where('entries.account_id', '=', $account->id)
                ->select('entries.debit', 'entries.credit')
                ->get();

            foreach ($entries as $entry) {
                $balance = $lastbalance + floatval($entry->debit) - floatval($entry->credit);
                $lastbalance = $balance;
            }
        }
        $gbalance11[$gi] = $balance;

        foreach ($gr->children as $group) {
            $balance = 0;
            $lastbalance = 0;

            foreach ($group->accounts as $account) {
                $entries = Illuminate\Support\Facades\DB::table('documents')
                    ->join('entries', 'documents.id', '=', 'entries.document_id')
                    // ->whereDate('documents.date', '<=', $year->end)
                    //According to selected date
                    // ->whereDate('documents.date', '>=', $start_date)
                    ->whereDate('documents.date', '<=', $date)
                    ->where('documents.company_id', session('company_id'))
                    ->where('entries.account_id', '=', $account->id)
                    ->select('entries.debit', 'entries.credit')
                    ->get();

                foreach ($entries as $entry) {
                    $balance = $lastbalance + floatval($entry->debit) - floatval($entry->credit);
                    $lastbalance = $balance;
                }
            }
            $gbalance1[$gi][$gite1] = $balance;
            if (count($group->children) > 0) {
                $gbalance1[$gi][$gite1++] = recurse($start_date , $date, $group, $year, $balance, $lastbalance, 1);
            } else {
                $gite1++;
            }
        }
        $gi++;
    }

    // dd($gbalance1);
    // @dd($date);
    // =================== Recursive function ============================
    function recurse($start_date, $date, $gr, $year, $balance, $lastbalance, $for_total)
    {
        foreach ($gr->children as $group) {
            foreach ($group->accounts as $account) {
                $entries = Illuminate\Support\Facades\DB::table('documents')
                    ->join('entries', 'documents.id', '=', 'entries.document_id')
                    // ->whereDate('documents.date', '<=', $year->end)
                    //According to selected date
                    // ->whereDate('documents.date', '>=', $start_date)
                    ->whereDate('documents.date', '<=', $date)
                    ->where('documents.company_id', session('company_id'))
                    ->where('entries.account_id', '=', $account->id)
                    ->select('entries.debit', 'entries.credit')
                    ->get();

                // dd($entries);
                if ($for_total == 1) {
                    foreach ($entries as $entry) {
                        $balance = $lastbalance + floatval($entry->debit) - floatval($entry->credit);
                        $lastbalance = $balance;
                    }
                } else {
                    foreach ($entries as $entry) {
                        $balance = $lastbalance + floatval($entry->credit) - floatval($entry->debit);
                        $lastbalance = $balance;
                    }
                }
            }
            if (count($group->children) > 0) {
                recurse($start_date , $date, $group, $year, $balance, $lastbalance);
            }
        }
        return $balance;
    }
    // ===============================================

    $id2 = \App\Models\AccountType::where('name', 'Liabilities')->first()->id;
    $grps2 = \App\Models\AccountGroup::where('company_id', session('company_id'))
        ->where('type_id', $id2)
        ->tree()
        ->get()
        ->ToTree();
    $gbalance2 = [];
    $gi = 0;
    foreach ($grps2 as $gr) {
        $gite1 = 0;
        $balance = 0;
        $lastbalance = 0;

        foreach ($gr->accounts as $account) {
            $entries = Illuminate\Support\Facades\DB::table('documents')
                ->join('entries', 'documents.id', '=', 'entries.document_id')
                // ->whereDate('documents.date', '<=', $year->end)
                //According to selected date
                // ->whereDate('documents.date', '>=', $start_date)
                ->whereDate('documents.date', '<=', $date)
                ->where('documents.company_id', session('company_id'))
                ->where('entries.account_id', '=', $account->id)
                ->select('entries.debit', 'entries.credit')
                ->get();

            foreach ($entries as $entry) {
                $balance = $lastbalance + floatval($entry->credit) - floatval($entry->debit);
                $lastbalance = $balance;
            }
        }
        $gbalance22[$gi] = $balance;
        // dd($gbalance22);

        foreach ($gr->children as $group) {
            $balance = 0;
            $lastbalance = 0;

            foreach ($group->accounts as $account) {
                $entries = Illuminate\Support\Facades\DB::table('documents')
                    ->join('entries', 'documents.id', '=', 'entries.document_id')
                    // ->whereDate('documents.date', '<=', $year->end)
                    //According to selected date
                    // ->whereDate('documents.date', '>=', $start_date)
                    ->whereDate('documents.date', '<=', $date)
                    ->where('documents.company_id', session('company_id'))
                    ->where('entries.account_id', '=', $account->id)
                    ->select('entries.debit', 'entries.credit')
                    ->get();

                foreach ($entries as $entry) {
                    $balance = $lastbalance + floatval($entry->credit) - floatval($entry->debit);
                    $lastbalance = $balance;
                }
            }
            $gbalance2[$gi][$gite1] = $balance;
            if (count($group->children) > 0) {
                $gbalance2[$gi][$gite1++] = recurse($start_date, $date, $group, $year, $balance, $lastbalance, 0);
            } else {
                $gite1++;
            }
        }
        $gi++;
    }

    $id3 = \App\Models\AccountType::where('id',3)->first()->id;
    $grps3 = \App\Models\AccountGroup::where('company_id', session('company_id'))
        ->where('type_id', $id3)
        ->tree()
        ->get()
        ->ToTree();
    $gbalance3 = [];
    $gi = 0;
    foreach ($grps3 as $gr) {
        $gite3 = 0;
        $balance = 0;
        $lastbalance = 0;

        foreach ($gr->accounts as $account) {
            $entries = Illuminate\Support\Facades\DB::table('documents')
                ->join('entries', 'documents.id', '=', 'entries.document_id')
                // ->whereDate('documents.date', '<=', $year->end)
                //According to selected date
                // ->whereDate('documents.date', '>=', $start_date)
                ->whereDate('documents.date', '<=', $date)
                ->where('documents.company_id', session('company_id'))
                ->where('entries.account_id', '=', $account->id)
                ->select('entries.debit', 'entries.credit')
                ->get();

            foreach ($entries as $entry) {
                $balance = $lastbalance + floatval($entry->credit) - floatval($entry->debit);
                $lastbalance = $balance;
            }
        }
        $gbalance33[$gi] = $balance;

        foreach ($gr->children as $group) {
            $balance = 0;
            $lastbalance = 0;

            foreach ($group->accounts as $account) {
                $entries = Illuminate\Support\Facades\DB::table('documents')
                    ->join('entries', 'documents.id', '=', 'entries.document_id')
                    // ->whereDate('documents.date', '<=', $year->end)
                    //According to selected date
                    // ->whereDate('documents.date', '>=', $start_date)
                    ->whereDate('documents.date', '<=', $date)
                    ->where('documents.company_id', session('company_id'))
                    ->where('entries.account_id', '=', $account->id)
                    ->select('entries.debit', 'entries.credit')
                    ->get();

                foreach ($entries as $entry) {
                    $balance = $lastbalance + floatval($entry->credit) - floatval($entry->debit);
                    $lastbalance = $balance;
                }
            }
            $gbalance3[$gi][$gite3] = $balance;
            if (count($group->children) > 0) {
                $gbalance3[$gi][$gite3++] = recurse($start_date, $date, $group, $year, $balance, $lastbalance, 0);
            } else {
                $gite3++;
            }
        }
        $gi++;
    }

    $id4 = \App\Models\AccountType::where('name', 'Revenue')->first()->id;
    $grps4 = \App\Models\AccountGroup::where('company_id', session('company_id'))
        ->where('type_id', $id4)
        ->tree()
        ->get()
        ->ToTree();
    $balance_total4 = [];
    $gbalance = [];
    $gi = 0;
    $balance4_inc = 0;
    foreach ($grps4 as $gr) {
        $gite4 = 0;
        $balance = 0;
        $lastbalance = 0;

        foreach ($gr->accounts as $account) {
            $entries = Illuminate\Support\Facades\DB::table('documents')
                ->join('entries', 'documents.id', '=', 'entries.document_id')
                // ->whereDate('documents.date', '<=', $year->end)
                //According to selected date
                // ->whereDate('documents.date', '>=', $start_date)
                ->whereDate('documents.date', '<=', $date)
                ->where('documents.company_id', session('company_id'))
                ->where('entries.account_id', '=', $account->id)
                ->select('entries.debit', 'entries.credit')
                ->get();

            foreach ($entries as $entry) {
                $balance = $lastbalance + floatval($entry->credit) - floatval($entry->debit);
                $lastbalance = $balance;
            }
        }
        $gbalance44[$gi] = $balance;
        $balance_total4[$balance4_inc++] = $balance;

        // dd($gbalance44);
        foreach ($gr->children as $group) {
            $balance = 0;
            $lastbalance = 0;

            foreach ($group->accounts as $account) {
                $entries = Illuminate\Support\Facades\DB::table('documents')
                    ->join('entries', 'documents.id', '=', 'entries.document_id')
                    // ->whereDate('documents.date', '<=', $year->end)
                    //According to selected date
                    // ->whereDate('documents.date', '>=', $start_date)
                    ->whereDate('documents.date', '<=', $date)
                    ->where('documents.company_id', session('company_id'))
                    ->where('entries.account_id', '=', $account->id)
                    ->select('entries.debit', 'entries.credit')
                    ->get();

                foreach ($entries as $entry) {
                    $balance = $lastbalance + floatval($entry->credit) - floatval($entry->debit);
                    $lastbalance = $balance;
                }
            }
            $gbalance4[$gi][$gite4] = $balance;
            $balance_total4[$balance4_inc++] = $balance;

            if (count($group->children) > 0) {
                $gbalance4[$gi][$gite4++] = recurse($start_date, $date, $group, $year, $balance, $lastbalance, 0);
            } else {
                $gite4++;
            }
        }
        $gi++;
    }

    $id5 = \App\Models\AccountType::where('name', 'Expenses')->first()->id;
    $grps5 = \App\Models\AccountGroup::where('company_id', session('company_id'))
        ->where('type_id', $id5)
        ->tree()
        ->get()
        ->ToTree();
    $balance_total5 = [];
    $gbalance5 = [];
    $gi = 0;
    $balance5_inc = 0;

    foreach ($grps5 as $gr) {
        $gite5 = 0;
        $balance = 0;
        $lastbalance = 0;
        foreach ($gr->accounts as $account) {
            $entries = Illuminate\Support\Facades\DB::table('documents')
                ->join('entries', 'documents.id', '=', 'entries.document_id')
                // ->whereDate('documents.date', '<=', $year->end)
                //According to selected date
                // ->whereDate('documents.date', '>=', $start_date)
                ->whereDate('documents.date', '<=', $date)
                ->where('documents.company_id', session('company_id'))
                ->where('entries.account_id', '=', $account->id)
                ->select('entries.debit', 'entries.credit')
                ->get();

            foreach ($entries as $entry) {
                $balance = $lastbalance + floatval($entry->debit) - floatval($entry->credit);
                $lastbalance = $balance;
            }
        }
        $gbalance55[$gi] = $balance;
        $balance_total5[$balance5_inc++] = $balance;

        foreach ($gr->children as $group) {
            $balance = 0;
            $lastbalance = 0;

            foreach ($group->accounts as $account) {
                $entries = Illuminate\Support\Facades\DB::table('documents')
                    ->join('entries', 'documents.id', '=', 'entries.document_id')
                    // ->whereDate('documents.date', '<=', $year->end)
                    //According to selected date
                    // ->whereDate('documents.date', '>=', $start_date)
                    ->whereDate('documents.date', '<=', $date)
                    ->where('documents.company_id', session('company_id'))
                    ->where('entries.account_id', '=', $account->id)
                    ->select('entries.debit', 'entries.credit')
                    ->get();

                foreach ($entries as $entry) {
                    $balance = $lastbalance + floatval($entry->debit) - floatval($entry->credit);
                    $lastbalance = $balance;
                }
            }
            $gbalance5[$gi][$gite5] = $balance;
            $balance_total5[$balance5_inc++] = $balance;

            if (count($group->children) > 0) {
                $gbalance5[$gi][$gite5++] = recurse($start_date, $date, $group, $year, $balance, $lastbalance, 1);
            } else {
                $gite5++;
            }
        }
        $gi++;
    }
    ?>


    <div class="information">
        <table width="100%" style="border-collapse: collapse;">
            <thead>
                <tr>
                     <th align="left" style="width: 50%;">
                        <h3>Balance Sheet</h3>
                    </th>
                    <th colspan='2' align="right" style="width: 30%;">
                        <h5>Generated on: {{ $dt }}</h5>
                        <h5> {{'Form: '.Carbon\Carbon::create($start_date)->format('M d, Y'). ' To: '. Carbon\Carbon::create($date)->format('M d, Y')}}</h5>
                    </th>
                </tr>
                <tr>
                    <th style="width: 70%;border-bottom:2pt solid black;">
                        <strong></strong>
                    </th>
                    <th style="width: 15%;border-bottom:2pt solid black;" align="centre">
                        <strong>Amount in Rs.</strong>
                    </th>
                </tr>
            </thead>
            <tbody>

                <tr>
                    <td><strong>ASSETS</strong></td>
                    <td></td>
                </tr>
                <?php
                $b_total_index = 0;
                $gbalance_total = [];
                ?>
                @foreach ($grps1 as $key => $group)
                    @if (count($group->children) == 0 && $gbalance11[$key] < 1)
                        @continue
                    @endif
                    <tr>
                        <td style="width: 15%; padding-left:10px">
                            <strong> {{ $group->name }}</strong>
                        </td>
                        <td style="width:10%;" align="right">
                            {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency($gbalance11[$key], 'Rs.')) }}
                        </td>
                    </tr>
                    <?php $gbalance_total[$b_total_index++] = $gbalance11[$key]; ?>

                    @foreach ($group->children as $value)
                        @if ($gbalance1[$key][$loop->index] == 0)
                            @continue
                        @endif
                        <tr>
                            <td style="width: 15%; padding-left:10px">
                                {{ $value->name }}
                            </td>
                            <td style="width: 10%;" align="right">
                                {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency($gbalance1[$key][$loop->index], 'Rs.')) }}
                            </td>
                        </tr>
                        <?php $gbalance_total[$b_total_index++] = $gbalance1[$key][$loop->index]; ?>
                    @endforeach
                @endforeach
                <tr>
                    <td style="width: 15%;">
                        <strong> Assets - Total</strong>
                    </td>
                    <td style="width: 10%; border-top: 1pt solid black; border-bottom: 3pt double black;"
                        align="right">
                        <strong>
                            {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency(array_sum($gbalance_total), 'Rs.')) }}</strong>
                    </td>
                </tr>

                <tr>
                    <td><strong>LIABILITIES</strong></td>
                    <td></td>
                </tr>
                <?php

                $gbalance_total2 = [];
                ?>
                @foreach ($grps2 as $key => $group)
                    @if (count($group->children) == 0 && $gbalance22[$key] < 1)
                        @continue
                    @endif
                    <tr>
                        <td style="width: 15%; padding-left:10px">
                            <strong> {{ $group->name }}</strong>
                        </td>
                        <td style="width:10%;" align="right">
                            {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency($gbalance22[$key], 'Rs.')) }}
                        </td>
                    </tr>
                    <?php $gbalance_total2[$b_total_index++] = $gbalance22[$key]; ?>

                    @foreach ($group->children as $value)
                        @if ($gbalance2[$key][$loop->index] == 0)
                            @continue
                        @endif
                        <tr>
                            <td style="width: 15%; padding-left:10px">
                                {{ $value->name }}
                            </td>
                            <td style="width: 10%;" align="right">
                                {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency($gbalance2[$key][$loop->index], 'Rs.')) }}
                            </td>
                        </tr>
                        <?php $gbalance_total2[$b_total_index++] = $gbalance2[$key][$loop->index]; ?>
                    @endforeach
                @endforeach
                <tr>
                    <td style="width: 15%;">
                        Liabilities - Total
                    </td>
                    <td style="width: 10%; border-top: 1pt solid black; border-bottom: 3pt double black;"
                        align="right">
                        {{-- {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency(array_sum($gbalance1), 'Rs.')) }} --}}
                        {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency(array_sum($gbalance_total2), 'Rs.')) }}
                    </td>
                </tr>
                <?php
                $b_total_index = 0;
                $gbalance_total3 = [];
                ?>

                <tr>
                    <td><strong>Share Capital And Equity</strong></td>
                    <td></td>
                </tr>

                {{-- @dd($gbalance33, $grps3); --}}
                @foreach ($grps3 as $key => $group)
                    @if (count($group->children) == 0 && $gbalance33[$key] < 1)
                        @continue
                    @endif
                    <tr>

                        <td style="width: 15%; padding-left:10px">
                            <strong> {{ $group->name }}</strong>
                        </td>
                        <td style="width:10%;" align="right">
                            {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency($gbalance33[$key], 'Rs.')) }}
                        </td>
                    </tr>
                    <?php $gbalance_total3[$b_total_index++] = $gbalance33[$key]; ?>

                    @foreach ($group->children as $value)
                        @if ($gbalance3[$key][$loop->index] == 0)
                            @continue
                        @endif
                        <tr>
                            <td style="width: 15%; padding-left:10px">
                                {{ $value->name }}
                            </td>
                            <td style="width: 10%;" align="right">
                                {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency($gbalance3[$key][$loop->index], 'Rs.')) }}
                            </td>
                        </tr>
                        <?php $gbalance_total3[$b_total_index++] = $gbalance3[$key][$loop->index]; ?>
                    @endforeach
                @endforeach
                <tr>
                    <td style="width: 15%;">
                        Share Capital - Total
                    </td>
                    <td style="width: 10%; border-top: 1pt solid black; border-bottom: 3pt double black;"
                        align="right">
                        {{-- {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency(array_sum($gbalance1), 'Rs.')) }} --}}
                        {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency(array_sum($gbalance_total3), 'Rs.')) }}
                    </td>
                </tr>

                <?php

                $profit = array_sum($balance_total4) - array_sum($balance_total5);

                $equity = array_sum($gbalance_total2) + array_sum($gbalance_total3) + $profit;
                // dd($equity);
                ?>
                @if ($profit != 0)
                    <tr>
                        <td style="width: 15%;">
                            Accumulated Profit
                        </td>
                        <td style="width: 10%; " align="right">
                            {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency($profit, 'Rs.')) }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td style="width: 15%;">
                        <strong> Equity - Total</strong>
                    </td>
                    <td style="width: 10%; border-top: 1pt solid black; border-bottom: 3pt double black;"
                        align="right">
                        <strong>{{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency($equity, 'Rs.')) }}</strong>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
    <br />
    <script type="text/php">
        if (isset($pdf)) {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            $x = 500;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            $y = 820;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            $font = null;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            $size = 10;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            $word_space = 0.0;  //  default
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            $char_space = 0.0;  //  default
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            $angle = 0.0;   //  default
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            $pdf->page_text($x, $y, $text, $font, $size, $word_space, $char_space, $angle);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        }











                                                    </script>
</body>

</html>
