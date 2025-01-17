<div
    x-show="showModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
    x-cloak
>
    <div class="w-full h-full bg-white p-6">
        <button
            x-on:click="showModal = false"
            class="absolute top-4 right-4 bg-gray-800 text-white px-4 py-2 rounded">
            Close
        </button>
        <h2 class="text-xl font-bold mb-4">Public Page</h2>
        <div class="text-gray-600">
            <p>https://sample.com/public/550e8400-e29b-41d4-a716-446655440000</p>
        </div>
    </div>
</div>
