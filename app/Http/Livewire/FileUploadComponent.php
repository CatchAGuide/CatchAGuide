<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;

class FileUploadComponent extends Component
{
    use WithFileUploads;

    public $files = [];

    public function upload($endpoint, $formData, $load, $error, $progress, $abort)
    {
        // Perform your file upload logic here
        // You can use Laravel's file upload handling

        // Example: Saving the uploaded file to a specific directory
        $path = $formData->store('public/uploads');

        // Emit Livewire event to update the files array
        $this->emit('fileUploaded', $path);

        // Notify FilePond that the file has been successfully uploaded
        $load();
    }

    public function getFileUrl($filePath)
    {
        return asset($filePath);
    }

    public function render()
    {
        return view('livewire.file-upload-component', [
            'uploadEndpoint' => '',
        ]);
    }
}
