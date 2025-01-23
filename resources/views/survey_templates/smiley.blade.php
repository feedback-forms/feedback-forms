<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 text-center">
                    <div class="grid grid-cols-2 gap-4">
                        <div style="display: flex; justify-content: center">
                            <x-far-face-smile class="w-20 h-20" />
                        </div>
                        <div style="display: flex; justify-content: center">
                            <x-far-face-frown class="w-20 h-20" />
                        </div>
                        <div>
                            <textarea id="message" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Ich finde gut, dass..."></textarea>
                        </div>
                        <div>
                            <textarea id="message" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Ich finde nicht gut, dass..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end pr-6 pb-6">
                    <x-primary-button>
                        Absenden <x-fas-arrow-right class="w-6 h-6" />
                    </x-primary-button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
