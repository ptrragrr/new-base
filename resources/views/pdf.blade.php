<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi Penjualan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1, h2 { text-align: center; margin-bottom: 0; }
        .info { text-align: center; margin-top: 5px; margin-bottom: 20px; font-size: 11px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Laporan Transaksi Penjualan</h1>
    <div class="info">
        Dicetak: {{ \Carbon\Carbon::now()->format('d M Y, H:i') }}
    </div>

    <!-- Di atas tabel -->
    @if (!request()->is('transaksi/download-pdf'))
    <div class="card-header align-items-center">
    <a
      href="/transaksi/download-pdf"
      target="_blank"
      class="flex items-center gap-2 bg-blue-600 text-lg font-bold px-4 py-2 rounded hover:bg-blue-700"
    >
      🗎 CETAK LAPORAN
    </a>
  </div>
  @endif
    <table border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th>No</th>
            <th>Kode Transaksi</th>
            <th>Nama Kasir</th>
            <th>Nama Barang</th>
            <th>Tanggal</th>
            <th>Metode Pembayaran</th>
            <th>Total Transaksi</th>
        </tr>
    </thead>
    <tbody>
        @php $total = 0; @endphp
        @foreach($data as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->kode_transaksi }}</td>
                <td>{{ $item->nama_kasir }}</td>
                <td>
                    @if($item->details)
                        <ul style="margin:0; padding-left:15px;">
                            @foreach($item->details as $d)
                                <li>{{ $d->barang->nama_barang ?? '-' }}</li>
                            @endforeach
                        </ul>
                    @else
                        -
                    @endif
                </td>
                <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $item->metode_pembayaran }}</td>
                <td>Rp {{ number_format($item->total_transaksi, 0, ',', '.') }}</td>
            </tr>
            @php $total += $item->total_transaksi; @endphp
        @endforeach
        <tr>
            <td colspan="6" align="right"><strong>Total Keseluruhan</strong></td>
            <td><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></td>
        </tr>
    </tbody>
</table>

    <!-- <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Transaksi</th>
                <th>Nama Kasir</th>
                <th>Nama Barang</th>
                <th>Tanggal</th>
                <th>Metode Pembayaran</th>
                <th>Total Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->kode_transaksi }}</td>
                    <td>{{ $item->nama_kasir }}</td>
                    <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ ucfirst($item->metode_pembayaran) }}</td>
                    <td>Rp {{ number_format($item->total_transaksi, 0, ',', '.') }}</td>
                </tr>
                @php $grandTotal += $item->total_transaksi; @endphp
            @endforeach
            <tr>
                <td colspan="5" class="total">Total Keseluruhan</td>
                <td class="total">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table> -->

</body>
</html>
