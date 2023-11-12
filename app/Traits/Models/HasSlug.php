<?php

namespace App\Traits\Models;

use Illuminate\Database\Eloquent\Model;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        // Добавляем к модели метод создания slug'а
        static::creating(function (Model $item) {
            $item->makeSlug();
        });
    }

    protected function makeSlug(): void
    {
        $slug = $this->slugUnique(
            str($this->{self::slugFrom()})
                ->slug()
                ->value()
        );

        $this->{$this->slugColumn()} = $this->{$this->slugColumn()} ?? $slug;
    }

    /**
     * Метот объяевления колонки slug
     * @return string
     */
    protected function slugColumn(): string
    {
        return 'slug';
    }

    /**
     * Метот объяевления откуда брать строку для генерации slug'га
     * @return string
     */
    protected function slugFrom(): string
    {
        return 'title';
    }

    /**
     * Генерация уникальных slug'ов
     * @param string $slug
     * @return string
     */
    private function slugUnique(string $slug): string
    {
        $originalSlug = $slug;  //Оригинальный слаг
        $i = 0;                 // Счетчик для поддержки уникальности

        // Генерация уникального слага, если такой имеется считаем количество слагов и добавляем счетчик в конец (-N)
        while ($this->isSlugExists($slug)) {
            $i++;
            $slug = $originalSlug. '-' .  $i;
        }

        return $slug;
    }

    /**
     * Проверка на существования slag'а в БД текущей модели
     * @param string $slug
     * @return bool
     */
    private function isSlugExists(string $slug): bool
    {
        $query = $this->newQuery()
            ->where(self::slugColumn(), $slug)                              // Ищем слаг по uid
            ->where($this->getKeyName(), '!=', $this->getKey())     // Исключаем текущую запись
            ->withoutGlobalScopes();                                        // Исключаем остальные скопы

        return $query->exists();
    }

}
