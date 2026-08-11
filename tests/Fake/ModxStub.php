<?php

/**
 * Заглушка modX и xPDOQuery для линии MODX 3.
 *
 * Линейно-специфичный файл: имена классов платформы и репозиториев у двоек и
 * троек разные, а сам тест уборки в обеих линиях обязан оставаться дословно
 * одинаковым — как и тест секрета клиента.
 */

namespace MODX\Revolution {
    if (!class_exists(modX::class, false)) {
        class modX
        {
            const LOG_LEVEL_ERROR = 1;

            /** @var \MxApi\Tests\Fake\FakeStorage */
            public $storage;

            public function __construct(\MxApi\Tests\Fake\FakeStorage $storage)
            {
                $this->storage = $storage;
            }

            public function newQuery($className)
            {
                return new \xPDO\Om\xPDOQuery($className);
            }

            public function getCount($className, $criteria = null)
            {
                return $this->storage->getCount($className, $criteria);
            }

            public function removeCollection($className, $criteria)
            {
                return $this->storage->removeCollection($className, $criteria);
            }

            public function log($level, $message)
            {
                $this->storage->logs[] = [$level, $message];
            }
        }
    }
}

namespace xPDO\Om {
    if (!class_exists(xPDOQuery::class, false)) {
        class xPDOQuery
        {
            /** @var string */
            public $className;

            /** @var array */
            public $conditions = [];

            public function __construct($className)
            {
                $this->className = $className;
            }

            public function where($conditions)
            {
                $this->conditions = array_merge($this->conditions, (array)$conditions);

                return $this;
            }

            public function sortby($column, $direction = 'ASC')
            {
                return $this;
            }

            public function limit($limit, $offset = 0)
            {
                return $this;
            }
        }
    }
}

namespace MxApi\Tests\Fake {

    use MODX\Revolution\modX;
    use MxApi\Model\MxApiLog;
    use MxApi\Model\MxApiToken;
    use MxApi\Platform\Modx3\Modx3LogRepository;
    use MxApi\Platform\Modx3\Modx3TokenRepository;

    /**
     * Мост между общим тестом уборки и конкретной линией.
     */
    class Stub
    {
        const TOKEN_CLASS = MxApiToken::class;
        const LOG_CLASS = MxApiLog::class;

        public static function modx(FakeStorage $storage)
        {
            return new modX($storage);
        }

        public static function tokenRepository($modx)
        {
            return new Modx3TokenRepository($modx);
        }

        public static function logRepository($modx)
        {
            return new Modx3LogRepository($modx);
        }
    }
}
