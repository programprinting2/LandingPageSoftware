@extends('layouts.softsale')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
    @include('shared.components.admin-login-gate')
    <div id="adminDashboardShell" hidden>
        @include('admin.index')
    </div>
    @include('shared.components.app-dialog')
@endsection
