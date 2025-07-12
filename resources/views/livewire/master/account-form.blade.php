<div class="container mt-4">
    <h2 class="mb-4 h3">
        {{ $account ? "Edit {$account->name}" : 'Create Account' }}
    </h2>

    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif
    <form wire:submit="save">
        <div class="row">
            <div class="col-lg-9">
                <div class="row">
                    <div class="col-lg-5 mb-3">
                        <!-- first column start -->
                        <label class="form-label">Name</label>
                        <input type="text" wire:model.live="name" class="form-control" />
                    </div>
                    <div class="col-lg-3 mb-3">
                        <label class="form-label">Account Group</label>
                        <select wire:model="group_id" class="form-select form-select-sm">
                            <option value="">-- Select Group --</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 mb-3">
                        <label class="form-label">Op. Balance</label>
                        <input type="number" wire:model="op_balance" class="form-control" />
                    </div>
                    <div class="col-lg-2 mb-3">
                        <label class="form-label">Cr/Dr</label>
                        <select wire:model="cr_dr" class="form-select form-select-sm">
                            <option value="">-- Cr/Dr --</option>
                            @foreach ($crdr_values as $key => $value)
                                <option value="{{ $key }}">
                                    {{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-12 mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" wire:model="address" class="form-control" />
                    </div>
                    <div class="col-lg-3 mb-3">
                        <label class="form-label">Mobile</label>
                        <input type="text" wire:model="mobile" class="form-control" />
                    </div>
                    <div class="col-lg-3 mb-3">
                        <label class="form-label">EMail</label>
                        <input type="email" wire:model="email" class="form-control" />
                    </div>
                    <div class="col-lg-2 mb-3 bg-white">
                        <label class="form-label">Registered Dealer?</label>
                        <input type="checkbox" wire:model="is_registered" style="display: block"
                            class="form-check-input" />
                    </div>
                    <div class="col-lg-4 mb-3">
                        <label class="form-label">GSTIN</label>
                        <input type="text" wire:model="gstin" class="form-control" />
                    </div>
                    <!-- first column end -->
                </div>
                <button {{ $is_editable ? '' : 'disabled' }} type="submit" class="btn btn-primary">
                    {{ $account ? 'Update' : 'Create' }}
                </button>
            </div>


            <div class="col-lg-3">
                <div class="row">
                    <div class="col-12 mb-3">
                        <!-- third column start -->
                        @if ($errors->any())
                            <div class="alert alert-danger mt-5">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <!-- third column end -->
                    </div>
                </div>
            </div>


    </form>
</div>
