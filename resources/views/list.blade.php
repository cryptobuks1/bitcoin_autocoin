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


<table class="table">
    <thead>
    <tr>
        <th>Time</th>
        <th>Base</th>
        <th>Prem</th>
        @foreach($currencies as $currency)
            <th>{{ $currency->currency_code }}_Base</th>
            <th>{{ $currency->currency_code }}_Prem</th>
            <th>{{ $currency->currency_code }}_Prem_%</th>
        @endforeach
        <th>USD/KRW</th>
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
                    <td>${{number_format($line->prem_currency_price, 2)}}</td>
                    <td><b>{{$line->prem_rate * 100}}% ♥</b></td>
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

</div>





{{--Pagination--}}



<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script>
    $('')
</script>




</body>
</html>