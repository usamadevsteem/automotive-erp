@extends('layouts.app')
@section('title', 'Parties')
@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-people-fill text-primary me-2"></i>
                Parties
            </h3>
            <p class="text-muted mb-0">
                Manage people, investors, businesses and showrooms with their debit & credit notes.
            </p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPartyModal">
            <i class="bi bi-plus-circle me-1"></i>
            Add Party
        </button>
    </div>


    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('parties.index') }}">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label small text-muted">Search</label>
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search name, phone or email..."
                            value="{{ request('search') }}"
                        >
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Party Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="person" {{ request('type') === 'person' ? 'selected' : '' }}>
                                Person
                            </option>
                            <option value="investor" {{ request('type') === 'investor' ? 'selected' : '' }}>
                                Investor
                            </option>
                            <option value="business" {{ request('type') === 'business' ? 'selected' : '' }}>
                                Business
                            </option>
                            <option value="showroom" {{ request('type') === 'showroom' ? 'selected' : '' }}>
                                Showroom
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i>
                            Filter
                        </button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('parties.index') }}" class="btn btn-light border w-100">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>


    {{-- Parties Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-list-ul text-primary me-2"></i>
                    All Parties
                </h5>
                <span class="text-muted small">
                    {{ $parties->count() }} record(s)
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Party</th>
                            <th>Type</th>
                            <th>Contact</th>
                            <th>Notes</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parties as $party)

                            <tr>
                                {{-- Party --}}
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center me-3"
                                             style="width:42px;height:42px;">
                                            @if($party->type === 'investor')
                                                <i class="bi bi-cash-stack"></i>
                                            @elseif($party->type === 'showroom')
                                                <i class="bi bi-building"></i>
                                            @elseif($party->type === 'business')
                                                <i class="bi bi-briefcase"></i>
                                            @else
                                                <i class="bi bi-person"></i>
                                            @endif

                                        </div>
                                        <div>
                                            <div class="fw-semibold">
                                                {{ $party->name }}
                                            </div>

                                            @if($party->branch)
                                                <small class="text-muted">
                                                    {{ $party->branch->name }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>


                                {{-- Type --}}
                                <td>
                                    @php
                                        $typeClass = match($party->type) {
                                            'investor' => 'success',
                                            'showroom' => 'primary',
                                            'business' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp

                                    <span class="badge bg-{{ $typeClass }}-subtle text-{{ $typeClass }} border border-{{ $typeClass }}-subtle">
                                        {{ $party->type_label }}
                                    </span>
                                </td>


                                {{-- Contact --}}
                                <td>
                                    @if($party->phone)
                                        <div>
                                            <i class="bi bi-telephone text-muted me-1"></i>
                                            {{ $party->phone }}
                                        </div>
                                    @endif

                                    @if($party->email)
                                        <small class="text-muted">
                                            {{ $party->email }}
                                        </small>
                                    @endif

                                    @if(!$party->phone && !$party->email)
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>


                                {{-- Notes --}}
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $party->party_notes_count ?? 0 }}
                                        Note(s)
                                    </span>
                                </td>


                                {{-- Status --}}
                                <td>
                                    @if($party->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border">
                                            Inactive
                                        </span>
                                    @endif
                                </td>


                                {{-- Action --}}
                                <td class="text-end pe-4">
                                    <a href="{{ route('parties.show', $party) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>
                                        View
                                    </a>
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="bi bi-people fs-1 text-muted"></i>
                                    </div>
                                    <h6 class="fw-semibold">No parties found</h6>
                                    <p class="text-muted mb-3">
                                        Start by adding a person, investor, business or showroom.
                                    </p>
                                    <button class="btn btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#createPartyModal">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Add First Party

                                    </button>
                                </td>
                           </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


{{-- Create Party Modal --}}
<div class="modal fade" id="createPartyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('parties.store') }}">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-person-plus text-primary me-2"></i>
                            Add Party
                        </h5>
                        <small class="text-muted">
                            Add a person, investor, business or showroom.
                        </small>
                    </div>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Name --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   placeholder="Enter party name"
                                   required>

                        </div>


                        {{-- Type --}}
                        <div class="col-md-6">
                            <label class="form-label">
                                Party Type <span class="text-danger">*</span>
                            </label>
                            <select name="type" class="form-select" required>
                                <option value="person">Person</option>
                                <option value="investor">Investor</option>
                                <option value="business">Business</option>
                                <option value="showroom">Showroom</option>

                            </select>
                        </div>


                        {{-- Phone --}}
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text"
                                   name="phone"
                                   class="form-control"
                                   placeholder="Phone number">

                        </div>


                        {{-- Email --}}
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   placeholder="Email address">

                        </div>


                        {{-- Address --}}
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address"
                                      class="form-control"
                                      rows="2"
                                      placeholder="Address"></textarea>

                        </div>


                        {{-- Notes --}}
                        <div class="col-12">
                            <label class="form-label">Internal Notes</label>
                            <textarea name="notes"
                                      class="form-control"
                                      rows="3"
                                      placeholder="Optional notes about this party"></textarea>

                        </div>
                    </div>
                </div>


                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-light border"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        Save Party

                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection