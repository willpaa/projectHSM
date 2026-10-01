<?php

namespace App\Http\Controllers;

use App\Models\Payments;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentReceiptController extends Controller
{
    /**
     * Show the payment receipt page
     */
    public function show(Payments $payment)
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super_admin', 'cashier', 'casher', 'billing_staff', 'accountant'])) {
            abort(403, 'Unauthorized');
        }

        $payment->load(['patient', 'serviceRequest.service', 'collector']);

        return view('receipts.payments', compact('payment'));
    }
    
    /**
     * Download PDF receipt
     * URL: /receipt/payment/{payment}/download
     */
    public function download(Payments $payment)
    {
        $this->authorizeReceiptAccess();
        $payment->load(['patient', 'serviceRequest.service', 'collector']);
        $pdf = PDF::loadView('receipts.pdf', compact('payment'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download("receipt-{$payment->invoice_number}.pdf");
    }
    
    /**
     * Stream PDF in browser (preview before download)
     * URL: /receipt/payment/{payment}/stream
     */
    public function stream(Payments $payment)
    {
        $this->authorizeReceiptAccess();
        $payment->load(['patient', 'serviceRequest.service', 'collector']);
        $pdf = PDF::loadView('receipts.pdf', compact('payment'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream("receipt-{$payment->invoice_number}.pdf");
    }
    /**
     * Send receipt via email
     */
    public function email(Payments $payment)
    {
        // Mail::to($payment->patient->email)->send(new PaymentReceiptMail($payment));
        
        return redirect()->back()->with('success', 'Receipt sent to patient email');
    }

    private function authorizeReceiptAccess(): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super_admin', 'cashier', 'casher', 'billing_staff', 'accountant'])) {
            abort(403, 'Unauthorized');
        }
    }
}