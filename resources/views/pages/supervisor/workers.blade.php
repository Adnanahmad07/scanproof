@extends('layouts.supervisor')

@section('title', 'Workers - ScanProof')
@section('page-title', 'Workers')
@section('page-subtitle', 'Manage your team members')

@section('content')
    <livewire:supervisor.worker-management />
@endsection
