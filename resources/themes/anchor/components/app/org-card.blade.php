<div
    class="relative p-5 w-full bg-white dark:bg-zinc-800 rounded-lg border duration-300 ease-out group border-slate-200 dark:border-zinc-700 hover:scale-[1.01] shadow-sm">
    <div class="flex flex-col justify-between h-full">
        <div class="flex justify-between items-center mb-3">
            <div class="flex">
                <span class="text-lg font-bold tracking-tight leading-tight text-slate-700 dark:text-white mr-2">
                    {{ $title ?? 'OrgChart' }}
                </span>
                <span
                    class="top-5 left-5 px-2 py-1 text-xs font-medium bg-{{$badge=='Public' ? 'success':'info'}}-100 text-{{$badge=='Public' ? 'success':'info'}}-600 rounded-lg">
                    {{ $badge ?? 'Public' }}
                </span>
            </div>
            <div class="relative">
                <x-elements.card-button id="menuButton-{{ $id }}" class="focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                         stroke="currentColor" class="w-6 h-6 text-gray-500 hover:text-gray-800">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v.01M12 12v.01M12 18v.01"/>
                    </svg>
                </x-elements.card-button>
                <div id="menuDropdown-{{ $id }}"
                     class="absolute right-0 mt-2 hidden bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg shadow-lg z-50">
                    <ul>
                        <li>
                            {{ ($this->deleteAction)(['id' => $id]) }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <p class="text-sm text-gray-600 dark:text-zinc-200 opacity-60 mb-4">
            {{ $description ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.' }}
        </p>

        <div class="flex justify-start">
            <a href="{{ $href ?? '#' }}"
               class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                View and Edit
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function (e) {
        const menuButton = document.querySelector(`#menuButton-{{ $id }}`);
        const menuDropdown = document.querySelector(`#menuDropdown-{{ $id }}`);

        if (menuButton.contains(e.target)) {
            menuDropdown.classList.toggle('hidden');
        } else if (!menuDropdown.contains(e.target)) {
            menuDropdown.classList.add('hidden');
        }
    });
</script>
