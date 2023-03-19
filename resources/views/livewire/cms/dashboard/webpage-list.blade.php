<div>
  @if (!is_null($webpages) && count($webpages) > 0)
    <div class="table-responsive border">
      <table class="table table-hover table-bordered-rm">
        <thead>
          <tr class="{{ env('OC_ASCENT_BG_COLOR') }} {{ env('OC_ASCENT_TEXT_COLOR') }}">
            <th>
              <input type="checkbox" class="mr-3">
              Name
            </th>
            <th>
              Author
            </th>
            @if (false)
            <th>
              Permalink
            </th>
            @endif
            <th>
              Categories
            </th>
            <th>
              Date
            </th>
            <th>
              Visibility
            </th>
            <th>
              Action
            </th>
          </tr>
        </thead>

        <tbody class="bg-white">
          @foreach ($webpages as $webpage)
            <tr>
              <td class="h4 font-weight-bold py-4">
                <input type="checkbox" class="mr-3">
                <span  wire:click="$emit('displayWebpage', {{ $webpage }})" class="text-dark" role="button">
                  {{ $webpage->name }}
                </span>
              </td>
              <td>
                {{ \Faker\Factory::create()->name() }}
              </td>
              @if (false)
              <td>
                {{ $webpage->permalink }}
              </td>
              @endif
              <td>
                @foreach ($webpage->webpageCategories as $webpageCategory)
                  <span class="badge badge-primary mr-3">
                    {{ $webpageCategory->name }}
                  </span>
                @endforeach
              </td>
              <td>
                Published
                <br/>
                {{ $webpage->created_at->toDateString() }}
              </td>
              <td>
                @if ($webpage->visibility == 'private')
                  <i class="fas fa-user text-secondary mr-2"></i>
                  Private
                @elseif ($webpage->visibility == 'public')
                  <span class="text-success">
                  <i class="fas fa-globe mr-2"></i>
                    Public
                  </span>
                @elseif ($webpage->visibility == null)
                  <i class="fas fa-exclamation-circle text-secondary"></i>
                  @if (false)
                  Not set
                  @endif
                @else
                  Whoops!
                @endif
              </td>
              <td>
                <span class="btn btn-light mr-3">
                  <i class="fas fa-pencil-alt"></i>
                </span>
                <span class="btn btn-light mr-3" wire:click="deleteWebpage({{ $webpage }})">
                  <i class="fas fa-trash"></i>
                </span>
                @if ($modes['delete'])
                  @if ($deletingWebpage->webpage_id == $webpage->webpage_id)
                    @if ($modes['cannotDelete'])
                      <span class="text-danger mr-3">
                        Cannot be deleted
                      </span>
                      <span class="btn btn-light mr-3" wire:click="deleteWebpageCancel">
                        Cancel
                      </span>
                    @else
                      <span class="btn btn-danger mr-3" wire:click="confirmDeleteWebpage">
                        Confirm delete
                      </span>
                      <span class="btn btn-light mr-3" wire:click="deleteWebpageCancel">
                        Cancel
                      </span>
                    @endif
                  @endif
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Pagination links --}}
    <div class="my-4">
      {{ $webpages->links() }}
    </div>
  @else
    No posts
  @endif
</div>
