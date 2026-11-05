{{-- A text/plain body, so nothing here is rendered as markup by a mail client.
     Raw output on purpose: Blade would HTML-escape an apostrophe in a name into
     a character reference, and a plain-text mail has no markup to escape into. --}}
Name: {!! $visitorName !!}
Email: {!! $visitorEmail !!}
Subject: {!! $heading !!}

----

{!! $body !!}

----

Sent through the contact form on {{ config('app.url') }}. This site has no
database, so the message above was never stored — it is only in transit.
