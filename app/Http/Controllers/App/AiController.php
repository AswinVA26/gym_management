<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\WorkoutPlan;
use App\Services\AiService;
use App\Services\GymStats;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(
        protected AiService $ai,
    ) {}

    public function index()
    {
        $this->authorize('use-ai');

        $members = Member::with('trainer')->get();
        $savedPlans = WorkoutPlan::with('member')->latest()->limit(20)->get();

        return view('app.ai.index', [
            'members' => $members,
            'savedPlans' => $savedPlans,
            'aiProviderLabel' => $this->ai->providerLabel(),
            'aiConfigured' => $this->ai->configured(),
        ]);
    }

    public function storeWorkoutPlan(Request $request)
    {
        $this->authorize('use-ai');

        $data = $request->validate([
            'member_id' => ['nullable', 'exists:tenant.members,id'],
            'goal' => ['required', 'string', 'max:255'],
            'fitness_level' => ['required', 'in:beginner,intermediate,advanced'],
            'days_per_week' => ['required', 'integer', 'min:1', 'max:7'],
            'equipment' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $member = ! empty($data['member_id']) ? Member::find($data['member_id']) : null;

        $profile = [
            'member_name' => $member?->name ?? ($request->user()->name ?? 'Member'),
            'member_age' => $member?->dob?->age,
            'member_gender' => $member?->gender,
            'goal' => $data['goal'],
            'fitness_level' => $data['fitness_level'],
            'days_per_week' => (int) $data['days_per_week'],
            'equipment' => $data['equipment'] ?? 'gym machines + free weights',
        ];

        $result = $this->ai->generateWorkoutPlan($profile);

        $title = $data['title'] ?? trim(($member?->name ?? 'Member').' - '.$data['goal']);

        $plan = WorkoutPlan::create([
            'member_id' => $member?->id,
            'title' => $title,
            'focus' => $data['goal'],
            'level' => $data['fitness_level'],
            'days_per_week' => (int) $data['days_per_week'],
            'plan' => $result['content'],
            'generated_by' => 'ai',
            'ai_meta' => [
                'source' => $result['source'],
                'used_fallback' => $result['used_fallback'],
                'profile' => $profile,
            ],
        ]);

        return response()->json([
            'id' => $plan->id,
            'title' => $plan->title,
            'content' => $plan->plan,
            'source' => $result['source'],
            'used_fallback' => $result['used_fallback'],
            'member' => $member,
        ]);
    }

    public function assistant(Request $request)
    {
        $this->authorize('use-ai');

        $data = $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:12'],
            'messages.*.role' => ['required', 'in:user,assistant,system'],
            'messages.*.content' => ['required', 'string', 'max:4000'],
        ]);

        $conversation = array_map(
            fn ($m) => ['role' => $m['role'], 'content' => $m['content']],
            array_slice($data['messages'], -12)
        );

        $context = GymStats::collect();
        $result = $this->ai->answerAssistant($conversation, $context);

        return response()->json([
            'reply' => $result['content'],
            'source' => $result['source'],
            'used_fallback' => $result['used_fallback'],
            'received_at' => now()->toDateTimeString(),
        ]);
    }

    public function insights()
    {
        $this->authorize('use-ai');

        $stats = GymStats::collect();
        $result = $this->ai->insights($stats);

        return response()->json([
            'insights' => $result['insights'],
            'source' => $result['source'],
            'used_fallback' => $result['used_fallback'],
            'stats' => $stats,
            'generated_at' => now()->toDateTimeString(),
        ]);
    }
}
