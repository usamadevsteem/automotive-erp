<form id="tradeInForm" action="{{ route('trade-ins.store') }}">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Customer</label>
            <select name="customer_id" class="form-select" required>
                <option value="">Select Customer</option>
                @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->full_name }} — {{ $c->mobile }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">New Vehicle (being purchased)</label>
            <select name="new_vehicle_id" class="form-select" required>
                <option value="">Select Vehicle</option>
                @foreach($vehicles as $v)<option value="{{ $v->id }}">{{ $v->stock_number }} — {{ $v->make->name }} {{ $v->vehicleModel->name }} ({{ $v->sale_price_formatted }})</option>@endforeach
            </select>
        </div>

        <div class="col-12"><hr class="my-1"><h6 class="small fw-semibold text-muted text-uppercase">Trade-In Vehicle Details</h6></div>

        <div class="col-md-6"><label class="form-label small fw-semibold required">Make</label><input type="text" name="trade_make" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label small fw-semibold required">Model</label><input type="text" name="trade_model" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label small fw-semibold required">Year</label><input type="number" name="trade_year" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Mileage</label><input type="number" name="trade_mileage" class="form-control"></div>
        <div class="col-md-4"><label class="form-label small fw-semibold">Color</label><input type="text" name="trade_color" class="form-control"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Registration #</label><input type="text" name="trade_registration" class="form-control"></div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold required">Condition</label>
            <select name="trade_condition" class="form-select" required>
                @foreach(['excellent'=>'Excellent','good'=>'Good','fair'=>'Fair','poor'=>'Poor'] as $k=>$v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Chassis #</label><input type="text" name="chassis_number" class="form-control"></div>
        <div class="col-md-6"><label class="form-label small fw-semibold">Engine #</label><input type="text" name="engine_number" class="form-control"></div>

        <div class="col-12"><hr class="my-1"><h6 class="small fw-semibold text-muted text-uppercase">Valuation</h6></div>

        <div class="col-md-4">
            <label class="form-label small fw-semibold required">Market Value (PKR)</label>
            <input type="number" name="market_value" class="form-control" required min="0">
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold required">Offered Value (PKR)</label>
            <input type="number" name="offered_value" class="form-control" required min="0">
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Notes</label>
            <input type="text" name="notes" class="form-control">
        </div>
    </div>
</form>
