<?php

use function Laravel\Folio\{middleware, name};

?>

<x-layouts.public>
    <x-app.container class="lg:space-y-6" x-cloak>
        <div class="flex justify-between">
            <x-app.heading
                title="{{$orgChart->name}}"
                :border="false"
            />
        </div>
            @livewire('chart-component', ['jsonData' => $orgChart->json_data])
    </x-app.container>
</x-layouts.public>


