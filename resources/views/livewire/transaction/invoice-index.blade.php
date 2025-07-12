    @push('body-styles')
        <style>
            body {
                background-color: {{ $bg_color ?? '#FFFFFF' }};
            }
        </style>
    @endpush
    <div class="container">
        <form wire:submit.prevent>
            <div class="row mb-3">
                <div class="col">
                    <input type="date" wire:model.defer="fromDate" class="form-control" placeholder="From Date">
                </div>
                <div class="col">
                    <input type="date" wire:model.defer="toDate" class="form-control" placeholder="To Date">
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-primary" wire:click="$refresh">Filter</button>
                </div>
            </div>
        </form>
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <span class="h5">List of {{ $type->name }}</span> <input type="text" id="searchInput"
                        autofocus placeholder="Search..." onkeyup="filterTable()" value=""> <a
                        class="btn btn-sm btn-primary" href="{{ route('invoice_create', [$type->slug]) }}">Add
                        {{ $type->name }}</a>
                    <table class="table table-bordered table-hover table-sm mt-3" id="dataTable">
                        <thead>
                            <tr>
                                <th style="width: 5%">Series</th>
                                <th style="width: 8%">Date</th>
                                <th style="width: 10%">Invoice#</th>
                                <th style="width: 15%">Account</th>
                                <th style="width: 20%">Item Details</th>
                                <th style="width: 5%">Quantiy</th>
                                <th style="width: 5%">Unit</th>
                                <th style="width: 10%">Price</th>
                                <th style="width: 10%">Amount</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $inv)
                                <tr class="invList" x-data
                                    @dblclick="window.location='{{ route('invoice_edit', [$inv->invoiceType->slug, $inv->id]) }}'">
                                    <td>{{ $inv->series->name }}</td>
                                    <td>{{ $inv->invoice_date }}</td>
                                    <td>{{ $inv->invoice_number }}</td>
                                    <td>{{ $inv->account->name }}</td>
                                    <td>@php $itms = $inv->items @endphp</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-end">
                                        {{ rupees($inv->getInvoiceTotal($inv->id, $inv->invoice_type)) }}
                                    </td>
                                </tr>
                                @foreach ($itms as $it)
                                    <tr x-data
                                        @dblclick="window.location='{{ route('invoice_edit', [$inv->invoiceType->slug, $inv->id]) }}'">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>{{ $it->item->name }}</td>
                                        <td class="text-end">{{ $it->quantity }}</td>
                                        <td>{{ $it->uom->name }}</td>
                                        <td class="text-end">{{ rupees($it->price) }}</td>
                                        <td class="text-end">{{ rupees($it->price * $it->quantity) }}</td>
                                        <td></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
