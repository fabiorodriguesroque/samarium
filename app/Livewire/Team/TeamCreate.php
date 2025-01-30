<?php

namespace App\Livewire\Team;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\ModesTrait;
use App\Team;
use App\GalleryImage;

class TeamCreate extends Component
{
    use WithFileUploads;
    use ModesTrait;

    public $name;
    public $comment;
    public $team_type;
    public $image;

    public $selectedMediaImage;

    public $modes = [
        'selectImageFromNewUploadMode' => false,
        'selectImageFromLibraryMode' =>false,
        'mediaFromLibrarySelected' => false,
    ];

    protected $listeners = [
        'mediaImageSelected',
    ];

    public function render()
    {
        return view('livewire.team.team-create');
    }

    public function store()
    {
        $validatedData = $this->validate([
            'name' => 'required',
            'comment' => 'nullable',
            'team_type' => 'nullable',
            'image' => 'nullable|image',
        ]);

        if ($this->image) {
            $imagePath = $this->image->store('teams', 'public');
            $validatedData['image_path'] = $imagePath;
        }

        if ($this->selectedMediaImage) {
            $validatedData['image_path'] = $this->selectedMediaImage->image_path;
        }

        Team::create($validatedData);

        $this->dispatch('teamCreated');
    }

    public function mediaImageSelected(GalleryImage $galleryImage)
    {
        $this->selectedMediaImage = $galleryImage;
        $this->enterModeSilent('mediaFromLibrarySelected');
    }
}
