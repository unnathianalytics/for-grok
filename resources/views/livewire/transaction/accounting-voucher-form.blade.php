<div class="container">
    <div class="row">
        <div class="col-lg-3">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <div class="col-lg-9">
            <form wire:submit.prevent="save" class="space-y-4">
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label>Transaction Date</label>
                        <input type="date" wire:model="accountingVoucherData.transaction_date"
                            class="form-control" />
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label>Voucher Number</label>
                        <input type="text" wire:model="accountingVoucherData.voucher_number" class="form-control" />
                    </div>
                    <div class="col-lg-12 mb-3">
                        <label>Notes</label>
                        <input wire:model="accountingVoucherData.voucher_notes" class="form-control">
                    </div>
                </div>
                <hr />

                <h4>Voucher Items</h4>

                <div class="table-responsive scrollable-table" style="min-height: 150px; max-height: 200px;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cr/Dr</th>
                                <th>Account</th>
                                <th>Dr Amount</th>
                                <th>Cr Amount</th>
                                <th>Short Narration</th>
                                <th style="width: 5%;">Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($accountingVoucherItems as $index => $item)
                                <tr>
                                    <td>
                                        <select wire:model="accountingVoucherItems.{{ $index }}.avr_item_type"
                                            required>
                                            <option value="">-- Select Type --</option>
                                            <option value="C">Credit</option>
                                            <option value="D">Debit</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select wire:model="accountingVoucherItems.{{ $index }}.cr_account_id"
                                            class="form-control">
                                            <option value="">-- Cr Account --</option>
                                            @foreach ($cr_accounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->name }} |
                                                    {{ $acc->parent->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01"
                                            wire:model="accountingVoucherItems.{{ $index }}.cr_amount"
                                            class="form-control" placeholder="Cr Amount" />
                                    </td>
                                    <td>
                                        <input type="number" step="0.01"
                                            wire:model="accountingVoucherItems.{{ $index }}.dr_amount"
                                            class="form-control" placeholder="Cr Amount" />
                                    </td>
                                    <td>
                                        <input type="text"
                                            wire:model="accountingVoucherItems.{{ $index }}.description"
                                            class="form-control" placeholder="Short Narration" />
                                    </td>

                                    <td></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div>
                        <button type="button" wire:click="addRow" class="btn btn-secondary">+ Add Row</button>
                    </div>

                    @error('accountingVoucherItems')
                        <div class="text-red-500">{{ $message }}</div>
                    @enderror

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Save Voucher</button>
                    </div>
            </form>
        </div>

    </div>

</div>
