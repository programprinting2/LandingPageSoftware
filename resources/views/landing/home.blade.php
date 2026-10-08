@extends('layouts.softsale')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
@endpush

@section('content')
    @include('landing.index')
    @include('shared.components.auth-modal')
    @include('shared.components.app-dialog')
    @if(!empty($liveEdit))
        @include('landing.components.live-edit')
    @endif
@endsection

@if(!empty($liveEdit))
    @push('scripts')
        <script src="{{ asset('js/live-edit.js') }}" defer></script>
    @endpush
@endif
