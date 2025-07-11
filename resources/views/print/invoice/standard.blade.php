@extends('layouts.print')
@section('content')
    @livewire('print.invoice.standard', ['invoiceId' => $invoiceId, 'formatId' => $format])
@endsection
