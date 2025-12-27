@props(['headers' => [], 'mobileCardSlot' => null])

{{-- Mobile: Card Layout --}}
<div class="d-lg-none">
    @if($mobileCardSlot)
        {{ $mobileCardSlot }}
    @else
        <div class="alert alert-info">
            Mobile card layout not configured. Use the mobileCardSlot.
        </div>
    @endif
</div>

{{-- Desktop: Table --}}
<div class="d-none d-lg-block">
    <div class="table-responsive">
        <table {{ $attributes->merge(['class' => 'table table-hover align-middle']) }}>
            @if(count($headers) > 0)
                <thead>
                    <tr>
                        @foreach($headers as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
