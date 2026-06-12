@extends('layouts.staff')

@section('title', 'My Tasks - ScanProof')
@section('page-title', 'My Tasks')
@section('page-subtitle', 'Tasks assigned to you')

@section('content')
    <livewire:staff.task-list />
@endsection
