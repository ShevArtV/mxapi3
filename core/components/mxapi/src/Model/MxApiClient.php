<?php
namespace MxApi\Model;

use xPDO\xPDO;

/**
 * Class MxApiClient
 *
 * @property string $name
 * @property string $client_key
 * @property string $secret_hash
 * @property integer $user_id
 * @property array $scopes
 * @property string $allowed_ips
 * @property array $contexts
 * @property integer $rate_limit
 * @property integer $token_ttl
 * @property boolean $active
 * @property string $description
 * @property integer $createdon
 * @property integer $editedon
 *
 * @property \MxApi\Model\MxApiToken[] $Tokens
 *
 * @package MxApi\Model
 */
class MxApiClient extends \xPDO\Om\xPDOSimpleObject
{
}
