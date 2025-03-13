<div class="flex flex-col gap-2 p-20">
    <!-- Header -->
    <h1 class="text-2xl font-bold text-gray-700 dark:text-gray-200 px-2">
        {{ __('options.title') }}
    </h1>
    <h2 class="font-bold text-gray-700 dark:text-gray-200 px-2">
        {{ __('options.add') }}
    </h2>
    <div class="bg-gray-50 dark:bg-gray-800 flex flex-col gap-10 p-10">
        <!-- Subject Form -->
        <div class="flex flex-col gap-4">
            <div class="p-4">
                <x-input-label for="subject" :value="__('options.subject.add')" class="mb-1 block" />
                <div class="flex flex-col sm:flex-row w-full gap-2">
                    <x-text-input 
                        wire:model="newSubject" 
                        id="subject" 
                        class="sm:w-2/4 w-full" 
                        type="text" 
                        required 
                        placeholder="{{ __('options.subject.name') }}" />
                    <x-text-input 
                        wire:model="newSubjectCode" 
                        id="subjectCode" 
                        class="sm:w-1/4 w-full" 
                        type="text" 
                        required 
                        placeholder="{{ __('options.modal.code') }}" />
                    <button wire:click="addSubject" class="sm:ml-1 mt-2 sm:mt-0 w-10 h-10 rounded-full bg-slate-800 border border-transparent text-center text-sm text-white transition-all shadow-sm hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none" type="button">
                        <x-fas-plus class="w-4 h-4 m-auto" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Department Form -->
        <div class="flex flex-col gap-4">
            <div class="p-4">
                <x-input-label for="department" :value="__('options.department.add')" class="mb-1 block" />
                <div class="flex flex-col sm:flex-row w-full gap-2">
                    <x-text-input 
                        wire:model="newDepartment" 
                        id="department" 
                        class="sm:w-2/4 w-full" 
                        type="text" 
                        required 
                        placeholder="{{ __('options.department.name') }}" />
                    <x-text-input 
                        wire:model="newDepartmentCode" 
                        id="departmentCode" 
                        class="sm:w-1/4 w-full" 
                        type="text" 
                        required 
                        placeholder="{{ __('options.modal.code') }}" />
                    <button wire:click="addDepartment" class="sm:ml-1 mt-2 sm:mt-0 w-10 h-10 rounded-full bg-slate-800 border border-transparent text-center text-sm text-white transition-all shadow-sm hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none" type="button">
                        <x-fas-plus class="w-4 h-4 m-auto" />
                    </button>
                </div>
            </div>
        </div>

        <!-- School Year Form -->
        <div class="flex flex-col gap-4">
            <div class="p-4">
                <x-input-label for="schoolYear" :value="__('options.school_year.add')" class="mb-1 block" />
                <div class="flex flex-col sm:flex-row w-full">
                    <x-text-input wire:model="newSchoolYear" id="schoolYear" class="sm:w-3/4 w-full" type="text" required />
                    <button wire:click="addSchoolYear" class="sm:ml-1 mt-2 sm:mt-0 w-10 h-10 rounded-full bg-slate-800 border border-transparent text-center text-sm text-white transition-all shadow-sm hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none" type="button">
                        <x-fas-plus class="w-4 h-4 m-auto" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Grade Level Form -->
        <div class="flex flex-col gap-4">
            <div class="p-4">
                <x-input-label for="gradeLevel" :value="__('options.grade_level.add')" class="mb-1 block" />
                <div class="flex flex-col sm:flex-row w-full">
                    <x-text-input wire:model="newGradeLevel" id="gradeLevel" class="sm:w-3/4 w-full" type="text" required />
                    <button wire:click="addGradeLevel" class="sm:ml-1 mt-2 sm:mt-0 w-10 h-10 rounded-full bg-slate-800 border border-transparent text-center text-sm text-white transition-all shadow-sm hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none" type="button">
                        <x-fas-plus class="w-4 h-4 m-auto" />
                    </button>
                </div>
            </div>
        </div>

        <!-- School Class Form -->
        <div class="flex flex-col gap-4">
            <div class="p-4">
                <x-input-label for="schoolClass" :value="__('options.school_class.add')" class="mb-1 block" />
                <div class="flex flex-col sm:flex-row w-full gap-2">
                    <x-text-input wire:model="newSchoolClass" id="schoolClass" class="sm:w-2/4 w-full" type="text" required placeholder="Klassenname" />
                    <select wire:model="selectedGradeLevel" class="sm:w-1/4 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                        <option value="">Jahrgang wählen</option>
                        @foreach($options[__('options.grade_level.name_plural')] ?? [] as $gradeLevel)
                            <option value="{{ $gradeLevel['id'] }}">{{ $gradeLevel['name'] }}</option>
                        @endforeach
                    </select>
                    <button wire:click="addSchoolClass" class="sm:ml-1 mt-2 sm:mt-0 w-10 h-10 rounded-full bg-slate-800 border border-transparent text-center text-sm text-white transition-all shadow-sm hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none" type="button">
                        <x-fas-plus class="w-4 h-4 m-auto" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <h2 class="font-bold text-gray-700 dark:text-gray-200 px-2">
        {{ __('options.manage') }}
    </h2>
    <div class="bg-gray-50 dark:bg-gray-800 flex flex-col gap-10 p-10">


        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            {{ __('options.table.option') }}
                        </th>
                        <th scope="col" class="px-6 py-3">
                            {{ __('options.table.manage') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($options as $key => $option)
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <td colspan="2" class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                {{ $key }}
                            </td>
                        </tr>
                        @foreach ($option as $value)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                                @if (array_key_exists('code', $value))
                                    <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $value['name'] }} - {{ $value['code'] }}
                                    </td>
                                @else
                                    <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $value['name'] }}
                                    </td>
                                @endif
                                <td class="px-6 py-4">       
                                    <button x-on:click.prevent="$dispatch('open-modal', 'edit-option')" 
                                            wire:click="editOption('{{ $key }}', {{ $value['id'] }})"       
                                        class="sm:ml-1 mt-2 sm:mt-0 w-10 h-10 rounded-full bg-slate-800 border border-transparent text-center text-sm text-white transition-all shadow-sm hover:shadow-lg focus:bg-slate-700 focus:shadow-none active:bg-slate-700 hover:bg-slate-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none" 
                                        type="button">
                                        <x-fas-pen-to-square class="w-4 h-4 m-auto" />
                                    </button>
                                    <div x-data="{ isOpen: false }" class="relative inline-block">
                                        <button @click="isOpen = !isOpen"
                                                @click.away="isOpen = false"
                                                class="sm:ml-1 mt-2 sm:mt-0 w-10 h-10 rounded-full bg-red-600 border border-transparent text-center text-sm text-white transition-all shadow-sm hover:shadow-lg focus:bg-red-700 focus:shadow-none active:bg-red-700 hover:bg-red-700 active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none">
                                            <x-fas-trash class="w-4 h-4 m-auto" />
                                        </button>
                                        
                                        <!-- Popover -->
                                        <div x-show="isOpen"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5">
                                            <div class="p-4">
                                                <p class="text-sm text-gray-700 dark:text-gray-200 mb-3">
                                                    Wirklich löschen?
                                                </p>
                                                <button wire:click="deleteOption('{{ $key }}', {{ $value['id'] }})"
                                                        @click="isOpen = false"
                                                        class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    Löschen
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>        
    </div>
    <x-modal name="edit-option" :show="$errors->isNotEmpty()" focusable>
        <h1 class="pt-4 pl-4 text-2xl font-bold text-gray-700 dark:text-gray-200 px-2">
            {{ __('options.modal.title') }}
        </h1>
        <form wire:submit.prevent="updateOption">
            <div>
                <x-input-label for="name" :value="__('options.modal.label')" />
                <input id="name" 
                       wire:model.live="editingOption"
                       type="text"
                       class="block mt-1 w-full dark:bg-gray-700 dark:text-white"
                       placeholder="{{ $editingName }}"
                       autofocus />
                <!-- Hidden input for key -->
                <input type="hidden" 
                       wire:model="editingKey" 
                       value="{{ $key }}" />
                <x-input-error :messages="$errors->get('editingOption')" class="mt-2" />
            </div>
            
            @if($editingCode != '')
                <div class="mt-4">
                    <x-input-label for="code" :value="__('options.modal.code')" />
                    <input id="code" 
                           wire:model="editingCode"
                           type="text"
                           class="block mt-1 w-full dark:bg-gray-700 dark:text-white"
                           placeholder="{{ $editingCode }}"
                           autofocus />
                    <x-input-error :messages="$errors->get('editingCode')" class="mt-2" />
                </div>
            @endif

            <div class="flex items-center justify-end mt-5 mr-4 mb-4">
                <x-primary-button type="submit">
                    {{ __('options.modal.button') }}
                </x-primary-button>
                <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'edit-option')">
                    {{ __('options.modal.cancel') }}
                </x-secondary-button>
            </div>               
        </form>
    </x-modal>
</div>