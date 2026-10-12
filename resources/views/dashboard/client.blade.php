@extends('layouts.softsale')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
    @include('client.index')
    @include('shared.components.app-dialog')
@endsection
