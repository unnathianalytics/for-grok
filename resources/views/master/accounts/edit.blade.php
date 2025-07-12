@extends('layouts.app')
@section('content')
    @livewire('master.account-form', ['account' => $account])
@endsection
