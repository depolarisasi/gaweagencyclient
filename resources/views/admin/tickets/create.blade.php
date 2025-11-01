@extends('layouts.app')

@section('title', 'Create Support Ticket')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/trix@2.0.0/dist/trix.css">
<style>
  .trix-button-group--file-tools { display: none !important; }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50">
  <div class="flex">
    
    @include('layouts.sidebar') 
    
    <div class="flex-1 p-8">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-3">
          <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left mr-2"></i>Back</a>
          <h1 class="text-2xl font-bold text-gray-900">Create Support Ticket</h1>
        </div>
      </div>

      <div class="bg-white rounded-md shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.tickets.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          @csrf

          <div class="form-control">
            <label class="label"><span class="label-text font-medium">Client *</span></label>
            <select name="user_id" class="select select-bordered" required>
              <option value="">Pilih client</option>
              @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
              @endforeach
            </select>
          </div>

          <div class="form-control">
            <label class="label"><span class="label-text font-medium">Assign To</span></label>
            <select name="assigned_to" class="select select-bordered">
              <option value="">Pilih staff</option>
              @foreach($staff as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-control">
            <label class="label"><span class="label-text font-medium">Priority *</span></label>
            <select name="priority" class="select select-bordered" required>
              <option value="high">High</option>
              <option value="medium" selected>Medium</option>
              <option value="low">Low</option>
            </select>
          </div>

          <div class="form-control">
            <label class="label"><span class="label-text font-medium">Category *</span></label>
            <select name="category" class="select select-bordered" required>
              <option value="general" selected>General</option>
              <option value="technical">Technical</option>
              <option value="billing">Billing</option>
              <option value="feature_request">Feature Request</option>
            </select>
          </div>

          <div class="md:col-span-2">
            <label class="label"><span class="label-text font-medium">Subject *</span></label>
            <input type="text" name="subject" class="input input-bordered w-full" required placeholder="Judul ringkas masalah">
          </div>

          <div class="md:col-span-2">
            <label class="label"><span class="label-text font-medium">Description *</span></label>
            <input id="admin-create-description" type="hidden" name="description">
            <trix-editor input="admin-create-description" class="trix-content" placeholder="Detail masalah atau permintaan..."></trix-editor>
          </div>

          <div class="md:col-span-2 flex justify-end gap-2 mt-2">
            <a href="{{ route('admin.tickets.index') }}" class="btn">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Create Ticket</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>
<script>
  document.addEventListener('trix-file-accept', function (event) { event.preventDefault(); });
  document.addEventListener('trix-attachment-add', function (event) { event.preventDefault(); });
</script>
@endsection