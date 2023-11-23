<?php

namespace App\Http\Livewire\School;

use Livewire\Component;

use App\Traits\ModesTrait;

class CalendarEventDisplay extends Component
{
    use ModesTrait;

    public $calendarEvent;

    public $modes = [
        'editNameMode' => false,
        'editDateMode' => false,
        'editIsHolidayMode' => false,
    ];

    protected $listeners = [
        'calendarEventUpdateNameCancelled',
        'calendarEventUpdateNameCompleted',
    ];

    public function render()
    {
        return view('livewire.school.calendar-event-display');
    }

    public function calendarEventUpdateNameCancelled()
    {
        $this->exitMode('editNameMode');
    }

    public function calendarEventUpdateNameCompleted()
    {
        $this->exitMode('editNameMode');
    }
}
