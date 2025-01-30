<?php

namespace App\Livewire\Educ\Institution\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use App\EducInstitution;

class InstitutionList extends Component
{
    use WithPagination;

    // public $educInstitutions;

    public function render()
    {
        $educInstitutions = EducInstitution::orderBy('educ_institution_id', 'DESC')->paginate(1);

        return view('livewire.educ.institution.dashboard.institution-list')
            ->with('educInstitutions', $educInstitutions);
    }
}
