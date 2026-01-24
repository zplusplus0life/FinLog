<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .page-break {
            page-break-after: always;
        }
        .container {
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
            font-weight: normal;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 40px;
            margin-bottom: 15px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .currency {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .pl-1 { padding-left: 1em; }
        .pl-2 { padding-left: 2em; }
        .italic { font-style: italic; }
        .footnote {
            font-size: 10px;
            color: #777;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Keuangan</h1>
        <h2>{{ $company_name ?? 'FinLog' }}</h2>
        <p>Untuk Periode yang Berakhir pada {{ $period }}</p>
    </div>

    <div class="container">
        <!-- Ringkasan Eksekutif -->
        <div class="report-title">Ringkasan Eksekutif</div>
        <table>
            <tbody>
                <tr class="total-row">
                    <td>Pendapatan Total</td>
                    <td class="currency">{{ number_format($data['summary']['total_revenue'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Beban</td>
                    <td class="currency">({{ number_format($data['summary']['total_expenses'], 0, ',', '.') }})</td>
                </tr>
                <tr class="total-row">
                    <td>Laba Bersih</td>
                    <td class="currency">{{ number_format($data['summary']['net_profit'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Margin Laba</td>
                    <td class="currency">{{ number_format($data['summary']['profit_margin'], 2, ',', '.') }} %</td>
                </tr>
            </tbody>
        </table>

        <!-- Laporan Laba Rugi -->
        <div class="report-title">Laporan Laba Rugi</div>
        <table>
            <thead>
                <tr>
                    <th>Keterangan</th>
                    <th class="currency">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="2"><strong>Pendapatan</strong></td></tr>
                @forelse($data['income_statement']['revenues'] as $item)
                    <tr>
                        <td class="pl-1">{{ $item['keterangan'] }}</td>
                        <td class="currency">{{ number_format($item['jumlah'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="italic">Tidak ada pendapatan.</td></tr>
                @endforelse
                <tr class="total-row">
                    <td>Total Pendapatan</td>
                    <td class="currency">{{ number_format($data['income_statement']['total_revenue'], 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2"><strong>Harga Pokok Penjualan</strong></td></tr>
                @forelse($data['income_statement']['cogs'] as $item)
                    <tr>
                        <td class="pl-1">{{ $item['keterangan'] }}</td>
                        <td class="currency">({{ number_format($item['jumlah'], 0, ',', '.') }})</td>
                    </tr>
                @empty
                     <tr><td colspan="2" class="italic">Tidak ada HPP.</td></tr>
                @endforelse
                <tr class="total-row">
                    <td>Total HPP</td>
                    <td class="currency">({{ number_format($data['income_statement']['total_cogs'], 0, ',', '.') }})</td>
                </tr>
                <tr class="total-row">
                    <td>Laba Kotor</td>
                    <td class="currency">{{ number_format($data['income_statement']['gross_profit'], 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2"><strong>Beban Operasional</strong></td></tr>
                @if(count($data['income_statement']['sales_marketing_expenses']) > 0)
                    <tr><td colspan="2" class="pl-1 italic">Beban Penjualan & Pemasaran</td></tr>
                    @foreach($data['income_statement']['sales_marketing_expenses'] as $item)
                        <tr>
                            <td class="pl-2">{{ $item['keterangan'] }}</td>
                            <td class="currency">({{ number_format($item['jumlah'], 0, ',', '.') }})</td>
                        </tr>
                    @endforeach
                @endif
                 @if(count($data['income_statement']['general_admin_expenses']) > 0)
                    <tr><td colspan="2" class="pl-1 italic">Beban Umum & Administrasi</td></tr>
                    @foreach($data['income_statement']['general_admin_expenses'] as $item)
                        <tr>
                            <td class="pl-2">{{ $item['keterangan'] }}</td>
                            <td class="currency">({{ number_format($item['jumlah'], 0, ',', '.') }})</td>
                        </tr>
                    @endforeach
                @endif
                <tr class="total-row">
                    <td>Total Beban Operasional</td>
                    <td class="currency">({{ number_format($data['income_statement']['total_operating_expenses'], 0, ',', '.') }})</td>
                </tr>
                <tr class="total-row">
                    <td>Laba Bersih (sebelum pajak)</td>
                    <td class="currency">{{ number_format($data['income_statement']['net_profit'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="page-break"></div>

        <!-- Laporan Neraca -->
        <div class="report-title">Neraca (Sederhana)</div>
        <table>
            <thead>
                <tr>
                    <th>Aktiva</th>
                    <th class="currency">Jumlah (Rp)</th>
                    <th>Passiva</th>
                    <th class="currency">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Aset Lancar</strong></td>
                    <td></td>
                    <td><strong>Liabilitas</strong></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="pl-1">{{ $data['balance_sheet']['assets']['keterangan'] }}</td>
                    <td class="currency">{{ number_format($data['balance_sheet']['assets']['jumlah'], 0, ',', '.') }}</td>
                    <td class="pl-1 italic">Tidak ada liabilitas</td>
                    <td class="currency">(0)</td>
                </tr>
                <tr class="total-row">
                    <td>Total Aset</td>
                    <td class="currency">{{ number_format($data['balance_sheet']['total_assets'], 0, ',', '.') }}</td>
                    <td>Total Liabilitas</td>
                    <td class="currency">(0)</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td><strong>Ekuitas</strong></td>
                    <td></td>
                </tr>
                 <tr>
                    <td></td>
                    <td></td>
                    <td class="pl-1">{{ $data['balance_sheet']['equity']['keterangan'] }}</td>
                    <td class="currency">{{ number_format($data['balance_sheet']['total_equity'], 0, ',', '.') }}</td>
                </tr>
                 <tr class="total-row">
                    <td></td>
                    <td></td>
                    <td>Total Ekuitas</td>
                    <td class="currency">{{ number_format($data['balance_sheet']['total_equity'], 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total Aset</td>
                    <td class="currency">{{ number_format($data['balance_sheet']['total_assets'], 0, ',', '.') }}</td>
                    <td>Total Liabilitas & Ekuitas</td>
                    <td class="currency">{{ number_format($data['balance_sheet']['total_liabilities'] + $data['balance_sheet']['total_equity'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        <p class="footnote">*Neraca disederhanakan, mengasumsikan semua aset adalah kas dan tidak ada utang.</p>

        <div class="page-break"></div>

        <!-- Laporan Arus Kas -->
        <div class="report-title">Laporan Arus Kas</div>
        <table>
            <thead>
                <tr>
                    <th>Keterangan</th>
                    <th class="currency">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="2"><strong>Arus Kas dari Aktivitas Operasi</strong></td></tr>
                <tr>
                    <td class="pl-1">Arus Kas Masuk dari Pelanggan</td>
                    <td class="currency">{{ number_format($data['cash_flow_statement']['operating']['inflows'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="pl-1">Pembayaran kepada Pemasok dan Karyawan</td>
                    <td class="currency">({{ number_format($data['cash_flow_statement']['operating']['outflows'], 0, ',', '.') }})</td>
                </tr>
                <tr class="total-row">
                    <td>Arus Kas Bersih dari Aktivitas Operasi</td>
                    <td class="currency">{{ number_format($data['cash_flow_statement']['operating']['net'], 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2"><strong>Arus Kas dari Aktivitas Investasi</strong></td></tr>
                <tr>
                    <td class="pl-1">Pembelian Aset Tetap</td>
                    <td class="currency">({{ number_format($data['cash_flow_statement']['investing']['outflows'], 0, ',', '.') }})</td>
                </tr>
                <tr class="total-row">
                    <td>Arus Kas Bersih dari Aktivitas Investasi</td>
                    <td class="currency">{{ number_format($data['cash_flow_statement']['investing']['net'], 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2"><strong>Arus Kas dari Aktivitas Pendanaan</strong></td></tr>
                <tr>
                    <td class="pl-1">Penerimaan dari Pinjaman/Modal</td>
                    <td class="currency">{{ number_format($data['cash_flow_statement']['financing']['inflows'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="pl-1">Pembayaran Pinjaman/Dividen</td>
                    <td class="currency">({{ number_format($data['cash_flow_statement']['financing']['outflows'], 0, ',', '.') }})</td>
                </tr>
                <tr class="total-row">
                    <td>Arus Kas Bersih dari Aktivitas Pendanaan</td>
                    <td class="currency">{{ number_format($data['cash_flow_statement']['financing']['net'], 0, ',', '.') }}</td>
                </tr>

                <tr class="total-row">
                    <td>Kenaikan (Penurunan) Bersih Kas</td>
                    <td class="currency">{{ number_format($data['cash_flow_statement']['net_cash_flow'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        <p class="footnote">*Arus kas dihitung berdasarkan asumsi dari nama kategori transaksi.</p>

        <div class="page-break"></div>

        <!-- Analisis Rasio Keuangan -->
        <div class="report-title">Analisis Rasio Keuangan</div>
        <table>
            <thead>
                <tr>
                    <th>Rasio Keuangan</th>
                    <th>Nilai</th>
                    <th>Interpretasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['ratios'] as $ratio)
                    <tr>
                        <td>{{ $ratio['rasio'] }}</td>
                        <td>{{ number_format($ratio['nilai'], 2, ',', '.') }} %</td>
                        <td class="italic">{{ $ratio['interpretasi'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="italic">Data rasio tidak tersedia.</td></tr>
                @endforelse
            </tbody>
        </table>

    </div>
</body>
</html>
