<x-box-create title="Create sale">

  <div class="form-group">
    <label for="">Name</label>
    <input type="text" class="form-control" wire:model.defer="c_name">
    @error('c_name') <span class="text-danger">{{ $message }}</span> @enderror
  </div>

  <div class="form-group">
    <label for="">Phone</label>
    <input type="text" class="form-control" wire:model.defer="c_phone" wire:keydown.enter="getCustomerInfo" />
    @error('c_phone') <span class="text-danger">{{ $message }}</span> @enderror
  </div>


  <div class="row">
    <div class="col-md-9 bg-warning-rm">
      <!-- Sale items -->
      <h4>Items</h4>

      <!-- Toolbar -->
      <div class="bg-alert" style="background-color: #eee; margin-bottom: 10px; padding: 5px; font-size: 10px; width: 25%;">
        <button class="btn btn-sm" wire:click="addRow">
          <i class="fas fa-plus"></i>
          Item
        </button>
        <button class="btn btn-sm" wire:click="clearSheet">
          <i class="fas fa-eraser"></i>
          Clear
        </button>
      </div>


      <div class="table-responsive" style="overflow: auto;">
        <table class="table" style="">
          <thead>
            <tr class="border p-1">
              <th class="border p-2">SN</th>
              <th class="border p-2">Item</th>
              <th class="border p-2">Price</th>
              <th class="border p-2">Qty</th>
              <th class="border p-2">Amount</th>
        
              <th class="border p-2">---</th>
            </tr>
          </thead>

          <tbody>
	          <!-- Our way: Use 2D Array -->
            @for ($i=0; $i < $totalNumOfRows; $i++)
              <tr class="m-0 p-0" style="">
                <td class="m-0 p-1 border">
                  {{ $i + 1 }}
                </td>
                <td class="m-0 p-1 border">
                  <div class="form-group m-0 p-0">
                    <select class="form-control" wire:model.defer="saleItems.{{ $i }}.product_id" wire:change="updateItemPrice({{ $i }})">

                      <option>---</option>

                      @foreach ($products as $product)
                        <option value="{{ $product->product_id }}">{{ $product->name }}</option>
                      @endforeach

                    </select>
                  </div>
                </td>
                <td class="m-0 p-0 border">
                  <input readonly type="text" class="border-0" wire:model.defer="saleItems.{{ $i }}.price" />
                </td>
                <td class="m-0 p-0 border">
                  <input type="text" class="border-0" wire:model.defer="saleItems.{{ $i }}.qty" wire:keydown.tab.prevent="setItemTotal({{ $i }})"/>
                </td>
                <td class="m-0 p-0 border">
                  <input readonly type="text" class="border-0" wire:model.defer="saleItems.{{ $i }}.amount" />
                </td>
                <td class="m-0 p-0 px-2 border">
                    <i class="fas fa-trash text-danger" wire:click="removeRow({{ $i }})"></i>
                </td>
              </tr>
            @endfor
            <tr>
              <th colspan="4" class="border py-2">
                Total
              </th>
              <td colspan="2" class="border py-2 font-weight-bold">
                {{  $total }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="col-md-3">

    </div>
  </div>

  <div class="col-md-3">
    <h3>Payment</h3>

    <div class="table-responsive" style="overflow: auto;">
      <table class="table" style="">
        <tbody>
          <tr class="m-0 p-0" style="">
            <th class="m-0 p-1 border">
              Cash
            </th>
            <td class="m-0 p-1 border">
              <input type="text" class="border-0" wire:model.defer="cashGiven" style="width: 100% !important;" />
            </td>
          </tr>
          <tr class="m-0 p-0" style="">
            <th class="m-0 p-1 border">
              Return
            </th>
            <td class="m-0 p-1 border">
              <input type="text" class="border-0" wire:model.defer="cashReturn" style="width: 100% !important;" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>




  <div class="mt-3 p-2">
    <button type="submit" class="btn btn-primary" wire:click="store">Submit</button>
    <button type="submit" class="btn btn-danger" wire:click="$emit('exitCreateMode')">Cancel</button>
  </div>

</x-box-create>
