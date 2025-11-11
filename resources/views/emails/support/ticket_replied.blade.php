@component('mail::message')
# Balasan Baru pada Ticket

Halo {{ $user->name }},

Ada balasan baru pada ticket Anda.

@component('mail::panel')
Nomor: #{{ $ticket->ticket_number }}

Subjek: {{ $ticket->subject }}
@endcomponent

@component('mail::panel')
Pesan:

{!! $reply->message !!}
@endcomponent

@component('mail::button', ['url' => route('client.tickets.show', $ticket)])
Lihat Ticket
@endcomponent

Terima kasih,
{{ config('app.name') }}
@endcomponent