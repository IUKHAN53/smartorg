<?php

use function Laravel\Folio\{middleware, name};

middleware('auth');
name('organization.view');
?>

<x-layouts.app>
    <x-app.container x-data='{ showModal: false }' class="lg:space-y-6" x-cloak>
        <div class="flex justify-between">
            <x-app.heading
                title="{{$orgChart->name}}"
                :border="false"
            />
            <a
                type="button"
                href="edit/{{$orgChart->id}}"
                class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">
                Settings
            </a>
        </div>
        @if(session()->has('message'))
            <div class="p-4 mt-4 text-green-700 bg-green-100 border border-green-300 rounded-md">
                {{ session('message') }}
            </div>
        @endif
        <div class="flex flex-col w-full mt-6 space-y-5 md:flex-row lg:mt-0 md:space-y-0 md:space-x-5">
           <x-elements.card>
                @livewire('chart-component', ['jsonData' => $orgChart->json_data])
           </x-elements.card>
        </div>
    </x-app.container>
</x-layouts.app>


