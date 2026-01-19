<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>A4 Batch Assets</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }

        .layout-table {
            width: 100%;
            border-collapse: collapse;
        }

        .layout-table td {
            width: 33.33%;
            vertical-align: top;
            padding-bottom: 5mm;
            padding-right: 2mm;
        }

        .sticker-box {
            width: 50mm;
            height: 16mm;
            
            border: 0.1pt dashed #ccc; 

            padding: 2px; 
            box-sizing: border-box;
            overflow: hidden;
        }

        .inner-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }

        .inner-table td {
            vertical-align: middle;
        }

        .col-qr {
            width: 35%;
            text-align: center;
            padding-right: 2px;
        }

        .qr-img {
            width: 100%; 
            max-width: 45px;
            height: auto;
        }

        .col-info {
            width: 65%;
            text-align: left;
            line-height: 1.1;
        }

        .company {
            font-size: 6px;
            font-weight: bold;
            text-transform: uppercase;
            color: #555;
            margin-bottom: 2px;
        }

        .tag {
            font-size: 10px;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }

        .name {
            font-size: 7px;
            margin-bottom: 2px;
            word-wrap: break-word;
            white-space: nowrap; 
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 30mm;
        }

        .sn {
            font-size: 6px;
            font-family: monospace;
        }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>

    <table class="layout-table">
        @foreach($assets->chunk(3) as $row)
            <tr>
                @foreach($row as $asset)
                    <td>
                        <div class="sticker-box">
                            <table class="inner-table">
                                <tr>
                                    <td class="col-qr">
                                        <img src="data:image/png;base64, {{ base64_encode(file_get_contents('https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data=' . $asset->asset_tag)) }}" 
                                             class="qr-img">
                                    </td>

                                    <td class="col-info">
                                        <div class="company">PT. PERUSAHAAN ANDA</div>
                                        <div class="tag">{{ $asset->asset_tag }}</div>
                                        <div class="name">
                                            {{ Str::limit($asset->name, 22) }}
                                        </div>
                                        <div class="sn">SN: {{ $asset->serial_number }}</div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                @endforeach
                @for($i = $row->count(); $i < 3; $i++)
                    <td></td>
                @endfor
            </tr>
        @endforeach
    </table>

</body>
</html>