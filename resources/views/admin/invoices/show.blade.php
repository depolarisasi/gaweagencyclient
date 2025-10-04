@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Invoice Details - {{ $invoice->invoice_number }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Invoices
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Invoice Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Invoice Number:</strong></td>
                                    <td>{{ $invoice->invoice_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td>Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tax Amount:</strong></td>
                                    <td>Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Amount:</strong></td>
                                    <td><strong>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Due Date:</strong></td>
                                    <td>{{ $invoice->due_date->format('d M Y') }}</td>
                                </tr>
                                @if($invoice->paid_date)
                                <tr>
                                    <td><strong>Paid Date:</strong></td>
                                    <td>{{ $invoice->paid_date->format('d M Y') }}</td>
                                </tr>
                                @endif
                                @if($invoice->payment_method)
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td>{{ $invoice->payment_method }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Client Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Client Name:</strong></td>
                                    <td>{{ $invoice->user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $invoice->user->email }}</td>
                                </tr>
                                @if($invoice->order)
                                <tr>
                                    <td><strong>Order Number:</strong></td>
                                    <td>{{ $invoice->order->order_number }}</td>
                                </tr>
                                @endif
                            </table>
                            
                            @if($invoice->description)
                            <h5>Description</h5>
                            <p>{{ $invoice->description }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="btn-group" role="group">
                                @if($invoice->status !== 'paid')
                                <button type="button" class="btn btn-success" onclick="markAsPaid({{ $invoice->id }})">
                                    <i class="fas fa-check"></i> Mark as Paid
                                </button>
                                @endif
                                
                                @if($invoice->status === 'draft')
                                <button type="button" class="btn btn-primary" onclick="sendInvoice({{ $invoice->id }})">
                                    <i class="fas fa-paper-plane"></i> Send Invoice
                                </button>
                                @endif
                                
                                @if($invoice->status !== 'paid' && $invoice->status !== 'overdue')
                                <button type="button" class="btn btn-warning" onclick="markAsOverdue({{ $invoice->id }})">
                                    <i class="fas fa-exclamation-triangle"></i> Mark as Overdue
                                </button>
                                @endif
                                
                                @if($invoice->status !== 'paid')
                                <button type="button" class="btn btn-danger" onclick="cancelInvoice({{ $invoice->id }})">
                                    <i class="fas fa-times"></i> Cancel Invoice
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markAsPaid(invoiceId) {
    if (confirm('Mark this invoice as paid?')) {
        const paymentMethod = prompt('Enter payment method:', 'Bank Transfer');
        if (paymentMethod) {
            fetch(`/admin/invoices/${invoiceId}/mark-paid`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    payment_method: paymentMethod
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
}

function sendInvoice(invoiceId) {
    if (confirm('Send this invoice to the client?')) {
        fetch(`/admin/invoices/${invoiceId}/send`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function markAsOverdue(invoiceId) {
    if (confirm('Mark this invoice as overdue?')) {
        fetch(`/admin/invoices/${invoiceId}/mark-overdue`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function cancelInvoice(invoiceId) {
    if (confirm('Cancel this invoice?')) {
        fetch(`/admin/invoices/${invoiceId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>
@endsection