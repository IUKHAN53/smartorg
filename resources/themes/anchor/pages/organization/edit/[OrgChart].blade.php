<?php

use Filament\Notifications\Notification;
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
        $this->fillValues();
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
        $this->orgChart->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'is_shared' => $validatedData['is_shared'] ?? false,
            'json_data' => $validatedData['json_data'],
        ]);
        Notification::make()
            ->title('OrgChart updated successfully!')
            ->success()
            ->send();
    }

    public function updatedData()
    {
        if ($this->data) {
            try {
                $jsonContent = $this->data->get();
                $decoded = json_decode($jsonContent, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->dataContent = json_encode($decoded, JSON_PRETTY_PRINT);
                } else {
                    $this->dataContent = 'Invalid JSON content.';
                    $this->addError('data', 'Uploaded file contains invalid JSON.');
                }
            } catch (\Exception $e) {
                $this->dataContent = 'Error processing the file.';
                $this->addError('data', 'There was an error uploading the file.');
            }
        } else {
            $this->dataContent = null;
        }
    }

    public function fillValues()
    {
        $this->name = $this->orgChart->name;
        $this->description = $this->orgChart->description;
        $this->is_shared = $this->orgChart->is_shared;
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

        <x-elements.card>
            <form class="mx-auto space-y-6">
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
                <div class="flex items-center space-x-4">
                    <label for="is_shared" class="relative inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            id="is_shared"
                            name="is_shared"
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

                @if($orgChart->share_uuid)
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
                        <x-button type="button" size="lg" wire:click="saveOrgChart">
                            Save Changes
                        </x-button>
                    </div>
                    @if ($dataContent)
                        <div class="mt-4">
                            <p class="text-sm text-gray-700">
                                Uploaded file content: {{ $data->getClientOriginalName() }}
                            </p>
                            <div
                                x-data="{ showJson: false }"
                                class="border border-gray-200 rounded-md"
                            >
                                <button
                                    @click="showJson = !showJson"
                                    type="button"
                                    class="flex w-full justify-between p-2 bg-gray-200 hover:bg-gray-300 focus:outline-none
                       focus:ring-2 focus:ring-blue-500 transition-colors rounded-t-md"
                                >
                                    <span class="font-medium text-gray-700">View JSON</span>
                                    <svg
                                        class="w-5 h-5 text-gray-700 transform transition-transform"
                                        :class="{ 'rotate-180': showJson }"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.23 7.21a.75.75 0 011.06.02L10 11.2l3.71-3.97a.75.75 0 111.08 1.04l-4.25 4.54a.75.75 0 01-1.08 0L5.23 8.27a.75.75 0 01.02-1.06z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                                <div
                                    x-show="showJson"
                                    x-collapse
                                    class="overflow-auto p-2 bg-white rounded-b-md"
                                    style="display: none;"
                                >
                <pre class="text-xs text-gray-700 whitespace-pre-wrap">
{{ $dataContent }}
                </pre>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </form>
        </x-elements.card>
    </x-app.container>
    @endvolt
</x-layouts.app>

