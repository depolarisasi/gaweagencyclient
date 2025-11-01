@extends('layouts.client')

@section('title', 'Create Support Ticket')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('client.tickets.index') }}" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left mr-2"></i>Back
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Create New Ticket</h1>
    </div>

    <div class="bg-white rounded-md shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('client.tickets.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Subject *</span></label>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="input input-bordered w-full" required maxlength="255" placeholder="Brief summary of your issue" />
                    @error('subject')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Priority *</span></label>
                    <select name="priority" class="select select-bordered w-full" required>
                        <option value="">Select priority</option>
                        <option value="high" {{ old('priority')==='high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ old('priority')==='medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ old('priority')==='low' ? 'selected' : '' }}>Low</option>
                    </select>
                    @error('priority')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Category *</span></label>
                    <select name="category" class="select select-bordered w-full" required>
                        <option value="">Select category</option>
                        <option value="technical" {{ old('category')==='technical' ? 'selected' : '' }}>Technical</option>
                        <option value="billing" {{ old('category')==='billing' ? 'selected' : '' }}>Billing</option>
                        <option value="general" {{ old('category')==='general' ? 'selected' : '' }}>General</option>
                        <option value="feature_request" {{ old('category')==='feature_request' ? 'selected' : '' }}>Feature Request</option>
                    </select>
                    @error('category')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Description *</span></label>
                <input id="ticket-description" type="hidden" name="description" value="{{ old('description') }}">
                <trix-editor input="ticket-description" class="trix-content" placeholder="Describe your issue or request in detail"></trix-editor>
                <p class="text-xs text-gray-500 mt-1">Rich text enabled. Images/file uploads are not allowed.</p>
                @error('description')
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
                <a href="{{ route('client.tickets.index') }}" class="btn">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane mr-2"></i>Submit Ticket
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