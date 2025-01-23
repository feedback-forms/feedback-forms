<?php

namespace Tests\Feature\Livewire\Templates;

use App\Livewire\Templates\Overview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OverviewTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(Overview::class)
            ->assertStatus(200);
    }
}