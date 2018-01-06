<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Document</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

</head>
<body>


<div class="page-header">
    <div class="page-footer">
        <div class="btn-group btn-group-sm">
            <a href="{{ url('/') }}" class="btn btn-primary active">Detailed</a>
            <a href="{{ url('/?premiumOnly=true') }}" class="btn btn-primary">Condensed</a>
        </div>
        <div class="btn-group btn-group-sm">
            <a href="{{ url('/?activeOnly=true') }}" class="btn btn-primary">Active Only</a>
        </div>
    </div>

</div>


<table class="table table-bordered">
    <thead>
    <tr>
        <th rowspan="3" style="vertical-align: top;">Time</th>
        <th rowspan="3" style="vertical-align: top;">Base&nbsp;Ex.</th>
        <th rowspan="3" style="vertical-align: top;">Prem&nbsp;Ex.</th>
        @foreach($currencies as $currency)
        <th colspan="21" style="vertical-align: top;">{{ $currency->currency_name }}&nbsp;({{$currency->currency_code}})</th>
        @endforeach
        <th rowspan="3" style="vertical-align: top;">USD/KRW</th>
    </tr>
    <tr>
        @foreach($currencies as $currency)
        <th rowspan="2" style="vertical-align: top;">Base&nbsp;Price</th>
        <th colspan="6" style="vertical-align: top;">Base&nbsp;Price&nbsp;Standard&nbsp;Deviation&nbsp;%</th>
        <th rowspan="2" style="vertical-align: top;">Premium&nbsp;Price</th>
        <th colspan="6" style="vertical-align: top;">Premium&nbsp;Price&nbsp;Standard&nbsp;Deviation&nbsp;%</th>
        <th rowspan="2" style="vertical-align: top;">Premium&nbsp;Rate</th>
        <th colspan="6" style="vertical-align: top;">Premium&nbsp;Rate&nbsp;Standard&nbsp;Deviation&nbsp;%</th>
        @endforeach
    </tr>
    <tr>
        @foreach($currencies as $currency)
            <th>5&nbsp;min</th>
            <th>10&nbsp;min</th>
            <th>30&nbsp;min</th>
            <th>60&nbsp;min</th>
            <th>120&nbsp;min</th>
            <th>240&nbsp;min</th>
            <th>5&nbsp;min</th>
            <th>10&nbsp;min</th>
            <th>30&nbsp;min</th>
            <th>60&nbsp;min</th>
            <th>120&nbsp;min</th>
            <th>240&nbsp;min</th>
            <th>5&nbsp;min</th>
            <th>10&nbsp;min</th>
            <th>30&nbsp;min</th>
            <th>60&nbsp;min</th>
            <th>120&nbsp;min</th>
            <th>240&nbsp;min</th>
        @endforeach
    </tr>
    </thead>

    <tbody>
    @foreach($records as $record)
        <tr>
            <td>{{ $record->recorded_at->format('Y/n/j_g:i:sA') }}</td>
            <td>{{ $record->baseExchange->exchange_name }}</td>
            <td>{{ $record->premExchange->exchange_name }}</td>
            @foreach($currencies as $currency)
                @php
                    $line = isset($record->lines[$currency->currency_code])?
                        $record->lines[$currency->currency_code]:false
                @endphp
                @if($line)
                    <td>${{number_format($line->base_currency_price, 2)}}</td>

                    <td>{{ $line->sd_bp_5? $line->sd_bp_5 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_bp_10? $line->sd_bp_10 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_bp_30? $line->sd_bp_30 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_bp_60? $line->sd_bp_60 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_bp_120? $line->sd_bp_120 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_bp_240? $line->sd_bp_240 * 100 : '---' }}%</td>

                    <td>${{number_format($line->prem_currency_price, 2)}}</td>

                    <td>{{ $line->sd_pp_5? $line->sd_pp_5 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_pp_10? $line->sd_pp_10 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_pp_30? $line->sd_pp_30 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_pp_60? $line->sd_pp_60 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_pp_120? $line->sd_pp_120 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_pp_240? $line->sd_pp_240 * 100 : '---' }}%</td>

                    <td><b>{{$line->prem_rate * 100}}% ♥</b></td>

                    <td>{{ $line->sd_pr_5? $line->sd_pr_5 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_pr_10? $line->sd_pr_10 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_pr_30? $line->sd_pr_30 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_pr_60? $line->sd_pr_60 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_pr_120? $line->sd_pr_120 * 100 : '---' }}%</td>
                    <td>{{ $line->sd_pr_240? $line->sd_pr_240 * 100 : '---' }}%</td>

                @else
                    <td>---</td>
                    <td>---</td>
                    <td>---</td>
                @endif
            @endforeach
            <td>{{ $record->exchange_rate }}</td>
        </tr>
    @endforeach
    </tbody>

</table>




<hr>

<div class="page-footer">
    <div class="btn-group btn-group-sm">
        <a href="{{ url('/') }}" class="btn btn-primary active">Detailed</a>
        <a href="{{ url('/?premiumOnly=true') }}" class="btn btn-primary">Condensed</a>
    </div>
    <div class="btn-group btn-group-sm">
        <a href="{{ url('/?activeOnly=true') }}" class="btn btn-primary">Active Only</a>
    </div>

</div>





{{--Pagination--}}



<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>




</body>
</html>