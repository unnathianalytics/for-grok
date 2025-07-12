<div class="position-relative w-100">
    <input type="text" wire:model.live="query" class="form-control" placeholder="Search..." autocomplete="off">

    @if (!empty($results))
        <div class="dropdown-menu show w-100 mt-1 shadow position-absolute" style="z-index: 1000;">
            @php $index = 0; @endphp
            @foreach ($results as $group)
                <h6 class="dropdown-header text-uppercase text-secondary small">
                    {{ $group['label'] }} ({{ count($group['items']) }})
                </h6>
                @foreach ($group['items'] as $item)
                    <div class="dropdown-item" wire:ignore>
                        <a href="{{ $item['url'] }}" class="d-block text-decoration-none text-white">
                            <div class="fw-semibold">{{ $item['title'] }}</div>
                            <div class="small">{{ $item['subtitle'] }}</div>
                            <div class="small">Query: {{ $item['query'] }}</div>
                        </a>
                    </div>
                    @php $index++; @endphp
                @endforeach
                <div class="dropdown-divider"></div>
            @endforeach
        </div>
    @endif
</div>
