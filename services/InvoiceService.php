<?php

declare(strict_types=1);

use Dompdf\Dompdf;
use Dompdf\Options;

final class InvoiceService
{
    private string $invoiceDirectory;

    public function __construct()
    {
        $this->invoiceDirectory = BASE_PATH . '/storage/invoice';

        if (!is_dir($this->invoiceDirectory)) {
            mkdir(
                $this->invoiceDirectory,
                0755,
                true
            );
        }
    }

    public function generate(array $transaction): array
    {
        if (($transaction['status'] ?? null) !== 'PAID') {
            throw new RuntimeException(
                'Invoice can only be generated for paid transactions.'
            );
        }

        $invoiceNumber = $this->generateInvoiceNumber(
            $transaction['transaction_code']
        );

        $filename = $this->generateFilename(
            $invoiceNumber
        );

        $pdfContent = $this->generatePdf(
            $transaction,
            $invoiceNumber
        );

        $filePath = $this->invoiceDirectory . '/' . $filename;

        $saved = file_put_contents(
            $filePath,
            $pdfContent
        );

        if ($saved === false) {
            throw new RuntimeException(
                'Failed to save invoice file.'
            );
        }

        $relativePath = 'invoice/' . $filename;

        return [
            'invoice_number' => $invoiceNumber,
            'invoice_path' => $relativePath,
            'invoice_generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function generateInvoiceNumber(
        string $transactionCode
    ): string {
        return 'INV/RA/' .
            date('Y/m') .
            '/' .
            $transactionCode;
    }

    private function generateFilename(
        string $invoiceNumber
    ): string {
        return str_replace(
            ['/', '\\'],
            '-',
            $invoiceNumber
        ) . '.pdf';
    }

    private function generatePdf(
        array $transaction,
        string $invoiceNumber
    ): string {
        $options = new Options();

        $options->set(
            'isRemoteEnabled',
            true
        );

        $options->set(
            'defaultFont',
            'DejaVu Sans'
        );

        $dompdf = new Dompdf($options);

        $html = $this->buildHtml(
            $transaction,
            $invoiceNumber
        );

        $dompdf->loadHtml($html);

        $dompdf->setPaper(
            'A4',
            'portrait'
        );

        $dompdf->render();

        return $dompdf->output();
    }

    private function buildHtml(
        array $transaction,
        string $invoiceNumber
    ): string {
        $customerName = htmlspecialchars(
            (string) ($transaction['customer_name'] ?? '-'),
            ENT_QUOTES,
            'UTF-8'
        );

        $productName = htmlspecialchars(
            (string) ($transaction['product_name'] ?? '-'),
            ENT_QUOTES,
            'UTF-8'
        );

        $transactionCode = htmlspecialchars(
            (string) ($transaction['transaction_code'] ?? '-'),
            ENT_QUOTES,
            'UTF-8'
        );

        $invoiceNumberHtml = htmlspecialchars(
            $invoiceNumber,
            ENT_QUOTES,
            'UTF-8'
        );

        $dealPrice = $this->formatRupiah(
            (float) ($transaction['deal_price'] ?? 0)
        );

        $createdAt = !empty($transaction['created_at'])
            ? date(
                'd F Y',
                strtotime($transaction['created_at'])
            )
            : date('d F Y');

        $companyAddress = 'Davenue Office Space, Jl Teratai Raya, Indah No.4 Blok F, Tj. Barat, Kec. Jagakarsa, Jakarta 12530';

        $companyPhone = '085894446021';

        $companyWebsite = 'ravatraacademy.id';

        $paymentAccountName = 'Ravatra Akademi Indonesia';

        $paymentBank = 'BCA Sudirman Mansion';

        $paymentAccountNumber = '5375375432';

        $invoiceNote = 'Surat Ket PP 55 tahun 2022<br>
                    Nomor: KET-00275/PP23-CT/KPP.3009/2025 terlampir';

        $logoPath = BASE_PATH . '/assets/logo-ravatra-academy-nobg.png';

        $logoHtml = '';

        if (file_exists($logoPath)) {
            $logoData = base64_encode(
                file_get_contents($logoPath)
            );

            $logoHtml = sprintf(
                '<img src="data:image/png;base64,%s" class="logo">',
                $logoData
            );
        }


        return <<<HTML
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <style>

        * {
            box-sizing: border-box;
        }

        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 42px 45px;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            background: #ffffff;
        }

        .watermark {
            position: fixed;
            top: 340px;
            left: 0;
            width: 100%;

            text-align: center;

            font-size: 78px;
            font-weight: bold;
            letter-spacing: 12px;

            color: #f3f4f6;

            z-index: -1;
        }

        .header {
            width: 100%;

            border-bottom: 2px solid #f59e0b;

            padding-bottom: 22px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-left {
            width: 55%;
            vertical-align: middle;
        }

        .header-right {
            width: 45%;
            text-align: right;
            vertical-align: middle;
        }

        .logo {
            width: 145px;
            height: auto;
        }

        .company-address {
            margin-top: 8px;

            width: 280px;

            font-size: 9px;
            line-height: 1.6;

            color: #6b7280;
        }

        .invoice-title {
            font-size: 31px;
            font-weight: bold;

            letter-spacing: 2px;

            color: #111827;
        }

        .invoice-number {
            margin-top: 8px;

            font-size: 11px;
            font-weight: bold;

            color: #f59e0b;
        }

        .invoice-date {
            margin-top: 5px;

            font-size: 10px;

            color: #6b7280;
        }

        .customer-section {
            margin-top: 30px;
        }

        .customer-label {
            font-size: 9px;
            font-weight: bold;

            text-transform: uppercase;
            letter-spacing: 1px;

            color: #9ca3af;
        }

        .customer-name {
            margin-top: 6px;

            font-size: 16px;
            font-weight: bold;

            color: #111827;
        }

        .transaction-info {
            margin-top: 22px;
        }

        .transaction-code-label {
            font-size: 9px;

            color: #9ca3af;
        }

        .transaction-code {
            margin-top: 4px;

            font-size: 10px;
            font-weight: bold;

            color: #374151;
        }

        .items {
            width: 100%;

            margin-top: 30px;

            border-collapse: collapse;
        }

        .items thead th {
            padding: 11px 10px;

            background: #f9fafb;

            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;

            font-size: 9px;
            font-weight: bold;

            color: #6b7280;

            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items tbody td {
            padding: 15px 10px;

            border-bottom: 1px solid #e5e7eb;

            vertical-align: top;

            color: #374151;
        }

        .column-number {
            width: 8%;

            text-align: center;
        }

        .column-description {
            width: 42%;
        }

        .column-qty {
            width: 12%;

            text-align: center;
        }

        .column-price {
            width: 19%;

            text-align: right;
        }

        .column-total {
            width: 19%;

            text-align: right;
        }

        .product-name {
            font-weight: bold;

            color: #111827;
        }

        .summary {
            width: 100%;

            margin-top: 20px;

            border-collapse: collapse;
        }

        .summary-label {
            width: 70%;

            text-align: right;

            font-size: 10px;
            font-weight: bold;

            color: #6b7280;
        }

        .summary-total {
            width: 30%;

            padding: 14px;

            text-align: right;

            background: #fff7ed;

            border: 1px solid #fed7aa;

            font-size: 17px;
            font-weight: bold;

            color: #ea580c;
        }

        .bottom-section {
            width: 100%;

            margin-top: 25px;

            border-collapse: collapse;
        }

        .bottom-left {
            width: 55%;

            padding-right: 25px;

            vertical-align: top;
        }

        .bottom-right {
            width: 45%;

            vertical-align: top;
        }

        .section-title {
            margin-bottom: 8px;

            font-size: 9px;
            font-weight: bold;

            letter-spacing: 0.8px;

            color: #9ca3af;

            text-transform: uppercase;
        }

        .payment-box {
            padding: 13px 15px;

            background: #f9fafb;

            border-left: 3px solid #f59e0b;
        }

        .payment-name {
            font-size: 11px;
            font-weight: bold;

            color: #111827;
        }

        .payment-detail {
            margin-top: 4px;

            font-size: 10px;
            line-height: 1.6;

            color: #4b5563;
        }

        .note-box {
            padding: 13px 15px;

            background: #f9fafb;

            font-size: 9px;
            line-height: 1.7;

            color: #6b7280;
        }

        .footer {
            margin-top: 35px;

            padding-top: 15px;

            border-top: 1px solid #e5e7eb;
        }

        .footer-table {
            width: 100%;

            border-collapse: collapse;
        }

        .footer-company {
            width: 60%;

            vertical-align: top;

            font-size: 9px;
            line-height: 1.6;

            color: #6b7280;
        }

        .footer-contact {
            width: 40%;

            text-align: right;
            vertical-align: top;

            font-size: 9px;
            line-height: 1.8;

            color: #6b7280;
        }

        .footer-brand {
            font-weight: bold;

            color: #f59e0b;
        }

        .footer-bottom {
            margin-top: 12px;

            text-align: center;

            font-size: 8px;

            color: #9ca3af;
        }

    </style>

</head>


<body>

    <div class="watermark">
        LUNAS
    </div>

    <div class="header">

        <table class="header-table">

            <tr>

                <td class="header-left">

                    {$logoHtml}

                    <div class="company-address">
                        {$companyAddress}
                    </div>

                </td>


                <td class="header-right">

                    <div class="invoice-title">
                        INVOICE
                    </div>

                    <div class="invoice-number">
                        {$invoiceNumberHtml}
                    </div>

                    <div class="invoice-date">
                        {$createdAt}
                    </div>

                </td>

            </tr>

        </table>

    </div>

    <div class="customer-section">

        <div class="customer-label">
            Kepada Yth.
        </div>

        <div class="customer-name">
            {$customerName}
        </div>

        <div class="transaction-info">

            <div class="transaction-code-label">
                Transaction Code
            </div>

            <div class="transaction-code">
                {$transactionCode}
            </div>

        </div>

    </div>

    <table class="items">

        <thead>

            <tr>

                <th class="column-number">
                    No
                </th>

                <th class="column-description">
                    Deskripsi
                </th>

                <th class="column-qty">
                    Qty
                </th>

                <th class="column-price">
                    Harga Satuan
                </th>

                <th class="column-total">
                    Harga Total
                </th>

            </tr>

        </thead>


        <tbody>

            <tr>

                <td class="column-number">
                    01
                </td>

                <td class="column-description">

                    <div class="product-name">
                        {$productName}
                    </div>

                </td>

                <td class="column-qty">
                    1
                </td>

                <td class="column-price">
                    {$dealPrice}
                </td>

                <td class="column-total">
                    {$dealPrice}
                </td>

            </tr>

        </tbody>

    </table>

    <table class="summary">

        <tr>

            <td class="summary-label">
                TOTAL PEMBAYARAN
            </td>

            <td class="summary-total">
                {$dealPrice}
            </td>

        </tr>

    </table>

    <table class="bottom-section">

        <tr>

            <td class="bottom-left">

                <div class="section-title">
                    Pembayaran diterima oleh rekening
                </div>

                <div class="payment-box">

                    <div class="payment-name">
                        {$paymentAccountName}
                    </div>

                    <div class="payment-detail">
                        {$paymentBank}
                        <br>
                        Acc No. {$paymentAccountNumber}
                    </div>

                </div>

            </td>


            <td class="bottom-right">

                <div class="section-title">
                    Catatan
                </div>

                <div class="note-box">
                    {$invoiceNote}
                </div>

            </td>

        </tr>

    </table>

    <div class="footer">

        <table class="footer-table">

            <tr>

                <td class="footer-company">

                    <span class="footer-brand">
                        RAVATRA ACADEMY
                    </span>

                    <br>

                    {$companyAddress}

                </td>


                <td class="footer-contact">

                    WhatsApp
                    <br>

                    {$companyPhone}

                    <br>

                    Website
                    <br>

                    {$companyWebsite}

                </td>

            </tr>

        </table>


        <div class="footer-bottom">
            Invoice ini diterbitkan secara elektronik oleh Ravatra Academy.
        </div>

    </div>


</body>

</html>
HTML;
    }

    private function formatRupiah(
        float $amount
    ): string {
        return 'Rp ' . number_format(
            $amount,
            0,
            ',',
            '.'
        );
    }
}