<?php

namespace App\Livewire\TeacherInterface;

use Livewire\Component;

class Overview extends Component
{
    public array $templates = [];
    public array $featuredItems = [];

    public function mount()
    {
        // Populate template gallery items
        $this->templates = [
            ['title' => 'Multiple-Choice', 'image' => 'img/preview.png'],
            ['title' => 'Checkbox Lists', 'image' => 'img/preview.png'],
            ['title' => 'Sliders', 'image' => 'img/preview.png'],
            ['title' => 'Rating Scale', 'image' => 'img/preview.png'],
            ['title' => 'Open Text', 'image' => 'img/preview.png'],
            ['title' => 'Customer Satisfaction Survey', 'image' => 'img/preview.png'],
            ['title' => 'Employee Engagement Survey', 'image' => 'img/preview.png'],
            ['title' => 'Product Feedback Form', 'image' => 'img/preview.png'],
            ['title' => 'Employee Satisfaction Survey', 'image' => 'img/preview.png'],
            ['title' => 'Customer Feedback Form', 'image' => 'img/preview.png'],
            ['title' => 'Employee Engagement Survey', 'image' => 'img/preview.png'],
            ['title' => 'Product Feedback Form', 'image' => 'img/preview.png'],
        ];

        // Populate featured items
        $this->featuredItems = [
            [
                'title' => 'Customer Satisfaction Survey',
                'description' => 'Comprehensive template for gathering customer feedback and measuring satisfaction levels.',
                'image' => 'img/preview.png'
            ],
            [
                'title' => 'Employee Engagement Survey',
                'description' => 'Template designed to measure employee satisfaction and engagement in the workplace.',
                'image' => 'img/preview.png'
            ],
            [
                'title' => 'Product Feedback Form',
                'description' => 'Structured template for collecting detailed product feedback from users.',
                'image' => 'img/preview.png'
            ],
        ];
    }

    public function render()
    {
        return view('livewire.teacherInterface.overview');
    }
}