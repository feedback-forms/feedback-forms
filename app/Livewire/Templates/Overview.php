<?php

namespace App\Livewire\Templates;

use App\Models\Feedback_template;
use Livewire\Component;

class Overview extends Component
{
    public array $templates = [];
    public array $featuredItems = [];
    public array $dbTemplates = [];

    public function mount()
    {
        // Get templates from database
        $this->dbTemplates = Feedback_template::all()->toArray();

        // Map database templates to display format
        $templateTypes = [
            'templates.feedback.target' => [
                'title' => __('templates.target_feedback'),
                'description' => __('templates.target_feedback_description'),
                'image' => 'img/bullseye-arrow.svg',
                'route' => 'feedback/target'
            ],
            'templates.feedback.table' => [
                'title' => __('templates.table_feedback'),
                'description' => __('templates.table_feedback_description'),
                'image' => 'img/table-list.svg',
                'route' => 'feedback/table'
            ],
            'templates.feedback.smiley' => [
                'title' => __('templates.smiley_feedback'),
                'description' => __('templates.smiley_feedback_description'),
                'image' => 'img/smile.svg',
                'route' => 'feedback/smiley'
            ],
            'templates.feedback.checkbox' => [
                'title' => __('templates.checkbox_feedback'),
                'description' => __('templates.checkbox_feedback_description'),
                'image' => 'img/checkbox.svg',
                'route' => 'feedback/checkbox'
            ],
        ];

        // Populate template gallery items
        $this->templates = [];
        foreach ($this->dbTemplates as $template) {
            if (isset($templateTypes[$template['name']])) {
                $templateInfo = $templateTypes[$template['name']];
                $templateType = str_replace('templates.feedback.', '', $template['name']);
                $this->templates[] = [
                    'id' => $template['id'],
                    'name' => $template['name'],
                    'title' => $templateInfo['title'],
                    'description' => $templateInfo['description'],
                    'image' => $templateInfo['image'],
                    'route' => $templateInfo['route'],
                    'create_url' => route('surveys.create.from-template', $templateType)
                ];
            }
        }

        // Add some additional templates for the gallery
        $additionalTemplates = [
            ['title' => 'Multiple-Choice', 'image' => 'img/preview.png'],
            ['title' => 'Checkbox Lists', 'image' => 'img/preview.png'],
            ['title' => 'Sliders', 'image' => 'img/preview.png'],
            ['title' => 'Rating Scale', 'image' => 'img/preview.png'],
            ['title' => 'Open Text', 'image' => 'img/preview.png'],
        ];

        $this->templates = array_merge($this->templates, $additionalTemplates);

        // Populate featured items with actual templates that have create_url
        $featuredTemplates = array_filter($this->templates, function($template) {
            return isset($template['create_url']);
        });

        $this->featuredItems = array_slice($featuredTemplates, 0, 3);
    }

    public function render()
    {
        return view('livewire.templates.overview')->title(__('title.template.choose'));
    }
}