<div class="container mt-4">
    <h2 class="mb-4 h3">
        {{ $item ? "Edit {$item->name}" : 'Create Item' }}
    </h2>

    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save">
        <div class="row">
            <div class="col-lg-4">
                <div class="row">
                    <div class="col-12 mb-3">
                        <!-- first column start -->
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" wire:model="name" class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Item Group</label>
                            <select wire:model="group_id" class="form-select form-select-sm">
                                <option value="">-- Select Group --</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Barcode</label>
                            <input type="text" wire:model="barcode" class="form-control" />
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Unit</label>
                                    <select wire:model="uom_id" class="form-select form-select-sm">
                                        <option value="">Select Unit</option>
                                        @foreach ($baseUoms as $uom)
                                            <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Opening Stock</label>
                                    <input type="text" wire:model.live="op_stock_qty" class="form-control" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 bg-white">
                            <label class="form-check-label mt-3"><input type="checkbox" class="form-check-input"
                                    wire:model.live="has_multi_uom" @if ($has_serial_number) disabled @endif
                                    wire:click="toggleMultiUom"> Has Multiple UoM </label>
                            @if ($has_multi_uom)
                                <table class="card table-sm table-bordered">
                                    @foreach ($uoms as $index => $uom)
                                        <tr>
                                            <td>
                                                <label>Alt Unit</label>
                                                <select wire:model="uoms.{{ $index }}.uom_id"
                                                    class="form-select form-select-sm">
                                                    <option value="">Select UOM</option>
                                                    @foreach ($baseUoms as $unit)
                                                        <option value="{{ $unit->id }}">{{ $unit->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <label>Conversion Factor</label>
                                                <input type="number" wire:model="uoms.{{ $index }}.factor"
                                                    class="form-control" placeholder="Factor" step="0.0001" />
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-secondary"
                                                    wire:click="removeUom({{ $index }})">Remove</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                                <button class="btn btn-sm btn-secondary" type="button" wire:click="addUom">Add
                                    UOM</button>
                            @endif
                        </div>
                        <!-- first column end -->
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="row">
                    <div class="col-12 mb-3">
                        <!-- second column start -->
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Tax Category</label>
                                    <select wire:model="tax_category_id" class="form-select form-select-sm">
                                        <option value="">-- Select Group --</option>
                                        @foreach ($taxCategories as $taxCategory)
                                            <option value="{{ $taxCategory->id }}">{{ $taxCategory->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">HSN/SAC</label>
                                    <input type="number" wire:model="hsn_sac" class="form-control" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Sale Price</label>
                                    <input type="number" step="0.01" wire:model="sale_price" class="form-control" />
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">MRP</label>
                                    <input type="number" step="0.01" wire:model="max_retail_price"
                                        class="form-control" />
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Min. Sale Price</label>
                                    <input type="number" step="0.01" wire:model="min_sale_price"
                                        class="form-control" />
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Purchase Price</label>
                                    <input type="number" step="0.01" wire:model="purchase_price"
                                        class="form-control" />
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3 bg-white">
                                    <label class="form-label">Has Serial Number</label>
                                    <input type="checkbox" @if ($has_multi_uom || $hasUsedSerialNumbers) disabled @endif
                                        class="form-check-input" wire:model.live="has_serial_number">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                @if ($has_serial_number && $op_stock_qty > 0)
                                    <div class="mb-3">
                                        <label class="form-label">Serial Numbers</label>
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Serial Number</th>
                                                    <th>Description</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($serialNumbers as $index => $sn)
                                                    @if ($sn['is_opening_stock'])
                                                        <tr>
                                                            <td>
                                                                <input type="text"
                                                                    wire:model="serialNumbers.{{ $index }}.serial_number"
                                                                    class="form-control"
                                                                    @if ($sn['is_used']) disabled @endif />
                                                            </td>
                                                            <td>
                                                                <input type="text"
                                                                    wire:model="serialNumbers.{{ $index }}.description"
                                                                    class="form-control"
                                                                    @if ($sn['is_used']) disabled @endif />
                                                            </td>
                                                            <td>
                                                                @if (!$sn['is_used'])
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-secondary"
                                                                        wire:click="removeSerialNumber({{ $index }})">Remove</button>
                                                                @else
                                                                    <span class="text-muted">Used</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <!-- second column end -->
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="row">
                    <div class="col-12 mb-3">
                        <button type="submit" class="btn btn-primary mb-3">
                            {{ $item ? 'Update' : 'Create' }}
                        </button>
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
        </div>
    </form>
</div>
