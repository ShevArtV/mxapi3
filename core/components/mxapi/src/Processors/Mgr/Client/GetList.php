<?php

namespace MxApi\Processors\Mgr\Client;

use MxApi\Model\MxApiClient;

/**
 * Клиенты интеграции конкретного пользователя — для вкладки mxApi на странице
 * правки пользователя.
 */
class GetList extends Base
{
    public function process()
    {
        $clients = $this->modx->getCollection(MxApiClient::class, [
            'user_id' => (int)$this->user->get('id'),
        ]);

        $rows = [];
        foreach ($clients as $client) {
            $rows[] = $this->clientToArray($client);
        }

        // Свежие сверху: только что созданного клиента ищут первым.
        usort($rows, function ($left, $right) {
            return (int)$right['createdon'] - (int)$left['createdon'];
        });

        return $this->outputArray($rows, count($rows));
    }
}
