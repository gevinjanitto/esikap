@props(['s', 'tid' => null])
<span class="badge {{ \App\Support\Esakip::badge($s) }}" @if ($tid) data-testid="{{ $tid }}" @endif>{{ \App\Support\Esakip::label($s) }}</span>
