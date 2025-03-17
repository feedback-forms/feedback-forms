<div class="flex flex-col gap-4 p-4 md:p-20">
    <!-- Header -->
    <h1 class="text-xl md:text-2xl font-bold text-gray-700 dark:text-gray-200 px-2">
        {{ __('options.title') }}
    </h1>
    
    <!-- Add Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <h2 class="font-bold text-gray-700 dark:text-gray-200 p-4 border-b border-gray-200 dark:border-gray-700">
            {{ __('options.add') }}
        </h2>
        
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Subject Form -->
            <div class="space-y-3">
                <x-input-label for="subject" :value="__('options.subject.add')" />
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 items-start">
                    <div class="flex-1 flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <x-text-input 
                            wire:model="newSubject" 
                            id="subject" 
                            class="w-full sm:w-2/3 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                            type="text"
                            placeholder="{{ __('options.subject.input') }}"
                            required />
                        <x-text-input 
                            wire:model="newSubjectCode" 
                            id="subjectCode" 
                            class="w-full sm:w-1/3 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                            type="text" 
                            placeholder="{{ __('options.subject.code') }}"
                            required />
                    </div>
                    <button wire:click="addSubject" 
                            class="shrink-0 w-full sm:w-10 h-10 rounded-full bg-indigo-600 dark:bg-indigo-500 text-white shadow-sm hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        <x-fas-plus class="w-4 h-4 mx-auto" />
                    </button>
                </div>
            </div>

            <!-- Department Form -->
            <div class="space-y-3">
                <x-input-label for="department" :value="__('options.department.add')" />
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 items-start">
                    <div class="flex-1 flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <x-text-input 
                            wire:model="newDepartment" 
                            id="department" 
                            class="w-full sm:w-2/3 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                            type="text" 
                            placeholder="{{ __('options.department.input') }}"
                            required />
                        <x-text-input 
                            wire:model="newDepartmentCode" 
                            id="departmentCode" 
                            class="w-full sm:w-1/3 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                            type="text" 
                            placeholder="{{ __('options.department.code') }}"
                            required />
                    </div>
                    <button wire:click="addDepartment" 
                            class="shrink-0 w-full sm:w-10 h-10 rounded-full bg-indigo-600 dark:bg-indigo-500 text-white shadow-sm hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        <x-fas-plus class="w-4 h-4 mx-auto" />
                    </button>
                </div>
            </div>

            <!-- Grade Level Form -->
            <div class="space-y-3">
                <x-input-label for="gradeLevel" :value="__('options.grade_level.add')" />
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 items-start">
                    <x-text-input 
                        wire:model="newGradeLevel" 
                        id="gradeLevel" 
                        class="w-full flex-1 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                        type="text" 
                        placeholder="{{ __('options.grade_level.input') }}"
                        required />
                    <button wire:click="addGradeLevel" 
                            class="shrink-0 w-full sm:w-10 h-10 rounded-full bg-indigo-600 dark:bg-indigo-500 text-white shadow-sm hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        <x-fas-plus class="w-4 h-4 mx-auto" />
                    </button>
                </div>
            </div>

            <!-- School Year Form -->
            <div class="space-y-3">
                <x-input-label for="schoolYear" :value="__('options.school_year.add')" />
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 items-start">
                    <x-text-input 
                        wire:model="newSchoolYear" 
                        id="schoolYear" 
                        class="w-full flex-1 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                        type="text" 
                        placeholder="{{ __('options.school_year.input') }}"
                        required />
                    <button wire:click="addSchoolYear" 
                            class="shrink-0 w-full sm:w-10 h-10 rounded-full bg-indigo-600 dark:bg-indigo-500 text-white shadow-sm hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        <x-fas-plus class="w-4 h-4 mx-auto" />
                    </button>
                </div>
            </div>

            <!-- School Class Form -->
            <div class="space-y-3">
                <x-input-label for="schoolClass" :value="__('options.school_class.add')" />
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 items-start">
                    <div class="flex-1 flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <x-text-input 
                            wire:model="newSchoolClass" 
                            id="schoolClass" 
                            class="w-full sm:w-2/3 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"
                            type="text" 
                            placeholder="{{ __('options.school_class.input') }}"
                            required />
                        <select 
                            wire:model="selectedGradeLevel" 
                            class="w-full sm:w-1/3 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('options.grade_level.select') }}</option>
                            @foreach($options[__('options.grade_level.name_plural')] ?? [] as $gradeLevel)
                                <option value="{{ $gradeLevel['id'] }}">{{ $gradeLevel['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button wire:click="addSchoolClass" 
                            class="shrink-0 w-full sm:w-10 h-10 rounded-full bg-indigo-600 dark:bg-indigo-500 text-white shadow-sm hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        <x-fas-plus class="w-4 h-4 mx-auto" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mt-4">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                            {{ __('options.table.option') }}
                        </th>
                        <th scope="col" class="px-6 py-4 text-right text-sm font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                            {{ __('options.table.manage') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($options as $key => $optionGroup)
                        <tr class="bg-gray-100 dark:bg-gray-600">
                            <td colspan="2" class="px-6 py-3 text-base font-medium text-gray-900 dark:text-white bg-opacity-75">
                                <div class="flex items-center">
                                    <span class="border-l-4 border-indigo-500 pl-3">
                                        {{ $key }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @foreach($optionGroup as $option)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $option['name'] }}
                                    @if(isset($option['code']))
                                        <span class="text-gray-500 dark:text-gray-400 ml-2">({{ $option['code'] }})</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <button 
                                            x-on:click.prevent="$dispatch('open-modal', 'edit-option')"
                                            wire:click="editOption('{{ $key }}', {{ $option['id'] }})"
                                            class="inline-flex items-center p-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                            <x-fas-pen-to-square class="w-4 h-4" />
                                        </button>
                                        <button 
                                            wire:click="deleteOption('{{ $key }}', {{ $option['id'] }})"
                                            class="inline-flex items-center p-2 text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                            <x-fas-trash class="w-4 h-4" />
                                        </button>
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
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-700 dark:text-gray-200 mb-6">
                {{ __('options.modal.title') }}
            </h1>
            
            <form wire:submit.prevent="updateOption" class="space-y-6">
                <div class="space-y-4">
                    <div>
                        <x-input-label for="name" :value="__('options.modal.label')" class="mb-2" />
                        <input id="name" 
                               wire:model.live="editingOption"
                               type="text"
                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               autofocus />
                        <x-input-error :messages="$errors->get('editingOption')" class="mt-2" />
                    </div>
                    
                    @if($editingCode != '')
                        <div>
                            <x-input-label for="code" :value="__('options.modal.code')" class="mb-2" />
                            <input id="code" 
                                   wire:model="editingCode"
                                   type="text"
                                   class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   autofocus />
                            <x-input-error :messages="$errors->get('editingCode')" class="mt-2" />
                        </div>
                    @endif

                    <input type="hidden" wire:model="editingKey" value="{{ $key }}" />
                </div>

                <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <x-secondary-button type="button" x-on:click="$dispatch('close-modal', 'edit-option')">
                        {{ __('options.modal.cancel') }}
                    </x-secondary-button>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:bg-indigo-700 dark:focus:bg-indigo-600 active:bg-indigo-900 dark:active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ __('options.modal.button') }}
                    </button>
                </div>               
            </form>
        </div>
    </x-modal>
</div>