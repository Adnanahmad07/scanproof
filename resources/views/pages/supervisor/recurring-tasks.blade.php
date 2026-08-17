@extends('layouts.supervisor')

@section('title', 'Recurring Tasks - ScanProof')
@section('page-title', 'Recurring Tasks')
@section('page-subtitle', 'Automate daily, weekly, or monthly tasks')

@section('content')
    <livewire:supervisor.recurring-task-management />
@endsection
