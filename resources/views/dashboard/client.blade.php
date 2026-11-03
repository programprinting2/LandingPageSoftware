@extends('layouts.softsale')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/client-commerce-modals.css') }}">
@endpush

@section('content')
    @include('client.index')
    @include('shared.components.checkout-modal')
    @include('shared.components.client-license-packages-modal')
    @include('shared.components.client-report-archive-modal')
    @include('shared.components.client-license-devices-modal')
    @include('shared.components.client-license-device-slots-modal')
    @include('shared.components.app-dialog')
@endsection
