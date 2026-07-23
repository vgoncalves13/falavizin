@props(['user'])

@php
    $avatarUrl = $user->avatar_url
        ? (Str::startsWith($user->avatar_url, ['http://', 'https://'])
            ? $user->avatar_url
            : Storage::url($user->avatar_url))
        : null;
@endphp

@if($avatarUrl)
    <img
        src="{{ $avatarUrl }}"
        alt="Foto de {{ $user->name }}"
        {{ $attributes->class(['shrink-0 rounded-full object-cover bg-stone-100']) }}
    />
@else
    <span
        role="img"
        aria-label="Avatar de {{ $user->name }}"
        {{ $attributes->class(['shrink-0 rounded-full bg-amber-100 text-amber-700 font-bold inline-flex items-center justify-center']) }}
    >{{ mb_substr($user->name, 0, 1) }}</span>
@endif
