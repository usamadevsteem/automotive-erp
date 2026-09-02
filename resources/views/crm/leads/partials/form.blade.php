<form id="leadForm"
      action="{{ isset($lead) ? route('leads.update', $lead) : route('leads.store') }}">
    @csrf
    @if(isset($lead)) @method('PUT') @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-person text-primary me-2"></i>Lead Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold required">Full Name</label>
                            <input type="text" name="full_name"
                                   value="{{ old('full_name', $lead->full_name ?? '') }}"
                                   class="form-control @error('full_name') is-invalid @enderror" required>
                            @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold required">Phone</label>
                            <input type="text" name="phone"
                                   value="{{ old('phone', $lead->phone ?? '') }}"
                                   class="form-control @error('phone') is-invalid @enderror" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="email"
                                   value="{{ old('email', $lead->email ?? '') }}"
                                   class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Link to Customer</label>
                            <select name="customer_id" class="form-select">
                                <option value="">New Prospect (not linked)</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}"
                                        {{ old('customer_id', $lead->customer_id ?? '') == $c->id ? 'selected' : '' }}>
                                        {{ $c->full_name }} — {{ $c->mobile }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Vehicle Interest</label>
                            <input type="text" name="vehicle_interest"
                                   value="{{ old('vehicle_interest', $lead->vehicle_interest ?? '') }}"
                                   class="form-control" placeholder="e.g. Toyota Fortuner 2022, white">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Budget (PKR)</label>
                            <input type="number" name="budget"
                                   value="{{ old('budget', $lead->budget ?? '') }}"
                                   class="form-control" placeholder="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Notes</label>
                            <textarea name="notes" rows="3" class="form-control"
                                      placeholder="Any additional notes...">{{ old('notes', $lead->notes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-semibold"><i class="bi bi-gear text-primary me-2"></i>Lead Settings</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold required">Source</label>
                        <select name="source" class="form-select" required>
                            @foreach(\App\Models\Lead::SOURCES as $k => $v)
                                <option value="{{ $k }}"
                                    {{ old('source', $lead->source ?? 'walk_in') === $k ? 'selected' : '' }}>
                                    {{ $v }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($salesUsers as $u)
                                <option value="{{ $u->id }}"
                                    {{ old('assigned_to', $lead->assigned_to ?? auth()->id()) == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Next Follow-Up</label>
                        <input type="datetime-local" name="next_follow_up"
                               value="{{ old('next_follow_up', isset($lead->next_follow_up) ? $lead->next_follow_up->format('Y-m-d\TH:i') : '') }}"
                               class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
