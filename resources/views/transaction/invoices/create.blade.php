@extends('layouts.app')
@section('content')
    @livewire('transaction.invoice-form', ['invoiceType' => $invoiceType])
@endsection
