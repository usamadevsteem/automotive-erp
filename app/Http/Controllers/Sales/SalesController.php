<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Quotation;
use App\Models\SaleInvoice;
use App\Models\TradeIn;
use App\Models\Vehicle;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function __construct(
        private readonly SaleService        $saleService,
        
    ) {}

    // ══════════════════════════════════════════════════════════════
    // QUOTATIONS
    // ══════════════════════════════════════════════════════════════

    public function quotationIndex(Request $request): View
    {
        $quotations = Quotation::with(['customer','vehicle.make','vehicle.vehicleModel','branch'])
            ->when($request->status, fn($q,$v) => $q->where('status',$v))
            ->latest()->paginate(20)->withQueryString();
        return view('sales.quotations.index', compact('quotations'));
    }

    public function quotationCreate(): View
    {
        $customers = Customer::orderBy('full_name')->get(['id','full_name','mobile']);
        $vehicles  = Vehicle::available()->with(['make','vehicleModel'])->orderBy('stock_number')->get();

        if (request()->ajax()) {
            return view('sales.quotations.partials.form', compact('customers', 'vehicles'));
        }

        return view('sales.quotations.create', compact('customers','vehicles'));
    }

    public function quotationStore(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'customer_id' => ['required','exists:customers,id'],
            'vehicle_id'  => ['required','exists:vehicles,id'],
            'lead_id'     => ['nullable','exists:leads,id'],
            'sale_price'  => ['required','numeric','min:0'],
            'discount'    => ['nullable','numeric','min:0'],
            'valid_until' => ['required','date','after:today'],
            'notes'       => ['nullable','string'],
        ]);

        $data['branch_id'] = auth()->user()->branch_id;
        $quotation = $this->saleService->createQuotation($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Quotation {$quotation->quotation_number} created.",
            ]);
        }

        return redirect()->route('quotations.show', $quotation)
            ->with('success', "Quotation {$quotation->quotation_number} created.");
    }

    public function quotationShow(Quotation $quotation): View
    {
        $quotation->load(['customer','vehicle.make','vehicle.vehicleModel','lead','createdBy']);

        if (request()->ajax()) {
            return view('sales.quotations.partials.show-modal', compact('quotation'));
        }

        return view('sales.quotations.show', compact('quotation'));
    }

    public function quotationUpdateStatus(Request $request, Quotation $quotation): RedirectResponse|JsonResponse
    {
        $request->validate(['status' => ['required','in:draft,sent,accepted,rejected,expired']]);
        $this->saleService->updateQuotationStatus($quotation, $request->status);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Quotation status updated.']);
        }

        return back()->with('success', 'Quotation status updated.');
    }

    // ══════════════════════════════════════════════════════════════
    // BOOKINGS
    // ══════════════════════════════════════════════════════════════

    public function bookingIndex(Request $request): View
    {
        $bookings = Booking::with(['customer','vehicle.make','vehicle.vehicleModel'])
            ->when($request->status, fn($q,$v) => $q->where('status',$v))
            ->latest()->paginate(20)->withQueryString();
        return view('sales.bookings.index', compact('bookings'));
    }

    public function bookingCreate(Request $request): View
    {
        $customers  = Customer::orderBy('full_name')->get(['id','full_name','mobile']);
        $vehicles   = Vehicle::available()->with(['make','vehicleModel'])->get();
       $quotations = Quotation::where('status','accepted')
        ->with('customer','vehicle')
        ->get();

    $quotation = $request->quotation_id
        ? Quotation::with('customer','vehicle')->find($request->quotation_id)
        : null;

    $preselectedVehicleId = $request->vehicle_id ?? $quotation?->vehicle_id;
    $preselectedCustomerId = $request->customer_id ?? $quotation?->customer_id;
    $preselectedSalePrice = $quotation?->net_price;

        if ($request->ajax()) {
            return view('sales.bookings.partials.form', compact(
            'customers',
            'vehicles',
            'quotations',
            'preselectedVehicleId',
            'preselectedCustomerId',
            'preselectedSalePrice',
            'quotation'
        ));
                }

        return view('sales.bookings.create', compact(
            'customers',
            'vehicles',
            'quotations',
            'preselectedVehicleId',
            'preselectedCustomerId',
            'preselectedSalePrice',
            'quotation'
        ));
    }

    public function bookingStore(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'customer_id'            => ['required','exists:customers,id'],
            'vehicle_id'             => ['required','exists:vehicles,id'],
            'quotation_id'           => ['nullable','exists:quotations,id'],
            'booking_amount'         => ['required','numeric','min:1'],
            'agreed_sale_price'      => ['required','numeric','min:0'],
            'expected_delivery_date' => ['nullable','date'],
            'payment_method'         => ['required','in:cash,bank_transfer,cheque,online'],
            'payment_reference'      => ['nullable','string','max:100'],
            'notes'                  => ['nullable','string'],
        ]);

        $data['branch_id'] = auth()->user()->branch_id;

        try {
            $booking = $this->saleService->createBooking($data);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Booking {$booking->booking_number} created. Vehicle reserved.",
                ]);
            }

            return redirect()->route('bookings.show', $booking)
                ->with('success', "Booking {$booking->booking_number} created. Vehicle reserved.");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function bookingShow(Booking $booking): View
    {
        $booking->load(['customer','vehicle.make','vehicle.vehicleModel','quotation','createdBy']);

        if (request()->ajax()) {
            return view('sales.bookings.partials.show-modal', compact('booking'));
        }

        return view('sales.bookings.show', compact('booking'));
    }

    public function bookingCancel(Request $request, Booking $booking): RedirectResponse|JsonResponse
    {
        $request->validate(['reason' => ['required','string','max:255']]);
        $this->saleService->cancelBooking($booking, $request->reason);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Booking cancelled. Vehicle released.']);
        }

        return back()->with('success', 'Booking cancelled. Vehicle released.');
    }

    // ══════════════════════════════════════════════════════════════
    // SALE INVOICES
    // ══════════════════════════════════════════════════════════════

    public function invoiceIndex(Request $request): View
    {
        $invoices = SaleInvoice::with(['customer','vehicle.make','vehicle.vehicleModel'])
            ->when($request->status, fn($q,$v) => $q->where('status',$v))
            ->latest()->paginate(20)->withQueryString();
        return view('sales.invoices.index', compact('invoices'));
    }

    public function invoiceCreate(Request $request): View
    {
        $customers  = Customer::orderBy('full_name')->get(['id','full_name','mobile','cnic','tax_status']);
        $vehicles   = Vehicle::whereIn('status',['available','reserved'])
                             ->with(['make','vehicleModel'])->get();
        $bookings   = Booking::where('status','active')->with('customer','vehicle')->get();
        $preVehicle = $request->vehicle_id ? Vehicle::with(['make','vehicleModel'])->find($request->vehicle_id) : null;
        $preBooking = $request->booking_id ? Booking::with(['customer','vehicle'])->find($request->booking_id) : null;

        if ($request->ajax()) {
            return view('sales.invoices.partials.form', compact('customers','vehicles','bookings','preVehicle','preBooking'));
        }

        return view('sales.invoices.create', compact('customers','vehicles','bookings','preVehicle','preBooking'));
    }

    public function invoiceStore(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'customer_id'      => ['required','exists:customers,id'],
            'vehicle_id'       => ['required','exists:vehicles,id'],
            'booking_id'       => ['nullable','exists:bookings,id'],
            'sale_price'       => ['required','numeric','min:0'],
            'discount'         => ['nullable','numeric','min:0'],
            'withholding_tax'  => ['nullable','numeric','min:0'],
            'net_amount'       => ['required','numeric','min:0'],
            'payment_type' => ['required','in:cash,bank_transfer,cheque'],
            'amount_paid'      => ['nullable','numeric','min:0'],
            'invoice_date'     => ['required','date'],
            'notes'            => ['nullable','string'],
        ]);

        $data['branch_id'] = auth()->user()->branch_id;

        try {
            $invoice = $this->saleService->createInvoice($data);

            if ($request->ajax()) {
                return response()->json([
                    'success'              => true,
                    'message'              => "Invoice {$invoice->invoice_number} created successfully.",
                    
                ]);
            }

           

            return redirect()->route('invoices.show', $invoice)
                ->with('success', "Invoice {$invoice->invoice_number} created successfully.");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function invoiceShow(SaleInvoice $invoice): View
    {
        $invoice->load([
            'customer','vehicle.make','vehicle.vehicleModel',
            'booking','delivery','dealFile','commissions.employee',
            
        ]);

        if (request()->ajax()) {
            return view('sales.invoices.partials.show-modal', compact('invoice'));
        }

        return view('sales.invoices.show', compact('invoice'));
    }

    public function invoiceRecordPayment(Request $request, SaleInvoice $invoice): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'amount'           => ['required','numeric','min:1','max:' . $invoice->balance_due],
            'payment_method'   => ['required','in:cash,cheque,bank_transfer,online'],
            'payment_date'     => ['required','date'],
            'reference_number' => ['nullable','string','max:100'],
            'notes'            => ['nullable','string','max:255'],
        ]);

        $this->saleService->recordPayment($invoice, $data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment of PKR ' . number_format($data['amount']) . ' recorded.',
            ]);
        }

        return back()->with('success', 'Payment of PKR ' . number_format($data['amount']) . ' recorded.');
    }

    public function invoiceCancel(Request $request, SaleInvoice $invoice): RedirectResponse|JsonResponse
    {
        $request->validate(['reason' => ['required','string','max:255']]);
        try {
            $this->saleService->cancelInvoice($invoice, $request->reason);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Invoice cancelled.']);
            }

            return back()->with('success', 'Invoice cancelled.');
        } catch (\LogicException $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════
    // DELIVERY ORDERS
    // ══════════════════════════════════════════════════════════════

    public function deliveryCreate(Request $request): View
    {
        $invoice = SaleInvoice::with(['customer','vehicle'])->findOrFail($request->invoice_id);
        return view('sales.deliveries.create', compact('invoice'));
    }

    public function deliveryStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sale_invoice_id'  => ['required','exists:sale_invoices,id'],
            'delivery_date'    => ['required','date'],
            'condition_notes'  => ['nullable','string'],
            'accessories_list' => ['nullable','array'],
            'notes'            => ['nullable','string'],
        ]);

        $invoice            = SaleInvoice::findOrFail($data['sale_invoice_id']);
        $data['customer_id']= $invoice->customer_id;
        $data['vehicle_id'] = $invoice->vehicle_id;
        $data['delivered_by']= auth()->id();
        $data['branch_id']  = auth()->user()->branch_id;

        $delivery = $this->saleService->createDelivery($data);
        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Delivery order {$delivery->delivery_number} created. Vehicle marked delivered.");
    }

    public function deliveryShow(DeliveryOrder $delivery): View
    {
        $delivery->load(['customer','vehicle.make','vehicle.vehicleModel','saleInvoice','deliveredBy']);
        return view('sales.deliveries.show', compact('delivery'));
    }

    // ══════════════════════════════════════════════════════════════
    // TRADE-INS
    // ══════════════════════════════════════════════════════════════

    public function tradeInIndex(): View
    {
        $tradeIns = TradeIn::with(['customer','newVehicle.make','newVehicle.vehicleModel'])
            ->latest()->paginate(20);
        return view('sales.trade-ins.index', compact('tradeIns'));
    }

    public function tradeInCreate(Request $request): View
    {
        $customers = Customer::orderBy('full_name')->get(['id','full_name','mobile']);
        $vehicles  = Vehicle::available()->with(['make','vehicleModel'])->get();

        if ($request->ajax()) {
            return view('sales.trade-ins.partials.form', compact('customers','vehicles'));
        }

        return view('sales.trade-ins.create', compact('customers','vehicles'));
    }

    public function tradeInStore(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'customer_id'    => ['required','exists:customers,id'],
            'new_vehicle_id' => ['required','exists:vehicles,id'],
            'trade_make'     => ['required','string','max:100'],
            'trade_model'    => ['required','string','max:100'],
            'trade_year'     => ['required','integer','min:1990'],
            'trade_registration' => ['nullable','string','max:30'],
            'trade_mileage'  => ['nullable','integer','min:0'],
            'trade_condition'=> ['required','in:excellent,good,fair,poor'],
            'trade_color'    => ['nullable','string','max:50'],
            'chassis_number' => ['nullable','string','max:50'],
            'engine_number'  => ['nullable','string','max:50'],
            'market_value'   => ['required','numeric','min:0'],
            'offered_value'  => ['required','numeric','min:0'],
            'notes'          => ['nullable','string'],
        ]);

        $data['tenant_id']    = app('tenant')->id;
        $data['branch_id']    = auth()->user()->branch_id;
        $data['evaluated_by'] = auth()->id();

        $tradeIn = TradeIn::create($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Trade-in evaluation submitted for approval.']);
        }

        return redirect()->route('trade-ins.show', $tradeIn)
            ->with('success', 'Trade-in evaluation submitted for approval.');
    }

    public function tradeInShow(TradeIn $tradeIn): View
    {
        $tradeIn->load(['customer','newVehicle.make','newVehicle.vehicleModel','evaluatedBy','approvedBy']);

        if (request()->ajax()) {
            return view('sales.trade-ins.partials.show-modal', compact('tradeIn'));
        }

        return view('sales.trade-ins.show', compact('tradeIn'));
    }

    public function tradeInApprove(Request $request, TradeIn $tradeIn): RedirectResponse|JsonResponse
    {
        $this->authorize('approve-trade-ins');
        $request->validate(['approved_value' => ['required','numeric','min:0']]);

        $tradeIn->update([
            'status'            => 'approved',
            'approved_value'    => $request->approved_value,
            'approved_by'       => auth()->id(),
            'difference_amount' => $tradeIn->newVehicle->sale_price - $request->approved_value,
        ]);

        $message = 'Trade-in approved at PKR ' . number_format($request->approved_value) . '.';

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
