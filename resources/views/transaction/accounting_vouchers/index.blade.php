@extends('layouts.app')
@section('content')
    @livewire('transaction.invoice-index', [
        'invoice_type_id' => $invoice_type_id,
    ])
@endsection
