@props(['routeName' => 'surveys.scan'])

<div
    x-data="qrCodeModal('{{ url(route($routeName)) }}')"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 flex items-center justify-center z-50 bg-gray-900 bg-opacity-50"
    style="display: none;"
>
    <div @click.away="show = false" class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{__('surveys.qr_code_title')}}</h3>
            <button @click="show = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
        <div class="text-center mb-4">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{__('surveys.scan_to_access')}}</p>
            <div class="flex justify-center mb-3">
                <div id="qrcode-container" class="relative">
                    <canvas id="qrcode-canvas" class="border border-gray-300 dark:border-gray-700"></canvas>
                    <!-- Loading state -->
                    <div id="qrcode-loading" class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-800 bg-opacity-90 dark:bg-opacity-90" style="display: flex;">
                        <div class="text-blue-500">
                            <svg class="animate-spin h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div id="qrcode-error" style="display: none;" class="text-red-500 text-xs mb-3">
                {{__('surveys.qr_code_error')}}
            </div>
            <div class="text-sm bg-gray-100 dark:bg-gray-700 p-2 rounded">
                <p class="text-xs text-gray-700 dark:text-gray-300 break-all" x-text="currentAccesskey"></p>
                <p class="text-xs text-gray-700 dark:text-gray-300 mt-1 break-all" x-text="surveyUrl"></p>
            </div>
        </div>
        <div class="flex justify-center">
            <button @click="show = false" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                {{__('surveys.close')}}
            </button>
        </div>
    </div>
</div>