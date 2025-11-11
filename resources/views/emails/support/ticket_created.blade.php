@component('mail::message')
# Ticket Dibuat

Halo {{ $user->name }},

Ticket dukungan Anda telah dibuat.

@component('mail::panel')
Nomor: #{{ $ticket->ticket_number }}

Subjek: {{ $ticket->subject }}

Prioritas: {{ ucfirst($ticket->priority) }}

Status: {{ ucfirst(str_replace('_',' ', $ticket->status)) }}
@endcomponent

@component('mail::button', ['url' => route('client.tickets.show', $ticket)])
Lihat Ticket
@endcomponent

Terima kasih,
{{ config('app.name') }}
@endcomponent