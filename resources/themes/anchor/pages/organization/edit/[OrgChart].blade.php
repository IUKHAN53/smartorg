<?php

use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;

middleware('auth');
name('organization.view');

new class extends Component {
    use WithFileUploads;

    public $orgChart;
    public $name;
    public $description;
    public $is_shared;
    public $data;
    public $dataContent = null;
    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'is_shared' => 'boolean',
        'data' => 'nullable|file|mimes:json|max:2048', // 2MB Max
    ];

    public function mount($orgChart)
    {
        $this->orgChart = $orgChart;
        $this->name = $orgChart->name;
        $this->description = $orgChart->description;
        $this->is_shared = $orgChart->is_shared;
    }

    public function saveOrgChart()
    {
        $validatedData = $this->validate();

        if ($this->data) {
            $jsonContent = $this->data->get();
            json_decode($jsonContent);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError('data', 'Uploaded file contains invalid JSON.');
                return;
            }
            $validatedData['json_data'] = $jsonContent;
        } else {
            $validatedData['json_data'] = $this->orgChart->json_data;
        }

        // Update the OrgChart model
        $this->orgChart->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'is_shared' => $validatedData['is_shared'] ?? false,
            'json_data' => $validatedData['json_data'],
        ]);

        $this->orgChart->refresh();

        session()->flash('message', 'Organization Chart updated successfully.');
    }

    public function updatedData()
    {
        if ($this->data) {
            try {
                // Retrieve the file content
                $jsonContent = $this->data->get();

                // Attempt to decode the JSON to ensure it's valid
                $decoded = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    // If valid, format it for pretty display
                    $this->dataContent = json_encode($decoded, JSON_PRETTY_PRINT);
                } else {
                    // If invalid, set an error message
                    $this->dataContent = 'Invalid JSON content.';
                    $this->addError('data', 'Uploaded file contains invalid JSON.');
                }
            } catch (\Exception $e) {
                // Handle any unexpected errors
                $this->dataContent = 'Error processing the file.';
                $this->addError('data', 'There was an error uploading the file.');
            }
        } else {
            // If no file is uploaded, reset dataContent
            $this->dataContent = null;
        }
    }
}
?>


<x-layouts.app>
    @volt()
    <x-app.container
        x-data="{ showModal: false, is_shared: @entangle('is_shared') }"
        class="lg:space-y-6"
        x-cloak
    >
        <div class="flex justify-between">
            <x-app.heading
                :title="'Settings for ' . $orgChart->name"
                :border="false"
            />
            <a
                href="{{ url('/organization/' . $orgChart->id) }}"
                class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none
                       focus:ring-4 focus:ring-gray-300 font-medium rounded-lg
                       text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800
                       dark:hover:bg-gray-700 dark:focus:ring-gray-700
                       dark:border-gray-700"
            >
                Back
            </a>
        </div>

        @if (session()->has('message'))
            <div
                class="p-4 mt-4 text-green-700 bg-green-100 border
                       border-green-300 rounded-md"
            >
                {{ session('message') }}
            </div>
        @endif

        <x-elements.card>
            <form class="mx-auto space-y-6">
                <!-- Name Field -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input
                        type="text"
                        id="name"
                        wire:model.defer="name"
                        class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm
                               focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="OrgChart 1"
                    />
                    @error('name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Description Field -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea
                        id="description"
                        wire:model.defer="description"
                        class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm
                               focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Sample description"
                        rows="3"
                    ></textarea>
                    @error('description') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Share Toggle -->
                <div class="flex items-center space-x-4">
                    <label for="is_shared" class="relative inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            id="is_shared"
                            wire:model.defer="is_shared"
                            class="sr-only peer"
                        />
                        <div
                            class="w-10 h-6 bg-gray-200 rounded-full peer peer-checked:bg-blue-600
                                   transition-colors duration-200"
                        ></div>
                        <div
                            class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full
                                   peer-checked:translate-x-4 transition-transform duration-200"
                        ></div>
                    </label>
                    <label class="block text-sm font-medium text-gray-700">Would you like to share this
                        OrgChart?</label>
                </div>

                @if($this->orgChart->share_uuid && $this->is_shared)
                    <div class="flex items-start flex-col">
                        <span class="text-sm text-gray-600">
                            {{ url('/') . '/public/' . ($orgChart->share_uuid ?? 'your-share-uuid') }}
                        </span>
                        <button type="button"
                                onclick="window.open('/public/{{ $orgChart->share_uuid ?? 'your-share-uuid' }}', '_blank')"
                                class="gap-1 text-white bg-gray-400 hover:bg-gray-500 focus:ring-4 focus:outline-none font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:focus:ring-gray-200 dark:hover:bg-gray-400 me-2 mb-2">
                            @svg('heroicon-o-eye', 'h-5 w-5')
                            View Public Link
                        </button>
                    </div>
                @endif
                <div>
                    <label for="data" class="block text-sm font-medium text-gray-700">Data</label>
                    <div
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 rounded-md"
                    >
                        <div class="space-y-1 text-center">
                            <svg
                                class="mx-auto h-12 w-12 text-gray-400"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 48 48"
                                aria-hidden="true"
                            >
                                <path
                                    d="M14 26V34H34V26H40L24 10L8 26H14Z"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label
                                    for="file-upload"
                                    class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600
                                           hover:text-blue-500 focus-within:outline-none focus-within:ring-2
                                           focus-within:ring-offset-2 focus-within:ring-blue-500"
                                >
                                    <span>Upload a file</span>
                                    <input
                                        id="file-upload"
                                        wire:model.defer="data"
                                        type="file"
                                        class="sr-only"
                                    />
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            @error('data') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            <p class="text-xs text-gray-500">JSON up to 2MB</p>
                        </div>
                    </div>
                    <div wire:loading wire:target="data" class="mt-2 mb-3">
                        <span class="text-sm text-blue-600">Uploading...</span>
                    </div>
                    <div class="flex justify-end mt-2">
                        <x-button type="submit" size="lg" wire:click="saveOrgChart">
                            Save Changes
                        </x-button>
                    </div>
                    @if ($dataContent)
                        <div class="mt-4">
                            <p class="text-sm text-gray-700">Uploaded file
                                content: {{ $data->getClientOriginalName() }}</p>
                            <pre class="mt-2 p-2 bg-gray-100 rounded-md overflow-auto text-xs text-gray-600">
{{ $dataContent }}
                            </pre>
                        </div>
                    @endif
                </div>
            </form>
        </x-elements.card>
    </x-app.container>
    @endvolt
</x-layouts.app>

