@props(['date'])

@if ($date)
    @php
        $localDate = $date->copy()->setTimezone(config('app.timezone'));
        $months = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
        ];
    @endphp
    <time datetime="{{ $localDate->toIso8601String() }}">{{ $localDate->day }} {{ $months[$localDate->month] }} {{ $localDate->year + 543 }}</time>
@endif
