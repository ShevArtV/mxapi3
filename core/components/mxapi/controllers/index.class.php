<?php

use MODX\Revolution\modExtraManagerController;

/**
 * CMP mxApi — каталог эндпоинтов (Vue + VueTools).
 *
 * Только чтение: маршруты и права правятся кодом и политиками MODX, а не
 * формой в админке. Страница отвечает на вопрос «что этот сайт отдаёт наружу и
 * кому», который иначе решается чтением репозитория.
 *
 * Имя класса плоское и в глобальном namespace — так его строит
 * MODX\Revolution\modManagerResponse::getControllerClassName() для пары
 * namespace=mxapi + action=index (ucfirst(namespace) . action . 'ManagerController').
 * PSR-4 src/ тут не резолвится, поэтому контроллер страницы лежит здесь.
 */
class MxapiindexManagerController extends modExtraManagerController
{
    /** @var string */
    private $corePath;

    /** @var string */
    private $assetsUrl;

    /** @var string */
    private $assetsPath;

    public function initialize()
    {
        $this->corePath = $this->modx->getOption('mxapi.core_path', null, MODX_CORE_PATH . 'components/mxapi/');
        $this->assetsUrl = $this->modx->getOption('mxapi.assets_url', null, MODX_ASSETS_URL . 'components/mxapi/');
        $this->assetsPath = $this->modx->getOption('mxapi.assets_path', null, MODX_ASSETS_PATH . 'components/mxapi/');

        parent::initialize();
    }

    public function getLanguageTopics()
    {
        return ['mxapi:default'];
    }

    public function checkPermissions()
    {
        return $this->modx->hasPermission('settings');
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('mxapi');
    }

    public function loadCustomCssJs()
    {
        $config = [
            'connector_url' => $this->assetsUrl . 'connector.php',
            'token' => $this->modx->user
                ? $this->modx->user->getUserToken($this->modx->context->get('key'))
                : '',
            'assets_url' => $this->assetsUrl,
            'route_prefix' => $this->modx->getOption('mxapi.route_prefix', null, '/mxapi/v1'),
            'site_url' => rtrim($this->modx->getOption('site_url'), '/'),
            'enabled' => (bool)$this->modx->getOption('mxapi.enabled', null, true),
        ];
        $json = json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->modx->regClientStartupHTMLBlock("<script>window.MxApiConfig = {$json};</script>");

        \MxApi\Manager\Assets::registerLexicon($this->modx);
        \MxApi\Manager\Assets::registerVueToolsCheck($this->modx);
        \MxApi\Manager\Assets::registerModule($this->modx, $this->assetsPath, $this->assetsUrl, 'catalog');
    }

    public function getTemplateFile()
    {
        return $this->corePath . 'templates/home.tpl';
    }
}
