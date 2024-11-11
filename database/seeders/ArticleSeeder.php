<?php

namespace Database\Seeders;

use App\Jobs\FetchArticlesJob;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run()
    {
        FetchArticlesJob::dispatchSync();

        $this->command->info('Articles fetched from APIs successfully!');
    }
}
