<?php
namespace MxApi\Model;

use xPDO\xPDO;

/**
 * Class MxApiToken
 *
 * @property string $token_hash
 * @property integer $client_id
 * @property integer $user_id
 * @property string $username
 * @property array $scopes
 * @property integer $createdon
 * @property integer $expireson
 * @property integer $last_usedon
 * @property integer $revokedon
 * @property string $user_agent
 * @property string $ip
 *
 * @package MxApi\Model
 */
class MxApiToken extends \xPDO\Om\xPDOSimpleObject
{
}
