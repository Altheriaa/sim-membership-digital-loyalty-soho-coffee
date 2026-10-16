@props(['name', 'label', 'checked' => false, 'hint' => null])

@php
    $id = $attributes->get('id', $name);
    $isChecked = (bool) old($name, $checked);
@endphp

<div class="flex flex-col gap-1">
    <label for="{{ $id }}" class="flex min-h-11 cursor-pointer items-center gap-3 text-sm sm:min-h-10">
        <input type="hidden" name="{{ $name }}" value="0">
        <april:input type="checkbox" :attributes="$attributes->merge(['id' => $id, 'name' => $name, 'value' => '1', 'checked' => $isChecked ? true : null])" />
        <span>{{ $label }}</span>
    </label>
    @if ($hint)
        <p class="pl-7 text-xs text-muted-foreground">{{ $hint }}</p>
    @endif
</div>
