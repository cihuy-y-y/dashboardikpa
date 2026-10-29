<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan {{ $jenis }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h2 {
            margin: 10px 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 13px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        th {
            background-color: #2c3e50;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #333;
        }
        
        td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
        }
        
        tr:nth-child(even) {
            background-color: #f5f5f5;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        
        @@media print {
            body {
                padding: 0;
                margin: 0;
            }
            .footer {
                page-break-before: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ config('ikpa.name') }}</h2>
        @if ($jenis === 'ba')
            <h2 style="font-size: 14px;">REKAP NILAI IKPA BERDASARKAN BA</h2>
        @else
            <h2 style="font-size: 14px;">REKAP NILAI IKPA SELURUH SATKER</h2>
        @endif
        <p>Periode: <strong>{{ $periodeLabel }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                @if ($jenis === 'ba')
                    <th>Kode BA</th>
                    <th>Nama BA</th>
                    <th class="text-center">Jumlah Satker</th>
                    <th class="text-right">Nilai Tertinggi</th>
                    <th class="text-right">Nilai Terendah</th>
                    <th class="text-right">Rata-rata Nilai</th>
                @else
                    <th class="text-center" style="width: 30px;">No</th>
                    <th>Kode Satker</th>
                    <th>Nama Satker</th>
                    <th class="text-right">Revisi DIPA</th>
                    <th class="text-right">Deviasi Hal. III</th>
                    <th class="text-right">Penyerapan</th>
                    <th class="text-right">Belanja Kontraktual</th>
                    <th class="text-right">Penyelesaian Tagihan</th>
                    <th class="text-right">UP/TUP</th>
                    <th class="text-right">Capaian Output</th>
                    <th class="text-right">Nilai Total</th>
                    <th class="text-right">Nilai Akhir</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if (empty($rows))
                <tr>
                    <td colspan="{{ $jenis === 'ba' ? '6' : '12' }}" class="text-center">Tidak ada data</td>
                </tr>
            @else
                @foreach ($rows as $i => $row)
                    <tr>
                        @if ($jenis === 'ba')
                            <td>{{ $row['kode_ba'] }}</td>
                            <td>{{ $row['nama_ba'] ?? '-' }}</td>
                            <td class="text-center">{{ $row['jumlah_satker'] }}</td>
                            <td class="text-right">{{ formatAngka($row['nilai_tertinggi']) }}</td>
                            <td class="text-right">{{ formatAngka($row['nilai_terendah']) }}</td>
                            <td class="text-right"><strong>{{ formatAngka($row['rata_rata']) }}</strong></td>
                        @else
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $row['kode_satker'] }}</td>
                            <td>{{ $row['nama_satker'] }}</td>
                            <td class="text-right">{{ formatAngka($row['revisi_dipa']) }}</td>
                            <td class="text-right">{{ formatAngka($row['deviasi_dipa']) }}</td>
                            <td class="text-right">{{ formatAngka($row['penyerapan']) }}</td>
                            <td class="text-right">{{ formatAngka($row['belanja_kontraktual']) }}</td>
                            <td class="text-right">{{ formatAngka($row['penyelesaian_tagihan']) }}</td>
                            <td class="text-right">{{ formatAngka($row['up_tup']) }}</td>
                            <td class="text-right">{{ formatAngka($row['capaian_output']) }}</td>
                            <td class="text-right">{{ formatAngka($row['nilai_total']) }}</td>
                            <td class="text-right"><strong>{{ formatAngka($row['nilai_akhir']) }}</strong></td>
                        @endif
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Total Baris: {{ count($rows) }}</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
