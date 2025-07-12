<?php

namespace App\Livewire\Master;

use Livewire\Component;
use App\Models\{AccountGroup, Account};

class AccountForm extends Component
{
    public ?Account $account = null;
    public $group_id, $name, $address, $mobile, $email, $is_registered = false, $gstin, $op_balance, $cr_dr, $is_editable = true, $is_deletable = true;
    public $crdr_values = [];
    public $groups;
    public function mount(?Account $account = null)
    {
        $this->groups = AccountGroup::all();
        $this->crdr_values = ['cr' => 'Credit', 'dr' => 'Debit'];

        $this->account = $account;

        if ($account && $account->exists) {
            $this->group_id = $account->group_id;
            $this->name = $account->name;
            $this->address = $account->address;
            $this->mobile = $account->mobile;
            $this->email = $account->email;
            $this->is_registered = $account->is_registered;
            $this->gstin = $account->gstin;
            $this->op_balance = $account->op_balance;
            $this->cr_dr = $account->cr_dr;
            $this->is_editable = $account->is_editable;
            $this->is_deletable = $account->is_deletable;
        } else {
            $this->account = null;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'group_id' => 'required|exists:account_groups,id',
            'address' => 'nullable|string|max:500',
            'mobile' => 'nullable|numeric|min:6000000000|max:9999999999',
            'email' => 'nullable|email|max:255',
            'is_registered' => 'boolean',
            'gstin' => $this->is_registered == true ? 'required|string|max:15' : 'nullable|string',
            'op_balance' => 'required|numeric',
            'cr_dr' => 'required',

        ]);

        Account::updateOrCreate(
            ['id' => $this->account?->id],
            $validated
        );

        session()->flash('message', $this->account ? 'Account updated successfully.' : 'Account created successfully.');
        return redirect()->route('account_index');
    }


    public function render()
    {
        return view('livewire.master.account-form');
    }
}
