<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\WorkoutPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiTest extends TestCase
{
    use RefreshDatabase;

    public function test_workout_plan_is_generated_and_persisted_in_the_tenant_database(): void
    {
        $tenant = $this->provisionGym();
        $this->actingAs($this->gymAdminFor($tenant));

        $response = $this->postJson(route('app.ai.workout.store'), [
            'goal' => 'muscle gain',
            'fitness_level' => 'beginner',
            'days_per_week' => 3,
            'equipment' => 'dumbbells',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['id', 'title', 'content', 'source', 'used_fallback'])
            ->assertJson(['used_fallback' => true]);

        // AI is not configured in the test environment, so the deterministic
        // local engine must be used - and the plan must land in the gym DB.
        $this->switchToGym($tenant);
        $this->assertSame(1, WorkoutPlan::count());
        $this->assertStringContainsStringIgnoringCase('muscle gain', WorkoutPlan::firstOrFail()->plan);
    }

    public function test_assistant_answers_with_local_context(): void
    {
        $tenant = $this->provisionGym();
        $this->actingAs($this->gymAdminFor($tenant));

        $response = $this->postJson(route('app.ai.assistant'), [
            'messages' => [
                ['role' => 'user', 'content' => 'How many members do we have?'],
            ],
        ]);

        $response->assertOk()
            ->assertJson(['used_fallback' => true])
            ->assertJsonStructure(['reply', 'source', 'used_fallback']);

        $this->assertNotEmpty($response->json('reply'));
    }

    public function test_insights_endpoint_always_returns_something_useful(): void
    {
        $tenant = $this->provisionGym();
        $this->actingAs($this->gymAdminFor($tenant));

        $this->getJson(route('app.ai.insights'))
            ->assertOk()
            ->assertJsonStructure(['insights', 'source', 'used_fallback', 'stats'])
            ->assertJson(['used_fallback' => true]);
    }

    public function test_ai_page_renders_when_saved_plans_exist(): void
    {
        $tenant = $this->provisionGym();
        $admin = $this->gymAdminFor($tenant);
        $this->switchToGym($tenant);

        Member::create([
            'member_code' => 'MB-000001',
            'name' => 'Rahul',
            'phone' => '9811111111',
            'status' => 'active',
        ]);

        $plan = WorkoutPlan::create([
            'member_id' => Member::firstOrFail()->id,
            'title' => 'Beginner build',
            'focus' => 'muscle gain',
            'level' => 'beginner',
            'days_per_week' => 3,
            'plan' => "Day 1 - Full-body\n- Squats 2-3 x 10-12\n",
            'generated_by' => 'ai',
            'ai_meta' => [
                'source' => 'builtin',
                'used_fallback' => true,
                'profile' => ['goal' => 'muscle gain'],
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('app.ai.index'))
            ->assertOk()
            ->assertSee($plan->title)
            ->assertSee('Built-in')
            ->assertSee('Squats');
    }

    public function test_member_passed_to_workout_generator_is_reflected(): void
    {
        $tenant = $this->provisionGym();
        $admin = $this->gymAdminFor($tenant);
        $this->actingAs($admin);

        $this->switchToGym($tenant);
        $member = Member::create([
            'member_code' => 'MB-000001',
            'name' => 'Rahul',
            'phone' => '9811111111',
            'status' => 'active',
        ]);

        $response = $this->postJson(route('app.ai.workout.store'), [
            'member_id' => $member->id,
            'goal' => 'weight loss',
            'fitness_level' => 'intermediate',
            'days_per_week' => 4,
        ]);

        $response->assertOk();

        $this->switchToGym($tenant);
        $plan = WorkoutPlan::firstOrFail();
        $this->assertSame($member->id, $plan->member_id);
        $this->assertStringContainsString('Rahul', $plan->title);
    }
}
