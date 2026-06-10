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
