@props([
    'type' => 'info',
    'title' => null,
    'messages' => [],
    'dismissible' => true,
    'autoDismiss' => false,
])
@php
    $messageList = collect(is_array($messages) ? $messages : [$messages])->filter()->values();
    $titles = [
        'success' => 'Berhasil',
        'error' => 'Periksa kembali',
        'warning' => 'Perlu perhatian',
        'info' => 'Informasi',
    ];
    $icons = ['success' => 'check', 'error' => 'alert', 'warning' => 'alert', 'info' => 'info'];
@endphp
@if($messageList->isNotEmpty())
    <section class="notice notice-{{ $type }}" role="{{ in_array($type, ['error', 'warning'], true) ? 'alert' : 'status' }}" data-notice @if($autoDismiss) data-auto-dismiss="6000" @endif>
        <span class="notice-icon">@include('partials.icon', ['name' => $icons[$type] ?? 'info'])</span>
        <div class="notice-content">
            <strong>{{ $title ?: ($titles[$type] ?? 'Informasi') }}</strong>
            @if($messageList->count() === 1)
                <p>{{ $messageList->first() }}</p>
            @else
                <ul>@foreach($messageList as $message)<li>{{ $message }}</li>@endforeach</ul>
            @endif
        </div>
        @if($dismissible)
            <button class="notice-close" type="button" data-notice-close aria-label="Tutup notifikasi">@include('partials.icon', ['name' => 'close'])</button>
        @endif
    </section>
@endif
