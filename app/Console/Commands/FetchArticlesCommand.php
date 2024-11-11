<?php

namespace App\Console\Commands;

use App\Jobs\FetchArticlesJob;
use Illuminate\Console\Command;

class FetchArticlesCommand extends Command
{
    protected $signature = 'app:fetch-articles';
    protected $description = 'Fetch articles from news APIs';

    public function handle(): void
    {
        FetchArticlesJob::dispatch();
        $this->info('Articles fetch job dispatched successfully');
    }

    public static function schedule(): string
    {
        return '0 * * * *'; // Run hourly
    }

}
