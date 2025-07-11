<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <span class="navbar-brand">Company</span>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">Administration</a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#">Masters</a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">Item
                                        Groups</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Add Item Group</a>
                                        </li>
                                        <li><a class="dropdown-item" href="#">List Item
                                                Groups</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">Items</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('item_create') }}">Add
                                                Item</a></li>
                                        <li><a class="dropdown-item" href="{{ route('item_index') }}">List
                                                Items</a></li>
                                    </ul>
                                </li>
                                <hr>
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">Account
                                        Groups</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Add Account Group</a>
                                        </li>
                                        <li><a class="dropdown-item" href="#">List Account
                                                Groups</a></li>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item dropdown-toggle" href="#">Accounts</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('account_create') }}">Add
                                                Account</a></li>
                                        <li><a class="dropdown-item" href="{{ route('account_index') }}">List
                                                Accounts</a></li>
                                    </ul>
                                </li>

                            </ul>
                        </li>

                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">Transactions</a>
                    <ul class="dropdown-menu">
                        @foreach (\App\Models\InvoiceType::orderBy('menu_order')->get() as $iType)
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#">{{ $iType->name }}</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item"
                                            href="{{ route('invoice_create', ['invoiceType' => $iType->slug]) }}">Add
                                            {{ $iType->name }}</a></li>
                                    <li><a class="dropdown-item"
                                            href="{{ route('invoice_index', ['invoiceType' => $iType->slug]) }}">List
                                            {{ $iType->name }}</a></li>
                                </ul>
                            </li>
                        @endforeach
                        <hr>
                        @foreach (\App\Models\AccountingType::all() as $aType)
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#">{{ $aType->name }}</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item"
                                            href="{{ route('accounting_voucher_create', ['accountingType' => $aType->slug]) }}">Add
                                            {{ $aType->name }}</a></li>
                                </ul>
                            </li>
                        @endforeach

                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">Display</a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#">Reports</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Sales Report</a></li>
                                <li><a class="dropdown-item" href="#">Purchase Report</a></li>
                                <li><a class="dropdown-item" href="#">Stock Summary</a></li>
                            </ul>
                        </li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#">Dashboards</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Sales Dashboard</a></li>
                                <li><a class="dropdown-item" href="#">Financial Dashboard</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <a class="nav-link" href="#">Print/Email</a>
                <a class="nav-link" href="#">SHS</a>
                <a class="nav-link" href="#">House-Keeping</a>
                <a class="nav-link" href="#">Help</a>
                <a class="nav-link" href="javascript:void(0)">Favourites</a>
                <a class="nav-link" href="javascript:void(0)">Add-On</a>

            </div>

            <div class="ms-auto">

                <livewire:global-search />

            </div>
            <div class="ms-auto">

                <button class="btn btn-outline-light btn-sm" onclick="toggleDarkMode()">🌙</button>
                <a href="{{ route('logout') }}" class="btn btn-outline-light btn-sm"
                    onclick="event.preventDefault(); document.getElementById('frm-logout').submit();">
                    Logout
                </a>
                <form id="frm-logout" action="{{ route('logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>

            </div>
        </div>
    </div>
</nav>
