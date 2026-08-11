<?php

namespace MxApi\Platform\Modx3;

use MODX\Revolution\modX;
use MxApi\Core\Auth\TokenRecord;
use MxApi\Core\Auth\TokenRepositoryInterface;
use MxApi\Model\MxApiToken;

/**
 * Хранилище токенов на xPDO 3 (модель MxApi\Model\MxApiToken).
 */
class Modx3TokenRepository implements TokenRepositoryInterface
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
    public function findByHash($tokenHash)
    {
        /** @var MxApiToken|null $token */
        $token = $this->modx->getObject(MxApiToken::class, ['token_hash' => $tokenHash]);

        return $token ? new TokenRecord($token->toArray()) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        /** @var MxApiToken $token */
        $token = $this->modx->newObject(MxApiToken::class);
        $token->fromArray([
            'token_hash' => $data['token_hash'],
            'client_id' => isset($data['client_id']) ? (int)$data['client_id'] : 0,
            'user_id' => (int)$data['user_id'],
            'username' => (string)$data['username'],
            'scopes' => $data['scopes'] ?? [],
            'createdon' => (int)$data['createdon'],
            'expireson' => (int)$data['expireson'],
            'last_usedon' => 0,
            'revokedon' => 0,
            'user_agent' => isset($data['user_agent']) ? (string)$data['user_agent'] : '',
            'ip' => isset($data['ip']) ? (string)$data['ip'] : '',
        ], '', true, true);

        if (!$token->save()) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[mxapi] Не удалось сохранить токен.');

            return null;
        }

        return new TokenRecord($token->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function touch($tokenHash, $timestamp)
    {
        /** @var MxApiToken|null $token */
        $token = $this->modx->getObject(MxApiToken::class, ['token_hash' => $tokenHash]);
        if (!$token) {
            return;
        }

        $token->set('last_usedon', (int)$timestamp);
        $token->save();
    }

    /**
     * {@inheritdoc}
     */
    public function revoke($tokenHash, $timestamp)
    {
        /** @var MxApiToken|null $token */
        $token = $this->modx->getObject(MxApiToken::class, ['token_hash' => $tokenHash]);
        if (!$token || (int)$token->get('revokedon') > 0) {
            return false;
        }

        $token->set('revokedon', (int)$timestamp);

        return (bool)$token->save();
    }

    /**
     * {@inheritdoc}
     */
    public function purgeExpired($before)
    {
        // Условия — массивом, а не объектом запроса: removeCollection() строит
        // запрос сам и кладёт второй аргумент внутрь where() как значение, а
        // xPDOQuery в строку не приводится — на удалении был fatal.
        $conditions = ['expireson:<' => (int)$before, 'expireson:!=' => 0];

        $count = $this->modx->getCount(MxApiToken::class, $conditions);
        if ($count > 0) {
            $this->modx->removeCollection(MxApiToken::class, $conditions);
        }

        return $count;
    }
}
