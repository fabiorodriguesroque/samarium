<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

use App\Customer;
use App\Sale;
use App\SaleItem;
use App\Product;

use App\SaleInvoice;
use App\SaleInvoiceItem;
use App\SaleInvoicePayment;

class SaleCreate extends Component
{
    public $customer = null;

    public $customerData = [
        'name' => null,
        'phone' => null,
        'email' => null,
        'address' => null,
        'pan_num' => null,
    ];
    public $c_name;
    public $c_phone;

    public $saleItems = array();
    public $customers;

    public $totalNumOfRows = 1;

    public $total = 0;

    public $products;

    public $cashGiven;
    public $cashReturn;

    public function render()
    {
        $this->products = Product::all();
        return view('livewire.sale-create');
    }

    public function store()
    {
        //dd('Foo');
        /* Todo: Validation */

        DB::beginTransaction();

        try {
            /* Customer */

            $customer = null;

            if (! $this->customer) {
                $customer = new Customer;

                $customer->name = $this->c_name;
                $customer->phone = $this->c_phone;

                $customer->save();
            } else {
                $customer = $this->customer;
            }


            /* Sale invoice */

            $saleInvoice = new SaleInvoice;

            $saleInvoice->customer_id = $customer->customer_id; 
            $saleInvoice->sale_invoice_date = date('Y-m-d'); 
            $saleInvoice->total_amount = $this->total; 
            $saleInvoice->payment_status = 'pending'; 

            $saleInvoice->save();


            /* Sale invoice items */

            foreach ($this->saleItems as $item) {
                $saleInvoiceItem = new SaleInvoiceItem;

                $saleInvoiceItem->sale_invoice_id = $saleInvoice->sale_invoice_id;
                $saleInvoiceItem->product_id = $item['product_id'];
                $saleInvoiceItem->quantity = $item['qty'];
                $saleInvoiceItem->save();
            }


            /* Payment */

            if ($this->cashGiven > 0) {
                $saleInvoicePayment = new SaleInvoicePayment;

                $saleInvoicePayment->payment_date = date('Y-m-d');
                $saleInvoicePayment->sale_invoice_id = $saleInvoice->sale_invoice_id;

                if ($this->cashGiven < $this->total) {
                    $saleInvoicePayment->amount = $this->cashGiven;
                    $saleInvoice->payment_status = 'partially_paid';
                    $this->cashReturn = 0;
                } else if ($this->cashGiven == $this->total) {
                    $saleInvoicePayment->amount = $cashGiven;
                    $saleInvoice->payment_status = 'paid';
                    $this->cashReturn = 0;
                } else {
                    $saleInvoicePayment->amount = $this->total;
                    $saleInvoice->payment_status = 'paid';
                    $this->cashReturn = $this->cashGiven - $this->total;
                }

                $saleInvoicePayment->save();
                $saleInvoice->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            dd ($e);
            session()->flash('errorDbTransaction', 'Some error in DB transaction.');
        }

        // $this->emit('clearModes');
    }

    public function addRow()
    {
        $this->totalNumOfRows++;
    }

    public function clearSheet()
    {
        $this->remittanceLines = [];
        $this->totalNumOfRows = 1;
    }

    public function calculateTotal()
    {
        $total = 0;

        foreach ($this->saleItems as $saleItem) {
            $total += $saleItem['amount'];
        }

        $this->total = $total;
    }

    public function getCustomerInfo()
    {
        $customer = Customer::where('phone', $this->c_phone)->first();

        if ($customer) {
            $this->customer = $customer;

            $this->c_name = $customer->name;
        }
    }

    public function updateItemPrice($index)
    {
        $product = Product::findOrFail($this->saleItems[$index]['product_id']);

        $this->saleItems[$index]['price'] = $product->selling_price;
    }

    public function setItemTotal($index)
    {
        if (isset($this->saleItems[$index]['price']) && isset($this->saleItems[$index]['qty'])) {
            $this->saleItems[$index]['amount'] = 
                $this->saleItems[$index]['price']
                *
                $this->saleItems[$index]['qty']; 

                $this->setTotal();
        }
    }

    public function setTotal()
    {
         $this->total = 0;

         for ($i=0; $i < $this->totalNumOfRows; $i++) {
             $this->total += $this->saleItems[$i]['amount'];
         }
    }
}
