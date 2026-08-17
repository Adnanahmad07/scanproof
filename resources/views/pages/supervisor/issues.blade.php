@extends('layouts.supervisor')

@section('title', 'Issues - ScanProof')
@section('page-title', 'Issues')
@section('page-subtitle', 'Manage and assign issues reported from your locations')

@section('content')
    @include('livewire.supervisor.issue-dashboard')
@endsection
