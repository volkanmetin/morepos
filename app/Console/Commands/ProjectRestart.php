<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProjectRestart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:project-restart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Proje yeniden başlatılıyor...');

        $this->info('Cache temizleniyor...');
        $this->call('cache:clear');

        $this->info('View cache temizleniyor...');
        $this->call('view:clear');

        $this->info('Veritabanı yeniden oluşturuluyor...');
        $this->call('migrate:fresh', [
            '--seed' => true,
            '--force' => true
        ]);

        $this->info('Proje başarıyla yeniden başlatıldı!');
    }
}
