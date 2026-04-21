<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;

class InvoiceService
{
    public static function generate($transaction)
    {
        $dompdf = new Dompdf();

        $html = '
<html>
<head>
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        color: #333;
        position: relative;
    }

    .container {
        padding: 30px;
    }

    .header {
        text-align: left;
        margin-bottom: 20px;
    }

    .title {
        font-size: 22px;
        font-weight: bold;
        color: #2563eb;
    }

    .invoice-info {
        margin-top: 10px;
    }

    .section {
        margin-top: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    table th {
        background: #f3f4f6;
        padding: 10px;
        text-align: left;
        border: 1px solid #ddd;
    }

    table td {
        padding: 10px;
        border: 1px solid #ddd;
    }

    .total {
        text-align: right;
        margin-top: 20px;
        font-size: 16px;
        font-weight: bold;
        color: #2563eb;
    }

    .footer {
        margin-top: 40px;
        font-size: 11px;
        text-align: center;
        color: #777;
    }

    /* 🔥 WATERMARK */
    .watermark {
        position: fixed;
        top: 40%;
        left: 20%;
        font-size: 100px;
        color: rgba(0, 128, 0, 0.08);
        transform: rotate(-30deg);
        z-index: -1;
    }
</style>
</head>

<body>

<div class="watermark">PAID</div>

<div class="container">

    <div class="header">
        <div class="title">Ravatra Academy</div>
        <div>Training & Certification</div>
    </div>

    <hr>

    <div class="section">
        <strong>Invoice ID:</strong> #' . $transaction['id'] . '<br>
        <strong>Date:</strong> ' . date('d M Y') . '
    </div>

    <div class="section">
        <strong>Billed To:</strong><br>
        ' . $transaction['firstname'] . ' ' . $transaction['lastname'] . '<br>
        ' . $transaction['email'] . '
    </div>

    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>' . $transaction['product_name'] . '</td>
                    <td>' . $transaction['product_type'] . '</td>
                    <td>Rp ' . number_format($transaction['product_price']) . '</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="total">
        Total: Rp ' . number_format($transaction['total_amount']) . '
    </div>

    <div class="footer">
        Terima kasih telah melakukan pembayaran.<br>
        Invoice ini adalah bukti pembayaran yang sah.
    </div>

</div>

</body>
</html>
';

        $dompdf->loadHtml($html);
        $dompdf->render();

        $output = $dompdf->output();

        $filePath = __DIR__ . "/../storage/invoices/invoice_{$transaction['id']}.pdf";

        file_put_contents($filePath, $output);

        return $filePath;
    }
}