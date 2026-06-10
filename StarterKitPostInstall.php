<?php

/**
 * Resrv Hotel — starter kit post-install hook.
 *
 * Lives in package/ because the v6 Exporter copies package/ contents to the
 * export root, which is where the installer's Hook::find() looks for it.
 *
 * Non-namespaced on purpose: Statamic's Hook::find() requires this file and
 * instantiates the bare class name, then calls handle($console) with only the
 * installer's console command. Module selections are NOT readable here (they
 * live on the Installer's protected $modules), so choices are read from
 * markers instead: the demo seeder file gates seeding, and the payments
 * option exports a marker under resources/starter-kit/payments/.
 */
class StarterKitPostInstall
{
    public function handle($console)
    {
        // Resrv's tables — `php artisan resrv:install` does NOT run migrations.
        $console->call('migrate', ['--force' => true]);

        if ($this->demoContentSelected()) {
            $console->call('db:seed', [
                '--class' => 'Database\\Seeders\\ResrvDemoSeeder',
                '--force' => true,
            ]);
            $console->info('Resrv Hotel demo data seeded (rolling 12-month availability — re-run the seeder any time to refresh it).');
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

        if ($this->demoContentSelected()) {
            $console->line('  → Demo reservations for the CP reports/abandoned-recovery demo: set RESRV_SEED_DEMO_RESERVATIONS=true and re-seed.');
        }

        $this->cleanUpMarkers();
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

        @rmdir("{$dir}/payments");
        @rmdir($dir);
    }
}
