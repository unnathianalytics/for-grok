<style>
    body {
        font-size: 11px;
        font-family: Tahoma, Helvetica, Arial, sans-serif !important
    }

    .table-sm th,
    .table-sm td {
        padding: 4px 6px;
    }

    .border-top-dashed {
        border-top: 1px dashed #000;
    }

    .signature-box {
        min-height: 50px;
    }
</style>
<div class="border p-2">
    @php
        $company = auth()->user()->company;
    @endphp
    <div class="text-center mb-2">
        <small><strong>Original Copy</strong> (This copy does not entitle the holder to claim Input Tax Credit)</small>
    </div>

    <div class="text-center mb-3">
        <h5 class="mb-0">{{ $company['name'] }}</h5>
        <p class="mb-0 small">{{ $company['address'] }}, {{ $company['city'] }} - {{ $company['pincode'] }}</p>
        <p class="mb-0 small">GSTIN : {{ $company['gstin'] }}</p>
        <p class="mb-0 small">Tel.: {{ $company['phone'] }} | Email: {{ $company['email'] }}</p>
    </div>

    <table class="table table-bordred mb-2">
        <tbody>
            <tr>
                <td class="border">
                    <p class="mb-0"><strong>Invoice No.:</strong> {{ $invoice->invoice_number }}</p>
                    <p class="mb-0"><strong>Dated:</strong> {{ $invoice->invoice_date }}
                        ({{ $invoice->invoice_time }})</p>
                    <p class="mb-0"><strong>Billed to:</strong> {{ $invoice->account->name }}</p>
                    <p class="mb-0"><strong>Address:</strong> {{ $invoice->account->address }}</p>
                    <p class="mb-0"><strong>GSTIN / UIN:{{ $invoice->account->gstin }}</strong></p>
                </td>
                <td class="border text-sm-end">
                    <p class="mb-0"><strong>Place of Supply:</strong> Karnataka (29)</p>
                    <p class="mb-0"><strong>Reverse Charge:</strong> N</p>
                    <p class="mb-0"><strong>Shipped to:</strong> {{ $invoice->account->name }}</p>
                    <p class="mb-0"><strong>Address:</strong> {{ $invoice->account->address }}</p>
                    <p class="mb-0"><strong>GSTIN / UIN:{{ $invoice->account->gstin }}</strong></p>
                </td>
            </tr>
        </tbody>
    </table>


    <table class="table table-bordered table-sm mb-2">
        <thead class="table-light">
            <tr>
                <th style="width: 30px;">S.N.</th>
                <th>Description of Goods</th>
                <th>HSN/SAC Code</th>
                <th class="text-end">Qty</th>
                <th>Unit</th>
                <th class="text-end">Price</th>
                <th class="text-end">Tax</th>
                <th class="text-end">CGST</th>
                <th class="text-end">CGST Amt</th>
                <th class="text-end">SGST</th>
                <th class="text-end">SGST Amt</th>
                <th class="text-end">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $loop->index + 1 }}</td>
                    <td>{{ $item->item->name }}
                        @if ($item->item->has_serial_number)
                            <br>
                            @foreach ($item->serial_numbers as $sl)
                                <small>{{ $sl->serial_number }}</small>,
                            @endforeach
                        @endif
                    </td>
                    <td>{{ $item->item->hsn_sac }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td>Pcs.</td>
                    <td class="text-end">{{ rupees($item->price) }}</td>
                    <td class="text-end">{{ rupees($item->item->taxCategory->name) }}</td>
                    <td class="text-end">{{ $item->cgst_pct }}%</td>
                    <td class="text-end">{{ rupees($item->cgst_amt) }}</td>
                    <td class="text-end">{{ $item->sgst_pct }}%</td>
                    <td class="text-end">{{ rupees($item->sgst_amt) }}</td>
                    <td class="text-end">{{ rupees($item->item_amount) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="12" style="height: 1px; border:1px solid #cccccc; padding: 0; margin: 0px;"></td>
            </tr>
            @foreach ($invoice->invoiceSundries as $sundry)
                <tr>
                    <td colspan="8"></td>
                    <td>{{ $sundry->amount_adjustment == '+' ? '(Add)' : '(Less)' }}</td>
                    <td colspan="2" style="font-style: italic">{{ $sundry->billSundry->name }}</td>
                    <td class="text-end">{{ rupees($sundry->sundry_amount) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="12" style="height: 1px; border:1px solid #cccccc; padding: 0; margin: 0px;"></td>
            </tr>
            <tr>
                <td colspan="4" class="text-end"><strong>Grand Total</strong></td>
                <td class="text-end"><strong>{{ collect($invoice->items)->sum('quantity') }}</strong></td>
                <td></td>
                <td colspan="5"></td>
                <td class="text-end"><strong>{{ rupees($invoiceAmount) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <table class="table table-bordered table-sm mb-2">
        <thead class="table-light">
            <tr>
                <th>Tax Rate</th>
                <th>Taxable Amt.</th>
                <th>CGST Amt.</th>
                <th>SGST Amt.</th>
                <th>Total Tax</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>18%</td>
                <td class="text-end">3,389.84</td>
                <td class="text-end">305.08</td>
                <td class="text-end">305.08</td>
                <td class="text-end">610.16</td>
            </tr>
        </tbody>
    </table>
    {{-- {{ dd($invoiceAmount) }} --}}
    <p class="mb-1"><strong>In words :</strong>
        {{ $invoiceAmount }}
        only
    </p>
    <p class="mb-1">- {{ rupees($invoiceAmount) }}</p>

    <p class="mb-1"><strong>Bank Details:</strong> VIKAS COMPUTERS, {{ $format->bank_name }}</p>
    <p class="mb-1">A/c No.: {{ $format['bank_account_number'] }} | IFSC: {{ $format['bank_ifsc_code'] }} | UPI:
        {{ $format['bank_upi_id'] }}</p>

    <div class="row mt-2">
        <div class="col-sm-6">
            <p class="mb-1"><strong>Govt. Recipient ID:</strong> 2900222766</p>
            <ul class="small ps-3 mb-1">
                <li>{{ $format->terms_conditions1 }}</li>
                <li>{{ $format->terms_conditions2 }}</li>
                <li>{{ $format->terms_conditions3 }}</li>
                <li>{{ $format->terms_conditions4 }}</li>
                <li>{{ $format->terms_conditions5 }}</li>
                <li>{{ $format->terms_conditions6 }}</li>
            </ul>
        </div>
        <div class="col-sm-6 text-end">
            <div class="border signature-box mb-1"></div>
            <p class="mb-0">Receiver's Signature</p>
            <p class="mt-4 mb-0 small">For {{ $company['name'] }}</p>
            <p class="mb-0 small">Authorised Signatory</p>
        </div>
    </div>
</div>
