<div>
    <input type="file" id="fileInput" wire:model="files" multiple>
    <ul>
        @foreach ($files as $file)
            <li>{{ $file->getClientOriginalName() }}</li>
        @endforeach
    </ul>
</div>

@push('js_push')
    <script>
        document.addEventListener('livewire:load', function () {
            const fileInput = document.getElementById('fileInput');
            
            FilePond.create(fileInput, {
                allowMultiple: true,
                server: {
                    process: (fieldName, file, metadata, load, error, progress, abort) => {
                        // Handle file upload using Livewire endpoint
                        let formData = new FormData();
                        formData.append(fieldName, file);

                        // Perform Livewire upload
                        @this.upload('{{ $uploadEndpoint }}', formData, load, error, progress, abort);
                    }
                }
            });
        });
    </script>
@endpush
