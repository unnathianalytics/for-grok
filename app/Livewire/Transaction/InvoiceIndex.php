<?php

namespace App\Livewire\Transaction;

use Livewire\Component;
use App\Models\Invoice;
use App\Models\InvoiceType;

class InvoiceIndex extends Component
{
    public $fromDate;
    public $toDate;
    public string $bg_color;
    public $invoice_type_id;

    public function mount()
    {
        $dates = fin_year();
        //$today = now()->format('Y-m-d');
        $this->fromDate = $dates['from_date'];
        $this->toDate = $dates['to_date'];
        $this->bg_color = InvoiceType::findOrFail($this->invoice_type_id)->bg_color;
    }

    public function render()
    {
        $query = Invoice::query();
        $query->where('invoice_type_id', '=', $this->invoice_type_id);
        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('invoice_date', [$this->fromDate, $this->toDate]);
        }

        $invoices = $query->with('items')->latest()->get();
        $type = InvoiceType::findOrFail($this->invoice_type_id);
        return view(
            'livewire.transaction.invoice-index',
            [
                'invoices' => $invoices,
                'type' => $type
            ]
        );
    }
}
