@extends('layouts.app')
@section('content')
    @livewire('transaction.accounting-voucher-form', ['accountingType' => $accountingType])
@endsection
