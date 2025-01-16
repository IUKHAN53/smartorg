<?php

use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;

middleware('auth');
name('dashboard');

new class extends Component {
    public $orgCharts;
    public $showModal = false; // Initialize as false
    public $name = '';
    public $description = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
    ];

    public function mount()
    {
        $this->orgCharts = auth()->user()->orgCharts;
    }

    public function saveOrgChart()
    {
        $this->validate();

        // Save the new OrgChart
        auth()->user()->orgCharts()->create([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        // Reload orgCharts
        $this->orgCharts = auth()->user()->orgCharts;

        // Reset form and close modal
        $this->reset(['name', 'description']);
        $this->showModal = false;

        session()->flash('message', 'OrgChart added successfully!');
    }
}
?>


<x-layouts.app>
    @volt('dashboard')
    <x-app.container x-data='{ showModal: false }' class="lg:space-y-6" x-cloak>
        <div class="flex justify-between">
            <x-app.heading
                title="Dashboard"
                :border="false"
            />
            <button
                type="button"
                @click="showModal = true"
                class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">
                Add OrgChart
            </button>
        </div>

        <div class="flex flex-col w-full mt-6 space-y-5 md:flex-row lg:mt-0 md:space-y-0 md:space-x-5">
            @forelse ($orgCharts as $orgChart)
                <x-app.org-card
                    href="organization/view/{{ $orgChart->id }}"
                    target="_blank"
                    :title="$orgChart->name"
                    :description="$orgChart->description"
                    :badge="$orgChart->is_shared ? 'Public' : 'Private'"
                    :id="$orgChart->id"
                />
            @empty
                <p class="text-gray-500">No OrgCharts available. Click "Add OrgChart" to create one.</p>
            @endforelse
        </div>

        <!-- Modal Implementation -->
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-100 bg-opacity-50"
            x-show="showModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            @keydown.escape.window="showModal = false"
        >
            <div
                class="bg-white rounded-lg shadow-lg w-full max-w-md mx-auto p-6"
                @click.away="showModal = false">
                <div class="flex justify-end items-center mb-4">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="text-gray-400 hover:text-gray-600"
                        aria-label="Close modal"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div>
                    <div class="flex justify-center flex-col">
                        <h1 class="mb-4 text-4xl  leading-none text-center tracking-tight text-gray-900 md:text-5xl lg:text-6xl dark:text-white">
                            Add New Organization</h1>
                        <p class="mb-6 text-lg font-normal text-center text-gray-500 lg:text-xl sm:px-16 xl:px-48 dark:text-gray-400">
                            Fill in the details below to create a new Organization</p>
                    </div>
                    <form wire:submit.prevent="saveOrgChart">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input
                                type="text"
                                id="name"
                                wire:model.defer="name"
                                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Example OrgChart"
                            />
                            @error('name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea
                                id="description"
                                wire:model.defer="description"
                                class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Optional description"
                                rows="3"
                            ></textarea>
                            @error('description') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </form>
                    <div class="flex justify-center">
                        <x-button size="lg" class="w-full lg:w-auto mr-2" wire:click="saveOrgChart">
                            Create
                        </x-button>
                        <x-button size="lg" @click="showModal = false" color="secondary" class="w-full lg:w-auto">
                            Cancel
                        </x-button>
                    </div>
                </div>
            </div>
        </div>

        @if(session()->has('message'))
            <div class="p-4 mt-4 text-green-700 bg-green-100 border border-green-300 rounded-md">
                {{ session('message') }}
            </div>
        @endif
    </x-app.container>
    @endvolt
</x-layouts.app>


