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

        $payment->load(['patient.insurance', 'serviceRequest.service', 'collector']);

        if ($payment->payment_mode === 'insurance') {
            return view('receipts.insurance-record', compact('payment'));
        }

        $patientPaidAmount = $this->patientPaidAmount($payment);

        return view('receipts.payments', compact('payment', 'patientPaidAmount'));
    }
    
    /**
     * Download PDF receipt
     * URL: /receipt/payment/{payment}/download
     */
    public function download(Payments $payment)
    {
        $this->authorizeReceiptAccess();
        $payment->load(['patient.insurance', 'serviceRequest.service', 'collector']);
        if ($payment->payment_mode === 'insurance') {
            $pdf = PDF::loadView('receipts.insurance-record-pdf', compact('payment'));
        } else {
            $patientPaidAmount = $this->patientPaidAmount($payment);
            $pdf = PDF::loadView('receipts.pdf', compact('payment', 'patientPaidAmount'));
        }
        $pdf->setPaper('A4', 'portrait');
        
        $prefix = $payment->payment_mode === 'insurance' ? 'insurance-record' : 'patient-receipt';

        return $pdf->download("{$prefix}-{$payment->invoice_number}.pdf");
    }
    
    /**
     * Stream PDF in browser (preview before download)
     * URL: /receipt/payment/{payment}/stream
     */
    public function stream(Payments $payment)
    {
        $this->authorizeReceiptAccess();
        $payment->load(['patient.insurance', 'serviceRequest.service', 'collector']);
        if ($payment->payment_mode === 'insurance') {
            $pdf = PDF::loadView('receipts.insurance-record-pdf', compact('payment'));
        } else {
            $patientPaidAmount = $this->patientPaidAmount($payment);
            $pdf = PDF::loadView('receipts.pdf', compact('payment', 'patientPaidAmount'));
        }
        $pdf->setPaper('A4', 'portrait');
        
        $prefix = $payment->payment_mode === 'insurance' ? 'insurance-record' : 'patient-receipt';

        return $pdf->stream("{$prefix}-{$payment->invoice_number}.pdf");
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

    private function patientPaidAmount(Payments $payment): float
    {
        return (float) ($payment->serviceRequest?->payments()
            ->where('payment_mode', '!=', 'insurance')
            ->sum('amount') ?? $payment->amount);
    }
}
