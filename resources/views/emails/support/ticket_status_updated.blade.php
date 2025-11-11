@component('mail::message')
# Status Ticket Diperbarui

Halo {{ $user->name }},

Status ticket Anda telah diperbarui dari **{{ ucfirst(str_replace('_',' ', $oldStatus)) }}** menjadi **{{ ucfirst(str_replace('_',' ', $newStatus)) }}**.

@component('mail::panel')
Nomor: #{{ $ticket->ticket_number }}

Subjek: {{ $ticket->subject }}
@endcomponent

@component('mail::button', ['url' => route('client.tickets.show', $ticket)])
Lihat Ticket
@endcomponent

Terima kasih,
{{ config('app.name') }}
@endcomponent