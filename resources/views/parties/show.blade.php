@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">{{ $party->name }}</h3>
            <p class="text-muted mb-0">
                Party Details & Debit/Credit History
            </p>
        </div>

        <a href="{{ route('parties.index') }}" class="btn btn-outline-secondary">
            ← Back to Parties
        </a>
    </div>


    {{-- Party Information --}}
    <div class="card mb-4">
        <div class="card-header">
            <strong>Party Information</strong>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">
                    <small class="text-muted">Name</small>
                    <div class="fw-bold">
                        {{ $party->name }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <small class="text-muted">Type</small>
                    <div class="fw-bold">
                        {{ $party->type_label }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <small class="text-muted">Status</small>
                    <div>
                        @if($party->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <small class="text-muted">Phone</small>
                    <div>
                        {{ $party->phone ?: '-' }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <small class="text-muted">Email</small>
                    <div>
                        {{ $party->email ?: '-' }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <small class="text-muted">Branch</small>
                    <div>
                        {{ $party->branch?->name ?? 'All Branches' }}
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <small class="text-muted">Address</small>
                    <div>
                        {{ $party->address ?: '-' }}
                    </div>
                </div>

                <div class="col-md-12">
                    <small class="text-muted">Notes</small>
                    <div>
                        {{ $party->notes ?: '-' }}
                    </div>
                </div>

            </div>

        </div>
    </div>


    {{-- Balance Summary --}}
    <div class="row mb-4">

        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body">
                    <small class="text-muted">Total Debit</small>
                    <h4 class="mb-0 text-danger">
                        PKR {{ number_format($totalDebit, 2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body">
                    <small class="text-muted">Total Credit</small>
                    <h4 class="mb-0 text-success">
                        PKR {{ number_format($totalCredit, 2) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body">
                    <small class="text-muted">Balance</small>
                    <h4 class="mb-0 text-primary">
                        PKR {{ number_format($balance, 2) }}
                    </h4>
                </div>
            </div>
        </div>

    </div>


    {{-- Debit / Credit History --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Debit / Credit History</strong>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary">
                {{ $party->partyNotes->count() }} Record(s)
            </span>
            <button
                type="button"
                class="btn btn-primary btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#addNoteModal">
                + Add Note
            </button>
        </div>
    </div>

        <div class="card-body p-0">
            @if($party->partyNotes->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Vehicle</th>
                                <th>Amount</th>
                                <th>Created By</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach($party->partyNotes as $note)
                                <tr>
                                    <td>
                                        {{ $note->note_date?->format('d M Y') }}
                                    </td>
                                   
                                    <td>
                                        @if($note->type === 'debit')
                                            <span class="badge bg-danger">
                                                Debit
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                Credit
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $note->description ?: '-' }}
                                    </td>

                                    <td>
                                         @if($note->vehicle)
                                                <div>
                                                    <strong>
                                                        {{ $note->vehicle->make?->name ?? '' }}
                                                        {{ $note->vehicle->vehicleModel?->name ?? '' }}
                                                    </strong>

                                                    @if($note->vehicle->year)
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $note->vehicle->year }}
                                                        </small>
                                                    @endif

                                                    @if($note->vehicle->stock_number)
                                                        <br>
                                                        <small class="text-muted">
                                                            Stock: {{ $note->vehicle->stock_number }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                    <td class="text-end fw-bold" style="text-align:start!important">
                                        PKR {{ number_format($note->amount, 2) }}
                                    </td>
                                    <td>
                                        {{ $note->createdBy?->name ?? '-' }}
                                    </td>

                                    <td>
                                        <div class="dropdown" style="text-align: right;">
                                            <button
                                                class="btn btn-light border dropdown-toggle"
                                                type="button"
                                                data-bs-toggle="dropdown"
                                                aria-expanded="false">

                                                Actions

                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end shadow">

                                                {{-- View Details --}}
                                                <li>
                                                    <button
                                                        type="button"
                                                        class="dropdown-item"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewNoteModal{{ $note->id }}">

                                                        <i class="bi bi-eye me-2"></i>
                                                        View Details

                                                    </button>
                                                </li>

                                                {{-- Edit --}}
                                                <li>
                                                    <button
                                                        type="button"
                                                        class="dropdown-item edit-note-btn"
                                                        data-note-id="{{ $note->id }}"
                                                        data-vehicle-id="{{ $note->vehicle_id }}"
                                                        data-type="{{ $note->type }}"
                                                        data-amount="{{ $note->amount }}"
                                                        data-note-date="{{ \Carbon\Carbon::parse($note->note_date)->format('Y-m-d') }}"
                                                        data-description="{{ $note->description }}"
                                                        data-update-url="{{ route('parties.notes.update', [$party, $note]) }}">
                                                        
                                                        <i class="bi bi-pencil"></i>
                                                        Edit
                                                    </button>
                                                </li>

                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>

                                                {{-- Remove --}}
                                                <li>
                                                    <button
                                                    type="button"
                                                    class="dropdown-item text-danger delete-note-btn"
                                                    data-note-id="{{ $note->id }}"
                                                    data-note-number="{{ $note->note_number }}"
                                                    data-delete-url="{{ route('parties.notes.destroy', [$party, $note]) }}">
                                                    <i class="bi bi-trash"></i>
                                                    Remove
                                                </button>
                                                </li>

                                            </ul>
                                        </div>
                                    </td>
                                </tr>

                            @endforeach
                        </tbody>
                    </table>
                </div>

            @else

                <div class="text-center py-5 text-muted">
                    <h5>No debit or credit notes found</h5>
                    <p class="mb-0">
                        Add a debit or credit note for this party.
                    </p>
                </div>

            @endif
        </div>
    </div>
</div>


{{-- Add Debit / Credit Note Modal --}}
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST"
              action="{{ route('parties.notes.store', $party) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Add Debit / Credit Note
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">
                    {{-- Note Type --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Type
                        </label>
                        <select
                            name="type"
                            class="form-select"
                            required>
                            <option value="">
                                Select Type
                            </option>
                            <option value="debit">
                                Debit
                            </option>
                            <option value="credit">
                                Credit
                            </option>
                        </select>
                    </div>


                    {{-- Amount --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Amount
                        </label>
                        <input
                            type="number"
                            name="amount"
                            class="form-control"
                            step="0.01"
                            min="0.01"
                            required>
                    </div>


                    {{-- Date --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Note Date
                        </label>
                        <input
                            type="date"
                            name="note_date"
                            class="form-control"
                            value="{{ now()->format('Y-m-d') }}"
                            required>
                    </div>

                    {{-- Related Vehicle (Optional) --}}
            <div class="mb-3">
                <label class="form-label">
                    Related Vehicle
                    <span class="text-muted">(Optional)</span>
                </label>

                <select name="vehicle_id" class="form-select">
                    <option value="">
                        No Vehicle / General Transaction
                    </option>

                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">
                            {{ $vehicle->stock_number ?? 'Vehicle #' . $vehicle->id }}
                            —
                            {{ $vehicle->make?->name }}
                            {{ $vehicle->vehicleModel?->name }}

                            @if($vehicle->registration_number)
                                ({{ $vehicle->registration_number }})
                            @endif
                        </option>
                    @endforeach
                </select>

                <div class="form-text">
                    Select a vehicle only if this debit or credit is related to a specific vehicle.
                </div>
            </div>


                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Description
                        </label>
                        <textarea
                            name="description"
                            class="form-control"
                            rows="3">
                        </textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary">
                        Save Note
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


    {{-- Edit Note Modal --}}
<div class="modal fade" id="editNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="editNoteForm">
            @csrf
            @method('PUT')

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Edit Note
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>

                <div class="modal-body">

                    {{-- Vehicle --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Vehicle <span class="text-muted">(Optional)</span>
                        </label>

                        <select
                            name="vehicle_id"
                            id="editVehicleId"
                            class="form-select">

                            <option value="">
                                No Vehicle
                            </option>

                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">
                                    {{ $vehicle->make?->name }}
                                    {{ $vehicle->vehicleModel?->name }}
                                    - {{ $vehicle->year }}
                                    - {{ $vehicle->stock_number }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    {{-- Type --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Type
                        </label>

                        <select
                            name="type"
                            id="editType"
                            class="form-select"
                            required>

                            <option value="debit">
                                Debit
                            </option>

                            <option value="credit">
                                Credit
                            </option>

                        </select>
                    </div>

                    {{-- Amount --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Amount
                        </label>

                        <input
                            type="number"
                            name="amount"
                            id="editAmount"
                            class="form-control"
                            step="0.01"
                            min="0.01"
                            required>
                    </div>

                    {{-- Note Date --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Note Date
                        </label>

                        <input
                            type="date"
                            name="note_date"
                            id="editNoteDate"
                            class="form-control"
                            required>
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Description
                        </label>

                        <textarea
                            name="description"
                            id="editDescription"
                            class="form-control"
                            rows="3">
                        </textarea>
                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary">
                        Update Note
                    </button>

                </div>

            </div>
        </form>
    </div>
</div>


{{-- Delete Note Confirmation Modal --}}
<div class="modal fade" id="deleteNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="deleteNoteForm">
            @csrf
            @method('DELETE')

            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        Remove Note
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>

                <div class="modal-body">

                    <p class="mb-2">
                        Are you sure you want to remove this note?
                    </p>

                    <div class="alert alert-warning mb-0">
                        <strong id="deleteNoteNumber"></strong>
                        <br>
                        This action cannot be undone.
                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-danger">
                        <i class="bi bi-trash"></i>
                        Remove Note
                    </button>

                </div>

            </div>
        </form>
    </div>
</div>


    {{-- View Note Details Modals --}}
@foreach($party->partyNotes as $note)

<div
    class="modal fade"
    id="viewNoteModal{{ $note->id }}"
    tabindex="-1"
    aria-labelledby="viewNoteModalLabel{{ $note->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            {{-- Header --}}
            <div class="modal-header">
                <div>
                    <h5
                        class="modal-title"
                        id="viewNoteModalLabel{{ $note->id }}">

                        Note Details
                    </h5>
                    <small class="text-muted">
                        {{ $note->note_number }}
                    </small>
                </div>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>
            </div>


            {{-- Body --}}
            <div class="modal-body">
                <div class="row g-4">
                    {{-- Note Number --}}
                    <div class="col-md-6">
                        <small class="text-muted">
                            Note Number
                        </small>

                        <div class="fw-semibold">
                            {{ $note->note_number }}
                        </div>
                    </div>

                    {{-- Type --}}
                    <div class="col-md-6">
                        <small class="text-muted">
                            Type
                        </small>
                        <div>
                            @if($note->type === 'debit')
                                <span class="badge bg-danger">
                                    Debit
                                </span>
                            @else
                                <span class="badge bg-success">
                                    Credit
                                </span>
                            @endif
                        </div>
                    </div>


                    {{-- Amount --}}
                    <div class="col-md-6">
                        <small class="text-muted">
                            Amount
                        </small>

                        <div class="fw-bold fs-5">
                            PKR {{ number_format($note->amount, 2) }}
                        </div>
                    </div>


                    {{-- Note Date --}}
                    <div class="col-md-6">
                        <small class="text-muted">
                            Note Date
                        </small>

                        <div class="fw-semibold">
                            {{ $note->note_date?->format('d M Y') }}
                        </div>
                    </div>


                    {{-- Description --}}
                    <div class="col-12">
                        <small class="text-muted">
                            Description
                        </small>

                        <div class="fw-semibold">
                            {{ $note->description ?: '—' }}
                        </div>
                    </div>


                    {{-- Vehicle --}}
                    <div class="col-12">

                        <hr>

                        <h6 class="mb-3">
                            <i class="bi bi-car-front me-2"></i>
                            Vehicle Details
                        </h6>

                        @if($note->vehicle)

                            <div class="row g-3">

                                <div class="col-md-4">
                                    <small class="text-muted">
                                        Vehicle
                                    </small>

                                    <div class="fw-semibold">
                                        {{ $note->vehicle->make?->name }}
                                        {{ $note->vehicle->vehicleModel?->name }}
                                    </div>
                                </div>


                                <div class="col-md-4">
                                    <small class="text-muted">
                                        Year
                                    </small>

                                    <div class="fw-semibold">
                                        {{ $note->vehicle->year ?: '—' }}
                                    </div>
                                </div>


                                <div class="col-md-4">
                                    <small class="text-muted">
                                        Stock Number
                                    </small>

                                    <div class="fw-semibold">
                                        {{ $note->vehicle->stock_number ?: '—' }}
                                    </div>
                                </div>

                            </div>

                        @else

                            <div class="text-muted">
                                No vehicle linked with this note.
                            </div>

                        @endif

                    </div>


                    {{-- Created By --}}
                    <div class="col-12">

                        <hr>

                        <small class="text-muted">
                            Created By
                        </small>

                        <div class="fw-semibold">
                            {{ $note->createdBy?->name ?? '—' }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- Footer --}}
            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    Close

                </button>

            </div>

        </div>

    </div>

</div>

@endforeach

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | EDIT NOTE
    |--------------------------------------------------------------------------
    */

    const editButtons = document.querySelectorAll('.edit-note-btn');

    editButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            // Get values from Edit button
            const vehicleId = this.dataset.vehicleId;
            const type = this.dataset.type;
            const amount = this.dataset.amount;
            const noteDate = this.dataset.noteDate;
            const description = this.dataset.description;
            const updateUrl = this.dataset.updateUrl;

            // Get Edit form
            const form = document.getElementById('editNoteForm');

            // Set Update URL
            form.action = updateUrl;

            // Fill existing data
            document.getElementById('editVehicleId').value = vehicleId || '';
            document.getElementById('editType').value = type || '';
            document.getElementById('editAmount').value = amount || '';
            document.getElementById('editNoteDate').value = noteDate || '';
            document.getElementById('editDescription').value = description || '';

            // Open Edit Modal
            const modalElement = document.getElementById('editNoteModal');

            const modal = new bootstrap.Modal(modalElement);

            modal.show();

        });

    });


    /*
    |--------------------------------------------------------------------------
    | DELETE / REMOVE NOTE
    |--------------------------------------------------------------------------
    */

    const deleteButtons = document.querySelectorAll('.delete-note-btn');

    deleteButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            // Get values from Remove button
            const deleteUrl = this.dataset.deleteUrl;
            const noteNumber = this.dataset.noteNumber;

            // Get Delete form
            const deleteForm = document.getElementById('deleteNoteForm');

            // Set Delete URL
            deleteForm.action = deleteUrl;

            // Show note number in confirmation modal
            document.getElementById('deleteNoteNumber').textContent = noteNumber;

            // Open Delete Modal
            const deleteModalElement =
                document.getElementById('deleteNoteModal');

            const deleteModal =
                new bootstrap.Modal(deleteModalElement);

            deleteModal.show();

        });

    });

});
</script>

@endsection 
