@extends('layouts.app')
@section ('content')

  @if (env('SITE_TYPE') == 'erp')

    {{-- Show on bigger screens --}}
    <div class="d-none d-md-block">
      <div class="row mb-4">

        @if (env('SITE_TYPE') == 'erp')
        <div class="col-md-3">
          @livewire ('flash-card-current-bookings')
        </div>
        @endif

        <div class="col-md-3">
          @livewire ('flash-card-total-bookings-today')
        </div>

        @if (env('SITE_TYPE') == 'erp')
          <div class="col-md-3">
            @livewire ('flash-card-total-takeaways-today')
          </div>
        @endif

      </div>
    </div>

    {{-- Show on smaller screens --}}
    <div class="d-md-none">
      <div class="row mb-4">

        @if (env('SITE_TYPE') == 'erp')
        <div class="col-md-3 p-4">
          <h2 class="h4">
            Current bookings: 2
          </h2>
          <h3 class="h3">
            2,154
          </h3>
        </div>
        @endif

        <div class="col-md-3 p-4">
          <h2 class="h4">
            Today bookings: 2
          </h2>
          <h3 class="h3">
            2,154
          </h3>
        </div>

        @if (env('SITE_TYPE') == 'erp')
        <div class="col-md-3 p-4">
          <h2 class="h4">
            Today bookings: 2
          </h2>
          <h3 class="h3">
            2,154
          </h3>
        </div>
        @endif

      </div>
    </div>

    @if (false)
    <div class="row mb-4">
      <div class="col-md-3">
        @livewire ('chart-week-sales', key(rand()))
      </div>
      <div class="col-md-6">
        @livewire ('chart-sale-by-category', key(rand()))
      </div>
      @if (false)
      <div class="col-md-6">
        @livewire ('table-top-selling-products-day')
      </div>
      @endif
    </div>
    @endif

  @elseif (env('SITE_TYPE') == 'ecs')
    @if (false)
      @livewire ('cms.nav-menu-component')
      @livewire ('ecs.webpage-component')
    @endif
  @endif

@endsection
