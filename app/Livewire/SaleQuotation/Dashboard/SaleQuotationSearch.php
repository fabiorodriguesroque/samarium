<?php

namespace App\Livewire\SaleQuotation\Dashboard;

use Livewire\Component;
use App\SaleQuotation;

class SaleQuotationSearch extends Component
{
    public $customer_search_name;

    public $saleQuotations;

    public function render()
    {
        return view('livewire.sale-quotation.dashboard.sale-quotation-search');
    }

    public function search()
    {
        $validatedData = $this->validate([
            'customer_search_name' => 'required',
        ]);

        $saleQuotations = SaleQuotation::whereHas('customer', function ($query) use ($validatedData) {
            $query->where( 'name', 'like', '%'.$validatedData['customer_search_name'].'%');
        })->get();

        $this->saleQuotations = $saleQuotations;
    }
}
