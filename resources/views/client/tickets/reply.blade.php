@extends('layouts.client')

@section('title', 'Reply Ticket #' . $ticket->ticket_number)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('client.tickets.show', $ticket) }}" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left mr-2"></i>Back to Ticket
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Reply: #{{ $ticket->ticket_number }} - {{ $ticket->subject }}</h1>
    </div>

    <div class="bg-white rounded-md border border-gray-200 p-6 space-y-4">
        <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">Status:</span>
            @if($ticket->status === 'open')
                <span class="badge badge-error">Open</span>
            @elseif($ticket->status === 'in_progress')
                <span class="badge badge-warning">In Progress</span>
            @elseif($ticket->status === 'resolved')
                <span class="badge badge-info">Resolved</span>
            @else
                <span class="badge">Closed</span>
            @endif
        </div>

        @if($ticket->status === 'closed')
            <div class="alert alert-warning">
                <i class="fas fa-info-circle mr-2"></i>This ticket is closed. You cannot add new replies.
            </div>
        @endif

        <form method="POST" action="{{ route('client.tickets.reply', $ticket) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Your Reply *</span></label>
                <input id="ticket-reply" type="hidden" name="message" value="">
                <trix-editor input="ticket-reply" class="trix-content" placeholder="Type your reply here..."></trix-editor>
                <p class="text-xs text-gray-500 mt-1">Rich text enabled. Images/file uploads are not allowed in editor.</p>
                @error('message')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Attachments (optional)</span></label>
                <input type="file" name="attachments[]" multiple accept=".pdf,.png,.jpg,.jpeg,.gif" class="file-input file-input-bordered w-full" />
                <p class="text-xs text-gray-500 mt-1">Allowed: PDF, PNG, JPG, GIF. Max 10MB per file.</p>
                @error('attachments')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
                @error('attachments.*')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('client.tickets.show', $ticket) }}" class="btn">Cancel</a>
                <button type="submit" class="btn btn-primary" @if($ticket->status === 'closed') disabled @endif>
                    <i class="fas fa-reply mr-2"></i>Send Reply
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
<script src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>
<style>
  .trix-button-group--file-tools { display: none !important; }
</style>
<script>
  // Disable file attachments in Trix (no images/files allowed via editor)
  document.addEventListener('trix-file-accept', function (event) {
    event.preventDefault();
  });
  document.addEventListener('trix-attachment-add', function (event) {
    event.preventDefault();
  });
</script>
@endpush