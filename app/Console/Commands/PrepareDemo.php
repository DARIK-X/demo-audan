<?php
namespace App\Console\Commands;
use App\Models\School;
use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class PrepareDemo extends Command {
    protected $signature = 'portal:demo';
    protected $description = 'Жергілікті көрсетілімге белгіленген үлгі мектептер мен материалдар қосу';
    public function handle(): int {
        if (app()->environment('production')) { $this->error('Демо деректер production ортасына қосылмайды. Бөлек жергілікті орта қолданыңыз.'); return self::FAILURE; }
        DB::transaction(function () {
            foreach (['Жалпы білім беретін мектеп', 'Мектеп-лицей', 'Мектеп-гимназия'] as $i => $type) {
                $key = 'school-'.($i + 1);
                if (!School::where('demo_key', $key)->exists()) {
                    (new School)->forceFill(['demo_key' => $key, 'name' => 'ДЕМО — №'.($i + 1).' мектеп', 'type' => $type, 'village' => 'Үлгі ауылы', 'address' => 'Демо көшесі, '.($i + 1), 'students' => 350 + $i * 100, 'description' => 'Көрсетілімге арналған ойдан алынған мәлімет. Бұл нақты білім беру ұйымы емес. Сайт сілтемесі де үлгі ретінде берілген.', 'website_url' => 'https://example.com', 'is_published' => true])->save();
                }
            }
            foreach (config('portal.categories') as $category => $title) {
                if (!Article::where('demo_key', 'article-'.$category)->exists()) {
                    (new Article)->forceFill(['demo_key' => 'article-'.$category, 'title' => 'ДЕМО — '.$title, 'category' => $category, 'excerpt' => 'Портал мүмкіндіктерін көрсетуге арналған үлгі материал.', 'body' => "Бұл мәтін көрсетілімге арналған. Нақты жаңалық, ресми құжат немесе нұсқаулық емес.\n\nӘкімші материалдың атауын, мәтінін, суретін, PDF файлын және жариялау уақытын басқара алады.", 'is_published' => true, 'published_at' => now()])->save();
                }
            }
        });
        $this->info('3 үлгі мектеп және 4 үлгі материал дайын. Бұрынғы деректер өзгертілмеді. Қайта іске қосқанда көшірмелер қосылмайды.');
        $this->line('Демо мектептердің сыртқы сілтемесі example.com үлгі доменіне өтеді. Өз мектеп сайтыңызды әкімшіде енгізіңіз.');
        return self::SUCCESS;
    }
}
