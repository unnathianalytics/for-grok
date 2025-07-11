<?php

namespace App\Livewire\Transaction;

use App\Models\Account;
use App\Models\AccountingType;
use App\Models\AccountingVoucher;
use App\Models\AccountingVoucherItem;
use Livewire\Attributes\On;
use Livewire\Component;

class AccountingVoucherForm extends Component
{
    public AccountingVoucher $voucher;
    public string $accountingType;
    public array $accountingVoucherItems = [];
    public $accountingVoucher;
    public $availableAccounts = [];
    public array $accountingVoucherData = [];

    public string $avcSel;
    public $dr_accounts = [];
    public $cr_accounts = [];


    public function mount(string $accountingType, ?int $accountingVoucherId = null)
    {

        $this->accountingType = $accountingType;

        $accountingTypeModel = AccountingType::where('slug', $accountingType)->firstOrFail();
        $this->accountingType = $accountingTypeModel->slug;

        switch ($accountingTypeModel->id) {
            case 1: //receipt
                $this->cr_accounts = Account::whereIn('group_id', [20, 16, 1, 18, 25, 27, 4, 7, 14, 30, 9, 28, 29, 10, 26])->get();
                $this->dr_accounts = Account::whereIn('group_id', [11, 21])->get();
                break;
            case 2: // Purchase
                $this->availableAccounts = Account::where('type', 'purchase')->get();
                break;
            case 3: // payment
                $this->dr_accounts = Account::whereIn('group_id', [20, 16, 1, 18, 25, 27, 4, 7, 14, 30, 9, 28, 29, 10, 26])->get();
                $this->cr_accounts = Account::whereIn('group_id', [11, 21])->get();
                break;
            default:
                $this->availableAccounts = Account::all();
        }
        // dd($accountingTypeModel->id, $this->cr_accounts, $this->dr_accounts);

        $this->accountingVoucher = $accountingVoucherId
            ? $this->loadAccountingVoucherModel($accountingVoucherId)
            : $this->newAccountingVoucherModel($accountingTypeModel->id);

        $this->accountingVoucherData = [
            'voucher_series_id' => 1,
            'transaction_date' => $this->accountingVoucher->transaction_date ?? now()->format('Y-m-d'),
            'transaction_time' => $this->accountingVoucher->transaction_time ?? now()->format('H:i'),
            'voucher_number' => $this->accountingVoucher->voucher_number ?? '',
            'description' => $this->accountingVoucher->description ?? '',
            'accounting_type_id' => $this->accountingVoucher->accounting_type_id ?? $accountingTypeModel->id,
        ];


        if ($accountingVoucherId) {
            $this->accountingVoucherItems = $this->accountingVoucher->accountingVoucherItems->map(function ($accountingVoucherItems) {
                return [
                    'avr_item_type' => $accountingVoucherItems->avr_item_type,
                    'cr_account_id' => $accountingVoucherItems->cr_account_id,
                    'dr_account_id' => $accountingVoucherItems->cr_account_id,
                    'cr_amount' => $accountingVoucherItems->cr_account_id,
                    'dr_amount' => $accountingVoucherItems->cr_account_id,
                    'description' => $accountingVoucherItems->description,
                ];
            })->toArray();
        } else {

            $this->accountingVoucherItems = [
                ['avr_item_type' => 'C', 'cr_account_id' => '', 'dr_account_id' => '', 'cr_amount' => 0, 'dr_amount' => 0, 'description' => ''],
                ['avr_item_type' => 'D', 'cr_account_id' => '', 'dr_account_id' => '', 'cr_amount' => 0, 'dr_amount' => 0, 'description' => ''],
            ];

            //check this
            $this->accountingVoucherItems[0]['avr_item_type'] = $accountingTypeModel->id == 1 ? 'C' : 'D';
            $this->accountingVoucherItems[1]['avr_item_type'] = $accountingTypeModel->id == 1 ? 'D' : 'C';;
        }

        if ($accountingVoucherId) {
        } else {
            $this->voucher = new AccountingVoucher([
                'accounting_type_id' => $accountingTypeModel->id,
            ]);
        }
    }


    protected function loadAccountingVoucherModel(int $id): AccountingVoucher
    {
        return AccountingVoucher::findOrFail($id);
    }

    protected function newAccountingVoucherModel(int $accountingTypeId): AccountingVoucher
    {
        return new AccountingVoucher(['accounting_type_id' => $accountingTypeId]);
    }


    public function addRow(): void
    {
        $this->accountingVoucherItems[] = ['cr_account_id' => '', 'dr_account_id' => '', 'cr_amount' => 0, 'dr_amount' => 0, 'description' => ''];
    }

    public function removeRow($index): void
    {
        if (count($this->accountingVoucherItems) > 2) {
            unset($this->accountingVoucherItems[$index]);
            $this->accountingVoucherItems = array_values($this->accountingVoucherItems);
        }
    }

    public function save()
    {
        $this->validate([
            'accountingVoucherData.transaction_date' => 'required|date',
            'accountingVoucherData.voucher_number' => 'required|string',
            'accountingVoucherData.voucher_notes' => 'nullable|string',
            'accountingVoucherItems' => 'required|array|min:2',
            'accountingVoucherItems.*.avr_item_type' => 'required|in:C,D',
            'accountingVoucherItems.*.cr_account_id' => 'nullable|exists:accounts,id',
            'accountingVoucherItems.*.dr_account_id' => 'nullable|exists:accounts,id',
            'accountingVoucherItems.*.cr_amount' => 'required|numeric|min:0',
            'accountingVoucherItems.*.dr_amount' => 'required|numeric|min:0',
            'accountingVoucherItems.*.description' => 'nullable|string',
        ]);

        $totalCredit = collect($this->accountingVoucherItems)->sum('cr_amount');
        $totalDebit = collect($this->accountingVoucherItems)->sum('dr_amount');
        if ($totalCredit != $totalDebit) {
            $this->addError('accountingVoucherItems', 'Total debit and credit amounts must match.');
            return;
        }

        $this->voucher->voucher_series_id = $this->accountingVoucherData['voucher_series_id'] ?? 1;
        $this->voucher->transaction_date = $this->accountingVoucherData['transaction_date'];
        $this->voucher->transaction_time = $this->accountingVoucherData['transaction_time'] ?? now()->format('H:i');
        $this->voucher->voucher_number = $this->accountingVoucherData['voucher_number'];
        $this->voucher->voucher_notes = $this->accountingVoucherData['voucher_notes'] ?? '';
        $this->voucher->accounting_type_id = $this->accountingVoucherData['accounting_type_id'];
        $this->voucher->save();



        $this->voucher->accountingVoucherItems->each->delete();
        foreach ($this->accountingVoucherItems as $accountingVoucherItems) {
            $this->voucher->accountingVoucherItems()->create($accountingVoucherItems);
        }

        $accountingTypeSlug = AccountingType::findOrFail($this->accountingVoucher->accounting_voucher_id)->slug;
        return redirect(route('invoice_index', ['invoiceType' => $accountingTypeSlug]));
    }

    public function render()
    {

        return view('livewire.transaction.accounting-voucher-form');
    }
}
