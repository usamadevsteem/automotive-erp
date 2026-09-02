<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\PartyNote;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartyController extends Controller
{
    /**
     * Show all parties.
     */
    public function index(Request $request)
    {
        $query = Party::query()
            ->with('branch')
            ->withCount('partyNotes');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $parties = $query
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('parties.index', compact('parties'));
    }

    /**
     * Create a new party.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Party::TYPES))],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $validated['created_by'] = auth()->id();

        Party::create($validated);

        return redirect()
            ->route('parties.index')
            ->with('success', 'Party created successfully.');
    }

   /**
 * Show one party and its debit/credit history.
 */
public function show(Party $party)
{
    $party->load([
        'branch',
        'createdBy',
        'partyNotes' => function ($query) {
            $query->with([
                'createdBy',
                'vehicle.make',
                'vehicle.vehicleModel',
            ])
            ->latest('note_date')
            ->latest('id');
        }
    ]);

    $totalDebit = $party->partyNotes()
        ->where('type', 'debit')
        ->sum('amount');

    $totalCredit = $party->partyNotes()
        ->where('type', 'credit')
        ->sum('amount');

    $balance = $totalDebit - $totalCredit;

    $vehicles = Vehicle::query()
        ->with(['make', 'vehicleModel'])
        ->orderByDesc('created_at')
        ->get();

    return view(
        'parties.show',
        compact(
            'party',
            'totalDebit',
            'totalCredit',
            'balance',
            'vehicles'
        )
    );
}

    
    /**
 * Add a debit or credit note to a party.
 */
    public function storeNote(Request $request, Party $party)
{
    $validated = $request->validate([
        'type' => ['required', Rule::in(array_keys(PartyNote::TYPES))],
        'amount' => ['required', 'numeric', 'min:0.01'],
        'note_date' => ['required', 'date'],
        'description' => ['nullable', 'string'],
        'vehicle_id' => ['nullable', 'exists:vehicles,id'],
        'reference_type' => ['nullable', 'string', 'max:100'],
        'reference_id' => ['nullable', 'integer'],
    ]);

    $validated['party_id'] = $party->id;
    $validated['branch_id'] = $party->branch_id;
    $validated['created_by'] = auth()->id();

    PartyNote::create($validated);

    return redirect()
        ->route('parties.show', $party)
        ->with(
            'success',
            ucfirst($validated['type']) . ' note added successfully.'
        );
}
        /**
     * Update an existing debit or credit note.
     */
    public function updateNote(Request $request, Party $party, PartyNote $note)
    {
        // Safety: make sure the note belongs to this party.
        abort_unless($note->party_id === $party->id, 404);

        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(PartyNote::TYPES))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
        ]);

        $note->update($validated);

        return redirect()
            ->route('parties.show', $party)
            ->with('success', 'Note updated successfully.');
    }

    public function destroyNote(Party $party, PartyNote $note)
    {
    // Security: make sure this note belongs to this party
    if ($note->party_id !== $party->id) {
        abort(404);
    }

    $note->delete();

    return redirect()
        ->route('parties.show', $party)
        ->with('success', 'Note removed successfully.');
    }
}