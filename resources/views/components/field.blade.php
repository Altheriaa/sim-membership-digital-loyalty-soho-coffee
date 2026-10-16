@props([
    'name',
    'label',
    'type' => 'text',
    'as' => 'input',
    'hint' => null,
    'value' => null,
    'optional' => false,
    'wrapperClass' => '',
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $describedBy = trim(($hint ? "{$id}-hint " : '').($hasError ? "{$id}-error" : ''));

    $control = $attributes->merge([
        'id' => $id,
        'name' => $name,
        'aria-invalid' => $hasError ? 'true' : null,
        'aria-describedby' => $describedBy !== '' ? $describedBy : null,
    ]);

    if ($as === 'input' && $type !== 'password' && $type !== 'file') {
        $control = $control->merge(['value' => old($name, $value)]);
    }
@endphp

<div class="flex flex-col gap-1.5 {{ $wrapperClass }}">
    <april:label for="{{ $id }}">
        {{ $label }}@if ($optional) <span class="font-normal text-muted-foreground">(opsional)</span>@endif
    </april:label>

    @if ($as === 'select')
        <april:native-select :attributes="$control->class('h-11 w-full sm:h-10')">
            {{ $slot }}
        </april:native-select>
    @else
        <april:input type="{{ $type }}" :attributes="$control->class('h-11 sm:h-10')" />
    @endif

    @if ($hint)
        <p id="{{ $id }}-hint" class="text-xs text-muted-foreground">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $id }}-error" class="text-sm text-destructive">{{ $message }}</p>
    @enderror
</div>
