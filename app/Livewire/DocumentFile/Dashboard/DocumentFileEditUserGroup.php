<?php

namespace App\Livewire\DocumentFile\Dashboard;

use Livewire\Component;
use App\UserGroup;
use App\DocumentFileUserGroup;

class DocumentFileEditUserGroup extends Component
{
    public $documentFile;

    public $userGroups;

    public $user_group_id;

    public function render()
    {
        $this->userGroups = UserGroup::all();

        return view('livewire.document-file.dashboard.document-file-edit-user-group');
    }

    public function store()
    {
        $validatedData = $this->validate([
            'user_group_id' => 'required',
        ]);

        $validatedData['document_file_id'] = $this->documentFile->document_file_id;

        DocumentFileUserGroup::create($validatedData);

        $this->dispatch('documentFileEditUserGroupCompleted');
    }
}
