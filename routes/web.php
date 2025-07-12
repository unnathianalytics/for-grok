<?php

use App\Models\Item;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\InvoiceType;
use App\Models\AccountingType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Redirect;
use App\Livewire\Transaction\AccountingVoucherForm;
use App\Models\InvoicePrintConfig;

Auth::routes();
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect('home'));
    Route::get('/logout', function () {
        Auth::logout();
        return Redirect::to('login');
    });

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    //Item Routes
    Route::view('/master/items', 'master.items.index')->name('item_index');
    Route::view('/master/items/create', 'master.items.create')->name('item_create');
    Route::get('/master/items/{item}/edit', fn(Item $item) => view('master.items.edit', ['item' => $item]))->name('item_edit');

    //Account Routes
    Route::view('/master/accounts', 'master.accounts.index')->name('account_index');
    Route::view('/master/accounts/create', 'master.accounts.create')->name('account_create');
    Route::get('/master/accounts/{account}/edit', fn(Account $account) => view('master.accounts.edit', ['account' => $account]))->name('account_edit');

    //Transaction Routes
    //1. Invoice
    Route::get('/transaction/invoices/{invoiceType}', function (InvoiceType $invoiceType) {
        return view('transaction.invoices.index', [
            'invoiceType' => $invoiceType->slug,
            'invoice_type_id' => $invoiceType->id,
            'bg_color' => $invoiceType->bg_color
        ]);
    })->name('invoice_index');

    // Create Route
    Route::get('/transaction/invoices/{invoiceType}/create', function (InvoiceType $invoiceType) {
        return view('transaction.invoices.create', [
            'invoiceType' => $invoiceType->slug,
        ]);
    })->name('invoice_create');

    // Edit Route
    Route::get('/transaction/invoices/{invoiceType}/{invoiceId}/edit', function (InvoiceType $invoiceType, int $invoiceId) {
        $invoice = Invoice::where('id', $invoiceId)
            ->where('invoice_type_id', $invoiceType->id)
            ->firstOrFail();
        return view('transaction.invoices.edit', [
            'invoiceType' => $invoiceType->slug,
            'invoiceId' => $invoice->id,
        ]);
    })
        ->name('invoice_edit');

    //2. Accounting Voucher
    Route::get('transaction/accountingvoucher/{accountingType}/create', function (AccountingType $accountingType) {
        return view('transaction.accounting_vouchers.create', [
            'accountingType' => $accountingType->slug,
        ]);
    })->name('accounting_voucher_create');

    Route::get('transaction/accountingvoucher/{slug}/{voucher}/edit', AccountingVoucherForm::class)->name('accounting_voucher_edit');

    //Autocomplete Items
    Route::get('/autocomplete-items', function (\Illuminate\Http\Request $request) {
        $term = $request->get('term');
        $results = Item::where('name', 'like', "%{$term}%")
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'label' => $item->name,
                'value' => $item->id,
            ]);
        return response()->json($results);
    })->name('autocomplete.items');

    //print
    //Invoice
    Route::get('/invoices/{invoiceType}/{invoiceId}/{format}/print', function (InvoiceType $invoiceType, int $invoiceId, $formatId) {
        $invoice = Invoice::where('id', $invoiceId)
            ->where('invoice_type_id', $invoiceType->id)
            ->firstOrFail();
        $format = InvoicePrintConfig::where('invoice_type_id', $invoiceType->id)
            ->where('id', $formatId)
            ->firstOrFail();
        return view('print.invoice.standard', [
            'invoiceId' => $invoice->id,
            'format' => $format->id,
        ]);
    })->name('invoice_standard');
});
