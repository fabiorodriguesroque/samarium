<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'customer_id';

    protected $fillable = [
         'name', 'phone',
         'email', 'address',
         'pan_num',
    ];


    /*-------------------------------------------------------------------------
     * Relationships
     *-------------------------------------------------------------------------
     *
     */

    /*
     * sale table.
     *
     */
    public function sales()
    {
        return $this->hasMany('App\Sale', 'customer_id', 'customer_id');
    }

    /*
     * sale_invoice table.
     *
     */
    public function saleInvoices()
    {
        return $this->hasMany('App\SaleInvoice', 'customer_id', 'customer_id');
    }


    /*-------------------------------------------------------------------------
     * Methods
     *-------------------------------------------------------------------------
     *
     */

    /*
     * get balance.
     *
     */
    public function getBalance()
    {
        $total = 0;

        foreach ($this->saleInvoices as $saleInvoice) {
            $total += $saleInvoice->getPendingAmount();
        }

        return $total;
    }

    /*
     * Get pending sale invoices of customer.
     *
     */
    public function getPendingSaleInvoices()
    {
        $invoices = $this->saleInvoices()->where('payment_status', '!=', 'paid')->get();

        return $invoices;
    }
}
