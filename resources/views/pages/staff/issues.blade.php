@extends('layouts.staff')

@section('title', 'My Issues - ScanProof')
@section('page-title', 'My Issues')
@section('page-subtitle', 'Issues assigned to you')

@section('content')
    @include('livewire.staff.issue-list')
@endsection
