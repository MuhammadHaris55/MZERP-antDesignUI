<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Transaction Detail</title>

    <style type="text/css">
        @page {
            margin: 20px;
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

        tbody td {margin:10px;padding:2px; border: 1px solid;}
        /* td {margin:10px;} */

        tfoot tr td {
            font-weight: bold;
            font-size: medium;
        }

        .information {
            background-color: #fff;
        }

        .information .logo {
            margin: 5px;
        }

        .information table {
            padding: 10px;

        }

        /* tr:nth-child(even) {
            background-color: #f2f2f2;
        } */

    </style>
</head>

<body>
    <?php
        $fmt = new NumberFormatter( 'en_GB', NumberFormatter::CURRENCY );
        $amt = new NumberFormatter( 'en_GB', NumberFormatter::SPELLOUT );
        $fmt->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);
        $fmt->setSymbol(NumberFormatter::CURRENCY_SYMBOL, '');
        $dt = \Carbon\Carbon::now(new DateTimeZone('Asia/Karachi'))->format('M d, Y - h:m a');
    ?>

    <div class="information">
        <table width="100%" style="border-collapse: collapse;">
            <thead>
                <tr>
                    <th align="left" style="width: 50%;">
                        <h3>Transaction Detail</h3>
                    </th>
                    <th colspan='4' align="right" style="width: 50%;">
                        <h5>Generated on: {{ $dt }}</h5>
                         <h5> {{'Form: '.Carbon\Carbon::create($start_date)->format('M d, Y'). ' To: '. Carbon\Carbon::create($date)->format('M d, Y')}}</h5>
                    </th>
                </tr>
                <tr>
                    <th style="width: 10%; border:2pt solid black;">
                        <strong>Date</strong>
                    </th>
                     <th style="width: 35%;border:2pt solid black;">
                        <strong>Particular</strong>
                    </th>
                    <th style="width: 20%;border:2pt solid black;">
                        <strong>Ref #</strong>
                    </th>
                    <th style="width: 15%;border:2pt solid black;" align="centre">
                        <strong>Debit</strong>
                    </th>
                    <th style="width: 15%;border:2pt solid black;" align="centre">
                        <strong>Credit</strong>
                    </th>
                </tr>
            </thead>
            <tbody>
                @php
                    $d_total = 0.00;
                    $c_total = 0.00;
                @endphp
                @forelse ($transactions as $trans)
                    @foreach ($trans->entries as $entry)
                    <tr>
                        <td>
                        </td>
                        <td>
                            @if($entry->credit > 0)
                             &nbsp;&nbsp;  {{ $entry->account->name .' - '.$entry->account->accountGroup->name }}
                            @else
                            {{ $entry->account->name .' - '.$entry->account->accountGroup->name}}
                            @endif
                        </td>
                        <td></td>
                        <td>
                            {{$fmt->formatCurrency($entry->debit,'Rs.')}}
                            @php
                                $d_total += $entry->debit
                            @endphp
                        </td>
                        <td>
                            {{$fmt->formatCurrency($entry->credit,'Rs.')}}
                            @php
                                $c_total += $entry->credit
                            @endphp
                        </td>

                    </tr>
                    @endforeach
                     <tr style="background-color: #f2f2f2;">
                        <td>
                            <strong>
                                {{ Carbon\Carbon::create($trans->date)->format('M j, Y') }}
                            </strong>
                        </td>
                        <td>
                            <strong>
                            {{($trans->description)}}
                            </strong>

                        </td>
                        <td>
                            <strong>
                            {{$trans->ref}}</td>
                            </strong>
                        <td></td>
                        <td> </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px"></td>
                        <td style="padding: 8px"></td>
                        <td style="padding: 8px"></td>
                        <td style="padding: 8px"></td>
                        <td style="padding: 8px"></td>
                    </tr>
                @empty
                @endforelse

                   <tr align="center" style="background-color: #f2f2f2; border-bottom:2px double;">
                        <td style="border-top:2px solid; border-left:2px solid; border-right:2px solid;"></td>
                        <td style="border-top:2px solid; border-left:2px solid; border-right:2px solid;"><strong>Total</strong></td>
                        <td style="border-top:2px solid; border-left:2px solid; border-right:2px solid;"></td>
                        <td style="border-top:2px solid; border-left:2px solid; border-right:2px solid;">{{ $fmt->formatCurrency($d_total,'Rs.') }}</td>
                        <td style="border-top:2px solid; border-left:2px solid; border-right:2px solid;">{{ $fmt->formatCurrency($c_total,'Rs.') }}</td>
                    </tr>
            </tbody>
        </table>
    </div>
    <br />
    <script type="text/php">if (isset($pdf)) {
                    $x = 500;
                    $y = 820;
                    $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
                    $font = null;
                    $size = 10;
                    $word_space = 0.0;  //  default
                    $char_space = 0.0;  //  default
                    $angle = 0.0;   //  default
                    $pdf->page_text($x, $y, $text, $font, $size, $word_space, $char_space, $angle);
                }</script>
</body>

</html>
