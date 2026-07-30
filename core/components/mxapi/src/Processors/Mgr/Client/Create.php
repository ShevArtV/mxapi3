<?php

namespace MxApi\Processors\Mgr\Client;

use MxApi\Core\Auth\ClientSecret;
use MxApi\Model\MxApiClient;

/**
 * Создание клиента интеграции.
 *
 * Секрет возвращается ровно здесь и больше нигде: в базе лежит только хэш.
 * Если админ закрыл окно, не скопировав, — остаётся перевыпуск.
 */
class Create extends Base
{
    public function process()
    {
        $name = trim((string)$this->getProperty('name'));
        if ($name === '') {
            return $this->failure($this->modx->lexicon('mxapi_client_err_name_ns'));
        }

        $scopes = $this->parseScopes($this->getProperty('scopes'), $error);
        if ($scopes === null) {
            return $this->failure($error);
        }

        $tokenTtl = $this->parseTokenTtl($this->getProperty('token_ttl', 0), $error);
        if ($tokenTtl === null) {
            return $this->failure($error);
        }

        $secret = ClientSecret::generateSecret();
        $now = time();

        /** @var MxApiClient $client */
        $client = $this->modx->newObject(MxApiClient::class);
        $client->fromArray([
            'name' => $name,
            'client_key' => ClientSecret::generateKey(),
            'secret_hash' => ClientSecret::hash($secret),
            'user_id' => (int)$this->user->get('id'),
            'scopes' => $scopes,
            'token_ttl' => $tokenTtl,
            'active' => true,
            'createdon' => $now,
            'editedon' => $now,
        ]);

        if (!$client->save()) {
            return $this->failure($this->modx->lexicon('mxapi_client_err_save'));
        }

        $row = $this->clientToArray($client);
        // Единственный момент, когда секрет существует в открытом виде.
        $row['client_secret'] = $secret;

        return $this->success('', $row);
    }
}
