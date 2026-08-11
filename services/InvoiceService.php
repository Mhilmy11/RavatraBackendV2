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

    /**
     * Generate Invoice
     */
    public function generate(array $transaction): array
    {
        /**
         * Transaction must be PAID
         */
        if (($transaction['status'] ?? null) !== 'PAID') {
            throw new RuntimeException(
                'Invoice can only be generated for paid transactions.'
            );
        }

        /**
         * Generate Invoice Number
         */
        $invoiceNumber = $this->generateInvoiceNumber(
            $transaction['transaction_code']
        );

        /**
         * Generate PDF filename
         */
        $filename = $this->generateFilename(
            $invoiceNumber
        );

        /**
         * Generate PDF
         */
        $pdfContent = $this->generatePdf(
            $transaction,
            $invoiceNumber
        );

        /**
         * Save PDF
         */
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

        /**
         * Database relative path
         */
        $relativePath = 'invoice/' . $filename;

        return [
            'invoice_number' => $invoiceNumber,
            'invoice_path' => $relativePath,
            'invoice_generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Generate Invoice Number
     */
    private function generateInvoiceNumber(
        string $transactionCode
    ): string {
        return 'INV/RA/' .
            date('Y/m') .
            '/' .
            $transactionCode;
    }

    /**
     * Generate PDF Filename
     */
    private function generateFilename(
        string $invoiceNumber
    ): string {
        return str_replace(
            ['/', '\\'],
            '-',
            $invoiceNumber
        ) . '.pdf';
    }

    /**
     * Generate PDF Content
     */
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

    /**
     * Build Invoice HTML
     */
    private function buildHtml(
        array $transaction,
        string $invoiceNumber
    ): string {
        $customerName = htmlspecialchars(
            (string) ($transaction['customer_name'] ?? '-'),
            ENT_QUOTES,
            'UTF-8'
        );

        $salesName = htmlspecialchars(
            (string) ($transaction['sales_name'] ?? '-'),
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

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 40px;
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
        }

        .header {
            width: 100%;
            margin-bottom: 40px;
        }

        .company {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
        }

        .company-subtitle {
            margin-top: 5px;
            color: #6b7280;
            font-size: 11px;
        }

        .invoice-title {
            margin-top: 35px;
            font-size: 28px;
            font-weight: bold;
            color: #111827;
        }

        .invoice-number {
            margin-top: 5px;
            color: #6b7280;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        .info-table td {
            padding: 5px 0;
            vertical-align: top;
        }

        .label {
            width: 150px;
            color: #6b7280;
        }

        .value {
            font-weight: bold;
            color: #111827;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 35px;
        }

        .items th {
            padding: 12px;
            background: #f3f4f6;
            border-bottom: 1px solid #d1d5db;
            text-align: left;
        }

        .items td {
            padding: 14px 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .price {
            text-align: right;
        }

        .total-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .total-table td {
            padding: 8px 0;
        }

        .total-label {
            text-align: right;
            font-weight: bold;
        }

        .total-price {
            width: 180px;
            text-align: right;
            font-size: 16px;
            font-weight: bold;
        }

        .status {
            margin-top: 30px;
            padding: 12px;
            background: #ecfdf5;
            color: #047857;
            font-weight: bold;
        }

        .footer {
            margin-top: 60px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 10px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="header">

        <div class="company">
            RAVATRA ACADEMY
        </div>

        <div class="company-subtitle">
            Professional Training & Education
        </div>

        <div class="invoice-title">
            INVOICE
        </div>

        <div class="invoice-number">
            {$invoiceNumberHtml}
        </div>

    </div>


    <table class="info-table">

        <tr>
            <td class="label">
                Transaction Code
            </td>

            <td class="value">
                {$transactionCode}
            </td>
        </tr>

        <tr>
            <td class="label">
                Invoice Date
            </td>

            <td class="value">
                {$createdAt}
            </td>
        </tr>

        <tr>
            <td class="label">
                Customer
            </td>

            <td class="value">
                {$customerName}
            </td>
        </tr>

        <tr>
            <td class="label">
                Sales
            </td>

            <td class="value">
                {$salesName}
            </td>
        </tr>

    </table>


    <table class="items">

        <thead>
            <tr>
                <th>
                    Product
                </th>

                <th class="price">
                    Price
                </th>
            </tr>
        </thead>

        <tbody>

            <tr>
                <td>
                    {$productName}
                </td>

                <td class="price">
                    {$dealPrice}
                </td>
            </tr>

        </tbody>

    </table>


    <table class="total-table">

        <tr>
            <td class="total-label">
                TOTAL
            </td>

            <td class="total-price">
                {$dealPrice}
            </td>
        </tr>

    </table>


    <div class="status">
        PAYMENT STATUS: PAID
    </div>


    <div class="footer">
        Thank you for your purchase.
        <br>
        Ravatra Academy
    </div>

</body>
</html>
HTML;
    }

    /**
     * Format Currency
     */
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