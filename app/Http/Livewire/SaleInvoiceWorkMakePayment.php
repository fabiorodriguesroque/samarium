<?php

namespace App\Http\Livewire;

use App\Traits\MiscTrait;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

use App\Customer;
use App\SaleInvoice;
use App\SaleInvoicePaymentType;
use App\SaleInvoicePayment;
use App\SaleInvoiceAddition;
use App\SaleInvoiceAdditionHeading;

use App\JournalEntry;
use App\JournalEntryItem;
use App\AbAccount;
use App\LedgerEntry;

class SaleInvoiceWorkMakePayment extends Component
{
    use MiscTrait;

    public $saleInvoice;

    public $total;
    public $pay_by;
    public $tender_amount;

    public $discount = 0;
    public $service_charge = 0;
    public $grand_total;

    /* Amount before taxes (VAT, etc) */
    public $taxable_amount;

    public $has_vat = false;

    public $discount_percentage = null;

    public $returnAmount;

    /* Customer to which sale_invoice will be made */
    public $customer = null;
    public $customer_id;

    /* List of customers */
    public $customers;

    /* Customer info */
    public $customer_name;
    public $customer_phone;
    public $customer_address;
    public $customer_pan;

    /* Misc */
    public $saleInvoiceAdditionHeadings;
    public $saleInvoiceAdditions = array();
    public $saleInvoicePaymentTypes;
    public $sale_invoice_payment_type_id;

    /* Multiple payments */
    public $multiPayments = array();

    public $modes = [
        'paid' => false,
        'customer' => false,
        'multiplePayments' => false,
        'manualDiscount' => false,
    ];

    protected $listeners = [
      'makePaymentPleaseUpdate' => 'mount',
      'updatePaymentComponent' => 'mount',
    ];

    public function mount()
    {
        $this->has_vat = $this->hasVat();

        $this->saleInvoicePaymentTypes = SaleInvoicePaymentType::all();

        $this->saleInvoiceAdditionHeadings = SaleInvoiceAdditionHeading::all();

        foreach (SaleInvoiceAdditionHeading::all() as $saleInvoiceAddition) {
            $this->saleInvoiceAdditions += [$saleInvoiceAddition->name => 0];
        }

        $this->total = $this->saleInvoice->getTotalAmountRaw();

        /* Calculate total before taxes. */
        $this->calculateTaxableAmount();

        if ($this->has_vat) {
            $this->saleInvoiceAdditions['VAT'] = $this->calculateSaleInvoiceVat();
        }

        /* Calculate Grand Total */
        $this->calculateGrandTotal();

        $this->customers = Customer::all();
    }

    public function render()
    {
        return view('livewire.sale-invoice-work-make-payment');
    }

    public function updatedSaleInvoiceAdditions()
    {
      $this->updateNumbers();
      $this->calculateGrandTotal();
    }

    public function updatedMultiPayments()
    {
      $this->calculateTenderAmount();
    }

    /* Clear modes */
    public function clearModes()
    {
        foreach ($this->modes as $key => $val) {
            $this->modes[$key] = false;
        }
    }

    /* Enter and exit mode */
    public function enterMode($modeName)
    {
        // $this->clearModes();

        $this->modes[$modeName] = true;
    }

    public function exitMode($modeName)
    {
        $this->modes[$modeName] = false;
    }

    public function enterModeSingle($modeName)
    {
        $this->modes[$modeName] = true;
    }

    public function store()
    {
        if ($this->modes['multiplePayments']) {
            // TODO
        } else {
            if ($this->tender_amount == 0) {
                $validatedData = $this->validate([
                    'tender_amount' => 'required|integer',
                ]);
            } else {
                $validatedData = $this->validate([
                    'tender_amount' => 'required|integer',
                    'sale_invoice_payment_type_id' => 'required|integer',
                ]);
            }
        }

        /* Get the sale_invoice */
        $saleInvoice = $this->saleInvoice;

        /* Final Payment Status */
        $finalPaymentStatus = $saleInvoice->payment_status;

        /* Calculate the grand_total */
        $this->calculateGrandTotal();

        /* Get current booking/invoice amount */
        $currentSaleInvoiceAmount = $this->saleInvoice->getPendingAmount();
        $currentSaleInvoiceGrandAmount = $this->saleInvoice->getTotalAmount();

        if (false) {
            /* Get the customer if given */
            if ($this->customer_id && $this->customer_id != '---') {
                $this->customer = Customer::find($this->customer_id);
            }
        }

        /* If no customer do not take less payments !!! */
        if (! $this->saleInvoice->customer && $this->tender_amount < $this->grand_total) {
            return;
        }

        DB::beginTransaction();

        try {
            /* Make Sale Invoice Additions if needed. */
            foreach ($this->saleInvoiceAdditions as $key => $val) {
                if ($val > 0) {
                    $saleInvoiceAdditionHeading = SaleInvoiceAdditionHeading::where('name', $key)->first();

                    $saleInvoiceAddition = new SaleInvoiceAddition;

                    $saleInvoiceAddition->sale_invoice_id = $saleInvoice->sale_invoice_id;
                    $saleInvoiceAddition->sale_invoice_addition_heading_id = $saleInvoiceAdditionHeading->sale_invoice_addition_heading_id;
                    $saleInvoiceAddition->amount = $val;

                    $saleInvoiceAddition->save();
                }
            }

            /* If payment received then create a payment record. */
            if ($this->tender_amount > 0) {
                /* If multipayments then do accordingly */
                if ($this->modes['multiplePayments']) {
                    $this->makeMultiplePayments($saleInvoice);

                    if ($this->tender_amount < $this->grand_total) {
                        $this->returnAmount = 0;
                        $finalPaymentStatus = 'partially_paid';
                    } else {
                        $this->returnAmount = $this->tender_amount - $this->grand_total;
                        $finalPaymentStatus = 'paid';
                    }
                } else {
                    /* Make sale_invoice_payment */
                    $saleInvoicePayment = new SaleInvoicePayment;

                    $saleInvoicePayment->sale_invoice_payment_type_id = $validatedData['sale_invoice_payment_type_id'];

                    $saleInvoicePayment->payment_date = date('Y-m-d');
                    $saleInvoicePayment->sale_invoice_id = $saleInvoice->sale_invoice_id;

                    if ($this->tender_amount < $this->grand_total) {
                        $saleInvoicePayment->amount = $this->tender_amount;
                        $this->returnAmount = 0;
                        $finalPaymentStatus = 'partially_paid';
                    } else {
                        $saleInvoicePayment->amount = $this->grand_total;
                        $this->returnAmount = $this->tender_amount - $this->grand_total;
                        $finalPaymentStatus = 'paid';
                    }

                    $saleInvoicePayment->save();
                }
            }


            /* Update payment_status of sale invoice */
            $saleInvoice->payment_status = $finalPaymentStatus;
            $saleInvoice->save();


            /* Update takeaway status */
            if ($this->saleInvoice->takeaway) {
                $takeaway = $this->saleInvoice->takeaway;
                $takeaway->status = 'closed';
                $takeaway->save();
                $this->saleInvoice = $this->saleInvoice->fresh();
            }

            /* Update seat table booking status */
            if ($this->saleInvoice->seatTableBooking) {
                $booking = $this->saleInvoice->seatTableBooking;
                $booking->status = 'closed';
                $booking->save();
                $this->saleInvoice = $this->saleInvoice->fresh();
            }

            /* Todo bugP1: This was used to fix THE bug. Why was this needed???? */
            $saleInvoice = SaleInvoice::find($saleInvoice->sale_invoice_id);

            /* Make accounting entries */
            // $this->makeAccountingEntry($saleInvoice);

            DB::commit();

            $this->enterModeSingle('paid');
        } catch (\Exception $e) {
            DB::rollback();
            dd ($e);
            session()->flash('errorDbTransaction', 'Some error in DB transaction.');
        }
    }

    public function finishPayment()
    {
        $this->emit('exitMakePaymentMode');
    }

    public function fetchCustomerData()
    {
        $customer = Customer::where('phone', $this->customer_phone)->first();

        if ($customer) {
            $this->customer = $customer;

            $this->customer_name = $customer->name;
            $this->customer_address = $customer->address;
        }
    }

    public function calculateGrandTotal()
    {
        /* Todo: Any validation needed ? */

        /* Todo: Really Hard code VAT ? Better way? */
        if ($this->has_vat) {
            $this->grand_total = $this->taxable_amount + $this->saleInvoiceAdditions['VAT'] ;
        } else {
            $this->grand_total = $this->taxable_amount;
        }
    }

    public function calculateTaxableAmount()
    {
        /* TODO
        $validatedData = $this->validate([
            'discount' => 'required|integer',
            'service_charge' => 'required|integer',
        ]);
        */

        $this->taxable_amount = $this->total;

        foreach ($this->saleInvoiceAdditions as $key => $val) {

            /* Dont add VAT (or any other taxes) while calculating taxable amount. */
            if ($key == 'VAT') {
                continue;
            }

            if (strtolower(SaleInvoiceAdditionHeading::where('name', $key)->first()->effect) == 'plus') {
                if (is_numeric($val)) {
                    $this->taxable_amount += $val;
                }
            } else if (strtolower(SaleInvoiceAdditionHeading::where('name', $key)->first()->effect) == 'minus') {
                if (is_numeric($val)) {
                    $this->taxable_amount -= $val;
                }
            } else {
                dd('Sale invoice addition heading configurations gone wrong! Contact your service provider.');
            }
        }
    }

    public function createPersonalAccount($name)
    {
        $abAccount = new AbAccount;

        $abAccount->name = $name;

        $abAccount->save();

        return $abAccount->getKey();
    }

    public function enterMultiplePaymentsMode()
    {
        foreach (SaleInvoicePaymentType::all() as $saleInvoicePaymentType) {
            $this->multiPayments[$saleInvoicePaymentType->name] = 0;
        }

        $this->enterMode('multiplePayments');

        $this->calculateTenderAmount();
    }

    public function exitMultiplePaymentsMode()
    {
        $this->multiplePayments = array();
        $this->tender_amount = '';
        $this->exitMode('multiplePayments');
    }

    public function calculateTenderAmount()
    {
        if ($this->modes['multiplePayments']) {
            $tenderAmount = 0;

            foreach ($this->multiPayments as $key => $val) {
                if ($val) {
                    $tenderAmount += $val;
                }
            }

            $this->tender_amount = $tenderAmount;
        } else {
            dd('Whoops!');
        }
    }

    public function makeMultiplePayments($saleInvoice)
    {
        $remainingAmount = $this->grand_total;

        foreach ($this->multiPayments as $key => $val) {

            /* Ignore cash first */
            if (strtolower($key) == 'cash') {
                continue;
            }

            /* Ignore zero values */
            if ($val == 0) {
                continue;
            }

            $saleInvoicePayment = new SaleInvoicePayment;

            $saleInvoicePayment->sale_invoice_payment_type_id =
                SaleInvoicePaymentType::where('name', $key)->first()->sale_invoice_payment_type_id;

            $saleInvoicePayment->payment_date = date('Y-m-d');
            $saleInvoicePayment->sale_invoice_id = $saleInvoice->sale_invoice_id;
            $saleInvoicePayment->amount = $val;

            $saleInvoicePayment->save();

            $remainingAmount -= $val;
        }

        foreach ($this->multiPayments as $key => $val) {

            /* Do only for cash */
            if (strtolower($key) != 'cash') {
                continue;
            }

            /* Ignore zero values */
            if ($val == 0) {
                continue;
            }

            $saleInvoicePayment = new SaleInvoicePayment;

            $saleInvoicePayment->sale_invoice_payment_type_id =
                SaleInvoicePaymentType::where('name', $key)->first()->sale_invoice_payment_type_id;

            $saleInvoicePayment->payment_date = date('Y-m-d');
            $saleInvoicePayment->sale_invoice_id = $saleInvoice->sale_invoice_id;

            if ($val < $remainingAmount) {
                $saleInvoicePayment->amount = $val;
            } else {
                $saleInvoicePayment->amount = $remainingAmount;
            }

            $saleInvoicePayment->save();
        }
    }

    public function calculateDiscount()
    {
        if ($this->discount_percentage == 'manual') {
            $this->enterMode('manualDiscount');
        } else {
            if ($this->modes['manualDiscount']) {
                $this->exitMode('manualDiscount');
            }

            $dp = (float) $this->discount_percentage;
            $this->saleInvoiceAdditions['Discount'] = ($dp / 100) * $this->saleInvoice->getTotalAmountRaw();
            $this->saleInvoiceAdditions['Discount'] = ceil ($this->saleInvoiceAdditions['Discount']);

            $this->updateNumbers();
            $this->calculateGrandTotal();
        }
    }

    public function calculateSaleInvoiceVat()
    {
        return ceil(0.13 * $this->taxable_amount);
    }

    public function updateNumbers()
    {
        $this->calculateTaxableAmount();

        if ($this->has_vat) {
            $this->saleInvoiceAdditions['VAT'] = $this->calculateSaleInvoiceVat();
        }

        $this->calculateGrandTotal();
    }

    public function hasVat()
    {
        if (SaleInvoiceAdditionHeading::where('name', 'vat')->first()) {
            return true;
        } else {
            return false;
        }
    }
}
