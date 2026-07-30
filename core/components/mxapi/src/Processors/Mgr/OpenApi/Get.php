<?php

namespace MxApi\Processors\Mgr\OpenApi;

use MODX\Revolution\Processors\Processor;
use MxApi\Core\OpenApi\OpenApiGenerator;
use MxApi\Processors\Mgr\KernelFactory;

/**
 * Выгрузка OpenAPI из админки: тот же документ, что отдаёт /meta/openapi, но
 * доступный менеджеру без выпуска токена — файлом, готовым к передаче
 * интегратору.
 */
class Get extends Processor
{
    /** @var string */
    public $permission = 'settings';

    /** @var array */
    public $languageTopics = ['mxapi:default'];

    /** @var \MxApi\Core\Kernel */
    private $kernel;

    public function initialize()
    {
        if (!$this->modx->hasPermission($this->permission)) {
            return $this->modx->lexicon('access_denied');
        }

        $this->kernel = KernelFactory::create($this->modx);
        if (!$this->kernel) {
            return $this->modx->lexicon('mxapi_err_no_vendor');
        }

        return true;
    }

    public function process()
    {
        $generator = new OpenApiGenerator($this->kernel->getRegistry(), $this->kernel->getConfig());

        $document = $generator->generate([
            'title' => $this->modx->getOption('site_name', null, 'mxApi'),
            'server' => rtrim($this->modx->getOption('site_url'), '/') . $this->kernel->getConfig()->get('route_prefix'),
        ]);

        $json = json_encode($document, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        if ((int)$this->getProperty('download', 0) === 1) {
            // Отдаём файлом и завершаем запрос: коннектор не должен обернуть
            // документ в свой JSON-конверт, иначе файл станет невалидным.
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="openapi.json"');
            echo $json;
            exit;
        }

        return $this->success('', ['openapi' => $json]);
    }
}
