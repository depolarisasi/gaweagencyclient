<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\InvoicePdfService;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceSentMail;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['user', 'order']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = User::where('role', 'client')->where('status', 'active')->get();
        return view('admin.invoices.create', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'due_date' => 'required|date|after:today',
            'description' => 'nullable|string|max:1000',
        ]);
        
        $invoice = Invoice::create([
            'invoice_number' => $validated['invoice_number'],
            'user_id' => $validated['user_id'],
            'order_id' => $validated['order_id'] ?? null,
            'amount' => $validated['amount'],
            'tax_amount' => $validated['tax_amount'] ?? 0,
            'total_amount' => $validated['total_amount'],
            'status' => $validated['status'],
            'due_date' => $validated['due_date'],
            'description' => $validated['description'] ?? null,
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'invoice' => $invoice
            ]);
        }
        
        return redirect()->route('admin.invoices.index')
                        ->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['user', 'order']);
        
        if (request()->expectsJson()) {
            return response()->json($invoice);
        }
        
        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        // Only allow editing draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('admin.invoices.index')
                            ->with('error', 'Only draft invoices can be edited.');
        }
        
        $clients = User::where('role', 'client')->where('status', 'active')->get();
        return view('admin.invoices.edit', compact('invoice', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        // Only allow updating draft invoices
        if ($invoice->status !== 'draft') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft invoices can be updated'
                ], 403);
            }
            
            return redirect()->route('admin.invoices.index')
                            ->with('error', 'Only draft invoices can be updated.');
        }
        
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'nullable|in:draft,sent,paid,overdue,cancelled',
            'due_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
        ]);
        
        $invoice->update([
            'user_id' => $validated['user_id'] ?? $invoice->user_id,
            'amount' => $validated['amount'],
            'tax_amount' => $validated['tax_amount'] ?? 0,
            'total_amount' => $validated['total_amount'],
            'status' => $validated['status'] ?? $invoice->status,
            'due_date' => $validated['due_date'],
            'description' => $validated['description'] ?? $invoice->description,
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully',
                'invoice' => $invoice
            ]);
        }
        
        return redirect()->route('admin.invoices.index')
                        ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        // Only allow deleting draft or cancelled invoices
        if (!in_array($invoice->status, ['draft', 'cancelled'])) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft or cancelled invoices can be deleted'
                ], 403);
            }
            
            return redirect()->route('admin.invoices.index')
                            ->with('error', 'Only draft or cancelled invoices can be deleted.');
        }
        
        $invoice->delete();
        
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully'
            ]);
        }
        
        return redirect()->route('admin.invoices.index')
                        ->with('success', 'Invoice deleted successfully.');
    }
    
    /**
     * Send invoice to client
     */
    public function send(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid'
            ], 400);
        }
        
        // Update status to sent
        $invoice->update(['status' => 'sent']);

        // Send email notification to client (best-effort)
        try {
            $invoice->loadMissing('user');
            if ($invoice->user && $invoice->user->email) {
                Mail::to($invoice->user->email)->send(new InvoiceSentMail($invoice));
            }
            $message = 'Invoice sent and email delivered (if possible)';
            $status = 'success';
        } catch (\Throwable $e) {
            \Log::error('Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            $message = 'Invoice marked as sent, but email failed to send';
            $status = 'warning';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'status' => $status,
        ]);
    }
    
    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid'
            ], 400);
        }
        
        $validated = $request->validate([
            'payment_method' => 'required|string|max:255',
            'paid_date' => 'nullable|date'
        ]);
        
        $invoice->update([
            'status' => 'paid',
            'paid_date' => $validated['paid_date'] ?? now(),
            'payment_method' => $validated['payment_method']
        ]);
        
        // TODO: Create project if this is from an order
        // TODO: Send payment confirmation email
        
        return response()->json([
            'success' => true,
            'message' => 'Invoice marked as paid successfully'
        ]);
    }
    
    /**
     * Cancel invoice
     */
    public function cancel(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Paid invoices cannot be cancelled'
            ], 400);
        }
        
        $invoice->update(['status' => 'cancelled']);
        
        return response()->json([
            'success' => true,
            'message' => 'Invoice cancelled successfully'
        ]);
    }
    
    /**
     * Mark invoice as overdue
     */
    public function markAsOverdue(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Paid invoices cannot be marked as overdue'
            ], 400);
        }
        
        $invoice->update(['status' => 'overdue']);
        
        return response()->json([
            'success' => true,
            'message' => 'Invoice marked as overdue successfully'
        ]);
    }
    
    /**
     * Download invoice PDF
     */
    public function download(Invoice $invoice)
    {
        $invoice->load(['user', 'order', 'order.product']);

        try {
            $service = app(InvoicePdfService::class);
            $pdfBinary = $service->generate($invoice);

            if (!$pdfBinary) {
                \Log::warning('Gagal generate PDF untuk invoice', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ]);
                return redirect()
                    ->route('admin.invoices.show', $invoice)
                    ->with('error', 'Gagal menghasilkan PDF invoice.');
            }

            $filename = 'Invoice-' . ($invoice->invoice_number ?? $invoice->id) . '.pdf';

            // Support inline viewing for printing if requested
            if (request('inline') === '1') {
                return response($pdfBinary, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $filename . '"',
                ]);
            }

            return response($pdfBinary, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error pada download PDF invoice', [
                'invoice_id' => $invoice->id,
                'message' => $e->getMessage(),
            ]);
            return redirect()
                ->route('admin.invoices.show', $invoice)
                ->with('error', 'Terjadi kesalahan saat mengunduh PDF invoice.');
        }
    }
    
    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        
        // Get the last invoice number for today
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . $date . '%')
                             ->orderBy('invoice_number', 'desc')
                             ->first();
        
        if ($lastInvoice) {
            // Extract the sequence number and increment
            $lastSequence = (int) substr($lastInvoice->invoice_number, -4);
            $sequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
        } else {
            // First invoice of the day
            $sequence = '0001';
        }
        
        return $prefix . $date . $sequence;
    }
    
    /**
     * Get invoice statistics
     */
    public function statistics()
    {
        $stats = [
            'total_invoices' => Invoice::count(),
            'total_amount' => Invoice::sum('total_amount'),
            'paid_amount' => Invoice::where('status', 'paid')->sum('total_amount'),
            'pending_amount' => Invoice::where('status', 'sent')->sum('total_amount'),
            'overdue_count' => Invoice::where('status', 'overdue')->count(),
            'this_month_revenue' => Invoice::where('status', 'paid')
                                          ->whereMonth('paid_date', now()->month)
                                          ->sum('total_amount'),
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:send,mark_paid,cancel,delete',
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'exists:invoices,id'
        ]);
        
        $invoiceIds = $request->invoice_ids;
        $action = $request->action;
        
        switch ($action) {
            case 'send':
                Invoice::whereIn('id', $invoiceIds)
                       ->whereNotIn('status', ['paid', 'cancelled'])
                       ->update(['status' => 'sent']);
                $message = 'Invoices sent successfully';
                break;
                
            case 'mark_paid':
                Invoice::whereIn('id', $invoiceIds)
                       ->where('status', '!=', 'paid')
                       ->update([
                           'status' => 'paid',
                           'paid_date' => now()->toDateString(),
                           'payment_method' => 'Manual'
                       ]);
                $message = 'Invoices marked as paid successfully';
                break;
                
            case 'cancel':
                Invoice::whereIn('id', $invoiceIds)
                       ->where('status', '!=', 'paid')
                       ->update(['status' => 'cancelled']);
                $message = 'Invoices cancelled successfully';
                break;
                
            case 'delete':
                Invoice::whereIn('id', $invoiceIds)
                       ->whereIn('status', ['draft', 'cancelled'])
                       ->delete();
                $message = 'Invoices deleted successfully';
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}