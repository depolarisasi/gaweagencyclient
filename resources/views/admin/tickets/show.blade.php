@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number)

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
          <h1 class="text-2xl font-bold text-gray-900">#{{ $ticket->ticket_number }} - {{ $ticket->subject }}</h1>
        </div>
        <div class="flex items-center space-x-2">
          @if($ticket->status === 'open')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><i class="fas fa-circle mr-1"></i>Open</span>
          @elseif($ticket->status === 'in_progress')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i>In Progress</span>
          @elseif($ticket->status === 'resolved')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><i class="fas fa-check mr-1"></i>Resolved</span>
          @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><i class="fas fa-times mr-1"></i>Closed</span>
          @endif
        </div>
      </div>

      <!-- Ticket Details -->
      <div class="bg-white rounded-md shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700 mb-4">
          <div><i class="fas fa-user mr-2"></i>Client: <span class="font-medium">{{ $ticket->user->name }} ({{ $ticket->user->email }})</span></div>
          <div><i class="fas fa-layer-group mr-2"></i>Priority: <span class="font-medium">{{ ucfirst($ticket->priority) }}</span></div>
          <div><i class="fas fa-tags mr-2"></i>Category: <span class="font-medium">{{ ucfirst(str_replace('_',' ',$ticket->category)) }}</span></div>
          <div>
            <i class="fas fa-user-check mr-2"></i>Assigned To:
            @if($ticket->assignedUser)
              <span class="font-medium">{{ $ticket->assignedUser->name }}</span>
            @else
              <span class="text-gray-500">Unassigned</span>
            @endif
          </div>
        </div>
        <div class="prose max-w-none text-gray-800">
          {!! $ticket->description !!}
        </div>
      </div>

      <!-- Actions -->
      <div class="bg-white rounded-md shadow-sm border border-gray-200 p-4 mb-6 flex flex-wrap items-center gap-2">
        @if(!$ticket->assignedUser)
        <form id="assignForm" class="flex items-center gap-2">
          @csrf
          <select name="assigned_to" class="select select-bordered select-sm">
            <option value="">Pilih staff</option>
            @foreach(\App\Models\User::where('role','staff')->where('status','active')->orderBy('name')->get() as $staff)
              <option value="{{ $staff->id }}">{{ $staff->name }}</option>
            @endforeach
          </select>
          <button type="submit" class="btn btn-sm btn-outline"><i class="fas fa-user-plus mr-2"></i>Assign</button>
        </form>
        @endif

        @if($ticket->status === 'open')
          <button type="button" class="btn btn-sm btn-outline" onclick="markInProgress({{ $ticket->id }})"><i class="fas fa-play mr-2"></i>Mark In Progress</button>
        @endif
        @if($ticket->status !== 'resolved' && $ticket->status !== 'closed')
          <button type="button" class="btn btn-sm btn-outline" onclick="markResolved({{ $ticket->id }})"><i class="fas fa-check mr-2"></i>Mark Resolved</button>
        @endif
        @if($ticket->status !== 'closed')
          <button type="button" class="btn btn-sm btn-outline" onclick="closeTicket({{ $ticket->id }})"><i class="fas fa-times mr-2"></i>Close Ticket</button>
        @endif
        @if($ticket->status === 'closed')
          <button type="button" class="btn btn-sm btn-outline" onclick="reopenTicket({{ $ticket->id }})"><i class="fas fa-undo mr-2"></i>Reopen Ticket</button>
        @endif
      </div>

      <!-- Replies -->
      <div class="space-y-4 mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Conversation</h2>
        @php use Illuminate\Support\Facades\Storage; @endphp
        @forelse($ticket->replies->sortBy('created_at') as $reply)
          <div class="bg-white rounded-md shadow-sm border {{ $reply->is_internal ? 'border-purple-300' : 'border-gray-200' }} p-4">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <i class="fas fa-user-circle text-gray-500"></i>
                <span class="font-medium text-gray-900">{{ $reply->user->name }}</span>
                @if($reply->is_internal)
                  <span class="text-xs text-purple-600">(Internal)</span>
                @endif
              </div>
              <div class="text-sm text-gray-500">{{ $reply->created_at->format('M d, Y H:i') }}</div>
            </div>
            <div class="prose max-w-none text-gray-700">{!! $reply->message !!}</div>
            @if($reply->attachments)
              <div class="mt-3 border-t pt-3">
                <div class="text-sm font-medium text-gray-700 mb-2">
                  <i class="fas fa-paperclip mr-2"></i>Attachments
                </div>
                <div class="flex flex-wrap gap-2">
                  @foreach($reply->attachments as $file)
                    <a href="{{ Storage::url($file['path']) }}" target="_blank" class="btn btn-xs btn-outline">
                      <i class="fas fa-file mr-1"></i>{{ $file['name'] ?? basename($file['path']) }}
                    </a>
                  @endforeach
                </div>
              </div>
            @endif
          </div>
        @empty
          <div class="bg-white rounded-md border border-gray-200 p-6 text-center text-gray-600">Belum ada balasan.</div>
        @endforelse
      </div>

      <!-- Reply Form -->
      @if($ticket->status !== 'closed')
      <div id="replySection" class="bg-white rounded-md shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Kirim Balasan</h3>
        <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}" enctype="multipart/form-data" class="space-y-4">
          @csrf
          <input id="admin-show-reply" type="hidden" name="message">
          <trix-editor input="admin-show-reply" class="trix-content" placeholder="Tulis balasan..."></trix-editor>
          <label class="label cursor-pointer">
            <span class="label-text">Catatan internal (tidak terlihat klien)</span>
            <input type="checkbox" name="is_internal" class="checkbox checkbox-primary">
          </label>
          <div class="form-control">
            <label class="label"><span class="label-text font-medium">Lampiran (opsional)</span></label>
            <input type="file" name="attachments[]" multiple accept=".pdf,.png,.jpg,.jpeg,.gif" class="file-input file-input-bordered w-full" />
            <p class="text-xs text-gray-500 mt-1">Diizinkan: PDF, PNG, JPG, GIF. Maks 10MB per file.</p>
          </div>
          <div class="flex justify-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-reply mr-2"></i>Kirim</button>
          </div>
        </form>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/trix@2.0.0/dist/trix.umd.min.js"></script>
<script>
  document.addEventListener('trix-file-accept', function (event) { event.preventDefault(); });
  document.addEventListener('trix-attachment-add', function (event) { event.preventDefault(); });

  const assignForm = document.getElementById('assignForm');
  if (assignForm) {
    assignForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(assignForm);
      fetch(`{{ route('admin.tickets.assign', $ticket) }}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        body: formData
      }).then(r => r.json()).then(data => {
        if (data.success) {
          Swal.fire({ title: 'Berhasil', text: 'Tiket diassign.', icon: 'success' }).then(() => location.reload());
        } else {
          Swal.fire({ title: 'Gagal', text: data.message || 'Gagal assign.', icon: 'error' });
        }
      }).catch(() => Swal.fire({ title: 'Gagal', text: 'Kesalahan jaringan.', icon: 'error' }));
    });
  }

  function markInProgress(ticketId) {
    Swal.fire({ title: 'Tandai in progress?', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Batal' })
      .then(res => { if (res.isConfirmed) {
        fetch(`/admin/tickets/${ticketId}/in-progress`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } })
          .then(r => r.json()).then(d => d.success ? Swal.fire({title:'Berhasil',text:'Ditandai in progress',icon:'success'}).then(()=>location.reload()) : Swal.fire({title:'Gagal',text:d.message||'Gagal update.',icon:'error'}))
          .catch(()=>Swal.fire({title:'Gagal',text:'Kesalahan jaringan.',icon:'error'}));
      }});
  }
  function markResolved(ticketId) {
    Swal.fire({ title: 'Tandai resolved?', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya', cancelButtonText: 'Batal' })
      .then(res => { if (res.isConfirmed) {
        fetch(`/admin/tickets/${ticketId}/resolve`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } })
          .then(r => r.json()).then(d => d.success ? Swal.fire({title:'Berhasil',text:'Ditandai resolved',icon:'success'}).then(()=>location.reload()) : Swal.fire({title:'Gagal',text:d.message||'Gagal update.',icon:'error'}))
          .catch(()=>Swal.fire({title:'Gagal',text:'Kesalahan jaringan.',icon:'error'}));
      }});
  }
  function closeTicket(ticketId) {
    Swal.fire({ title: 'Tutup tiket ini?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, tutup', cancelButtonText: 'Batal' })
      .then(res => { if (res.isConfirmed) {
        fetch(`/admin/tickets/${ticketId}/close`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } })
          .then(r => r.json()).then(d => d.success ? Swal.fire({title:'Berhasil',text:'Tiket ditutup',icon:'success'}).then(()=>location.reload()) : Swal.fire({title:'Gagal',text:d.message||'Gagal menutup.',icon:'error'}))
          .catch(()=>Swal.fire({title:'Gagal',text:'Kesalahan jaringan.',icon:'error'}));
      }});
  }
  function reopenTicket(ticketId) {
    Swal.fire({ title: 'Buka kembali tiket ini?', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, buka', cancelButtonText: 'Batal' })
      .then(res => { if (res.isConfirmed) {
        fetch(`/admin/tickets/${ticketId}/reopen`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
          .then(r => r.json()).then(d => d.success ? Swal.fire({title:'Berhasil',text:'Tiket dibuka kembali',icon:'success'}).then(()=>location.reload()) : Swal.fire({title:'Gagal',text:d.message||'Gagal membuka kembali.',icon:'error'}))
          .catch(()=>Swal.fire({title:'Gagal',text:'Kesalahan jaringan.',icon:'error'}));
      }});
  }
</script>
@endsection