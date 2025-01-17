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
    public $data; // For file upload
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
            // Extract JSON content from the uploaded file
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

        if ($validatedData['is_shared'] && !$this->orgChart->share_uuid) {
            $validatedData['share_uuid'] = Str::uuid();
        } elseif (!$validatedData['is_shared']) {
            $validatedData['share_uuid'] = null;
        }

        // Update the OrgChart model
        $this->orgChart->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'is_shared' => $validatedData['is_shared'],
            'share_uuid' => $validatedData['share_uuid'],
            'json_data' => $validatedData['json_data'],
        ]);

        session()->flash('message', 'Organization Chart updated successfully.');
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
            <form wire:submit.prevent="saveOrgChart" class="mx-auto space-y-6">
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

                <!-- Toggle Field -->
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

                <!-- Shared URL and Button -->
                <div class="flex items-start flex-col" x-show="is_shared">
                    <span class="text-sm text-gray-600">
                        /public/{{ $orgChart->share_uuid ?? 'your-share-uuid' }}
                    </span>
                    <x-button
                        type="button"
                        size="sm"
                        class="mt-2 border border-gray-700 text-gray-700 hover:bg-gray-700
                               hover:text-white flex items-center"
                        onclick="window.open('/public/{{ $orgChart->share_uuid ?? 'your-share-uuid' }}', '_blank')"
                    >
                        <!-- Eye Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.522 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274
                                     4.057-5.064 7-9.542 7-4.478 0-8.268-2.943-9.542-7z" />
                        </svg>
                        View Public Page
                    </x-button>
                </div>

                <!-- Data Field -->
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
                                        wire:model="data"
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
                </div>

                <!-- Submit Button Aligned to Far Right -->
                <div class="flex justify-end">
                    <x-button type="submit" size="lg">
                        Save Changes
                    </x-button>
                </div>
            </form>
        </x-elements.card>
    </x-app.container>
    @endvolt
</x-layouts.app>
