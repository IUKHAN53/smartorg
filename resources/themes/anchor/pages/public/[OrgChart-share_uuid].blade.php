<?php

use function Laravel\Folio\{middleware, name};
?>

<x-layouts.app>
    <x-app.container class="lg:space-y-6" x-cloak>
        <div class="flex justify-between">
            <x-app.heading
                title="{{$orgChart->name}}"
                :border="false"
            />
        </div>
        <div class="flex flex-col w-full mt-6 space-y-5 md:flex-row lg:mt-0 md:space-y-0 md:space-x-5">
           <x-elements.card>
                @livewire('chart-component', ['jsonData' => $orgChart->json_data])
           </x-elements.card>
        </div>
    </x-app.container>
</x-layouts.app>


