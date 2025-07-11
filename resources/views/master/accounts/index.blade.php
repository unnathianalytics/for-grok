@extends('layouts.app')
@php $accounts = App\Models\Account::orderBy('name')->get(); @endphp
@section('content')
    <div class="container">
        <h4>All Accounts</h4>
        <input type="text" id="searchInput" autofocus placeholder="Search..." onkeyup="filterTable()" value=""> <a
            class="btn btn-sm btn-primary" href="{{ route('account_create') }}">Add Account</a>
        <table class="table table-bordered table-sm mt-3" id="dataTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Group</th>
                    <th>Op Balane</th>
                    <th>Cr/Dr</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($accounts as $act)
                    <tr style="background-color: {{ $act->cr_dr == 'cr' ? 'lightred' : '' }}" x-data
                        @dblclick="window.location='{{ route('account_edit', $act->id) }}'">
                        <td>{{ $act->name }}</td>
                        <td>{{ $act->parent->name ?? '-' }}</td>
                        <td class="text-end">{{ rupees($act->op_balance) }}</td>
                        <td>{{ ucwords($act->cr_dr) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No accounts found.</td>
                    </tr>
                @endforelse
                @for ($i = $accounts->count(); $i < 20; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
@endsection
