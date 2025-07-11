@extends('layouts.app')
@php $items = App\Models\Item::orderBy('name')->paginate(20); @endphp
@section('content')
    <div class="container">
        <h4>All Items</h4>
        <input type="text" id="searchInput" autofocus placeholder="Search..." onkeyup="filterTable()" value=""> <a
            class="btn btn-sm btn-primary" href="{{ route('item_create') }}">Add Item</a>
        <table class="table table-bordered table-sm mt-3" id="dataTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Group</th>
                    <th>Opening Stock</th>
                    <th>Unit</th>
                    <th>Sale Price</th>
                    <th>Purchase Price</th>
                    <th>Tax Category</th>
                    <th>HSN</th>
                    <th>Closing Stock</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $itm)
                    <tr x-data @dblclick="window.location='{{ route('item_edit', $itm->id) }}'">
                        <td>{{ $itm->name }}</td>
                        <td>{{ $itm->parent->name ?? '-' }}</td>
                        <td class="text-end">{{ $itm->op_stock_qty ?? '0' }}</td>
                        <td>{{ $itm->baseUom->name }}</td>
                        <td class="text-end">{{ rupees($itm->sale_price) }}</td>
                        <td class="text-end">{{ rupees($itm->purchase_price) }}</td>
                        <td>{{ $itm->taxcategory->name }}</td>
                        <td>{{ $itm->hsn_sac }}</td>
                        <td class="text-end"><strong>{{ $itm->getStock() ?? '0' }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No items found.</td>
                    </tr>
                @endforelse
                @for ($i = $items->count(); $i < 20; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>
        {{ $items->links() }}
    </div>
@endsection
