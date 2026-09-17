<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI integration speaking the OpenAI-compatible Chat Completions protocol.
 *
 * Works out-of-the-box with fully free sources: Ollama (local, no key),
 * Groq free tier, or OpenRouter free models. Every method falls back to a
 * safe deterministic local implementation when no endpoint is configured.
 */
class AiService
{
    public function __construct(
        protected array $config = [],
    ) {
        $this->config = $config ?: [
            'provider' => config('ai.provider'),
            'base_url' => config('ai.base_url'),
            'api_key' => config('ai.api_key'),
            'model' => config('ai.model'),
            'timeout' => config('ai.timeout'),
        ];
    }

    public function configured(): bool
    {
        $base = rtrim((string) ($this->config['base_url'] ?? ''), '/');

        return $base !== '' && preg_match('#^https?://#', $base) === 1;
    }

    public function providerLabel(): string
    {
        if (! $this->configured()) {
            return 'Built-in engine';
        }

        $label = match (($this->config['provider'] ?? '')) {
            'ollama' => 'Ollama (local, free)',
            'groq' => 'Groq free tier',
            'openrouter' => 'OpenRouter free models',
            'openai' => 'OpenAI',
            default => 'Custom endpoint',
        };

        return $label.' - '.($this->config['model'] ?? '');
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, array $options = []): string
    {
        $base = rtrim((string) $this->config['base_url'], '/');
        $payload = [
            'model' => $this->config['model'] ?? 'llama3.1',
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 1500,
        ];

        if (($options['json'] ?? false) && $this->supportsJsonMode()) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $http = Http::timeout((int) ($this->config['timeout'] ?? 60))
            ->connectTimeout(min(10, (int) ($this->config['timeout'] ?? 60)))
            ->acceptJson();

        if (! empty($this->config['api_key'])) {
            $http = $http->withToken((string) $this->config['api_key']);
        }

        if (($this->config['provider'] ?? null) === 'openrouter') {
            $http = $http->withHeaders([
                'HTTP-Referer' => (string) config('app.url'),
                'X-Title' => (string) config('app.name'),
            ]);
        }

        try {
            $response = $http->post("{$base}/chat/completions", $payload);
        } catch (\Throwable $e) {
            throw new \RuntimeException('AI service unreachable: '.$e->getMessage(), 0, $e);
        }

        if (! $response->successful()) {
            throw new \RuntimeException('AI service error ('.$response->status().'): '.mb_substr((string) $response->body(), 0, 300));
        }

        return (string) ($response->json('choices.0.message.content') ?? '');
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array{content: string, used_fallback: bool, source: string}
     */
    public function generateWorkoutPlan(array $profile): array
    {
        if ($this->configured()) {
            try {
                $content = $this->chat([
                    ['role' => 'system', 'content' => $this->workoutSystemPrompt()],
                    ['role' => 'user', 'content' => json_encode($profile, JSON_PRETTY_PRINT)],
                ], ['temperature' => 0.6, 'max_tokens' => 2200]);

                if (mb_strlen(trim($content)) > 40) {
                    return ['content' => $content, 'used_fallback' => false, 'source' => 'ai'];
                }
            } catch (\Throwable $e) {
                Log::warning('AI workout generation failed, falling back', ['error' => $e->getMessage()]);
            }
        }

        return ['content' => $this->fallbackWorkoutPlan($profile), 'used_fallback' => true, 'source' => 'builtin'];
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $conversation
     * @return array{content: string, used_fallback: bool, source: string}
     */
    public function answerAssistant(array $conversation, array $context = []): array
    {
        if ($this->configured()) {
            try {
                $messages = [
                    ['role' => 'system', 'content' => $this->assistantSystemPrompt()],
                    ['role' => 'system', 'content' => 'Live gym context (JSON): '.json_encode($context, JSON_PRETTY_PRINT)],
                ];
                foreach ($conversation as $message) {
                    $messages[] = ['role' => (string) $message['role'], 'content' => (string) $message['content']];
                }

                $content = $this->chat($messages, ['temperature' => 0.5, 'max_tokens' => 900]);

                if (mb_strlen(trim($content)) > 0) {
                    return ['content' => $content, 'used_fallback' => false, 'source' => 'ai'];
                }
            } catch (\Throwable $e) {
                Log::warning('AI assistant failed, falling back', ['error' => $e->getMessage()]);
            }
        }

        return ['content' => $this->fallbackAssistant($conversation, $context), 'used_fallback' => true, 'source' => 'builtin'];
    }

    /**
     * @return array{insights: array<int, string>, used_fallback: bool, source: string}
     */
    public function insights(array $stats): array
    {
        if ($this->configured()) {
            try {
                $content = $this->chat([
                    ['role' => 'system', 'content' => $this->insightsSystemPrompt()],
                    ['role' => 'user', 'content' => json_encode($stats, JSON_PRETTY_PRINT)],
                ], ['temperature' => 0.4, 'max_tokens' => 900]);

                $bullets = collect(explode("\n", $content))
                    ->map(fn (string $line) => trim(preg_replace('/^[-*\\d.\\s]+/', '', $line)))
                    ->filter(fn (string $line) => mb_strlen($line) > 8)
                    ->values()
                    ->all();

                if (count($bullets) >= 1) {
                    return ['insights' => array_slice($bullets, 0, 8), 'used_fallback' => false, 'source' => 'ai'];
                }
            } catch (\Throwable $e) {
                Log::warning('AI insights failed, falling back', ['error' => $e->getMessage()]);
            }
        }

        return ['insights' => $this->fallbackInsights($stats), 'used_fallback' => true, 'source' => 'builtin'];
    }

    protected function supportsJsonMode(): bool
    {
        // Ollama may not enforce response_format, so never require it.
        return ($this->config['provider'] ?? '') !== 'ollama';
    }

    protected function workoutSystemPrompt(): string
    {
        return 'You are an expert certified fitness coach. Create a personalized, structured, and safe workout plan. '
            .'Use the member profile as JSON. Consider their goal, fitness level, available days per week and equipment, '
            .'then produce a clear weekly schedule with a specific warm-up, main exercises (sets x reps), and a cooldown per day. '
            .'Keep it practical, include rest guidance and recovery tips, and safety briefings for beginners. '
            .'Write in clean markdown with headings. Do not invent medical information; advise consulting a professional where relevant.';
    }

    protected function assistantSystemPrompt(): string
    {
        return 'You are GymHub Copilot, a friendly and precise assistant for gym staff and managers. '
            .'You are given live JSON context about the gym. Answer questions using ONLY that context; '
            .'if you lack data, say so and suggest the right page to get it. Be concise, practical, and never '
            .'share credentials or private personal data beyond what the context contains.';
    }

    protected function insightsSystemPrompt(): string
    {
        return 'You are a gym business analyst. Review the JSON metrics and produce a short list of 3-6 punchy, '
            .'actionable insights separated one per line. Focus on risks (memberships expiring, members inactive, '
            .'low trainer utilisation) and opportunities (upsells, reactivation, peak hours). Numbers only from the context.';
    }

    /*
    |--------------------------------------------------------------------------
    | Deterministic fallbacks
    |--------------------------------------------------------------------------
    */

    protected function fallbackWorkoutPlan(array $profile): string
    {
        $name = (string) ($profile['member_name'] ?? 'Member');
        $goal = strtolower((string) ($profile['goal'] ?? 'general fitness'));
        $level = strtolower((string) ($profile['fitness_level'] ?? 'beginner'));
        $days = max(1, min(7, (int) ($profile['days_per_week'] ?? 3)));

        $focus = $this->goalFocus($goal);

        $routine = [];
        foreach (range(1, $days) as $day) {
            $routine[] = $this->fallbackDay($day, $level, $focus, $days);
        }

        return 'Personalized workout plan for '.$name."\n"
            .'Goal: '.ucfirst($goal).' | Level: '.ucfirst($level).' | Days/week: '.$days."\n\n"
            .implode("\n", $routine)
            ."\n\nNotes:\n"
            ."- Warm up 5-10 minutes before every session and cool down after.\n"
            ."- Rest 48 hours between sessions targeting the same muscles.\n"
            ."- Increase weight/reps progressively; stay hydrated.\n"
            .'- Stop if you feel sharp pain and consult a professional.';
    }

    protected function fallbackDay(int $day, string $level, string $focus, int $totalDays): string
    {
        $strength = $totalDays >= 4 ? "- Heavy compounds (squat, deadlift, bench) if available\n" : '';

        return 'Day '.$day.' - '.$this->fallbackTitle($day, $focus, $totalDays)."\n"
            .'- '.$strength
            .$this->fallbackExercises($focus, $level, 4)
            ."- Cooldown: 2-3 min light stretching + breathing\n";
    }

    protected function fallbackTitle(int $day, string $focus, int $totalDays): string
    {
        if ($totalDays <= 3 || $focus === 'general fitness') {
            return in_array($day, [1, 3, 5], true) ? 'Full-body strength' : 'Cardio + core';
        }

        $titles = ['Push day', 'Pull day', 'Leg day', 'Upper body', 'Lower body', 'Cardio + mobility', 'Active recovery'];

        return $titles[($day - 1) % count($titles)];
    }

    protected function fallbackExercises(string $focus, string $level, int $count): string
    {
        $sets = match ($level) {
            'intermediate' => '3-4 sets x 8-10 reps',
            'advanced', 'expert' => '4-5 sets x 6-8 reps',
            default => '2-3 sets x 10-12 reps',
        };

        $menu = match ($this->goalFocus($focus)) {
            'weight loss' => ['Jump rope / treadmill', 'Cycling intervals', 'Lunges', 'Plank', 'Kettlebell swings', 'Mountain climbers', 'Squats', 'Burpees'],
            'muscle gain' => ['Bench press', 'Rows', 'Shoulder press', 'Pull-ups', 'Biceps curls', 'Triceps dips', 'Leg press', 'Deadlifts'],
            'strength' => ['Squats', 'Deadlifts', 'Bench press', 'Overhead press', 'Rows', 'Pull-ups', 'Farmer walks'],
            'endurance' => ['Rowing', 'Treadmill intervals', 'Jump rope', 'Cycling', 'Stair climber', 'Bodyweight circuits'],
            'flexibility' => ['Dynamic stretching', 'Yoga flows', 'Foam rolling', 'Hip openers', 'Held stretches'],
            default => ['Squats', 'Push-ups', 'Rows', 'Plank', 'Cardio intervals', 'Hip thrusts', 'Lat pull-downs'],
        };

        return implode("\n", array_map(
            fn (string $exercise) => "- $exercise - $sets",
            array_slice($menu, 0, $count)
        ))."\n";
    }

    protected function goalFocus(string $goal): string
    {
        if (str_contains($goal, 'weight') || str_contains($goal, 'fat') || str_contains($goal, 'slim')) {
            return 'weight loss';
        }
        if (str_contains($goal, 'muscle') || str_contains($goal, 'gain') || str_contains($goal, 'bulk')) {
            return 'muscle gain';
        }
        if (str_contains($goal, 'strength') || str_contains($goal, 'power')) {
            return 'strength';
        }
        if (str_contains($goal, 'endurance') || str_contains($goal, 'cardio') || str_contains($goal, 'stamina')) {
            return 'endurance';
        }
        if (str_contains($goal, 'flexib') || str_contains($goal, 'mobil')) {
            return 'flexibility';
        }

        return 'general fitness';
    }

    protected function fallbackAssistant(array $conversation, array $context): string
    {
        $last = collect($conversation)->last();
        $question = strtolower((string) ($last['content'] ?? ''));

        $members = (int) ($context['members_count'] ?? 0);
        $active = (int) ($context['active_memberships'] ?? 0);
        $revenue = (float) ($context['revenue_total'] ?? 0);
        $expiring = (int) ($context['expiring_soon'] ?? 0);
        $trainers = (int) ($context['trainers_count'] ?? 0);
        $plans = (array) ($context['plan_counts'] ?? []);

        foreach (['can you', 'please', 'tell me', 'recommend', 'help', 'whats ', 'what is', 'how many', 'how much'] as $word) {
            $question = str_replace($word, '', $question);
        }

        if (str_contains($question, 'member') || str_contains($question, 'student') || str_contains($question, 'client')) {
            return "We currently have {$members} members on record, {$active} with an active membership. I can break this down by plan or status if you'd like.";
        }
        if (str_contains($question, 'revenue') || str_contains($question, 'earning') || str_contains($question, 'income') || str_contains($question, 'money')) {
            return 'Total recorded revenue is currently '.$this->money($revenue).'. I can show you payments by month or plan next.';
        }
        if (str_contains($question, 'expir') || str_contains($question, 'ending') || str_contains($question, 'renew')) {
            return "There are {$expiring} memberships expiring in the next 30 days. Check the AI insights panel to see who needs a renewal nudge.";
        }
        if (str_contains($question, 'plan') || str_contains($question, 'pricing') || str_contains($question, 'tariff')) {
            if (count($plans)) {
                return 'Here are the current plans and how many members each has: '
                    .collect($plans)->map(fn ($count, $name) => "$name ({$count})")->implode(', ').'.';
            }

            return 'You currently have no membership plans defined. Create one from the Membership Plans page first.';
        }
        if (str_contains($question, 'trainer') || str_contains($question, 'coach')) {
            return "You have {$trainers} trainers on your team. Use the AI workout generator to build personalized sessions for any member.";
        }
        if (str_contains($question, 'workout') || str_contains($question, 'exercise') || str_contains($question, 'routine') || str_contains($question, 'gym')) {
            return 'Share the member goal, level and available days and I can build a workout plan. Or open the AI page and use the generator.';
        }

        return 'I can help you understand your gym - ask me about members, revenue, expiring memberships, plans, or trainers. '
            .'To unlock full natural-language answers, connect a free AI endpoint (Ollama, Groq or OpenRouter) in the AI config.';
    }

    protected function fallbackInsights(array $stats): array
    {
        $members = (int) ($stats['members_count'] ?? 0);
        $active = (int) ($stats['active_memberships'] ?? 0);
        $expiring = (int) ($stats['expiring_soon'] ?? 0);
        $revenue = (float) ($stats['revenue_total'] ?? 0);
        $attendance = (int) ($stats['attendance_this_week'] ?? 0);
        $inactive = (int) ($stats['inactive_members'] ?? 0);

        $lines = [
            "Active memberships: {$active} of {$members} members.",
            'Total recorded revenue: '.$this->money($revenue).'.',
        ];

        if ($expiring > 0) {
            $lines[] = "{$expiring} membership(s) expire within 30 days - reach out early to secure renewals.";
        } elseif ($members > 0) {
            $lines[] = 'Nothing expires in the next 30 days - a good window to upsell upgrades.';
        }

        if ($inactive > 0) {
            $lines[] = "{$inactive} member(s) are marked inactive. A targeted reactivation campaign could recover revenue.";
        }

        if ($members > 0 && $attendance > 0) {
            $rate = round($attendance * 100 / $members);
            $lines[] = "This week shows {$attendance} check-ins (~{$rate}% of members). Use AI to spot members missing from the schedule.";
        } elseif ($members > 0) {
            $lines[] = 'No check-ins recorded this week yet - log attendance to unlock engagement analytics.';
        } else {
            $lines[] = 'No members yet. Add your first member to start tracking growth.';
        }

        return $lines;
    }

    protected function money(float $amount): string
    {
        return 'Rs. '.number_format($amount, 2);
    }
}
