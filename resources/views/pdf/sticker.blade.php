<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $asset->asset_tag }}</title>
    <style>
        @page {
            size: 50mm 16mm;
            margin: 0px; 
        }
        
        body {
            margin: 3px;
            padding: 3px;
            font-family: sans-serif;
        }

        table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: middle;
        }

        .col-qr {
            width: 35%;
            text-align: center;
            padding-right: 5px;
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
        }

        .sn {
            font-size: 6px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td class="col-qr">
                <img src="data:image/png;base64, {{ base64_encode(file_get_contents('https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data=' . $asset->asset_tag)) }}" 
                     class="qr-img">
            </td>

            <td class="col-info">
                <div class="company">PT. PERUSAHAAN ANDA</div>
                <div class="tag">{{ $asset->asset_tag }}</div>  
                <div class="name">
                    {{ Str::limit($asset->name, 25) }}
                </div>
                <div class="sn">SN: {{ $asset->serial_number }}</div>
            </td>
        </tr>
    </table>
</body>
</html>