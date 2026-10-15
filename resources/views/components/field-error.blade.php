@props(['name', 'client' => false])
@error($name)
    <small class="field-error" id="{{ str_replace(['.', '[', ']'], '-', $name) }}-error" role="alert" data-field-error>{{ $message }}</small>
@else
    @if($client)<small class="field-error" data-field-error hidden></small>@endif
@enderror
