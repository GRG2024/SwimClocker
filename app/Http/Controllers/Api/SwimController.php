<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SwimSession;
use App\Models\SwimSplit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SwimController extends Controller
{
    /**
     * Save a complete session with all splits.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'team_name' => 'required|string|max:255',
            'total_time_ms' => 'required|integer|min:0',
            'swimmers' => 'required|array|min:1',
            'swimmers.*' => 'string|max:100',
            'started_at' => 'required|date',
            'lap_distance_m' => 'integer|min:1',
            'splits' => 'required|array|min:1',
            'splits.*.swimmer_index' => 'required|integer|min:0',
            'splits.*.swimmer_name' => 'required|string|max:100',
            'splits.*.round' => 'required|integer|min:1',
            'splits.*.split_number' => 'required|integer|min:1',
            'splits.*.lap_time_ms' => 'required|integer|min:0',
            'splits.*.total_time_ms' => 'required|integer|min:0',
        ]);

        $lapDistance = $validated['lap_distance_m'] ?? 50;
        $totalDistance = count($validated['splits']) * $lapDistance;

        $session = DB::transaction(function () use ($validated, $totalDistance) {
            $session = SwimSession::create([
                'name' => $validated['name'],
                'team_name' => $validated['team_name'],
                'total_time_ms' => $validated['total_time_ms'],
                'total_splits' => count($validated['splits']),
                'total_rounds' => (int) ceil(count($validated['splits']) / count($validated['swimmers'])),
                'total_distance_m' => $totalDistance,
                'swimmers' => $validated['swimmers'],
                'started_at' => $validated['started_at'],
            ]);

            foreach ($validated['splits'] as $split) {
                $session->splits()->create($split);
            }

            return $session;
        });

        return response()->json([
            'success' => true,
            'session' => $session->load('splits'),
        ], 201);
    }

    /**
     * List all sessions, newest first.
     */
    public function index(Request $request): JsonResponse
    {
        $sessions = SwimSession::query()
            ->when($request->swimmer, function ($q, $swimmer) {
                $q->whereHas('splits', fn ($sq) => $sq->where('swimmer_name', 'like', "%{$swimmer}%"));
            })
            ->when($request->team, function ($q, $team) {
                $q->where('team_name', 'like', "%{$team}%");
            })
            ->orderByDesc('started_at')
            ->paginate(20);

        return response()->json($sessions);
    }

    /**
     * Get a single session with all splits.
     */
    public function show(SwimSession $session): JsonResponse
    {
        return response()->json($session->load('splits'));
    }

    /**
     * Delete a session.
     */
    public function destroy(SwimSession $session): JsonResponse
    {
        $session->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Stats per swimmer across all sessions.
     * This is the core view: individual swimmer performance over time.
     */
    public function swimmerStats(): JsonResponse
    {
        $stats = SwimSplit::query()
            ->select(
                'swimmer_name',
                DB::raw('COUNT(*) as total_laps'),
                DB::raw('COUNT(*) * 50 as total_distance_m'),
                DB::raw('ROUND(AVG(lap_time_ms)) as avg_lap_ms'),
                DB::raw('MIN(lap_time_ms) as best_lap_ms'),
                DB::raw('MAX(lap_time_ms) as worst_lap_ms'),
                DB::raw('SUM(lap_time_ms) as total_swim_time_ms'),
                DB::raw('COUNT(DISTINCT swim_session_id) as total_sessions'),
            )
            ->groupBy('swimmer_name')
            ->orderBy('swimmer_name')
            ->get()
            ->map(function ($row) {
                // Recent form: avg of last 10 laps
                $recentLaps = SwimSplit::where('swimmer_name', $row->swimmer_name)
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->pluck('lap_time_ms');

                $row->recent_avg_ms = $recentLaps->count() > 0 ? round($recentLaps->avg()) : null;

                // Best session (fastest avg)
                $row->best_session_avg_ms = SwimSplit::where('swimmer_name', $row->swimmer_name)
                    ->select('swim_session_id', DB::raw('ROUND(AVG(lap_time_ms)) as avg_ms'))
                    ->groupBy('swim_session_id')
                    ->orderBy('avg_ms')
                    ->value('avg_ms');

                return $row;
            });

        return response()->json($stats);
    }

    /**
     * Detailed log for a specific swimmer.
     */
    public function swimmerLog(Request $request, string $name): JsonResponse
    {
        $splits = SwimSplit::with('session:id,started_at,name,team_name')
            ->where('swimmer_name', $name)
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json($splits);
    }

    /**
     * Generate next team name for today.
     */
    public function nextTeamName(): JsonResponse
    {
        $today = now()->format('j M');
        $count = SwimSession::whereDate('started_at', today())
            ->where('team_name', 'like', "{$today} - Ploeg %")
            ->count();

        return response()->json([
            'team_name' => "{$today} - Ploeg " . ($count + 1),
            'number' => $count + 1,
        ]);
    }

    /**
     * Export a session as CSV.
     */
    public function exportSession(SwimSession $session)
    {
        $session->load('splits');
        $filename = str_replace(' ', '_', $session->team_name) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($session) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [$session->team_name, 'Datum: ' . $session->started_at->format('d-m-Y')]);
            fputcsv($out, []);

            $swimmers = $session->swimmers;
            fputcsv($out, array_merge(['Ronde'], $swimmers, ['Totale tijd']));

            $rounds = $session->splits->groupBy('round');
            foreach ($rounds as $round => $splits) {
                $row = [$round];
                foreach ($swimmers as $i => $name) {
                    $split = $splits->firstWhere('swimmer_index', $i);
                    $row[] = $split ? number_format($split->lap_time_ms / 1000, 2) : '';
                }
                $lastSplit = $splits->sortByDesc('total_time_ms')->first();
                $totalSec = $lastSplit ? $lastSplit->total_time_ms / 1000 : '';
                $row[] = $totalSec ? gmdate('i:s', (int) $totalSec) . '.' . str_pad(round(($totalSec - floor($totalSec)) * 100), 2, '0', STR_PAD_LEFT) : '';
                fputcsv($out, $row);
            }

            fputcsv($out, []);
            fputcsv($out, ['Statistiek', ...array_map(fn () => '', $swimmers)]);

            // Per-swimmer stats
            $gemRow = ['Gem.'];
            $minRow = ['Min'];
            $maxRow = ['Max'];
            $distRow = ['Afstand'];
            foreach ($swimmers as $i => $name) {
                $swimmerSplits = $session->splits->where('swimmer_index', $i);
                $times = $swimmerSplits->pluck('lap_time_ms');
                $gemRow[] = $times->count() ? number_format($times->avg() / 1000, 2) : '';
                $minRow[] = $times->count() ? number_format($times->min() / 1000, 2) : '';
                $maxRow[] = $times->count() ? number_format($times->max() / 1000, 2) : '';
                $distRow[] = ($times->count() * 50) . 'm';
            }
            fputcsv($out, $gemRow);
            fputcsv($out, $minRow);
            fputcsv($out, $maxRow);
            fputcsv($out, $distRow);

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export all swimmer stats as CSV.
     */
    public function exportSwimmers()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="zwemmers_overzicht.csv"',
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Zwemmer', 'Sessies', 'Banen', 'Afstand', 'Gem (s)', 'Snelste (s)', 'Langzaamst (s)', 'Recent gem (s)']);

            $stats = SwimSplit::query()
                ->select('swimmer_name',
                    DB::raw('COUNT(*) as total_laps'),
                    DB::raw('ROUND(AVG(lap_time_ms)) as avg_ms'),
                    DB::raw('MIN(lap_time_ms) as best_ms'),
                    DB::raw('MAX(lap_time_ms) as worst_ms'),
                    DB::raw('COUNT(DISTINCT swim_session_id) as sessions'))
                ->groupBy('swimmer_name')
                ->orderBy('swimmer_name')
                ->get();

            foreach ($stats as $s) {
                $recent = SwimSplit::where('swimmer_name', $s->swimmer_name)
                    ->orderByDesc('created_at')->limit(10)->pluck('lap_time_ms');

                fputcsv($out, [
                    $s->swimmer_name,
                    $s->sessions,
                    $s->total_laps,
                    ($s->total_laps * 50) . 'm',
                    number_format($s->avg_ms / 1000, 2),
                    number_format($s->best_ms / 1000, 2),
                    number_format($s->worst_ms / 1000, 2),
                    $recent->count() ? number_format($recent->avg() / 1000, 2) : '',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
