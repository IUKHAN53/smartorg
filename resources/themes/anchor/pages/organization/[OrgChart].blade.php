<?php

use function Laravel\Folio\{middleware, name};

middleware('auth');
name('organization.view');
?>

<x-layouts.app>
    <x-app.container x-data='{ showModal: false }' class="lg:space-y-6" x-cloak>
        <div class="flex justify-between">
            <div class="space-y-0.5 flex flex-row">
                <h3 class="text-lg sm:text-xl font-semibold tracking-tight dark:text-zinc-100">{{$orgChart->name}}</h3>
                <span style="max-height: 28px"
                      class="top-5 left-5 px-2 py-1 text-xs font-medium bg-{{$orgChart->is_shared ? 'green':'orange'}}-100 text-{{$orgChart->is_shared ? 'green':'orange'}}-600 rounded-lg ml-2">
                    {{$orgChart->is_shared ? 'Public' : 'Private'}}
                </span>
            </div>
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
            @livewire('chart-component', ['jsonData' => $orgChart->json_data])
    </x-app.container>
</x-layouts.app>


