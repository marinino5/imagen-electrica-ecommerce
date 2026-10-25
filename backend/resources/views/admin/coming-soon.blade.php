@extends('layouts.admin')

@section('title', $title)

@section('breadcrumb')
    <li class="breadcrumb-item">Catálogo</li>
    <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
@endsection

@section('content')
    <div class="page-header">
        <h1>{{ $title }}</h1>
    </div>

    <div class="card">
        <x-empty-state icon="tools" title="Módulo en construcción" message="Este listado estará disponible en la próxima entrega." />
    </div>
@endsection
