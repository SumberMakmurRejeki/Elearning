<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * @return array<int, string>
     */
    public static function monthOptions(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    /**
     * @return array{
     *     totalEmployees: int,
     *     totalTrainings: int,
     *     activeTrainings: int,
     *     completedTrainings: int,
     *     averagePostTest: string,
     *     passedEmployees: int,
     *     failedEmployees: int,
     *     summaryCards: array<int, array<string, string>>,
     *     progressChart: array<int, array<string, int|string>>,
     *     scoreChart: array<int, array<string, int|string|null>>,
     *     passChart: array<int, array<string, int|string>>,
     *     monthOptions: array<int, string>,
     *     yearOptions: array<int, string>,
     *     trainingOptions: array<int, string>,
     *     hasEmptyState: bool,
     * }
     */
    public function build(int $month, int $year, ?int $trainingId): array
    {
        $today = CarbonImmutable::today();
        $allTrainings = $this->allTrainings();
        $selectedTrainings = $this->selectedTrainings($allTrainings, $month, $year, $trainingId);
        $selectedTrainingIds = $selectedTrainings->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all();
        $trainingOptions = $allTrainings->mapWithKeys(static fn (object $training): array => [(int) $training->id => (string) $training->title])->all();

        return [
            'totalEmployees' => $this->totalEmployees(),
            'totalTrainings' => $selectedTrainings->count(),
            'activeTrainings' => $this->countActiveTrainings($selectedTrainings, $today),
            'completedTrainings' => $this->countCompletedTrainings($selectedTrainings, $today),
            'averagePostTest' => $this->formatAverage($this->averagePostTestScore($selectedTrainingIds)),
            'passedEmployees' => $this->countDistinctProgressByStatus($selectedTrainingIds, 'passed'),
            'failedEmployees' => $this->countDistinctProgressByStatus($selectedTrainingIds, 'failed'),
            'summaryCards' => $this->summaryCards($selectedTrainings, $today, $selectedTrainingIds),
            'progressChart' => $this->progressChart($selectedTrainingIds),
            'scoreChart' => $this->scoreChart($selectedTrainings, $selectedTrainingIds),
            'passChart' => $this->passChart($selectedTrainingIds),
            'monthOptions' => self::monthOptions(),
            'yearOptions' => $this->yearOptions($allTrainings, $year),
            'trainingOptions' => $trainingOptions,
            'hasEmptyState' => $selectedTrainings->isEmpty(),
        ];
    }

    /**
     * @return Collection<int, object>
     */
    private function allTrainings(): Collection
    {
        return DB::table('trainings')
            ->select(['id', 'title', 'start_date', 'end_date', 'status'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param Collection<int, object> $allTrainings
     * @return Collection<int, object>
     */
    private function selectedTrainings(Collection $allTrainings, int $month, int $year, ?int $trainingId): Collection
    {
        return $allTrainings
            ->filter(static function (object $training) use ($month, $year, $trainingId): bool {
                if ($trainingId !== null && (int) $training->id !== $trainingId) {
                    return false;
                }

                $startDate = CarbonImmutable::parse((string) $training->start_date);

                return (int) $startDate->format('n') === $month && (int) $startDate->format('Y') === $year;
            })
            ->values();
    }

    /**
     * @param Collection<int, object> $allTrainings
     * @return array<int, string>
     */
    private function yearOptions(Collection $allTrainings, int $fallbackYear): array
    {
        $years = $allTrainings
            ->map(static fn (object $training): int => (int) CarbonImmutable::parse((string) $training->start_date)->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        if ($years->isEmpty()) {
            return [
                $fallbackYear => (string) $fallbackYear,
            ];
        }

        return $years->mapWithKeys(static fn (int $year): array => [$year => (string) $year])->all();
    }

    private function totalEmployees(): int
    {
        return (int) DB::table('employees')->where('is_active', true)->count();
    }

    /**
     * @param Collection<int, object> $selectedTrainings
     * @param array<int, int> $selectedTrainingIds
     * @return array<int, array<string, string>>
     */
    private function summaryCards(Collection $selectedTrainings, CarbonImmutable $today, array $selectedTrainingIds): array
    {
        return [
            [
                'key' => 'total-employees',
                'label' => 'Total Karyawan',
                'value' => (string) $this->totalEmployees(),
                'description' => 'Karyawan aktif perusahaan',
            ],
            [
                'key' => 'total-trainings',
                'label' => 'Total Training',
                'value' => (string) $selectedTrainings->count(),
                'description' => 'Training yang masuk ke filter',
            ],
            [
                'key' => 'active-trainings',
                'label' => 'Training Aktif',
                'value' => (string) $this->countActiveTrainings($selectedTrainings, $today),
                'description' => 'Sedang berjalan saat ini',
            ],
            [
                'key' => 'completed-trainings',
                'label' => 'Training Selesai',
                'value' => (string) $this->countCompletedTrainings($selectedTrainings, $today),
                'description' => 'Sudah melewati tanggal akhir',
            ],
            [
                'key' => 'average-post-test',
                'label' => 'Rata-rata Nilai Post-Test',
                'value' => $this->formatAverage($this->averagePostTestScore($selectedTrainingIds)),
                'description' => 'Nilai akhir post-test pada filter aktif',
            ],
            [
                'key' => 'passed-employees',
                'label' => 'Jumlah Karyawan Lulus',
                'value' => (string) $this->countDistinctProgressByStatus($selectedTrainingIds, 'passed'),
                'description' => 'Status kelulusan berhasil',
            ],
            [
                'key' => 'failed-employees',
                'label' => 'Jumlah Karyawan Tidak Lulus',
                'value' => (string) $this->countDistinctProgressByStatus($selectedTrainingIds, 'failed'),
                'description' => 'Status kelulusan belum tercapai',
            ],
        ];
    }

    /**
     * @param Collection<int, object> $selectedTrainings
     */
    private function countActiveTrainings(Collection $selectedTrainings, CarbonImmutable $today): int
    {
        return $selectedTrainings->filter(static function (object $training) use ($today): bool {
            if ((string) $training->status !== 'published') {
                return false;
            }

            $startDate = CarbonImmutable::parse((string) $training->start_date)->startOfDay();
            $endDate = CarbonImmutable::parse((string) $training->end_date)->endOfDay();

            return $startDate->lessThanOrEqualTo($today) && $endDate->greaterThanOrEqualTo($today);
        })->count();
    }

    /**
     * @param Collection<int, object> $selectedTrainings
     */
    private function countCompletedTrainings(Collection $selectedTrainings, CarbonImmutable $today): int
    {
        return $selectedTrainings->filter(static function (object $training) use ($today): bool {
            if ((string) $training->status === 'archived') {
                return true;
            }

            $endDate = CarbonImmutable::parse((string) $training->end_date)->endOfDay();

            return $endDate->lessThan($today);
        })->count();
    }

    /**
     * @param array<int, int> $selectedTrainingIds
     */
    private function averagePostTestScore(array $selectedTrainingIds): ?float
    {
        if ($selectedTrainingIds === []) {
            return null;
        }

        $average = DB::table('test_attempts')
            ->whereIn('training_id', $selectedTrainingIds)
            ->where('test_type', 'post_test')
            ->whereNotNull('final_score')
            ->avg('final_score');

        return $average === null ? null : (float) $average;
    }

    /**
     * @param array<int, int> $selectedTrainingIds
     */
    private function countDistinctProgressByStatus(array $selectedTrainingIds, string $status): int
    {
        if ($selectedTrainingIds === []) {
            return 0;
        }

        return (int) DB::table('employee_training_progress')
            ->whereIn('training_id', $selectedTrainingIds)
            ->where('final_status', $status)
            ->distinct()
            ->count('employee_id');
    }

    /**
     * @param array<int, int> $selectedTrainingIds
     * @return array<int, array<string, int|string>>
     */
    private function progressChart(array $selectedTrainingIds): array
    {
        $labels = [
            'not_started' => 'Belum Mulai',
            'in_progress' => 'Berjalan',
            'material_completed' => 'Materi Selesai',
            'completed' => 'Selesai',
        ];

        $counts = $this->progressStatusCounts($selectedTrainingIds);
        $max = max(1, ...array_values($counts));

        return array_map(
            static function (string $status, string $label) use ($counts, $max): array {
                $value = (int) ($counts[$status] ?? 0);

                return [
                    'key' => $status,
                    'label' => $label,
                    'value' => $value,
                    'percent' => $max === 0 ? 0 : (int) round(($value / $max) * 100),
                ];
            },
            array_keys($labels),
            array_values($labels)
        );
    }

    /**
     * @param array<int, int> $selectedTrainingIds
     * @return array<string, int>
     */
    private function progressStatusCounts(array $selectedTrainingIds): array
    {
        if ($selectedTrainingIds === []) {
            return [
                'not_started' => 0,
                'in_progress' => 0,
                'material_completed' => 0,
                'completed' => 0,
            ];
        }

        $counts = DB::table('employee_training_progress')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->whereIn('training_id', $selectedTrainingIds)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return [
            'not_started' => (int) ($counts['not_started'] ?? 0),
            'in_progress' => (int) ($counts['in_progress'] ?? 0),
            'material_completed' => (int) ($counts['material_completed'] ?? 0),
            'completed' => (int) ($counts['completed'] ?? 0),
        ];
    }

    /**
     * @param Collection<int, object> $selectedTrainings
     * @param array<int, int> $selectedTrainingIds
     * @return array<int, array<string, int|string|null>>
     */
    private function scoreChart(Collection $selectedTrainings, array $selectedTrainingIds): array
    {
        if ($selectedTrainings->isEmpty()) {
            return [];
        }

        $rows = DB::table('test_attempts')
            ->select('training_id', 'test_type', DB::raw('AVG(final_score) as average_score'))
            ->whereIn('training_id', $selectedTrainingIds)
            ->whereIn('test_type', ['pre_test', 'post_test'])
            ->whereNotNull('final_score')
            ->groupBy('training_id', 'test_type')
            ->get();

        $scores = [];

        foreach ($rows as $row) {
            $trainingId = (int) $row->training_id;
            $scores[$trainingId][$row->test_type] = (float) $row->average_score;
        }

        return $selectedTrainings
            ->map(static function (object $training) use ($scores): array {
                $trainingId = (int) $training->id;
                $preScore = $scores[$trainingId]['pre_test'] ?? null;
                $postScore = $scores[$trainingId]['post_test'] ?? null;

                return [
                    'key' => 'training-'.$trainingId,
                    'label' => (string) $training->title,
                    'preScore' => $preScore,
                    'postScore' => $postScore,
                    'prePercent' => $preScore === null ? 0 : (int) round(min(100, $preScore)),
                    'postPercent' => $postScore === null ? 0 : (int) round(min(100, $postScore)),
                ];
            })
            ->all();
    }

    /**
     * @param array<int, int> $selectedTrainingIds
     * @return array<int, array<string, int|string>>
     */
    private function passChart(array $selectedTrainingIds): array
    {
        $passed = $this->countDistinctProgressByStatus($selectedTrainingIds, 'passed');
        $failed = $this->countDistinctProgressByStatus($selectedTrainingIds, 'failed');
        $total = max(1, $passed + $failed);

        return [
            [
                'key' => 'passed',
                'label' => 'Lulus',
                'value' => $passed,
                'percent' => (int) round(($passed / $total) * 100),
            ],
            [
                'key' => 'failed',
                'label' => 'Tidak Lulus',
                'value' => $failed,
                'percent' => (int) round(($failed / $total) * 100),
            ],
        ];
    }

    private function formatAverage(?float $score): string
    {
        if ($score === null) {
            return '—';
        }

        $formatted = number_format($score, 1, '.', '');

        return str_ends_with($formatted, '.0') ? substr($formatted, 0, -2) : $formatted;
    }
}
