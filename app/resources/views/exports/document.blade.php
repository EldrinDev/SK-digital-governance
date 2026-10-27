{{--
    An ExportDocument laid out for Dompdf, which understands plain CSS 2 only (no Tailwind,
    no flexbox). Expects $document, $format (value, type) and $footnote.
--}}
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $document->title }}</title>
    <style>
        @page { margin: 26px 30px 40px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 9.5px; color: #0d1f38; }
        .brand { font-size: 8.5px; color: #64748b; border-bottom: 2px solid #f5b82e; padding-bottom: 6px; }
        .brand strong { color: #1a7f4e; }
        h1 { font-size: 18px; margin: 12px 0 3px; }
        .subtitle { color: #475569; margin: 0 0 10px; font-size: 10px; }
        .details { margin: 0 0 6px; border-collapse: collapse; }
        .details td { padding: 1px 14px 1px 0; vertical-align: top; }
        .details td.label { color: #64748b; }
        h2 { font-size: 12.5px; margin: 18px 0 6px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #0d1f38; color: #fff; font-weight: bold; text-align: left; padding: 5px 6px; font-size: 8.5px; }
        table.data td { padding: 4px 6px; border-bottom: 1px solid #d9dee5; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #f5f7fa; }
        table.data .num { text-align: right; white-space: nowrap; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        .empty { color: #64748b; font-style: italic; }
        .footnote { position: fixed; bottom: -24px; left: 0; right: 0; font-size: 7.5px; color: #64748b; }
        .page:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="footnote">{{ $footnote }} <span style="float: right">Page <span class="page"></span></span></div>

    <div class="brand"><strong>SK Digital Governance</strong> &nbsp; Sangguniang Kabataan, Olongapo City</div>
    <h1>{{ $document->title }}</h1>
    @if ($document->subtitle !== '')
        <p class="subtitle">{{ $document->subtitle }}</p>
    @endif

    @if ($document->details !== [])
        <table class="details">
            @foreach ($document->details as $label => $value)
                <tr><td class="label">{{ $label }}</td><td>{{ $value }}</td></tr>
            @endforeach
        </table>
    @endif

    @foreach ($document->sections as $section)
        @php
            $types = array_values($section['columns']);
        @endphp
        <h2>{{ $section['heading'] }}</h2>

        @if ($section['summary'] !== [])
            <table class="details">
                @foreach ($section['summary'] as $label => $value)
                    <tr><td class="label">{{ $label }}</td><td><strong>{{ $value }}</strong></td></tr>
                @endforeach
            </table>
        @endif

        @if ($section['rows'] === [])
            <p class="empty">{{ $section['empty'] }}</p>
        @else
            <table class="data">
                <thead>
                    <tr>
                        @foreach (array_keys($section['columns']) as $index => $heading)
                            <th @class(['num' => in_array($types[$index], ['number', 'money'], true)])>{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($section['rows'] as $values)
                        <tr>
                            @foreach ($values as $index => $value)
                                <td @class(['num' => in_array($types[$index] ?? 'text', ['number', 'money'], true)])>{{ $format($value, $types[$index] ?? 'text') }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
</body>
</html>
