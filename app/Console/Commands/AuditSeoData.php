<?php

namespace App\Console\Commands;

use App\Services\Storefront\SeoTextNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditSeoData extends Command
{
    protected $signature = 'seo:audit-data {--dry-run : Scan only; this command never changes data}';

    protected $description = 'Report UTF-8, Mojibake and HTML issues in public SEO data without modifying records';

    public function handle(SeoTextNormalizer $normalizer): int
    {
        $findings = [];

        foreach ([
            'products' => ['name', 'description', 'meta_title', 'meta_description'],
            'posts' => ['title', 'summary', 'content', 'meta_title', 'meta_description'],
            'post_categories' => ['name', 'description'],
            'media_libraries' => ['alt_text', 'caption'],
        ] as $table => $columns) {
            $this->scanTable($table, $columns, $normalizer, $findings);
            $this->scanDuplicateMeta($table, $findings);
        }

        $this->scanEmptyCriticalContent($normalizer, $findings);

        if (Schema::hasTable('settings')) {
            DB::table('settings')->orderBy('key')->each(function (object $setting) use ($normalizer, &$findings): void {
                $isSeoText = in_array($setting->key, [
                    'site.name', 'site.tagline', 'site.description',
                    'seo.default_title', 'seo.default_description',
                ], true);
                $issues = $normalizer->issues($setting->value, $isSeoText);
                if ($issues !== []) {
                    $findings[] = [
                        $this->severity($issues),
                        'settings',
                        (string) $setting->key,
                        'value',
                        implode(', ', $issues),
                        $this->preview((string) $setting->value),
                        $this->preview($normalizer->normalize((string) $setting->value)),
                    ];
                }
            });

            $this->scanEmptySiteSettings($normalizer, $findings);
        }

        if ($findings === []) {
            $this->info('SEO data audit passed: no UTF-8, Mojibake, control-character, or META HTML issues found.');

            return self::SUCCESS;
        }

        $this->warn('SEO data audit found '.count($findings).' record(s). No data was changed.');
        $this->table(['severity', 'table', 'id/key', 'column', 'issue', 'current value', 'suggested plain text'], $findings);

        return collect($findings)->contains(fn (array $finding) => $finding[0] === 'ERROR')
            ? self::FAILURE
            : self::SUCCESS;
    }

    /**
     * @param  list<string>  $columns
     * @param  list<array<int, string>>  $findings
     */
    private function scanTable(string $table, array $columns, SeoTextNormalizer $normalizer, array &$findings): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $available = array_values(array_intersect($columns, Schema::getColumnListing($table)));
        if ($available === []) {
            return;
        }

        DB::table($table)
            ->select(['id', ...$available])
            ->orderBy('id')
            ->eachById(function (object $record) use ($table, $available, $normalizer, &$findings): void {
                foreach ($available as $column) {
                    $value = (string) ($record->{$column} ?? '');
                    $issues = $normalizer->issues($value, in_array($column, ['meta_title', 'meta_description'], true));
                    if ($issues === []) {
                        continue;
                    }

                    $findings[] = [
                        $this->severity($issues),
                        $table,
                        (string) $record->id,
                        $column,
                        implode(', ', $issues),
                        $this->preview($value),
                        $this->preview($normalizer->normalize($value)),
                    ];
                }
            });
    }

    private function preview(string $value): string
    {
        return mb_strimwidth($value, 0, 100, '…');
    }

    /**
     * @param  list<array<int, string>>  $findings
     */
    private function scanDuplicateMeta(string $table, array &$findings): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $available = Schema::getColumnListing($table);
        foreach (['meta_title', 'meta_description'] as $column) {
            if (! in_array($column, $available, true)) {
                continue;
            }

            DB::table($table)
                ->select([$column, DB::raw('COUNT(*) as duplicate_count')])
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->groupBy($column)
                ->having('duplicate_count', '>', 1)
                ->get()
                ->each(function (object $duplicate) use ($table, $column, &$findings): void {
                    $findings[] = [
                        'WARNING',
                        $table,
                        'multiple',
                        $column,
                        'duplicate-meta ('.$duplicate->duplicate_count.' records)',
                        $this->preview((string) $duplicate->{$column}),
                        'Review and make each indexed URL unique.',
                    ];
                });
        }
    }

    /**
     * @param  list<array<int, string>>  $findings
     */
    private function scanEmptyCriticalContent(SeoTextNormalizer $normalizer, array &$findings): void
    {
        if (Schema::hasTable('products')) {
            DB::table('products')
                ->select(['id', 'name', 'description', 'meta_description'])
                ->where('status', 'active')
                ->orderBy('id')
                ->eachById(function (object $product) use ($normalizer, &$findings): void {
                    if ($normalizer->normalize($product->name) === '') {
                        $findings[] = ['ERROR', 'products', (string) $product->id, 'name', 'empty-critical-seo', '', 'Add a public product name.'];
                    }

                    if ($normalizer->normalize($product->description) === '' && $normalizer->normalize($product->meta_description) === '') {
                        $findings[] = ['WARNING', 'products', (string) $product->id, 'description', 'empty-critical-seo', '', 'Add a product description or a dedicated META description.'];
                    }
                });
        }

        if (Schema::hasTable('posts')) {
            DB::table('posts')
                ->select(['id', 'title', 'summary', 'content', 'meta_description'])
                ->where('status', 'published')
                ->orderBy('id')
                ->eachById(function (object $post) use ($normalizer, &$findings): void {
                    if ($normalizer->normalize($post->title) === '') {
                        $findings[] = ['ERROR', 'posts', (string) $post->id, 'title', 'empty-critical-seo', '', 'Add a public article title.'];
                    }

                    if (
                        $normalizer->normalize($post->summary) === ''
                        && $normalizer->normalize($post->content) === ''
                        && $normalizer->normalize($post->meta_description) === ''
                    ) {
                        $findings[] = ['WARNING', 'posts', (string) $post->id, 'content', 'empty-critical-seo', '', 'Add article content, summary, or a dedicated META description.'];
                    }
                });
        }
    }

    /**
     * Report explicitly configured but empty site-wide fields. A missing setting
     * is not an error because fresh installations use the application defaults.
     *
     * @param  list<array<int, string>>  $findings
     */
    private function scanEmptySiteSettings(SeoTextNormalizer $normalizer, array &$findings): void
    {
        $settings = DB::table('settings')
            ->whereIn('key', ['site.name', 'site.description'])
            ->pluck('value', 'key');

        foreach (['site.name' => 'ERROR', 'site.description' => 'WARNING'] as $key => $severity) {
            if (! $settings->has($key) || $normalizer->normalize($settings->get($key)) !== '') {
                continue;
            }

            $findings[] = [
                $severity,
                'settings',
                $key,
                'value',
                'empty-critical-seo',
                '',
                $key === 'site.name' ? 'Set a public site name.' : 'Set a site description.',
            ];
        }
    }

    /** @param list<string> $issues */
    private function severity(array $issues): string
    {
        return array_intersect($issues, ['invalid-utf8', 'replacement-character', 'control-character']) !== []
            ? 'ERROR'
            : 'WARNING';
    }
}
