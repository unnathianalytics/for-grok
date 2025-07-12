@push('body-styles')
    <style>
        body {
            background-color: {{ $bg_color ?? '#FFFFFF' }};
        }

        .item-id-input {
            width: 0;
            height: 0;
            border: none;
            padding: 0;
            margin: 0;
            position: absolute;
            opacity: 0;
        }
    </style>
@endpush
<div class="container-fluid">
    <form wire:submit="save" id="invoice-form">
        <input type="hidden" wire:model="invoiceData.invoice_type_id">
        @error('invoiceData.invoice_type_id')
            <span class="text-danger">{{ $message }}</span>
        @enderror
        <div class="row mb-3">
            <div class="col-lg-2">
                <div class="title-breadcrumb">
                    <h3>{{ $breadcrumb_header ?? 'Select from menu' }}</h3>
                </div>
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
            <div class="col-lg-10 card">
                <div class="row card-body">
                    <div class="col-lg-2 mb-3">
                        <label for="voucher_series_id" class="form-label">Series</label>
                        <select wire:model="invoiceData.voucher_series_id" class="form-control" id="voucher_series_id">
                            <option value="">Select</option>
                            @foreach ($voucherSeries as $series)
                                <option value="{{ $series->id }}">{{ $series->name }}</option>
                            @endforeach
                        </select>
                        @error('invoiceData.voucher_series_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-lg-1 mb-3">
                        <label for="invoice_date" class="form-label">Date</label>
                        <input type="text" id="invoice_date" class="focusable form-control date-iso"
                            wire:model.defer="invoiceData.invoice_date" placeholder="yyyy-mm-dd"
                            data-max='{{ date('Y-m-d') }}' maxlength="10">
                    </div>
                    <div class="col-lg-2 mb-3">
                        <label for="invoice_number" class="form-label">Invoice Number</label>
                        <input type="text" id="invoice_number" class="form-control focusable"
                            wire:model="invoiceData.invoice_number">
                    </div>
                    <div class="col-lg-2 mb-3">
                        <label for="tax_type_id" class="form-label">Tax Type</label>
                        <select wire:model="invoiceData.tax_type_id" class="form-select form-select-sm focusable"
                            id="tax_type_id">
                            <option value="">Select</option>
                            @foreach ($taxTypes as $taxType)
                                <option value="{{ $taxType->id }}">{{ $taxType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 mb-3">
                        <label for="account_id" class="form-label">Party</label>
                        <select wire:model="invoiceData.account_id" class="form-select form-select-sm focusable"
                            id="account_id">
                            <option value="">Select</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 mb-3">
                        <label for="mc_id" class="form-label">Material Center</label>
                        <select wire:model="mc_id" class="form-select form-select-sm focusable" id="mc_id">
                            <option value="">Select</option>
                            @foreach ($allMCs as $mc)
                                <option value="{{ $mc->id }}">{{ $mc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="table-responsive scrollable-table" style="min-height: 150px; max-height: 200px;">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th style="width: 2%">#</th>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>MRP</th>
                                    <th>Price (Rs.)</th>
                                    <th>Dis%</th>
                                    <th>Dis (Total)</th>
                                    <th>GST% (C+S+Cess)</th>
                                    <th style="width: 10%">Amount (Rs.)</th>
                                    <th style="width: 10%;">SN</th>
                                    <th style="width: 5%;">Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoiceItems as $index => $item)
                                    <tr wire:key="row-{{ $index }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td style="width: 20%">
                                            <div wire:ignore>
                                                <input type="text" class="form-control item-autocomplete"
                                                    data-index="{{ $index }}"
                                                    id="item_name_{{ $index }}"
                                                    value="{{ $item['item_name'] ?? '' }}"
                                                    placeholder="Search item...">
                                            </div>
                                            <input type="text" class="item-id-input"
                                                id="item_id_{{ $index }}"
                                                wire:model.debounce.500ms="invoiceItems.{{ $index }}.item_id">
                                        </td>
                                        <td style="width: 5%">
                                            <input type="number" style="text-align:right" step="0.01"
                                                placeholder="Qty" class="form-control focusable"
                                                id="quantity_{{ $index }}"
                                                wire:model.lazy="invoiceItems.{{ $index }}.quantity">
                                            @error('invoiceItems.' . $index . '.quantity')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        <td style="width: 5%">
                                            <select class="form-control focusable"
                                                wire:model="invoiceItems.{{ $index }}.uom_id">
                                                <option value=""></option>
                                                @if (!empty($itemUomOptions[$index]))
                                                    @foreach ($itemUomOptions[$index] as $uom1)
                                                        <option value="{{ $uom1['id'] }}">{{ $uom1['name'] }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" style="text-align:right" step="0.01"
                                                class="form-control focusable"
                                                wire:model.lazy="invoiceItems.{{ $index }}.max_retail_price">
                                        </td>
                                        <td>
                                            <input type="number" style="text-align:right" step="0.01"
                                                placeholder="Price" class="form-control focusable"
                                                wire:model.lazy="invoiceItems.{{ $index }}.price">
                                            <input type="text" style="display: none"
                                                wire:model.lazy="invoiceItems.{{ $index }}.tax_category_id">
                                        </td>
                                        <td>
                                            <input type="number" style="text-align:right" step="0.01"
                                                placeholder="Dis%" class="form-control focusable"
                                                wire:model.lazy="invoiceItems.{{ $index }}.discount_pct">
                                        </td>
                                        <td>
                                            <input type="number" style="text-align:right" step="0.01"
                                                placeholder="DisAmt" class="form-control focusable"
                                                wire:model.lazy="invoiceItems.{{ $index }}.discount_amt">
                                        </td>
                                        <td>
                                            <input type="text" style="text-align:right" placeholder="GST%"
                                                wire:model.lazy="invoiceItems.{{ $index }}.gst_percent"
                                                disabled readonly>
                                        </td>
                                        <td>
                                            <input type="number" style="text-align:right" step="0.01"
                                                placeholder="Amount" class="form-control focusable amount-input"
                                                id="amount_{{ $index }}"
                                                wire:model.lazy="invoiceItems.{{ $index }}.item_amount">
                                        </td>
                                        <td>
                                            @if ($invoiceTypeModel->sn_input_type !== 'none' && $item['has_serial_number'] && !empty($item['quantity']))
                                                <button type="button" class="btn btn-sm btn-primary"
                                                    wire:click="openSerialNumberModal({{ $index }})">
                                                    Add SNs
                                                </button>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-btn"
                                                wire:click="removeItemRow({{ $index }})"><span
                                                    class="material-symbols-outlined">
                                                    delete
                                                </span></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <td class="m-0 border-0">
                                    <div class="m-0 border-0">
                                        <button type="button" class="btn btn-primary btn-sm"
                                            wire:click="addItemRow">Add Item</button>
                                    </div>
                                </td>
                                <th colspan="9">Sub Total</th>
                                <th style="width: 10%; text-align: end;font-weight:bold; font-size:.9rem;">
                                    {{ Number::currency($itemSubTotal, 'INR') }}</th>
                                <th style="width: 5%;"></th>
                            </tr>
                        </thead>
                    </table>

                    <hr>
                    <div class="row">
                        <div class="col-lg-6">
                            @if ($this->current_stock)
                                <small class="text-sm text-danger">(Cur. Stock = {{ $this->current_stock }}
                                    {{ $this->current_uom }})</small>
                            @endif
                        </div>
                        <div class="col-lg-6">
                            <div class="table-responsive scrollable-table">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Bill Sundry</th>
                                            <th style="width: 20%">Amount (Rs.)</th>
                                            мед <th style="width: 5%;">Remove</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sundries as $index => $sundry)
                                            <tr wire:key="sundry-{{ $index }}">
                                                <td>
                                                    <input type="text" readonly class="form-control"
                                                        wire:model="sundries.{{ $index }}.amount_adjustment"
                                                        placeholder="Type">
                                                </td>
                                                <td>
                                                    <select id="sundry-{{ $index }}-select"
                                                        wire:model.lazy="sundries.{{ $index }}.bill_sundry_id"
                                                        class="form-select form-select-sm">
                                                        <option value="">-- Select --</option>
                                                        @foreach ($allBillSundries as $bs)
                                                            <option value="{{ $bs->id }}">
                                                                ({{ $bs->adjustment == '+' ? 'Add' : 'Less' }})
                                                                {{ $bs->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" style="text-align:right"
                                                        placeholder="Amount"
                                                        id="sundry-{{ $index }}-sundry_amount" step="0.01"
                                                        class="form-control"
                                                        wire:model.lazy="sundries.{{ $index }}.sundry_amount">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger remove-btn"
                                                        wire:click="removeSundry({{ $index }})">Delete</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <thead>
                                        <tr>
                                            <th class="border-0">
                                                <button type="button" class="btn btn-primary btn-sm m-2"
                                                    wire:click="addSundry">Add BillSundry</button>
                                            </th>
                                            <th>Bill Sundry Sub Total</th>
                                            <th style="text-align: end;font-weight:bold; font-size:.9rem;">
                                                {{ Number::currency($sundrySubTotal, 'INR') }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <thead>
                                        <tr>
                                            <th colspan="2">Grand Total</th>
                                            <th
                                                style="width: 10%; text-align: end;font-weight:bold; font-size:1.2rem;">
                                                {{ Number::currency($sundrySubTotal + $itemSubTotal, 'INR') }}</th>
                                            <th style="width: 5%;"></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-9"></div>
                        <div class="col-lg-3">
                            <button type="submit" class="btn btn-primary btn-sm m-3">
                                {{ $invoice->id ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Serial Number Modal -->
            @if ($currentItemIndex !== null && $invoiceTypeModel->sn_input_type !== 'none')
                <div class="modal fade show" style="display: block;" tabindex="-1" wire:ignore.self>
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Serial Numbers for
                                    {{ $invoiceItems[$currentItemIndex]['item_name'] }}</h5>
                                <button type="button" class="btn-close" wire:click="$set('currentItemIndex', null)"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Serial Number</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoiceItems[$currentItemIndex]['serial_numbers'] as $i => $sn)
                                            <tr>
                                                @if ($invoiceTypeModel->sn_input_type === 'input')
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            placeholder="Serial Number"
                                                            wire:model="invoiceItems.{{ $currentItemIndex }}.serial_numbers.{{ $i }}.serial_number"
                                                            @if ($sn['is_used']) disabled @endif />
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control"
                                                            placeholder="Description"
                                                            wire:model="invoiceItems.{{ $currentItemIndex }}.serial_numbers.{{ $i }}.description"
                                                            @if ($sn['is_used']) disabled @endif />
                                                    </td>
                                                @elseif ($invoiceTypeModel->sn_input_type === 'select')
                                                    <td>
                                                        <select class="form-select form-select-sm"
                                                            wire:model="invoiceItems.{{ $currentItemIndex }}.serial_numbers.{{ $i }}.id">
                                                            <option value="">Select Serial Number</option>
                                                            @php
                                                                $allowedCategories = [];
                                                                if (
                                                                    $invoiceTypeModel->transaction_category ===
                                                                        'return' &&
                                                                    in_array($invoiceTypeModel->slug, ['sale-return'])
                                                                ) {
                                                                    $allowedCategories = ['sale'];
                                                                } elseif (
                                                                    $invoiceTypeModel->transaction_category ===
                                                                        'return' &&
                                                                    in_array($invoiceTypeModel->slug, [
                                                                        'purchase-return',
                                                                    ])
                                                                ) {
                                                                    $allowedCategories = ['purchase'];
                                                                } elseif (
                                                                    $invoiceTypeModel->transaction_category ===
                                                                    'material'
                                                                ) {
                                                                    $allowedCategories = ['return'];
                                                                }
                                                            @endphp
                                                            @foreach (\App\Models\SerialNumber::where('item_id', $invoiceItems[$currentItemIndex]['item_id'])->when(
            $invoiceTypeModel->in_out === 'inward',
            fn($query) => $query->whereIn(
                'id',
                \App\Models\InvoiceItemSerialNumber::whereIn('invoice_item_id', $invoice->items->pluck('id'))->pluck('serial_number_id')->merge(\App\Models\SerialNumber::whereNotNull('invoice_item_id')->pluck('id')),
            ),
        )->when(
            $invoiceTypeModel->in_out === 'outward',
            fn($query) => $query->where(function ($q) use ($invoice, $index) {
                $q->whereNull('invoice_item_id')->orWhereIn('invoice_item_id', $invoice->items->pluck('id'));
            }),
        )->when(!empty($allowedCategories), fn($query) => $query->whereHas('invoiceItem.invoice.invoiceType', fn($q) => $q->whereIn('transaction_category', $allowedCategories)))->with('invoiceItem.invoice')->get() as $sn)
                                                                <option value="{{ $sn->id }}"
                                                                    {{ $sn->id == $invoiceItems[$currentItemIndex]['serial_numbers'][$i]['id'] ? 'selected' : '' }}>
                                                                    {{ $sn->serial_number }}
                                                                    @if ($invoiceTypeModel->in_out === 'inward' && $sn->invoiceItem && $sn->invoiceItem->invoice)
                                                                        (Invoice
                                                                        #{{ $sn->invoiceItem->invoice->invoice_number }},
                                                                        {{ $sn->invoiceItem->invoice->invoice_date }})
                                                                    @endif
                                                                    @if ($sn->description)
                                                                        - {{ $sn->description }}
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" readonly
                                                            value="{{ $sn['description'] ?? '' }}" />
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-secondary"
                                    wire:click="$set('currentItemIndex', null)">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-backdrop fade show"></div>
            @endif
    </form>
    @if ($invoice->id)
        <form wire:submit="printInvoice">
            <select wire:model="printFormats">
                @foreach ($printFormats as $printFormat)
                    <option value="{{ $printFormat['id'] }}">{{ $printFormat['name'] }}</option>
                @endforeach
            </select>
            <button type="submit">Print</button>
        </form>
    @endif
</div>
@push('scripts')
    <script>
        (function() {
            if (window.invoiceFormScriptInitialized) {
                return;
            }
            window.invoiceFormScriptInitialized = true;

            let listenerBound = false;

            function bindAutocomplete($elements) {
                if (typeof $.fn.autocomplete === 'undefined') {
                    console.error('jQuery UI Autocomplete is not loaded');
                    return;
                }
                $elements.each(function() {
                    const $input = $(this);
                    if ($input.hasClass('autocomplete-bound')) return;
                    const index = $input.data('index');
                    $input.addClass('autocomplete-bound');
                    $input.autocomplete({
                        source: function(request, response) {
                            $.get("{{ route('autocomplete.items') }}", {
                                    term: request.term
                                })
                                .done(function(data) {
                                    response(data);
                                })
                                .fail(function(jqXHR, textStatus, errorThrown) {
                                    response([]);
                                });
                        },
                        minLength: 2,
                        select: function(event, ui) {
                            const $textInput = $(`#item_id_${index}`);
                            const $quantityInput = $(`#quantity_${index}`);
                            setTimeout(() => {
                                $textInput.val(ui.item.value);
                                const inputEvent = new Event('input', {
                                    bubbles: true
                                });
                                $textInput[0].dispatchEvent(inputEvent);
                                Livewire.dispatchTo('transaction.invoice-form',
                                    'update', {
                                        name: `invoiceItems.${index}.item_id`,
                                        value: ui.item.value
                                    });
                                $quantityInput.focus();
                            }, 50);
                            $input.val(ui.item.label);
                            return false;
                        }
                    });
                });
            }

            function handleEnterKey(e) {
                if (e.key === 'Enter' && e.target.classList.contains('focusable')) {
                    e.preventDefault();
                    const input = e.target;
                    if (input.classList.contains('amount-input')) {
                        const confirmAdd = window.confirm('Add another item?');
                        if (confirmAdd) {
                            try {
                                const component = Livewire.find(document.querySelector('[wire\\:id]').getAttribute(
                                    'wire:id'));
                                if (component) {
                                    component.call('addItemRow');
                                } else {
                                    console.error('Livewire component not found');
                                }
                                Livewire.dispatchTo('transaction.invoice-form', 'addItemRow');
                            } catch (error) {
                                console.error('Failed to dispatch addItemRow:', error);
                            }
                        }
                    } else {
                        const focusableInputs = document.querySelectorAll('.focusable');
                        const currentIndex = Array.from(focusableInputs).indexOf(input);
                        const nextIndex = currentIndex + 1;
                        if (nextIndex < focusableInputs.length) {
                            const nextInput = focusableInputs[nextIndex];
                            nextInput.focus();
                        }
                    }
                }
            }

            function initializeEnterKeyNavigation() {
                const form = document.querySelector('#invoice-form');
                if (!form) {
                    return;
                }
                if (listenerBound) {
                    return;
                }
                form.addEventListener('keydown', handleEnterKey);
                listenerBound = true;
            }

            document.addEventListener('DOMContentLoaded', function() {
                bindAutocomplete($('.item-autocomplete:not(.autocomplete-bound)'));
                initializeEnterKeyNavigation();
            });

            document.addEventListener('livewire:updated', function() {
                bindAutocomplete($('.item-autocomplete:not(.autocomplete-bound)'));
                initializeEnterKeyNavigation();
            });

            document.addEventListener('livewire:navigated', function() {
                bindAutocomplete($('.item-autocomplete:not(.autocomplete-bound)'));
                initializeEnterKeyNavigation();
            });

            window.addEventListener('item-row-added', () => {
                setTimeout(() => {
                    const $newInputs = $('.item-autocomplete:not(.autocomplete-bound)');
                    if ($newInputs.length > 0) {
                        bindAutocomplete($newInputs);
                        const newIndex = $newInputs.first().data('index');
                        const $newAutocomplete = $(`#item_name_${newIndex}`);
                        $newAutocomplete.focus();
                    }
                }, 100);
            });

            window.addEventListener('open-serial-number-modal', function() {
                const modal = document.querySelector('.modal');
                if (modal) {
                    modal.classList.add('show');
                    modal.style.display = 'block';
                    document.querySelector('.modal-backdrop').classList.add('show');
                }
            });

            window.addEventListener('confirm-material-center-change', () => {
                if (!confirm('Changing the Material Center will update all invoice items. Continue?')) {
                    const originalMcId = '{{ old('mc_id', $invoice->material_center_id) }}';
                    Livewire.dispatchTo('transaction.invoice-form', 'set', { name: 'mc_id', value: originalMcId });
                }
            });
        })();
    </script>
@endpush
