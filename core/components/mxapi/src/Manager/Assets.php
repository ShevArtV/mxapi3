<?php

namespace MxApi\Manager;

use MODX\Revolution\modX;

/**
 * Подключение Vue-бандлов админки.
 *
 * Общий код для CMP-контроллера и плагина вкладки на странице пользователя:
 * оба монтируют Vue-приложение в странице менеджера и оба обязаны одинаково
 * страховаться от отсутствия VueTools и прокидывать лексикон.
 */
class Assets
{
    /**
     * Записи лексикона в MODx.lang.
     *
     * ⚠️ Composable useLexicon из VueTools читает только то, что уже лежит в
     * window.MODx.lang, а его load() не реализован. Поэтому страница обязана
     * прокинуть строки сама, иначе интерфейс покажет сырые ключи.
     *
     * ⚠️ Префикс без подчёркивания: ключ `mxapi` (название пакета, оно же
     * заголовок вкладки) при выборке по `mxapi_` не попадал в MODx.lang, и
     * вкладка подписывалась сырым ключом.
     *
     * @param modX $modx
     * @param string $prefix
     * @return void
     */
    public static function registerLexicon(modX $modx, $prefix = 'mxapi')
    {
        $modx->lexicon->load('mxapi:default');
        $entries = $modx->lexicon->fetch($prefix);
        if (empty($entries)) {
            return;
        }

        $json = json_encode($entries, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $modx->regClientStartupHTMLBlock(
            '<script>window.MODx = window.MODx || {}; MODx.lang = Object.assign(MODx.lang || {}, ' . $json . ');</script>'
        );
    }

    /**
     * Inline-проверка Import Map пакета VueTools (один раз на страницу).
     *
     * Нет карты с ключом vue → снимаем свои data-vue-module скрипты и говорим
     * человеческим языком, чего не хватает. Иначе браузер выдаёт «Failed to
     * resolve module specifier "vue"» без объяснения, а страница молча пустая.
     * Объявить зависимость от VueTools в transport нельзя — билдер прописывает
     * requires только на php и modx.
     *
     * @param modX $modx
     * @return void
     */
    public static function registerVueToolsCheck(modX $modx)
    {
        $title = json_encode($modx->lexicon('mxapi') ?: 'mxApi', JSON_UNESCAPED_UNICODE);
        $message = json_encode(
            $modx->lexicon('mxapi_vuetools_required')
                ?: 'Для работы интерфейса требуется пакет VueTools. Установите его через Менеджер пакетов.',
            JSON_UNESCAPED_UNICODE
        );

        $script = <<<JS
<script>
(function () {
    if (window.__mxApiVueToolsChecked) { return; }
    window.__mxApiVueToolsChecked = true;
    var map = document.querySelector('script[type="importmap"]');
    var ok = false;
    if (map) {
        try { var j = JSON.parse(map.textContent); ok = !!(j.imports && j.imports.vue); } catch (e) { ok = false; }
    }
    if (!ok) {
        document.querySelectorAll('script[type="module"][data-vue-module]').forEach(function (el) { el.remove(); });
        var alertFn = function () {
            if (typeof MODx !== 'undefined' && MODx.msg) { MODx.msg.alert({$title}, {$message}); }
            else { alert({$message}); }
        };
        if (typeof Ext !== 'undefined') { Ext.onReady(alertFn); }
        else { document.addEventListener('DOMContentLoaded', function () { setTimeout(alertFn, 500); }); }
    }
})();
</script>
JS;
        $modx->regClientStartupHTMLBlock($script);
    }

    /**
     * Бандл Vue-приложения: CSS плюс entry как ES-модуль.
     *
     * addJavascript()/addLastJavascript() не годятся — они не умеют
     * type="module", без которого Import Map не работает. Cache-bust по mtime:
     * менеджер отдаёт объединённый кэш скриптов, и без него правка не доезжает
     * до браузера.
     *
     * @param modX $modx
     * @param string $assetsPath
     * @param string $assetsUrl
     * @param string $name Имя бандла без расширения.
     * @return void
     */
    public static function registerModule(modX $modx, $assetsPath, $assetsUrl, $name)
    {
        $distPath = rtrim($assetsPath, '/') . '/js/mgr/vue-dist/';
        $distUrl = rtrim($assetsUrl, '/') . '/js/mgr/vue-dist/';

        $version = @filemtime($distPath . $name . '.min.js') ?: '0';

        // Общие стили обоих приложений живут в отдельном чанке (Vite выносит их
        // туда вместе с общим кодом) и подключаются первыми — иначе утилиты
        // вроде .mxapi-muted просто не приезжают на страницу.
        if (is_file($distPath . 'shared.min.css')) {
            $sharedVersion = @filemtime($distPath . 'shared.min.css') ?: $version;
            $modx->regClientCSS($distUrl . 'shared.min.css?v=' . rawurlencode((string)$sharedVersion));
        }

        if (is_file($distPath . $name . '.min.css')) {
            $cssVersion = @filemtime($distPath . $name . '.min.css') ?: $version;
            $modx->regClientCSS($distUrl . $name . '.min.css?v=' . rawurlencode((string)$cssVersion));
        }

        $modx->regClientStartupHTMLBlock(
            '<script type="module" data-vue-module src="'
            . $distUrl . $name . '.min.js?v=' . rawurlencode((string)$version) . '"></script>'
        );
    }
}
