<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Profit or Loss Account</title>

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
    
    $year = \App\Models\Year::find(session('year_id'));
    $id4 = \App\Models\AccountType::where('name', 'Revenue')->first()->id;
    $grps4 = \App\Models\AccountGroup::where('company_id', session('company_id'))->where('type_id', $id4)->tree()->get()->ToTree();
    $gbalance4 = [];
    $gi = 0;
    
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
                ->select('entries.debit', 'entries.credit', 'entries.document_id')
                ->get();
            if ($year->closed == 1) {
                $doc = App\Models\Document::where('company_id', session('company_id'))->where('year_id', session('year_id'))->orderBy('id', 'desc')->first();
                foreach ($entries as $entry) {
                    if ($entry->document_id == $doc->id) {
                        continue;
                    } else {
                        $balance = $lastbalance + floatval($entry->credit) - floatval($entry->debit);
                        $lastbalance = $balance;
                    }
                }
            } else {
                foreach ($entries as $entry) {
                    $balance = $lastbalance + floatval($entry->credit) - floatval($entry->debit);
                    $lastbalance = $balance;
                }
            }
        }
        $gbalance44[$gi] = $balance;
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
                    ->select('entries.debit', 'entries.credit', 'entries.document_id')
                    ->get();
    
                if ($year->closed == 1) {
                    $doc = App\Models\Document::where('company_id', session('company_id'))->where('year_id', session('year_id'))->orderBy('id', 'desc')->first();
                    foreach ($entries as $entry) {
                        if ($entry->document_id == $doc->id) {
                            continue;
                        } else {
                            $balance = $lastbalance + floatval($entry->credit) - floatval($entry->debit);
                            $lastbalance = $balance;
                        }
                    }
                } else {
                    foreach ($entries as $entry) {
                        $balance = $lastbalance + floatval($entry->credit) - floatval($entry->debit);
                        $lastbalance = $balance;
                    }
                }
            }
            $gbalance4[$gi][$gite4] = $balance;
            if (count($group->children) > 0) {
                $gbalance4[$gi][$gite4++] = recurse($date, $group, $year, $balance, $lastbalance, 1);
            } else {
                $gite4++;
            }
        }
        $gi++;
    }
    
    $id5 = \App\Models\AccountType::where('name', 'Expenses')->first()->id;
    $grps5 = \App\Models\AccountGroup::where('company_id', session('company_id'))->where('type_id', $id5)->tree()->get()->ToTree();
    $gbalance5 = [];
    $gi = 0;
    foreach ($grps5 as $gr) {
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
                ->select('entries.debit', 'entries.credit', 'entries.document_id')
                ->get();
            if ($year->closed == 1) {
                $doc = App\Models\Document::where('company_id', session('company_id'))->where('year_id', session('year_id'))->orderBy('id', 'desc')->first();
                foreach ($entries as $entry) {
                    if ($entry->document_id == $doc->id) {
                        continue;
                    } else {
                        $balance = $lastbalance + floatval($entry->debit) - floatval($entry->credit);
                        $lastbalance = $balance;
                    }
                }
            } else {
                foreach ($entries as $entry) {
                    $balance = $lastbalance + floatval($entry->debit) - floatval($entry->credit);
                    $lastbalance = $balance;
                }
            }
        }
        $gbalance55[$gi] = $balance;
    
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
                    ->select('entries.debit', 'entries.credit', 'entries.document_id')
                    ->get();
                if ($year->closed == 1) {
                    $doc = App\Models\Document::where('company_id', session('company_id'))->where('year_id', session('year_id'))->orderBy('id', 'desc')->first();
                    foreach ($entries as $entry) {
                        if ($entry->document_id == $doc->id) {
                            continue;
                        } else {
                            $balance = $lastbalance + floatval($entry->debit) - floatval($entry->credit);
                            $lastbalance = $balance;
                        }
                    }
                } else {
                    foreach ($entries as $entry) {
                        $balance = $lastbalance + floatval($entry->debit) - floatval($entry->credit);
                        $lastbalance = $balance;
                    }
                }
            }
            $gbalance5[$gi][$gite1] = $balance;
            if (count($group->children) > 0) {
                $gbalance5[$gi][$gite1++] = recurse($date, $group, $year, $balance, $lastbalance, 1);
            } else {
                $gite1++;
            }
        }
        $gi++;
    }
    // =================== Recursive function ============================
    function recurse($date, $gr, $year, $balance, $lastbalance, $for_total)
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
                recurse($start_date, $date, $group, $year, $balance, $lastbalance);
            }
        }
        return $balance;
    }
    
    ?>

    <div class="information">
        <table width="100%" style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th align="left" style="width: 50%;">
                        <h3>Profit or Loss Account</h3>
                    </th>
                    <th colspan='2' align="right" style="width: 30%;">
                        <h5>Generated on: {{ $dt }}</h5>
                        <h5> {{ 'Form: ' . Carbon\Carbon::create($start_date)->format('M d, Y') . ' To: ' . Carbon\Carbon::create($date)->format('M d, Y') }}
                        </h5>

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
                    <td><strong>REVENUE</strong></td>
                    <td></td>
                </tr>
                <?php
                $b_total_index = 0;
                $gbalance_total = [];
                ?>
                @foreach ($grps4 as $key => $group)
                    <tr>
                        <td style="width: 15%; padding-left:10px">
                            <strong> {{ $group->name }}</strong>
                        </td>
                        <td style="width:10%;" align="right">
                            {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency($gbalance44[$key], 'Rs.')) }}
                        </td>
                    </tr>
                    <?php $gbalance_total[$b_total_index++] = $gbalance44[$key]; ?>

                    @foreach ($group->children as $value)
                        @if ($gbalance4[$key][$loop->index] == 0)
                            @continue
                        @endif
                        <tr>
                            <td style="width: 15%; padding-left:10px">
                                {{ $value->name }}
                            </td>
                            <td style="width: 10%;" align="right">
                                {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency($gbalance4[$key][$loop->index], 'Rs.')) }}
                            </td>
                        </tr>
                        <?php $gbalance_total[$b_total_index++] = $gbalance4[$key][$loop->index]; ?>
                    @endforeach
                @endforeach
                <tr>
                    <td style="width: 15%;">
                        Revenue - Total
                    </td>
                    <td style="width: 10%; border-top: 1pt solid black; border-bottom: 3pt double black;"
                        align="right">
                        {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency(array_sum($gbalance_total), 'Rs.')) }}
                    </td>
                </tr>

                <tr>
                    <td><strong>Expenses</strong></td>
                    <td></td>
                </tr>
                <?php
                $b_total_index = 0;
                $gbalance_total5 = [];
                ?>
                @foreach ($grps5 as $key => $group)
                    <tr>
                        <td style="width: 15%; padding-left:10px">
                            <strong> {{ $group->name }}</strong>
                        </td>

                        <td style="width:10%;" align="right">
                            {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency($gbalance55[$key], 'Rs.')) }}
                        </td>
                    </tr>
                    <?php $gbalance_total5[$b_total_index++] = $gbalance55[$key]; ?>

                    @foreach ($group->children as $value)
                        @if ($gbalance5[$key][$loop->index] == 0)
                            @continue
                        @endif
                        <tr>
                            <td style="width: 15%; padding-left:10px">
                                {{ $value->name }}
                            </td>
                            <td style="width: 10%;" align="right">
                                {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency($gbalance5[$key][$loop->index], 'Rs.')) }}
                            </td>
                        </tr>
                        <?php $gbalance_total5[$b_total_index++] = $gbalance5[$key][$loop->index]; ?>
                    @endforeach
                @endforeach
                <tr>
                    <td style="width: 15%;">
                        Expenses - Total
                    </td>
                    <td style="width: 10%; border-top: 1pt solid black; border-bottom: 1pt solid black;" align="right">
                        {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency(array_sum($gbalance_total5), 'Rs.')) }}
                    </td>
                </tr>
                {{-- @dd($gbalance_total,$gbalance_total5) --}}
                <?php $profit = array_sum($gbalance_total) - array_sum($gbalance_total5); ?>
                {{-- @dd($profit); --}}
                <tr>
                    {{-- @dd($profit) --}}
                    <td style="width: 15%;">
                        Net Profit
                    </td>
                    <td style="width: 10%; border-top: 1pt solid black; border-bottom: 3pt double black;"
                        align="right">
                        {{ str_replace(['Rs.', '.00'], '', $fmt->formatCurrency($profit, 'Rs.')) }}
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
