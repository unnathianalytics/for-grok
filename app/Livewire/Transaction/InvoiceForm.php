<?php

namespace App\Livewire\Transaction;

use Livewire\Component;
use Illuminate\Support\Carbon;
use App\Models\InvoicePrintConfig;
use Illuminate\Support\Facades\Log;
use App\Models\{Account, BillSundry, TaxType, Item, Invoice, VoucherSeries, ItemUom, Uom, InvoiceSundry, InvoiceType, SerialNumber, InvoiceItem, InvoiceItemSerialNumber};

class InvoiceForm extends Component
{
    public $breadcrumb_header;
    public string $invoiceType = '';
    public string $bg_color = '#FFFFFF';
    public array $invoiceData = [];
    public $invoice_number, $invoice_date, $voucher_series_id, $tax_type_id, $invoice_time, $account_id, $description;
    public $countable, $stock_update_date, $item_id, $item_description1, $item_description2, $item_description3, $item_description4, $uom_id, $quantity, $base_quantity, $batch_no, $batch_exp, $sale_price, $max_retail_price, $price, $tax_category_id, $igst_pct, $cgst_pct, $sgst_pct, $cess_pct, $igst_amt, $cgst_amt, $sgst_amt, $cess_amt, $discount_pct, $discount_amt, $taxable_amt, $item_amount;
    public $current_stock = 0;
    public $current_uom;
    public $voucherSeries;
    public $accounts = [];
    public $allItems;
    public array $invoiceItems = [];
    public $itemSubTotal = 0;
    public $sundrySubTotal = 0;
    public $taxTypes = [];
    public $uoms;
    public $itemUoms;
    public array $itemUomOptions = [];
    public $sundries;
    public $allBillSundries;
    protected $itemUomsByItemUomId;
    protected $allItemsById;
    public Invoice $invoice;
    public string $lastEditedField = '';
    public int|null $lastEditedIndex = null;
    public $invoiceTotalAmount = 0;
    public $currentItemIndex = null;
    public $selectAll = false;
    public $defaultItemValues = [
        'current_stock' => 0,
        'item_id' => null,
        'item_name' => '',
        'item_description1' => '',
        'item_description2' => '',
        'item_description3' => '',
        'item_description4' => '',
        'uom_id' => null,
        'quantity' => null,
        'base_quantity' => null,
        'batch_no' => '',
        'batch_exp' => null,
        'sale_price' => 0.00,
        'max_retail_price' => 0.00,
        'price' => 0.00,
        'tax_category_id' => null,
        'igst_pct' => 0.00,
        'cgst_pct' => 0.00,
        'sgst_pct' => 0.00,
        'cess_pct' => 0.00,
        'igst_amt' => 0.00,
        'cgst_amt' => 0.00,
        'sgst_amt' => 0.00,
        'cess_amt' => 0.00,
        'discount_pct' => 0.00,
        'discount_amt' => 0.00,
        'taxable_amt' => 0.00,
        'item_amount' => 0.00,
        'countable' => true,
        'stock_update_date' => null,
        'has_serial_number' => false,
        'serial_numbers' => [],
    ];
    //Print
    public $printFormats;
    public $printConfigId;

    public function mount(string $invoiceType, ?int $invoiceId = null)
    {
        $invoiceTypeModel = InvoiceType::where('slug', $invoiceType)->firstOrFail();
        $this->invoiceType = $invoiceTypeModel->slug;
        $this->bg_color = $invoiceTypeModel->bg_color ?? '#FFFFFF';
        $this->voucherSeries = VoucherSeries::all();
        $this->accounts = Account::invoiceAccounts()->get();
        $this->allItems = Item::all();
        $this->taxTypes = TaxType::all();
        $this->uoms = Uom::all();
        $this->itemUoms = ItemUom::all();
        $this->allBillSundries = BillSundry::all();
        $this->invoice = $invoiceId
            ? $this->loadInvoiceModel($invoiceId)
            : $this->newInvoiceModel($invoiceTypeModel->id);

        $this->invoiceData = [
            'voucher_series_id' => $this->invoice->voucher_series_id ?? null,
            'invoice_date' => $this->invoice->invoice_date ?? now()->format('Y-m-d'),
            'invoice_number' => $this->invoice->invoice_number ?? '',
            'invoice_time' => $this->invoice->invoice_time ?? now()->format('H:i'),
            'tax_type_id' => $this->invoice->tax_type_id ?? null,
            'account_id' => $this->invoice->account_id ?? null,
            'description' => $this->invoice->description ?? '',
            'invoice_type_id' => $this->invoice->invoice_type_id ?? $invoiceTypeModel->id,
        ];

        $this->invoiceItems = [];
        foreach ($this->invoice->items as $item) {
            $serialNumbers = InvoiceItemSerialNumber::where('invoice_item_id', $item->id)
                ->with('serialNumber')
                ->get()
                ->map(function ($sn) {
                    return [
                        'id' => $sn->serial_number_id,
                        'serial_number' => $sn->serialNumber->serial_number,
                        'description' => $sn->serialNumber->description,
                        'is_used' => !is_null($sn->serialNumber->invoice_item_id),
                    ];
                })->toArray();
            $this->invoiceItems[] = array_merge($this->defaultItemValues, [
                'item_id' => $item->item_id,
                'item_name' => $item->item?->name ?? '',
                'item_description1' => $item->item_description1,
                'item_description2' => $item->item_description2,
                'item_description3' => $item->item_description3,
                'item_description4' => $item->item_description4,
                'uom_id' => $item->uom_id,
                'quantity' => $item->quantity,
                'base_quantity' => $item->base_quantity,
                'batch_no' => $item->batch_no,
                'batch_exp' => $item->batch_exp,
                'sale_price' => $item->sale_price,
                'max_retail_price' => $item->max_retail_price,
                'price' => $item->price,
                'tax_category_id' => $item->tax_category_id,
                'igst_pct' => $item->igst_pct,
                'cgst_pct' => $item->cgst_pct,
                'sgst_pct' => $item->sgst_pct,
                'cess_pct' => $item->cess_pct,
                'igst_amt' => $item->igst_amt,
                'cgst_amt' => $item->cgst_amt,
                'sgst_amt' => $item->sgst_amt,
                'cess_amt' => $item->cess_amt,
                'discount_pct' => $item->discount_pct,
                'discount_amt' => $item->discount_amt,
                'taxable_amt' => $item->taxable_amt,
                'item_amount' => $item->item_amount,
                'countable' => $item->countable,
                'stock_update_date' => $item->stock_update_date,
                'has_serial_number' => $item->item?->has_serial_number ?? false,
                'serial_numbers' => $serialNumbers,
            ]);

            //Print
            if ($invoiceId) {
                $this->printFormats = InvoicePrintConfig::where('invoice_type_id', '=', $invoiceTypeModel->id)->get();
            }
        }

        foreach ($this->invoiceItems as $index => $item) {
            $this->fillItemDefaults($index);
        }

        $this->itemSubTotal = collect($this->invoiceItems)->sum('item_amount');

        if (empty($this->invoiceItems)) {
            $this->invoiceItems[] = $this->defaultItemValues;
        }

        if ($invoiceId) {
            $this->invoice = Invoice::with(['invoiceSundries', 'items'])->findOrFail($invoiceId)->fresh();
            $this->sundries = collect();
            foreach ($this->invoice->invoiceSundries as $sundry) {
                $this->sundries->push([
                    'amount_adjustment' => $sundry->billSundry->adjustment,
                    'bill_sundry_id' => $sundry->bill_sundry_id,
                    'sundry_amount' => $sundry->sundry_amount,
                ]);
                $this->sundrySubTotal += ($sundry->billSundry->adjustment === '-' ? -1 : 1) * $sundry->sundry_amount;
            }
        } else {
            $this->invoice = new Invoice([
                'invoice_type_id' => $invoiceTypeModel->id,
            ]);
            $this->sundries = collect([]);
        }

        Log::debug('Invoice Items on Mount: ', $this->invoiceItems);
        $this->breadcrumb_header = ucwords($invoiceTypeModel->name);
    }

    protected function loadInvoiceModel(int $id): Invoice
    {
        return Invoice::with(['items'])->findOrFail($id)->fresh();
    }

    protected function newInvoiceModel(int $invoiceTypeId): Invoice
    {
        return new Invoice(['invoice_type_id' => $invoiceTypeId]);
    }

    public function addItemRow()
    {
        $this->invoiceItems[] = $this->defaultItemValues;
        $this->current_stock = null;
        $this->dispatch('item-row-added');
    }

    public function removeItemRow($index)
    {
        unset($this->invoiceItems[$index]);
        $this->invoiceItems = array_values($this->invoiceItems);
        $this->recalculateItemAmounts();
        $this->recalculateSundryAmount();
    }

    public function addSundry()
    {
        $this->sundries[] = [
            'amount_adjustment' => '',
            'bill_sundry_id' => '',
            'sundry_amount' => 0,
        ];
    }

    public function removeSundry($index)
    {
        $this->sundries->forget($index);
        $this->sundries = $this->sundries->values();
        $this->recalculateSundryAmount();
    }

    public function addSerialNumberRow($index)
    {
        if (count($this->invoiceItems[$index]['serial_numbers']) < $this->invoiceItems[$index]['quantity']) {
            $this->invoiceItems[$index]['serial_numbers'][] = ['id' => null, 'serial_number' => '', 'description' => '', 'is_used' => false];
        }
    }

    public function removeSerialNumberRow($index, $rowIndex)
    {
        if (!$this->invoiceItems[$index]['serial_numbers'][$rowIndex]['is_used']) {
            unset($this->invoiceItems[$index]['serial_numbers'][$rowIndex]);
            $this->invoiceItems[$index]['serial_numbers'] = array_values($this->invoiceItems[$index]['serial_numbers']);
        }
    }

    protected function fillItemDefaults(int $index): void
    {
        $itemId = $this->invoiceItems[$index]['item_id'] ?? null;
        if (!$itemId) {
            $this->itemUomOptions[$index] = [];
            return;
        }
        $item = $this->allItems->firstWhere('id', $itemId);
        if (!$item) {
            $this->itemUomOptions[$index] = [];
            return;
        }
        $this->invoiceItems[$index]['item_name'] = $item->name;
        $this->invoiceItems[$index]['has_serial_number'] = $item->has_serial_number;

        if (!isset($this->invoiceItems[$index]['price']) || !$this->invoiceItems[$index]['price']) {
            $this->invoiceItems[$index]['price'] = $item->sale_price ?? 0.00;
        }

        $this->invoiceItems[$index]['max_retail_price'] = $item->max_retail_price ?? 0.00;
        $this->invoiceItems[$index]['tax_category_id'] = $item->tax_category_id;
        $this->current_stock = $item->getStock();
        if ($item->taxcategory) {
            $this->invoiceItems[$index]['igst_pct'] = $item->taxcategory->igst_pct ?? 0.00;
            $this->invoiceItems[$index]['cgst_pct'] = $item->taxcategory->cgst_pct ?? 0.00;
            $this->invoiceItems[$index]['sgst_pct'] = $item->taxcategory->sgst_pct ?? 0.00;
            $this->invoiceItems[$index]['cess_pct'] = $item->taxcategory->cess_pct ?? 0.00;
            $this->invoiceItems[$index]['gst_percent'] = sprintf(
                '%s+%s+%s',
                $item->taxcategory->cgst_pct ?? 0,
                $item->taxcategory->sgst_pct ?? 0,
                $item->taxcategory->cess_pct ?? 0
            );
        } else {
            $this->invoiceItems[$index]['igst_pct'] = 0.00;
            $this->invoiceItems[$index]['cgst_pct'] = 0.00;
            $this->invoiceItems[$index]['sgst_pct'] = 0.00;
            $this->invoiceItems[$index]['cess_pct'] = 0.00;
            $this->invoiceItems[$index]['gst_percent'] = '0+0+0';
        }

        $this->invoiceItems[$index]['taxable_amt'] = $item->taxable_amt ?? 0.00;

        if (!isset($this->invoiceItems[$index]['uom_id']) || !$this->invoiceItems[$index]['uom_id']) {
            $this->invoiceItems[$index]['uom_id'] = $item->uom_id;
        }

        $uomsById = $this->uoms->keyBy('id');
        $itemUomIds = $this->itemUoms
            ->where('item_id', $itemId)
            ->pluck('uom_id')
            ->unique()
            ->prepend($item->uom_id)
            ->unique();

        $uoms = $itemUomIds
            ->map(fn($id) => isset($uomsById[$id])
                ? ['id' => $id, 'name' => $uomsById[$id]->name]
                : null)
            ->filter()
            ->values()
            ->all();
        $this->itemUomOptions[$index] = $uoms;
        $this->current_uom = $item->baseUom->name ?? null;

        // Initialize serial numbers if quantity is set
        if ($this->invoiceItems[$index]['has_serial_number'] && !empty($this->invoiceItems[$index]['quantity']) && empty($this->invoiceItems[$index]['serial_numbers'])) {
            $quantity = (int) $this->invoiceItems[$index]['quantity'];
            $this->invoiceItems[$index]['serial_numbers'] = array_fill(0, $quantity, ['id' => null, 'serial_number' => '', 'description' => '', 'is_used' => false]);
        }
    }

    public function updatedInvoiceItems($value, $key)
    {
        if (preg_match('/^(\d+)\.(price|item_amount)$/', $key, $matches)) {
            $this->lastEditedIndex = (int) $matches[1];
            $this->lastEditedField = $matches[2];
        }
        if (preg_match('/^(\d+)\.item_id$/', $key, $matches)) {
            $index = (int) $matches[1];
            $this->invoiceItems[$index]['serial_numbers'] = [];
            $this->fillItemDefaults($index);
        }
        if (preg_match('/^(\d+)\.quantity$/', $key, $matches)) {
            $index = (int) $matches[1];
            if ($this->invoiceItems[$index]['has_serial_number'] && !empty($value)) {
                $quantity = (int) $value;
                $currentSNCount = count($this->invoiceItems[$index]['serial_numbers']);
                if ($quantity > $currentSNCount) {
                    for ($i = $currentSNCount; $i < $quantity; $i++) {
                        $this->invoiceItems[$index]['serial_numbers'][] = ['id' => null, 'serial_number' => '', 'description' => '', 'is_used' => false];
                    }
                } elseif ($quantity < $currentSNCount) {
                    $this->invoiceItems[$index]['serial_numbers'] = array_slice(
                        array_filter($this->invoiceItems[$index]['serial_numbers'], fn($sn) => $sn['is_used'] || !empty($sn['serial_number'])),
                        0,
                        $quantity
                    );
                }
            } else {
                $this->invoiceItems[$index]['serial_numbers'] = [];
            }
        }
        $this->recalculateItemAmounts();
    }

    public function updatedSundries($value, $key)
    {
        if (str_ends_with($key, 'bill_sundry_id')) {
            [$index, $field] = explode('.', $key);
            $selectedId = $this->sundries[$index]['bill_sundry_id'] ?? null;

            if ($selectedId) {
                $bs = $this->allBillSundries->firstWhere('id', $selectedId);
                $this->sundries = collect($this->sundries)
                    ->map(function ($sundry, $i) use ($index, $bs) {
                        if ($i == $index) {
                            $sundry['amount_adjustment'] = $bs?->adjustment ?? '';
                        }
                        return $sundry;
                    })
                    ->toArray();
            }
        }
        $this->recalculateSundryAmount();
    }

    public function openSerialNumberModal($index)
    {
        if (!isset($this->invoiceItems[$index]['quantity']) || $this->invoiceItems[$index]['quantity'] <= 0) {
            $this->addError('invoiceItems.' . $index . '.quantity', 'Quantity must be set to assign serial numbers.');
            return;
        }
        if (!$this->invoiceItems[$index]['has_serial_number']) {
            return;
        }
        $this->currentItemIndex = $index;
        $this->dispatch('open-serial-number-modal');
    }

    public function recalculateSundryAmount()
    {
        $this->sundrySubTotal = 0;
        foreach ($this->sundries as $s) {
            $tempAmount = (float) ($s['sundry_amount'] ?? 0);
            $adjustment = $s['amount_adjustment'] === '-' ? -1 : 1;
            $this->sundrySubTotal += $adjustment * $tempAmount;
        }
    }

    public function recalculateItemAmounts()
    {
        foreach ($this->invoiceItems as $i => $item) {
            $qty = floatval($item['quantity'] ?? 0);
            $price = floatval($item['price'] ?? 0);
            $discount_pct = floatval($item['discount_pct'] ?? 0);
            $discount_amt = floatval($item['discount_amt'] ?? 0);
            $cgst_pct = floatval($item['cgst_pct'] ?? 0);
            $sgst_pct = floatval($item['sgst_pct'] ?? 0);
            $cess_pct = floatval($item['cess_pct'] ?? 0);
            $total_gst_pct = $cgst_pct + $sgst_pct + $cess_pct;

            $item_amount = floatval($item['item_amount'] ?? 0);
            $discount_per_unit = 0;
            $cgst_amt = 0;
            $sgst_amt = 0;
            $cess_amt = 0;
            $taxable_amt = 0;
            $final_item_amount = 0;

            if (
                $this->lastEditedIndex === $i &&
                $this->lastEditedField === 'item_amount' &&
                $qty > 0
            ) {
                $taxable_amt = $item_amount / (1 + $total_gst_pct / 100);
                $subtotal = $taxable_amt / (1 - ($discount_pct / 100));
                $price = $qty > 0 ? $subtotal / $qty : 0;

                $discount_amt = $subtotal - $taxable_amt;
                $discount_per_unit = $qty > 0 ? $discount_amt / $qty : 0;

                $cgst_amt = ($taxable_amt * $cgst_pct) / 100;
                $sgst_amt = ($taxable_amt * $sgst_pct) / 100;
                $cess_amt = ($taxable_amt * $cess_pct) / 100;

                $final_item_amount = $taxable_amt + $cgst_amt + $sgst_amt + $cess_amt;
            } elseif (
                $this->lastEditedIndex === $i &&
                $this->lastEditedField === 'discount_amt' &&
                $qty > 0
            ) {
                $subtotal = $qty * $price;
                $discount_pct = $subtotal > 0 ? ($discount_amt / $subtotal) * 100 : 0;
                $discount_per_unit = $qty > 0 ? $discount_amt / $qty : 0;
                $taxable_amt = $subtotal - $discount_amt;

                $cgst_amt = ($taxable_amt * $cgst_pct) / 100;
                $sgst_amt = ($taxable_amt * $sgst_pct) / 100;
                $cess_amt = ($taxable_amt * $cess_pct) / 100;

                $final_item_amount = $taxable_amt + $cgst_amt + $sgst_amt + $cess_amt;
            } else {
                $subtotal = $qty * $price;
                $discount_amt = ($subtotal * $discount_pct) / 100;
                $discount_per_unit = $qty > 0 ? $discount_amt / $qty : 0;
                $taxable_amt = $subtotal - $discount_amt;

                $cgst_amt = ($taxable_amt * $cgst_pct) / 100;
                $sgst_amt = ($taxable_amt * $sgst_pct) / 100;
                $cess_amt = ($taxable_amt * $cess_pct) / 100;

                $final_item_amount = $taxable_amt + $cgst_amt + $sgst_amt + $cess_amt;
            }

            $this->invoiceItems[$i]['price'] = round($price, 2);
            $this->invoiceItems[$i]['discount_amt'] = round($discount_amt, 2);
            $this->invoiceItems[$i]['discount_pct'] = round($discount_pct, 2);
            $this->invoiceItems[$i]['discount_per_unit'] = round($discount_per_unit, 2);
            $this->invoiceItems[$i]['cgst_amt'] = round($cgst_amt, 2);
            $this->invoiceItems[$i]['sgst_amt'] = round($sgst_amt, 2);
            $this->invoiceItems[$i]['cess_amt'] = round($cess_amt, 2);
            $this->invoiceItems[$i]['taxable_amt'] = round($taxable_amt, 2);
            $this->invoiceItems[$i]['item_amount'] = round($final_item_amount, 2);
        }

        $this->itemSubTotal = collect($this->invoiceItems)->sum('item_amount');
        $this->lastEditedIndex = null;
        $this->lastEditedField = '';
    }

    protected function getUomConversionFactor(int $itemId, int $uomId): float
    {
        if (blank($this->allItems) || blank($this->itemUoms)) {
            return 1.0;
        }
        $this->allItemsById ??= $this->allItems->keyBy('id');
        $this->itemUomsByItemUomId ??= $this->itemUoms
            ->groupBy(fn($row) => $row->item_id . ':' . $row->uom_id);
        $item = $this->allItemsById[$itemId] ?? null;
        if ($item?->uom_id == $uomId) {
            return 1.0;
        }
        $conversion = $this->itemUomsByItemUomId[$itemId . ':' . $uomId][0] ?? null;
        return (float) ($conversion->conversion_factor ?? 1.0);
    }

    public function save()
    {
        $dates = fin_year();
        $fromDate = $dates['from_date'];
        $invoiceType = InvoiceType::findOrFail($this->invoiceData['invoice_type_id']);
        $rules = [
            'invoiceData.invoice_date' => "required|date|date_format:Y-m-d|after_or_equal:{$fromDate}|before:tomorrow",
            'invoiceData.voucher_series_id' => 'required',
            'invoiceData.invoice_number' => 'required|string|min:1',
            'invoiceData.account_id' => 'required',
            'invoiceData.description' => 'nullable|string|max:255',
            'invoiceData.tax_type_id' => 'required',
            'invoiceData.invoice_type_id' => 'required|exists:invoice_types,id',
            'invoiceItems' => 'required|array|min:1',
            'invoiceItems.*.item_id' => 'required|integer|exists:items,id',
            'invoiceItems.*.item_description1' => 'nullable|string|max:255',
            'invoiceItems.*.item_description2' => 'nullable|string|max:255',
            'invoiceItems.*.item_description3' => 'nullable|string|max:255',
            'invoiceItems.*.item_description4' => 'nullable|string|max:255',
            'invoiceItems.*.uom_id' => 'nullable',
            'invoiceItems.*.quantity' => 'required|numeric|min:0.01',
            'invoiceItems.*.base_quantity' => 'nullable',
            'invoiceItems.*.batch_no' => 'nullable|string|max:50',
            'invoiceItems.*.batch_exp' => 'nullable|date',
            'invoiceItems.*.max_retail_price' => 'nullable|numeric|min:0',
            'invoiceItems.*.tax_category_id' => 'required',
            'invoiceItems.*.price' => 'required|numeric|min:0',
            'invoiceItems.*.discount_pct' => 'nullable|numeric|min:0|max:100',
            'invoiceItems.*.discount_amt' => 'nullable|numeric|min:0',
            'invoiceItems.*.item_amount' => 'required|numeric|min:0',
            'sundries.*.bill_sundry_id' => 'required|exists:bill_sundries,id',
            'sundries.*.sundry_amount' => 'required|numeric|min:0',
        ];

        if ($invoiceType->sn_input_type !== 'none') {
            foreach ($this->invoiceItems as $index => $item) {
                if ($item['has_serial_number']) {
                    $rules["invoiceItems.{$index}.serial_numbers"] = ['required', 'array', 'size:' . (int) $item['quantity']];
                    if ($invoiceType->sn_input_type === 'input') {
                        $rules["invoiceItems.{$index}.serial_numbers.*.serial_number"] = [
                            'required',
                            'string',
                            'max:55',
                            function ($attribute, $value, $fail) use ($item, $index) {
                                $serialNumberId = $this->invoiceItems[$index]['serial_numbers'][explode('.', $attribute)[3]]['id'] ?? null;
                                $existing = SerialNumber::where('item_id', $item['item_id'])
                                    ->where('serial_number', $value)
                                    ->when($serialNumberId, fn($query) => $query->where('id', '!=', $serialNumberId))
                                    ->exists();
                                if ($existing) {
                                    $fail("Serial number {$value} is already used for item {$item['item_name']}.");
                                }
                                $serialNumbers = array_column($this->invoiceItems[$index]['serial_numbers'], 'serial_number');
                                if (count(array_keys($serialNumbers, $value)) > 1) {
                                    $fail("Duplicate serial number {$value} entered for item {$item['item_name']} in row " . ($index + 1) . ".");
                                }
                            }
                        ];
                        $rules["invoiceItems.{$index}.serial_numbers.*.description"] = 'nullable|string|max:55';
                    } elseif ($invoiceType->sn_input_type === 'select') {
                        $rules["invoiceItems.{$index}.serial_numbers.*.id"] = [
                            'required',
                            'exists:serial_numbers,id',
                            function ($attribute, $value, $fail) use ($item, $invoiceType, $index) {
                                $sn = SerialNumber::find($value);
                                if (!$sn) {
                                    Log::error("Serial number ID {$value} not found for item {$item['item_name']}, index {$index}");
                                    $fail("Invalid serial number selected for item {$item['item_name']} in row " . ($index + 1) . ".");
                                    return;
                                }
                                // Handle null invoiceItem case
                                $issuingInvoiceType = null;
                                if ($sn->invoiceItem) {
                                    $issuingInvoiceType = $sn->invoiceItem->invoice->invoice_type;
                                } else {
                                    Log::warning("Serial number ID {$value} has no associated invoice item for item {$item['item_name']}, index {$index}");
                                }

                                // Restrict SNs based on transaction_category
                                if ($invoiceType->transaction_category === 'return' && $issuingInvoiceType) {
                                    if (in_array($invoiceType->slug, ['sale-return']) && $issuingInvoiceType->transaction_category !== 'sale') {
                                        $fail("Only serial numbers from Sale are allowed for Sale Return, item {$item['item_name']} in row " . ($index + 1) . ".");
                                    } elseif (in_array($invoiceType->slug, ['purchase-return']) && $issuingInvoiceType->transaction_category !== 'purchase') {
                                        $fail("Only serial numbers from Purchase are allowed for Purchase Return, item {$item['item_name']} in row " . ($index + 1) . ".");
                                    }
                                } elseif ($invoiceType->transaction_category === 'material' && $issuingInvoiceType) {
                                    if ($issuingInvoiceType->transaction_category !== 'return' || !in_array($issuingInvoiceType->slug, ['purchase-return'])) {
                                        $fail("Only serial numbers from Purchase Return are allowed for Material Received, item {$item['item_name']} in row " . ($index + 1) . ".");
                                    }
                                }
                                if ($invoiceType->in_out === 'inward' && is_null($sn->invoice_item_id)) {
                                    $fail("Selected serial number must be used for {$invoiceType->name}, item {$item['item_name']}.");
                                } elseif ($invoiceType->in_out === 'outward') {
                                    $currentItemIds = $this->invoice->items->pluck('id')->toArray();
                                    if (!is_null($sn->invoice_item_id) && !in_array($sn->invoice_item_id, $currentItemIds)) {
                                        $fail("Selected serial number must be available or belong to this invoice for {$invoiceType->name}, item {$item['item_name']}.");
                                    }
                                }
                                $selectedIds = array_column($this->invoiceItems[$index]['serial_numbers'], 'id');
                                $duplicateCount = count(array_keys($selectedIds, $value)) - 1;
                                if ($duplicateCount > 0 && $invoiceType->in_out === 'inward') {
                                    $fail("Duplicate serial number selected for item {$item['item_name']} in row " . ($index + 1) . ".");
                                }
                            }
                        ];
                    }
                }
            }
        }

        $this->validate($rules, [
            'invoiceData.invoice_date.required' => 'Invoice date is required.',
            'invoiceData.invoice_date.before' => 'Invoice date should not be greater than today.',
            'invoiceData.voucher_series_id.required' => 'Invoice series is required.',
            'invoiceData.invoice_number.required' => 'Invoice number cannot be empty.',
            'invoiceData.invoice_number.min' => 'Invoice number cannot be empty.',
            'invoiceData.account_id.required' => 'Party account is required.',
            'invoiceData.description.string' => 'Description must be a string.',
            'invoiceData.invoice_type_id.required' => 'Invoice type is required.',
            'invoiceItems.required' => 'At least one invoice item is required.',
            'invoiceItems.*.item_id.required' => 'Item is required for row ' . ($index + 1) . '.',
            'invoiceItems.*.item_id.integer' => 'Select a valid item for row ' . ($index + 1) . '.',
            'invoiceItems.*.quantity.required' => 'Quantity is required for row ' . ($index + 1) . '.',
            'invoiceItems.*.quantity.numeric' => 'Quantity must be a number for row ' . ($index + 1) . '.',
            'invoiceItems.*.sale_price.numeric' => 'Sale price must be a number for row ' . ($index + 1) . '.',
            'invoiceItems.*.max_retail_price.numeric' => 'Max retail price must be a number for row ' . ($index + 1) . '.',
            'invoiceItems.*.price.required' => 'Price is required for row ' . ($index + 1) . '.',
            'invoiceItems.*.price.numeric' => 'Price must be a number for row ' . ($index + 1) . '.',
            'invoiceItems.*.discount_pct.numeric' => 'Discount percentage must be a number for row ' . ($index + 1) . '.',
            'invoiceItems.*.discount_pct.min' => 'Discount percentage must be at least 0 for row ' . ($index + 1) . '.',
            'invoiceItems.*.discount_pct.max' => 'Discount percentage cannot exceed 100 for row ' . ($index + 1) . '.',
            'invoiceItems.*.discount_amt.numeric' => 'Discount amount must be a number for row ' . ($index + 1) . '.',
            'invoiceItems.*.item_amount.required' => 'Amount is required for row ' . ($index + 1) . '.',
            'invoiceItems.*.item_amount.numeric' => 'Amount must be a number for row ' . ($index + 1) . '.',
            'invoiceItems.*.item_amount.min' => 'Amount must be at least 0 for row ' . ($index + 1) . '.',
            'invoiceItems.*.serial_numbers.required' => "Serial numbers are required for item {$item['item_name']} in row " . ($index + 1) . '.',
            'invoiceItems.*.serial_numbers.size' => "Exactly {$item['quantity']} serial numbers are required for item {$item['item_name']} in row " . ($index + 1) . '.',
            'invoiceItems.*.serial_numbers.*.serial_number.required' => "Serial number is required for item {$item['item_name']} in row " . ($index + 1) . '.',
            'invoiceItems.*.serial_numbers.*.serial_number.max' => "Serial number must not exceed 55 characters for item {$item['item_name']} in row " . ($index + 1) . '.',
            'invoiceItems.*.serial_numbers.*.description.max' => "Description must not exceed 55 characters for item {$item['item_name']} in row " . ($index + 1) . '.',
            'invoiceItems.*.serial_numbers.*.id.required' => "Serial number selection is required for item {$item['item_name']} in row " . ($index + 1) . '.',
        ]);

        if (empty($this->invoiceData['invoice_number'])) {
            $this->addError('invoiceData.invoice_number', 'Invoice number cannot be empty.');
            return;
        }

        $this->invoice->invoice_type_id = $this->invoiceData['invoice_type_id'];
        $this->invoice->voucher_series_id = $this->invoiceData['voucher_series_id'];
        $this->invoice->invoice_date = $this->invoiceData['invoice_date'];
        $this->invoice->invoice_time = now()->format('H:i');
        $this->invoice->tax_type_id = $this->invoiceData['tax_type_id'];
        $this->invoice->account_id = $this->invoiceData['account_id'];
        $this->invoice->description = $this->invoiceData['description'];
        $this->invoice->invoice_number = $this->invoiceData['invoice_number'];
        $this->invoice->save();

        $this->invoice->items()->delete();
        $this->invoice->invoiceSundries()->delete();

        foreach ($this->invoiceItems as $index => $itemData) {
            $selectedUomId = $itemData['uom_id'];
            $qty = $itemData['quantity'];
            $conversion = $this->getUomConversionFactor($itemData['item_id'], $selectedUomId);
            $baseQty = $qty * $conversion;

            $invoiceItem = $this->invoice->items()->create([
                'item_id' => $itemData['item_id'],
                'item_description1' => $itemData['item_description1'],
                'item_description2' => $itemData['item_description2'],
                'item_description3' => $itemData['item_description3'],
                'item_description4' => $itemData['item_description4'],
                'uom_id' => $selectedUomId,
                'quantity' => $qty,
                'base_quantity' => $baseQty,
                'batch_no' => $itemData['batch_no'],
                'batch_exp' => $itemData['batch_exp'],
                'sale_price' => $itemData['sale_price'],
                'max_retail_price' => $itemData['max_retail_price'],
                'price' => $itemData['price'],
                'tax_category_id' => $itemData['tax_category_id'],
                'igst_pct' => $itemData['igst_pct'],
                'cgst_pct' => $itemData['cgst_pct'],
                'sgst_pct' => $itemData['sgst_pct'],
                'cess_pct' => $itemData['cess_pct'],
                'igst_amt' => $itemData['igst_amt'],
                'cgst_amt' => $itemData['cgst_amt'],
                'sgst_amt' => $itemData['sgst_amt'],
                'cess_amt' => $itemData['cess_amt'],
                'discount_pct' => $itemData['discount_pct'],
                'discount_amt' => $itemData['discount_amt'],
                'taxable_amt' => $itemData['taxable_amt'],
                'item_amount' => $itemData['item_amount'],
                'countable' => $itemData['countable'],
                'stock_update_date' => now(),
            ]);

            if ($itemData['has_serial_number'] && $invoiceType->sn_input_type !== 'none') {
                foreach ($itemData['serial_numbers'] as $sn) {
                    if ($invoiceType->sn_input_type === 'input') {
                        if (!$sn['is_used']) {
                            $serialNumber = SerialNumber::create([
                                'item_id' => $itemData['item_id'],
                                'serial_number' => $sn['serial_number'],
                                'description' => $sn['description'],
                                'invoice_item_id' => null,
                            ]);
                            InvoiceItemSerialNumber::create([
                                'invoice_item_id' => $invoiceItem->id,
                                'serial_number_id' => $serialNumber->id,
                            ]);
                        } else {
                            InvoiceItemSerialNumber::create([
                                'invoice_item_id' => $invoiceItem->id,
                                'serial_number_id' => $sn['id'],
                            ]);
                        }
                    } elseif ($invoiceType->sn_input_type === 'select') {
                        $serialNumber = SerialNumber::findOrFail($sn['id']);
                        if ($invoiceType->in_out === 'inward') {
                            $serialNumber->update(['invoice_item_id' => null]);
                        } elseif ($invoiceType->in_out === 'outward') {
                            $serialNumber->update(['invoice_item_id' => $invoiceItem->id]);
                        }
                        InvoiceItemSerialNumber::create([
                            'invoice_item_id' => $invoiceItem->id,
                            'serial_number_id' => $sn['id'],
                        ]);
                    }
                }
            }
        }

        foreach ($this->sundries as $sundry) {
            InvoiceSundry::create([
                'invoice_id' => $this->invoice->id,
                'amount_adjustment' => $sundry['amount_adjustment'],
                'bill_sundry_id' => $sundry['bill_sundry_id'],
                'sundry_amount' => $sundry['sundry_amount'],
            ]);
        }

        $this->invoice->load('items', 'invoiceSundries');
        $invoiceTypeSlug = InvoiceType::findOrFail($this->invoice->invoice_type_id)->slug;
        return redirect(route('invoice_index', ['invoiceType' => $invoiceTypeSlug]));
    }

    public function render()
    {
        return view('livewire.transaction.invoice-form', [
            'bg_color' => $this->bg_color,
            'invoiceTypeModel' => InvoiceType::findOrFail($this->invoiceData['invoice_type_id']),
        ]);
    }

    public function printInvoice()
    {
        $format = $this->printFormats->first()->id;
        $parameters = ['invoiceType' => $this->invoice->invoiceType->slug, 'invoiceId' => $this->invoice->id, 'format' => $format];
        return redirect(route('invoice_standard', $parameters));
    }
}
