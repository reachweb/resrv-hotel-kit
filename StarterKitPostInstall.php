<?php

use Statamic\Facades\YAML;

/**
 * Resrv Hotel — starter kit post-install hook.
 *
 * Lives in package/ because the v6 Exporter copies package/ contents to the
 * export root, which is where the installer's Hook::find() looks for it.
 *
 * Non-namespaced on purpose: Statamic's Hook::find() requires this file and
 * instantiates the bare class name, then calls handle($console) with only the
 * installer's console command. Module selections are NOT readable here (they
 * live on the Installer's protected $modules), so choices are read from the
 * files they installed instead: the demo seeder file means demo content, a
 * resources/starter-kit/demo-data.yaml marker means "fill Resrv with demo
 * data", the spa and dining page files mean those venues, the contact form
 * file means the contact_form module, and the payments option exports a marker
 * under resources/starter-kit/payments/.
 */
class StarterKitPostInstall
{
    /**
     * Demo pages whose module can be declined (spa, restaurant), keyed by entry id,
     * with the URL the rest of the demo content links them by.
     *
     * @var array<string, string>
     */
    private const OPTIONAL_PAGES = [
        'spa' => '/spa',
        'dining' => '/dining',
    ];

    /**
     * The home tiles section's heading names both venues. When only one of them
     * was installed, it takes that venue's heading instead.
     *
     * @var array<string, string>
     */
    private const SINGLE_VENUE_HEADINGS = [
        'spa' => 'An hour at the spa',
        'dining' => 'Dinner at La Marea',
    ];

    private const BOTH_VENUES_HEADING = 'La Marea and the spa';

    public function handle($console)
    {
        // Content edits first, so nothing below indexes the content before them.
        if ($this->addBlankHomePage(base_path())) {
            $console->info('No demo content: added a blank home page, so checkout and the other booking pages keep their own URLs.');
        }

        if ($trimmed = $this->trimMissingVenues(base_path())) {
            $console->info('Removed the links to the spa or restaurant pages you left out from: '.implode(', ', $trimmed).'.');
        }

        // Resrv's tables — `php artisan resrv:install` does NOT run migrations.
        // These MUST run in a subprocess: the installer's PHP process booted
        // before composer required the addon, so its autoloader/providers can't
        // see Resrv's migrations or models ($console->call() silently runs the
        // app-only migrations and the seeder fatals on the missing model class).
        $migrated = $this->artisan('migrate --force', $console);
        $seeded = false;

        if ($migrated && $this->demoContentSelected()) {
            if ($this->demoDataSelected()) {
                $seeded = $this->artisan('db:seed --class="Database\\Seeders\\ResrvDemoSeeder" --force', $console);

                if ($seeded) {
                    $console->info('Resrv Hotel demo data seeded (rolling 12-month availability — re-run the seeder any time to refresh it).');
                }
            } else {
                // The seeder is what registers the demo entries with Resrv. Without it,
                // the control panel can't manage their availability, rates or extras.
                $this->artisan('resrv:import-entries', $console);
            }
        }

        $gateway = $this->selectedPaymentGateway();
        $this->applyPaymentGateway($gateway, $console);

        $console->newLine();
        $console->line('  <info>✓ Resrv Hotel installed.</info>');
        $console->line('  → Run `npm install && npm run build` (Tailwind v4 + Vite) to recompile the front-end after changes.');
        $console->line('  → Add real photography per IMAGES.md — every image is a named slot that degrades gracefully until then.');
        $console->line('  → The booking calendar is themed via CSS variables — see CALENDAR-THEMING.md.');

        if ($gateway === 'stripe' || $gateway === 'both') {
            $console->line("  → Payments: you chose [{$gateway}] — add your RESRV_STRIPE_* keys and follow STRIPE.md to finish gateway setup.");
        } else {
            $console->line('  → Payments: offline gateway is active (no keys needed). To take card payments later, see STRIPE.md.');
        }

        if (! $this->contactFormSelected()) {
            $console->line('  → No contact form (Statamic Core includes one form, and Resrv\'s checkout uses it). On Pro, add a form any time and pick it in the Map + contact block.');
        }

        if ($seeded) {
            $console->line('  → Demo reservations for the CP reports/abandoned-recovery demo: set RESRV_SEED_DEMO_RESERVATIONS=true and re-seed.');
        } elseif ($this->demoContentSelected() && ! $this->demoDataSelected()) {
            $console->line('  → Resrv is empty: add availability and rates in the CP, or fill it with the demo data any time (see DEMO-DATA.md).');
        }

        $this->cleanUpMarkers();
    }

    /**
     * Run an artisan command in a fresh subprocess (whose autoloader includes the
     * dependencies composer just installed). Returns true on exit code 0.
     */
    private function artisan(string $command, $console): bool
    {
        $cmd = escapeshellarg(PHP_BINARY).' '.escapeshellarg(base_path('artisan')).' '.$command.' --no-interaction 2>&1';

        $console->line("  Running: php artisan {$command}");
        passthru($cmd, $exitCode);

        if ($exitCode !== 0) {
            $console->warn("  [php artisan {$command}] failed (exit {$exitCode}) — run it by hand after the install.");
        }

        return $exitCode === 0;
    }

    /**
     * The ResrvDemoSeeder ships only with the demo_content module, so its presence
     * is the module-selection marker (file check, not class_exists, so we do not
     * depend on the freshly-installed app's autoloader state).
     */
    private function demoContentSelected(): bool
    {
        return file_exists(base_path('database/seeders/ResrvDemoSeeder.php'));
    }

    /**
     * The resrv_data module (nested in demo_content) exports only this marker.
     */
    private function demoDataSelected(): bool
    {
        return file_exists(base_path('resources/starter-kit/demo-data.yaml'));
    }

    /**
     * The contact_form module exports the contact form's config.
     */
    private function contactFormSelected(): bool
    {
        return file_exists(base_path('resources/forms/contact.yaml'));
    }

    /**
     * Without the demo content, the install has the booking pages (checkout,
     * checkout-complete, manage-booking, pay) but no home page and no page tree.
     * The pages collection expects a root, and Statamic puts pages missing from
     * the tree at its end, so the first booking page would take "/". This adds
     * a blank home page and a tree that starts with it. Returns true when it did.
     */
    public function addBlankHomePage(string $root): bool
    {
        $pagesDir = "{$root}/content/collections/pages";
        $treePath = "{$root}/content/trees/collections/pages.yaml";

        if (! is_dir($pagesDir) || file_exists("{$pagesDir}/home.md") || file_exists($treePath)) {
            return false;
        }

        $pageIds = array_map(
            fn (string $path) => YAML::file($path)->parse()['id'] ?? basename($path, '.md'),
            glob("{$pagesDir}/*.md") ?: [],
        );
        sort($pageIds);

        file_put_contents("{$pagesDir}/home.md", YAML::dumpFrontMatter([
            'id' => 'home',
            'blueprint' => 'page',
            'title' => 'Home',
            'template' => 'home',
        ]));

        if (! is_dir(dirname($treePath))) {
            mkdir(dirname($treePath), 0755, true);
        }

        file_put_contents($treePath, YAML::dump([
            'tree' => array_map(fn (string $id) => ['entry' => $id], ['home', ...$pageIds]),
        ]));

        return true;
    }

    /**
     * Remove what the demo content links to a declined venue's page: home tiles,
     * buttons, and nav and page-tree items. Without it, a hotel-only install would
     * keep tiles and buttons that lead to a 404. The content stays as the control
     * panel would save it, so the page builder shows no tiles the site never renders.
     * Returns the paths it changed, relative to the content folder.
     *
     * @return list<string>
     */
    public function trimMissingVenues(string $root): array
    {
        $pagesDir = "{$root}/content/collections/pages";

        if (! file_exists("{$pagesDir}/home.md")) {
            return [];
        }

        $missing = array_filter(
            self::OPTIONAL_PAGES,
            fn (string $url, string $id) => ! file_exists("{$pagesDir}/{$id}.md"),
            ARRAY_FILTER_USE_BOTH,
        );

        if ($missing === []) {
            return [];
        }

        $changed = [];

        foreach (glob("{$pagesDir}/*.md") ?: [] as $path) {
            $data = YAML::file($path)->parse();
            $content = $data['content'] ?? null;
            unset($data['content']);

            $builder = $this->trimBuilder($data['builder'] ?? [], $missing);

            if ($builder !== ($data['builder'] ?? [])) {
                $data['builder'] = $builder;
                file_put_contents($path, YAML::dumpFrontMatter($data, $content));
                $changed[] = 'collections/pages/'.basename($path);
            }
        }

        $trees = array_merge(
            ["{$root}/content/trees/collections/pages.yaml"],
            glob("{$root}/content/trees/navigation/*.yaml") ?: [],
        );

        foreach ($trees as $path) {
            if (! file_exists($path)) {
                continue;
            }

            $data = YAML::file($path)->parse();
            $tree = $this->trimTree($data['tree'] ?? [], $missing);

            if ($tree !== ($data['tree'] ?? [])) {
                $data['tree'] = $tree;
                file_put_contents($path, YAML::dump($data));
                $changed[] = 'trees/'.basename(dirname($path)).'/'.basename($path);
            }
        }

        return $changed;
    }

    /**
     * Page-builder sets: feature tiles lose the tiles that link to a missing page
     * (and the section goes when none is left); a button that links to one is dropped.
     *
     * @param  array<int, array<string, mixed>>  $sets
     * @param  array<string, string>  $missing
     * @return array<int, array<string, mixed>>
     */
    private function trimBuilder(array $sets, array $missing): array
    {
        $trimmed = [];

        foreach ($sets as $set) {
            if (($set['type'] ?? null) === 'feature_tiles' && isset($set['tiles'])) {
                $tiles = array_values(array_filter(
                    $set['tiles'],
                    fn (array $tile) => ! $this->linksToMissingPage($tile['link'] ?? null, $missing),
                ));

                if ($tiles === []) {
                    continue;
                }

                if (count($tiles) < count($set['tiles']) && ($set['heading'] ?? null) === self::BOTH_VENUES_HEADING) {
                    $remaining = array_diff_key(self::SINGLE_VENUE_HEADINGS, $missing);
                    $set['heading'] = count($remaining) === 1 ? reset($remaining) : $set['heading'];
                }

                $set['tiles'] = $tiles;
            }

            if ($this->linksToMissingPage($set['cta_url'] ?? null, $missing)) {
                unset($set['cta_label'], $set['cta_url']);
            }

            $trimmed[] = $set;
        }

        return $trimmed;
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @param  array<string, string>  $missing
     * @return array<int, array<string, mixed>>
     */
    private function trimTree(array $nodes, array $missing): array
    {
        $trimmed = [];

        foreach ($nodes as $node) {
            if (isset($missing[$node['entry'] ?? '']) || $this->linksToMissingPage($node['url'] ?? null, $missing)) {
                continue;
            }

            if (isset($node['children'])) {
                $node['children'] = $this->trimTree($node['children'], $missing);
            }

            $trimmed[] = $node;
        }

        return $trimmed;
    }

    /**
     * True for a missing page's URL itself or anything under it (/spa, /spa#treatments).
     *
     * @param  array<string, string>  $missing
     */
    private function linksToMissingPage(mixed $url, array $missing): bool
    {
        if (! is_string($url)) {
            return false;
        }

        foreach ($missing as $pageUrl) {
            if ($url === $pageUrl || preg_match('#^'.preg_quote($pageUrl, '#').'[/?\#]#', $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Each payments option exports exactly one marker file named after the choice.
     * Returns 'offline', 'stripe' or 'both' (offline assumed if nothing was found —
     * the shipped resrv-config.php defaults to the offline gateway either way).
     */
    private function selectedPaymentGateway(): string
    {
        foreach (['stripe', 'both', 'offline'] as $gateway) {
            if (file_exists(base_path("resources/starter-kit/payments/{$gateway}.yaml"))) {
                return $gateway;
            }
        }

        return 'offline';
    }

    /**
     * Apply the chosen gateway to config/resrv-config.php. The kit ships the file
     * with the offline gateway active (works keyless), so 'offline' is a no-op.
     * 'stripe' swaps the single gateway; 'both' populates the payment_gateways
     * array (first entry is the checkout default), which surfaces Resrv's gateway
     * picker. Plain string replacement against the exact shipped lines — if a
     * future edit changes them, we warn instead of guessing.
     */
    private function applyPaymentGateway(string $gateway, $console): void
    {
        if ($gateway === 'offline') {
            return;
        }

        $this->writeStripeEnvPlaceholders();

        $configPath = base_path('config/resrv-config.php');
        $contents = file_get_contents($configPath);

        if ($gateway === 'stripe') {
            $search = "'payment_gateway' => OfflinePaymentGateway::class,";
            $replace = "'payment_gateway' => \\Reach\\StatamicResrv\\Http\\Payment\\StripePaymentGateway::class,";
        } else { // both
            $search = "'payment_gateways' => [],";
            $replace = <<<'PHP'
'payment_gateways' => [
        'stripe' => [
            'class' => \Reach\StatamicResrv\Http\Payment\StripePaymentGateway::class,
            'label' => 'Credit Card',
        ],
        'offline' => [
            'class' => \Reach\StatamicResrv\Http\Payment\OfflinePaymentGateway::class,
            'label' => 'Bank Transfer / Pay at Property',
        ],
    ],
PHP;
        }

        if (substr_count($contents, $search) === 1) {
            file_put_contents($configPath, str_replace($search, $replace, $contents));
            $console->info("Payment gateway configured for [{$gateway}] in config/resrv-config.php.");
        } else {
            $console->warn("Could not update config/resrv-config.php automatically — set the [{$gateway}] gateway by hand (see STRIPE.md).");
        }

        if ($gateway === 'stripe') {
            $this->removeNoCardNote($console);
        }
    }

    /**
     * A Stripe-only checkout takes a card, so the booking global's room-panel note
     * ("No card needed to reserve flexible rates") would be untrue. Drop it; the
     * room page hides the note when the field is empty. 'both' keeps it, since
     * guests can still choose the offline gateway.
     */
    private function removeNoCardNote($console): void
    {
        $path = base_path('content/globals/default/booking.yaml');

        if (! file_exists($path)) {
            return;
        }

        $updated = preg_replace('/^room_panel_note:.*\R/m', '', file_get_contents($path), 1, $count);

        if ($count === 1) {
            file_put_contents($path, $updated);
            $console->info('Removed the "no card needed" room-panel note (Stripe takes a card at checkout).');
        }
    }

    /**
     * Append the RESRV_STRIPE_* placeholders to .env / .env.example (idempotent).
     */
    private function writeStripeEnvPlaceholders(): void
    {
        $block = "\n# Statamic Resrv — Stripe gateway keys (see STRIPE.md)\n"
            ."RESRV_STRIPE_SECRET=\n"
            ."RESRV_STRIPE_PUBLISHABLE=\n"
            ."RESRV_STRIPE_WEBHOOK_SECRET=\n";

        foreach (['.env', '.env.example'] as $file) {
            $path = base_path($file);

            if (file_exists($path) && ! str_contains(file_get_contents($path), 'RESRV_STRIPE_SECRET')) {
                file_put_contents($path, $block, FILE_APPEND);
            }
        }
    }

    /**
     * The marker directory is install-time plumbing — remove it from the new site.
     */
    private function cleanUpMarkers(): void
    {
        $dir = base_path('resources/starter-kit');

        if (! is_dir($dir)) {
            return;
        }

        foreach (glob("{$dir}/payments/*.yaml") ?: [] as $file) {
            @unlink($file);
        }

        @unlink("{$dir}/demo-data.yaml");

        @rmdir("{$dir}/payments");
        @rmdir($dir);
    }
}
