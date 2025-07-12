<?php

namespace App\Livewire\Master;

use Throwable;
use App\Models\Uom;
use App\Models\Item;
use Livewire\Component;
use App\Models\ItemGroup;
use App\Models\InvoiceItem;
use App\Models\TaxCategory;
use App\Models\SerialNumber;

class ItemForm extends Component
{
    public ?Item $item = null;
    public $name, $barcode, $is_physical, $has_multi_uom = false, $has_serial_number = false, $op_stock_qty, $op_stock_amount, $hsn_sac, $sale_price, $purchase_price, $max_retail_price, $min_sale_price, $self_val_price, $description1, $description2, $description3, $description4, $description5, $min_level_qty, $reorder_level_qty, $max_level_qty;
    public $both_checkboxes;
    public $group_id, $tax_category_id, $uom_id;
    public $serialNumbers = [];
    public bool $hasUsedSerialNumbers = false;
    public $groups;
    public $baseUoms;
    public $uoms = [];
    public $taxCategories;
    public bool $isUomLocked = false;
    public function mount(?Item $item = null)
    {
        $this->groups = ItemGroup::all();
        $this->baseUoms = Uom::all();
        $this->taxCategories = TaxCategory::all();

        $this->item = $item;
        $this->isUomLocked = InvoiceItem::where('item_id', $item->id)->exists();

        if ($item && $item->exists) {

            $this->name = $item->name;
            $this->barcode = $item->barcode;
            $this->hsn_sac = $item->hsn_sac;
            $this->sale_price = $item->sale_price;
            $this->purchase_price = $item->purchase_price;
            $this->max_retail_price = $item->max_retail_price;
            $this->min_sale_price = $item->min_sale_price;
            $this->group_id = $item->group_id;
            $this->uom_id = $item->uom_id;
            $this->op_stock_qty = $item->op_stock_qty;
            $this->tax_category_id = $item->tax_category_id;
            $this->has_multi_uom = $item && $item->uoms->isNotEmpty();
            $this->has_serial_number = $item->has_serial_number;
            $this->uoms = $this->has_multi_uom
                ? $item->uoms->map(fn($u) => ['uom_id' => $u->uom_id, 'factor' => $u->conversion_factor])->toArray()
                : [];

            $this->serialNumbers = $this->has_serial_number && $item->op_stock_qty > 0
                ? SerialNumber::where('item_id', $item->id)
                ->where('is_opening_stock', true)
                ->get()
                ->map(fn($sn) => [
                    'id' => $sn->id,
                    'serial_number' => $sn->serial_number,
                    'description' => $sn->description,
                    'is_used' => !is_null($sn->invoice_item_id),
                    'is_opening_stock' => $sn->is_opening_stock
                ])
                ->toArray()
                : [];

            $this->hasUsedSerialNumbers = $this->has_serial_number
                ? SerialNumber::where('item_id', $item->id)->whereNotNull('invoice_item_id')->exists()
                : false;
        } else {
            $this->item = null;
            $this->uoms = [['uom_id' => '', 'factor' => '']];
            $this->serialNumbers = [];
            $this->hasUsedSerialNumbers = false;
        }
    }
    public function toggleMultiUom()
    {
        if (!$this->has_multi_uom) {
            $this->uoms = [];
        } elseif (empty($this->uoms)) {
            $this->uoms[] = ['uom_id' => '', 'factor' => ''];
        }
    }
    public function addUom()
    {
        $this->uoms[] = ['uom_id' => '', 'factor' => ''];
    }

    public function removeUom($index)
    {
        unset($this->uoms[$index]);
        $this->uoms = array_values($this->uoms);
    }
    public function removeSerialNumber($index)
    {
        if (isset($this->serialNumbers[$index]) && !$this->serialNumbers[$index]['is_used']) {
            unset($this->serialNumbers[$index]);
            $this->serialNumbers = array_values($this->serialNumbers);

            $this->op_stock_qty = count($this->serialNumbers);
        }
    }
    public function updatedHasSerialNumber($value)
    {
        if (!$value && $this->hasUsedSerialNumbers) {

            $this->has_serial_number = true;
            $this->addError('has_serial_number', 'Cannot disable serial numbers because some are already used.');
        } elseif (!$value) {

            $this->serialNumbers = [];
        } elseif ($value && $this->op_stock_qty > 0 && empty($this->serialNumbers)) {

            for ($i = 0; $i < $this->op_stock_qty; $i++) {
                $this->serialNumbers[] = ['id' => null, 'serial_number' => '', 'description' => '', 'is_used' => false, 'is_opening_stock' => true];
            }
        }
    }
    public function updatedOpStockQty($value)
    {
        if ($this->has_serial_number && $value >= 0) {

            $usedSerialNumberCount = $this->item
                ? SerialNumber::where('item_id', $this->item->id)->whereNotNull('invoice_item_id')->count()
                : 0;
            if ($value < $usedSerialNumberCount) {

                $this->op_stock_qty = $usedSerialNumberCount;
                $this->addError('op_stock_qty', "Opening stock quantity cannot be less than the {$usedSerialNumberCount} used serial numbers for item {$this->name}.");
            } else {
                $currentCount = count($this->serialNumbers);
                if ($value > $currentCount) {

                    $unusedSNs = SerialNumber::where('item_id', $this->item?->id ?? 0)
                        ->where('is_opening_stock', true)
                        ->whereNull('invoice_item_id')
                        ->whereNotIn('serial_number', array_column($this->serialNumbers, 'serial_number'))
                        ->limit($value - $currentCount)
                        ->get()
                        ->map(fn($sn) => [
                            'id' => $sn->id,
                            'serial_number' => $sn->serial_number,
                            'description' => $sn->description,
                            'is_used' => false,
                            'is_opening_stock' => $sn->is_opening_stock
                        ])->toArray();
                    $this->serialNumbers = array_merge($this->serialNumbers, $unusedSNs);
                    for ($i = count($this->serialNumbers); $i < $value; $i++) {
                        $this->serialNumbers[] = ['id' => null, 'serial_number' => '', 'description' => '', 'is_used' => false, 'is_opening_stock' => true];
                    }
                } elseif ($value < $currentCount) {

                    $usedSNs = array_filter($this->serialNumbers, fn($sn) => $sn['is_used']);
                    $unusedSNs = array_filter($this->serialNumbers, fn($sn) => !$sn['is_used']);
                    $excess = $currentCount - $value;
                    if ($excess <= count($unusedSNs)) {
                        $this->serialNumbers = array_merge(
                            array_values($usedSNs),
                            array_values(array_slice(array_values($unusedSNs), 0, count($unusedSNs) - $excess))
                        );
                    } else {
                        $this->op_stock_qty = $currentCount;
                        $this->addError('op_stock_qty', "Opening stock quantity cannot be less than the number of used serial numbers.");
                    }
                }
            }
        } else {
            $this->serialNumbers = [];
        }
    }
    public function save()
    {
        if ($this->has_serial_number && $this->op_stock_qty > 0 && count($this->serialNumbers) > $this->op_stock_qty) {
            $this->serialNumbers = array_slice($this->serialNumbers, 0, $this->op_stock_qty);
        }

        $validated = $this->validate([
            'name' => 'required|string',
            'uom_id' => 'required',
            'op_stock_qty' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->has_serial_number && $this->item) {
                        $usedSerialNumberCount = SerialNumber::where('item_id', $this->item->id)
                            ->whereNotNull('invoice_item_id')
                            ->count();
                        if ($value < $usedSerialNumberCount) {
                            $fail("Opening stock quantity cannot be less than the {$usedSerialNumberCount} used serial numbers for item {$this->name}.");
                        }
                    }

                    if ($this->has_serial_number && count($this->serialNumbers) != $value) {
                        $this->serialNumbers = array_slice($this->serialNumbers, 0, $value);
                    }
                },
            ],
            'has_multi_uom' => 'boolean',
            'has_serial_number' => 'boolean',
            'hsn_sac' => 'required|numeric',
            'barcode' => 'numeric|nullable',
            'sale_price' => 'required|numeric',
            'purchase_price' => 'required|numeric',
            'max_retail_price' => 'required|numeric',
            'min_sale_price' => 'required|numeric',
            'group_id' => 'required',
            'tax_category_id' => 'required',
            'both_checkboxes' => [function ($attribute, $value, $fail) {
                if ($this->has_multi_uom && $this->has_serial_number) {
                    $fail('You cannot enable both Multi UOM and Serial Number at the same time.');
                }
            }],
        ]);

        if ($this->has_multi_uom) {
            $this->validate(
                [
                    'uoms.*.uom_id' => 'required|exists:uoms,id|different:uom_id',
                    'uoms.*.factor' => 'required|numeric|min:0.0001',
                ],
                [
                    'uoms.*.uom_id.required' => 'Conversion unit is required.',
                    'uoms.*.factor.required' => 'Conversion factor is required.',
                    'uoms.*.factor.min' => 'Conversion factor must be greater than 0.',
                ]
            );
        }

        if ($this->has_serial_number && $this->op_stock_qty > 0) {
            $this->validate(
                [
                    'serialNumbers' => [
                        'required',
                        'array',
                        "size:{$this->op_stock_qty}",
                        function ($attribute, $value, $fail) {
                            $serialNumbers = array_column($this->serialNumbers, 'serial_number');
                            if (count(array_unique($serialNumbers)) < count($serialNumbers)) {
                                $fail('Duplicate serial numbers are not allowed within the same item.');
                            }

                            foreach ($this->serialNumbers as $index => $sn) {
                                if (empty($sn['id'])) {
                                    $existing = SerialNumber::where('item_id', $this->item?->id ?? 0)
                                        ->where('serial_number', $sn['serial_number'])
                                        ->where('is_opening_stock', true)
                                        ->exists();
                                    if ($existing) {
                                        $fail("Serial number {$sn['serial_number']} is already used for this item.");
                                    }
                                }
                            }
                        },
                    ],
                    'serialNumbers.*.serial_number' => 'required|string|max:55',
                    'serialNumbers.*.description' => 'nullable|string|max:55',
                ],
                [
                    'serialNumbers.required' => 'Serial numbers are required.',
                    'serialNumbers.size' => "Exactly {$this->op_stock_qty} serial numbers are required for item {$this->name}.",
                    'serialNumbers.*.serial_number.required' => 'Serial number is required.',
                    'serialNumbers.*.serial_number.max' => 'Serial number must not exceed 55 characters.',
                    'serialNumbers.*.description.max' => 'Description must not exceed 55 characters.',
                ]
            );
        }

        $item = Item::updateOrCreate(
            ['id' => $this->item?->id],
            $validated
        );

        $item->uoms()->delete();
        if ($this->has_multi_uom) {
            foreach ($this->uoms as $uom) {
                $item->uoms()->create([
                    'uom_id' => $uom['uom_id'],
                    'conversion_factor' => $uom['factor'],
                ]);
            }
        }

        if ($this->has_serial_number && $this->op_stock_qty > 0) {
            $currentSerialNumbers = array_column($this->serialNumbers, 'serial_number');
            SerialNumber::where('item_id', $item->id)
                ->where('is_opening_stock', true)
                ->whereNull('invoice_item_id')
                ->whereNotIn('serial_number', $currentSerialNumbers)
                ->delete();

            foreach ($this->serialNumbers as $sn) {
                if (!$sn['is_used']) {
                    SerialNumber::updateOrCreate(
                        ['item_id' => $item->id, 'serial_number' => $sn['serial_number']],
                        ['description' => $sn['description'], 'invoice_item_id' => null, 'is_opening_stock' => true]
                    );
                }
            }
        } else {
            SerialNumber::where('item_id', $item->id)
                ->where('is_opening_stock', true)
                ->whereNull('invoice_item_id')
                ->delete();
        }
        return redirect()->route('item_index');
    }
    public function render()
    {
        return view('livewire.master.item-form');
    }
}
