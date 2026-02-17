<table>
    <thead>
        <tr>
            @if(count($data) > 0)
                @foreach((array) $data[0] as $column => $value)
                    <th style="background-color: #b2ebf2; font-weight: bold; border: 1px solid #000;">{{ $column }}</th>
                @endforeach
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($data as $item)
            <tr>
                @foreach((array) $item as $value)
                    <td style="border: 1px solid #000;">{{ $value }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>