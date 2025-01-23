<?php

namespace Tests\Feature\Livewire\Surveys;

use App\Livewire\Surveys\Overview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
