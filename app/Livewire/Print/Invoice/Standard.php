<?php

namespace App\Livewire\Print\Invoice;

use App\Models\Item;
use App\Models\Invoice;
use App\Models\InvoicePrintConfig;
use Livewire\Component;

class Standard extends Component
{
    public $invoice;
    public $format;

    public $itemsAmount = 0;
    public float $sundriesAmount = 0;

    public float $invoiceAmount = 0;
    public function mount($invoiceId, $formatId)
    {
        $this->invoice = Invoice::with('items', 'items.serial_numbers', 'invoiceType', 'invoiceSundries', 'account')->find($invoiceId);
        $this->format = InvoicePrintConfig::findOrFail($formatId);
        foreach ($this->invoice->invoiceSundries as $sundry) {
            $this->sundriesAmount += ($sundry->billSundry->adjustment === '-' ? -1 : 1) * $sundry->sundry_amount;
        }
        $this->itemsAmount = collect($this->invoice->items)->sum('item_amount');
        $this->invoiceAmount = $this->itemsAmount + $this->sundriesAmount ?? 0;
    }
    public function render()
    {
        return view('livewire.print.invoice.standard', [
            'invoice' => $this->invoice,
            'format' => $this->format
        ]);
    }
}
