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
                                @if(!empty($invoice->tripay_reference))
                                <tr>
                                    <td><strong>Tripay Reference:</strong></td>
                                    <td><span class="badge badge-info">{{ $invoice->tripay_reference }}</span></td>
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
                    
                    <!-- Invoice Items -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Invoice Items</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Deskripsi</th>
                                            <th>Periode / Siklus</n></th>
                                            <th class="text-right">Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($invoice->items && $invoice->items->count() > 0)
                                            @foreach($invoice->items as $item)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $item->description ?? 'Item' }}</strong>
                                                        @if(!empty($item->billing_type))
                                                            <div class="text-muted small">{{ ucfirst($item->billing_type) }}</div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $start = isset($item->billing_period_start) ? \Carbon\Carbon::parse($item->billing_period_start) : null;
                                                            $end = isset($item->billing_period_end) ? \Carbon\Carbon::parse($item->billing_period_end) : null;
                                                        @endphp
                                                        @if($start && $end)
                                                            {{ $start->format('d M Y') }} - {{ $end->format('d M Y') }}
                                                        @else
                                                            {{ ucfirst($item->billing_cycle ?? 'monthly') }}
                                                        @endif
                                                    </td>
                                                    <td class="text-right">
                                                        Rp {{ number_format($item->amount ?? 0, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @elseif($invoice->order)
                                            <tr>
                                                <td>
                                                    <strong>{{ $invoice->order->product->name ?? 'Service' }}</strong>
                                                    @if($invoice->order->order_details && isset($invoice->order->order_details['template']))
                                                        <div class="text-muted small">Template: {{ $invoice->order->order_details['template']['name'] }}</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($invoice->billing_period_start && $invoice->billing_period_end)
                                                        {{ $invoice->billing_period_start->format('d M Y') }} - {{ $invoice->billing_period_end->format('d M Y') }}
                                                    @else
                                                        {{ ucfirst($invoice->order->billing_cycle) }}
                                                    @endif
                                                </td>
                                                <td class="text-right">Rp {{ number_format($invoice->order->subscription_amount ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                            @if($invoice->order->order_details && isset($invoice->order->order_details['addons']))
                                                @foreach($invoice->order->order_details['addons'] as $addon)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $addon['name'] }}</strong>
                                                            <div class="text-muted small">{{ $addon['billing_type'] }}</div>
                                                        </td>
                                                        <td>-</td>
                                                        <td class="text-right">Rp {{ number_format($addon['price'], 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        @else
                                            <tr>
                                                <td>Service</td>
                                                <td>-</td>
                                                <td class="text-right">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
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
                                <a href="{{ route('admin.invoices.download', $invoice) }}" class="btn btn-outline-primary" target="_blank">
                                    <i class="fas fa-file-download"></i> Download PDF
                                </a>
                                <button type="button" class="btn btn-outline-secondary" onclick="previewPdf({{ $invoice->id }})">
                                    <i class="fas fa-eye"></i> Preview PDF
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="previewPdf({{ $invoice->id }}, true)">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function markAsPaid(invoiceId) {
    Swal.fire({
        title: 'Tandai invoice dibayar?',
        text: 'Masukkan metode pembayaran.',
        input: 'text',
        inputPlaceholder: 'Metode pembayaran',
        inputValue: 'Bank Transfer',
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        preConfirm: (paymentMethod) => {
            if (!paymentMethod) {
                Swal.showValidationMessage('Metode pembayaran wajib diisi');
            }
            return paymentMethod;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const paymentMethod = result.value;
            fetch(`/admin/invoices/${invoiceId}/mark-paid`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ payment_method: paymentMethod })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: 'Invoice ditandai dibayar.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Kesalahan saat menandai invoice dibayar.',
                        icon: 'error'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    title: 'Gagal',
                    text: 'Terjadi kesalahan jaringan.',
                    icon: 'error'
                });
            });
        }
    });
}

function sendInvoice(invoiceId) {
    Swal.fire({
        title: 'Kirim invoice?',
        text: 'Invoice akan dikirim ke klien.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, kirim',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/invoices/${invoiceId}/send`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Terkirim',
                        text: 'Invoice berhasil dikirim.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Kesalahan saat mengirim invoice.',
                        icon: 'error'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    title: 'Gagal',
                    text: 'Terjadi kesalahan jaringan.',
                    icon: 'error'
                });
            });
        }
    });
}

function markAsOverdue(invoiceId) {
    Swal.fire({
        title: 'Tandai overdue?',
        text: 'Invoice akan ditandai sebagai overdue.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, tandai',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/invoices/${invoiceId}/mark-overdue`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: 'Invoice ditandai sebagai overdue.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Kesalahan saat menandai invoice overdue.',
                        icon: 'error'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    title: 'Gagal',
                    text: 'Terjadi kesalahan jaringan.',
                    icon: 'error'
                });
            });
        }
    });
}

function cancelInvoice(invoiceId) {
    Swal.fire({
        title: 'Batalkan invoice?',
        text: 'Invoice akan dibatalkan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, batalkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/invoices/${invoiceId}/cancel`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Dibatalkan',
                        text: 'Invoice berhasil dibatalkan.',
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Kesalahan saat membatalkan invoice.',
                        icon: 'error'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    title: 'Gagal',
                    text: 'Terjadi kesalahan jaringan.',
                    icon: 'error'
                });
            });
        }
    });
}

function previewPdf(invoiceId, shouldPrint = false) {
    fetch(`/admin/invoices/${invoiceId}/download`, {
        method: 'GET',
        headers: {
            'Accept': 'application/pdf'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Gagal mengambil PDF');
        return response.blob();
    })
    .then(blob => {
        const url = URL.createObjectURL(blob);
        const w = window.open('', '_blank');
        if (!w) {
            Swal.fire({
                title: 'Popup diblokir',
                text: 'Izinkan popup untuk melihat/print PDF.',
                icon: 'warning'
            });
            return;
        }
        w.document.write(`<!DOCTYPE html><html><head><title>Invoice PDF</title><meta charset="utf-8"/></head><body style="margin:0;overflow:hidden"><embed src="${url}" type="application/pdf" width="100%" height="100%" /></body></html>`);
        w.document.close();
        if (shouldPrint) {
            setTimeout(() => {
                w.focus();
                w.print();
            }, 800);
        }
    })
    .catch(() => {
        Swal.fire({
            title: 'Gagal',
            text: 'Tidak dapat menampilkan PDF.',
            icon: 'error'
        });
    });
}
</script>
@endsection