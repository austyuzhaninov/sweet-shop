<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Events\QueryExecuted;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Разработка:
         * preventLazyLoading - Исключение N+1
         * preventSilentlyDiscardingAttributes - Исключение при заполнении полей модели (fillable)
         * отсутствие данных из поля объекта ($product->name = null)
         */
        Model::shouldBeStrict(!app()->isProduction());

        // Только для рабочего стенда, для тестовго нет смысла тк отслеживается каждый шаг в телескопе
        if (app()->isProduction()) {

            // Логирование коннекта, от начала и до конца
            DB::whenQueryingForLongerThan(CarbonInterval::seconds(5), function (Connection $connection, QueryExecuted $event) {
                logger()
                    ->channel('telegram')
                    ->debug('whenQueryingForLongerThan:' . $connection->query()->toSql());
            });

            // Прослушка всех запросов с бд
            DB::listen(static function ($query) {
                // Время выполнения каждого запроса
                // dump($query->time);
                // dump($query->sql);
                if ($query->time > 200) {
                    logger()
                        ->channel('telegram')
                        ->debug('whenQueryingForLongerThan:' . $query->sql, $query->bindings);
                }

            });

            $kernel = app(Kernel::class);
            $kernel->whenRequestLifecycleIsLongerThan(
                CarbonInterval::seconds(4),
                function () {
                    logger()
                        ->channel('telegram')
                        ->debug('whenRequestLifecycleIsLongerThan:' . request()->url());
                }
            );
        }
    }
}
