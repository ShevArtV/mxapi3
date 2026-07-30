<?php

namespace MxApi\Platform\Modx3;

use MODX\Revolution\modX;
use MxApi\Core\Auth\ClientRecord;
use MxApi\Core\Auth\ClientRepositoryInterface;
use MxApi\Model\MxApiClient;

/**
 * Хранилище клиентов интеграций на xPDO 3 (модель MxApi\Model\MxApiClient).
 */
class Modx3ClientRepository implements ClientRepositoryInterface
{
    /** @var modX */
    private $modx;

    public function __construct(modX $modx)
    {
        $this->modx = $modx;
    }

    /**
     * {@inheritdoc}
     */
    public function findByKey($clientKey)
    {
        /** @var MxApiClient|null $client */
        $client = $this->modx->getObject(MxApiClient::class, ['client_key' => $clientKey]);

        return $client ? new ClientRecord($client->toArray()) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        /** @var MxApiClient|null $client */
        $client = $this->modx->getObject(MxApiClient::class, ['id' => (int)$id]);

        return $client ? new ClientRecord($client->toArray()) : null;
    }
}
