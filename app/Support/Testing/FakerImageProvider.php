<?php

namespace App\Support\Testing;

use Faker\Provider\Base;
use Illuminate\Support\Facades\Storage;

class FakerImageProvider extends Base
{
    public function fixturesImage(string $fixturesDir, string $storageDir): string
    {
        // Проверяем на существование директории, если нет - создаём
        if(!Storage::exists($storageDir)) {
            Storage::makeDirectory($storageDir);
        }

        // Генерируем и сохраняем файл
        $file = $this->generator->file(
            base_path("tests/Fixtures/images/$fixturesDir"),
            Storage::path($storageDir),
            false
        );

        // Отдаём путь файла
        return '/storage/' . trim($storageDir, '/') . '/' . $file;
    }
}
