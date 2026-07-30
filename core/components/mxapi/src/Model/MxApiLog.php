<?php
namespace MxApi\Model;

use xPDO\xPDO;

/**
 * Class MxApiLog
 *
 * @property integer $createdon
 * @property integer $client_id
 * @property integer $user_id
 * @property string $endpoint
 * @property string $context
 * @property string $route
 * @property string $method
 * @property integer $status
 * @property string $error_code
 * @property integer $duration_ms
 * @property string $ip
 * @property string $actor
 * @property string $idempotency_key
 * @property array $request_summary
 * @property array $response_summary
 *
 * @package MxApi\Model
 */
class MxApiLog extends \xPDO\Om\xPDOSimpleObject
{
}
