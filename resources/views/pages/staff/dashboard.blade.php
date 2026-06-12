@extends('layouts.staff')

@section('title', 'Dashboard - ScanProof')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back, {{ auth()->user()->name }}')

@section('content')
    <livewire:staff.dashboard />
@endsection
