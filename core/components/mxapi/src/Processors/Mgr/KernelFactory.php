<?php

namespace MxApi\Processors\Mgr;

use MODX\Revolution\modX;
use MxApi\Bootstrap;
use MxApi\Core\Kernel;

/**
 * Ядро mxApi для менеджерных процессоров.
 *
 * Сначала спрашиваем DI-контейнер: сервис регистрирует bootstrap.php пакета, и
 * в одном запросе ядро собирается один раз. Если namespace ещё не попал в кэш
 * расширений (сразу после установки, до сброса кэша), собираем сами, подключив
 * пакетный автозагрузчик.
 */
class KernelFactory
{
    /**
     * @param modX $modx
     * @return Kernel|null null — пакет не установлен целиком (нет vendor).
     */
    public static function create(modX $modx)
    {
        if ($modx->services->has('mxapi')) {
            $kernel = $modx->services->get('mxapi');
            if ($kernel instanceof Kernel) {
                return $kernel;
            }
        }

        $corePath = $modx->getOption('mxapi.core_path', null, MODX_CORE_PATH . 'components/mxapi/');
        $autoload = $corePath . 'vendor/autoload.php';
        if (!is_readable($autoload)) {
            return null;
        }
        require_once $autoload;

        return Bootstrap::createKernel($modx);
    }
}
