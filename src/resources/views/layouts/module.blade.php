{{-- Main layout for the cost calculator module --}}
@extends('layouts.app')

@section('title', $title ?? __('td-cost-calcultaror::messages.module_name'))

@section('pageHeader')
    <x-page-header pageHeaderTitle="{{ $title ?? __('td-cost-calcultaror::messages.module_name') }}"></x-page-header>
@endsection

@section('content')
    <div class="container-fluid">
        @if(isset($subtitle))
            <p class="mb-3">{{ $subtitle }}</p>
        @endif
        
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        
        {{-- Module Navigation --}}
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('td-cost-calcultaror.dashboard') ? 'active' : '' }}" 
                   href="{{ route('td-cost-calcultaror.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> {{ __('td-cost-calcultaror::messages.dashboard') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('td-cost-calcultaror.cost-items.*') ? 'active' : '' }}" 
                   href="{{ route('td-cost-calcultaror.cost-items.index') }}">
                    <i class="fas fa-coins"></i> {{ __('td-cost-calcultaror::messages.cost_items') }}
                </a>
            </li>
            <li class="nav-item">
        {{-- Navigation --}}
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('td-cost-calcultaror.dashboard') ? 'active' : '' }}" 
                   href="{{ route('td-cost-calcultaror.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> {{ __('td-cost-calcultaror::messages.dashboard') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('td-cost-calcultaror.cost-items.*') ? 'active' : '' }}" 
                   href="{{ route('td-cost-calcultaror.cost-items.index') }}">
                    <i class="fas fa-coins"></i> {{ __('td-cost-calcultaror::messages.cost_items') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('td-cost-calcultaror.products.*') ? 'active' : '' }}" 
                   href="{{ route('td-cost-calcultaror.products.index') }}">
                    <i class="fas fa-box"></i> {{ __('td-cost-calcultaror::messages.products') }}
                </a>
            </li>
        </ul>
        
        {{-- Main Content --}}
        @yield('module-content')
    </div>
@endsection
