<?php
namespace App\Services;
use Illuminate\Support\Collection;

class TraceabilityService
{
    public function traceByBatch(string $searchTerm, ?string $date = null): Collection
    {
        $tokens = $this->tokenize($searchTerm);
        return collect(config('traceability.modules'))
            ->map(function (array $cfg, string $key) use ($tokens, $date) {
                $matched = $this->matchDetails($cfg, $tokens, $date);
                if ($matched->isEmpty()) {
                    return null;
                }
                $routeKey = $cfg['route_key_column'] ?? 'uuid';
                $forms = $matched
                    ->groupBy(fn($d) => data_get($d, "{$cfg['form_relation']}.{$routeKey}"))
                    ->map(function ($group) use ($cfg, $routeKey) {
                        $form = data_get($group->first(), $cfg['form_relation']);
                        return [
                            'form_key' => $form->{$routeKey},
                            'date' => $form->date,
                            'shift' => $form->shift ?? null,
                            'pdf_url' => $cfg['pdf_route']
                                ? route(
                                    $cfg['pdf_route'],
                                    isset($cfg['pdf_route_params'])
                                    ? $cfg['pdf_route_params']($form, $group)
                                    : $form->{$routeKey}
                                )
                                : null,
                            'match_count' => $group->count(),
                        ];
                    })
                    ->sortByDesc('date')
                    ->values();
                return ['module' => $key, 'label' => $cfg['label'], 'forms' => $forms];
            })
            ->filter()
            ->values();
    }

    public function getFormDetails(
        string $moduleKey,
        string $formKey,
        string $searchTerm,
        ?string $date = null
    ): array {
        $cfg = config("traceability.modules.{$moduleKey}");
        abort_if(!$cfg, 404, 'Modul tidak ditemukan.');
        $tokens = $this->tokenize($searchTerm);
        $routeKey = $cfg['route_key_column'] ?? 'uuid';
        $matched = $this->matchDetails($cfg, $tokens, $date)
            ->filter(fn($d) => (string) data_get($d, "{$cfg['form_relation']}.{$routeKey}") === (string) $formKey);

        if ($matched->isEmpty()) {
            return ['details' => []];
        }

        $keyName = (new $cfg['model'])->getKeyName();
        $ids = $matched->pluck($keyName);
        $with = array_merge(
            collect($cfg['detail_with'] ?? [])->values()->all(),
            collect($cfg['related'] ?? [])->map(fn($r) => $r['relation'])->values()->all()
        );
        $rows = $cfg['model']::with($with)
            ->whereIn($keyName, $ids)
            ->get();

        if (!empty($cfg['partial'])) {
            $form = data_get($rows->first(), $cfg['form_relation']);

            return [
                'mode' => 'html',
                'html' => view($cfg['partial'], [
                    'details' => $rows,
                    'report'  => $form,
                ])->render(),
            ];
        }

        // Mode tabel (jika modul mendefinisikan table_columns)
        if (!empty($cfg['table_columns'])) {
            $form = data_get($rows->first(), $cfg['form_relation']);

            return [
                'mode'    => 'table',
                'columns' => $cfg['table_columns'],
                'rows'    => $rows->map(fn($d) => $cfg['table_row']($d))->values(),
                'notes'   => isset($cfg['form_notes']) && $form ? ($cfg['form_notes'])($form) : null,
            ];
        }

        // Mode lama (field list) untuk modul lain
        $details = $rows->map(function ($detail) use ($cfg) {
            $related = collect($cfg['related'] ?? [])
                ->map(function ($rcfg) use ($detail) {
                    $rel = $detail->{$rcfg['relation']};
                    if (!$rel) return null;
                    return ['label' => $rcfg['label'], 'fields' => $rcfg['display_fields']($rel)];
                })
                ->filter()->values();

            return ['fields' => $cfg['display_fields']($detail), 'related' => $related];
        })->values();

        return ['mode' => 'fields', 'details' => $details];
    }
    protected function matchDetails(
        array $cfg,
        Collection $tokens,
        ?string $date = null
    ): Collection {
        $ownColumns = $cfg['search_columns'] ?? array_filter([$cfg['column'] ?? null]);
        $query = $cfg['model']::query();
        foreach ($tokens as $token) {
            $query->where(function ($q) use ($token, $cfg, $ownColumns) {
                $matched = false;
                foreach ($ownColumns as $col) {
                    $q->orWhere($col, 'like', "%{$token}%");
                    $matched = true;
                }
                foreach ($cfg['search_relations'] ?? [] as $relation => $col) {
                    $q->orWhereHas($relation, fn($rq) => $rq->where($col, 'like', "%{$token}%"));

                    $matched = true;
                }
                if (!$matched) {
                    $q->whereRaw('1 = 0'); // modul salah konfigurasi
                }
            });
        }
        $query->whereHas($cfg['form_relation'], function ($q) use ($date) {
            $q->where(fn($w) => $w->where('is_audit', 0)->orWhereNull('is_audit'));

            if ($date) {
                $q->whereDate('date', $date);
            }
        });
        $with = array_merge(
            [$cfg['form_relation']],
            collect($cfg['search_relations'] ?? [])->keys()->values()->all()
        );
        return $query->with($with)
            ->get()
            ->filter(function ($detail) use ($cfg, $tokens, $ownColumns) {
                $haystack = strtolower(collect($ownColumns)
                    ->map(fn($col) => $detail->{$col} ?? null)
                    ->merge(collect($cfg['search_relations'] ?? [])
                        ->map(fn($col, $relation) => data_get($detail, "{$relation}.{$col}")))

                    ->filter()
                    ->implode(' '));
                return $tokens->every(fn($t) => str_contains($haystack, strtolower($t)));
            })
            ->values();
    }
    protected function tokenize(string $term): Collection
    {
        return collect(preg_split('/\s+/', trim($term)))->filter()->values();
    }
}