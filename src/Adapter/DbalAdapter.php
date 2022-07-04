<?php

declare(strict_types=1);

namespace Thingston\Cache\Adapter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Psr\Cache\CacheItemInterface;
use Thingston\Cache\Exception\InvalidArgumentException;
use Throwable;

/**
 * @phpstan-type OverrideParams = array{
 *     charset?: string,
 *     dbname?: string,
 *     default_dbname?: string,
 *     driver?: key-of<\Doctrine\DBAL\DriverManager::DRIVER_MAP>,
 *     driverClass?: class-string<\Doctrine\DBAL\Driver>,
 *     driverOptions?: array<mixed>,
 *     host?: string,
 *     password?: string,
 *     path?: string,
 *     pdo?: \PDO,
 *     platform?: \Doctrine\DBAL\Platforms\AbstractPlatform,
 *     port?: int,
 *     user?: string,
 *     unix_socket?: string,
 * }
 */
final class DbalAdapter extends AbstractAdapter
{
    public const TABLE_NAME = 'cache_items';
    public const COLUMN_KEY = 'item_key';
    public const COLUMN_VALUE = 'item_value';

    /**
     * @var array{
     *     charset?: string,
     *     dbname?: string,
     *     default_dbname?: string,
     *     driver?: key-of<\Doctrine\DBAL\DriverManager::DRIVER_MAP>,
     *     driverClass?: class-string<\Doctrine\DBAL\Driver>,
     *     driverOptions?: array<mixed>,
     *     host?: string,
     *     keepSlave?: bool,
     *     keepReplica?: bool,
     *     master?: OverrideParams,
     *     memory?: bool,
     *     password?: string,
     *     path?: string,
     *     pdo?: \PDO,
     *     platform?: \Doctrine\DBAL\Platforms\AbstractPlatform,
     *     port?: int,
     *     primary?: OverrideParams,
     *     replica?: array<OverrideParams>,
     *     sharding?: array<string,mixed>,
     *     slaves?: array<OverrideParams>,
     *     user?: string,
     *     wrapperClass?: class-string<T>,
     * }
     */
    private array $params = [];

    /**
     * @var Connection|null
     */
    private ?Connection $connection = null;

    /**
     * @param string|array{
     *     charset?: string,
     *     dbname?: string,
     *     default_dbname?: string,
     *     driver?: key-of<\Doctrine\DBAL\DriverManager::DRIVER_MAP>,
     *     driverClass?: class-string<\Doctrine\DBAL\Driver>,
     *     driverOptions?: array<mixed>,
     *     host?: string,
     *     keepSlave?: bool,
     *     keepReplica?: bool,
     *     master?: OverrideParams,
     *     memory?: bool,
     *     password?: string,
     *     path?: string,
     *     pdo?: \PDO,
     *     platform?: \Doctrine\DBAL\Platforms\AbstractPlatform,
     *     port?: int,
     *     primary?: OverrideParams,
     *     replica?: array<OverrideParams>,
     *     sharding?: array<string,mixed>,
     *     slaves?: array<OverrideParams>,
     *     user?: string,
     *     wrapperClass?: class-string<T>,
     * } $params
     * @param string $table
     * @param string $columnKey
     * @param string $columnValue
     */
    public function __construct(
        string|array $params,
        private string $table = self::TABLE_NAME,
        private string $columnKey = self::COLUMN_KEY,
        private string $columnValue = self::COLUMN_VALUE
    ) {
        $this->params = is_string($params) ? ['url' => $params] : $params;
        $this->table = $table;
        $this->columnValue = $columnKey;
        $this->columnKey = $columnValue;
    }

    private function getConnection(): Connection
    {
        if (null === $this->connection) {
            try {
                /** @phpstan-ignore-next-line */
                $connection = DriverManager::getConnection($this->params);
                $this->createTable($connection);
                $this->connection = $connection;
            } catch (Throwable $e) {
                throw new InvalidArgumentException('Unable to get connection.', 0, $e);
            }
        }

        return $this->connection;
    }

    private function createTable(Connection $connection): void
    {
        $scheme = $connection->getSchemaManager();

        if (false === $scheme->tablesExist($this->table)) {
            $table = new Table($this->table);
            $table->addColumn($this->columnKey, Types::STRING);
            $table->addColumn($this->columnValue, Types::BLOB);
            $table->setPrimaryKey([$this->columnKey]);
            $scheme->createTable($table);
        }
    }

    protected function fetchItem(string $key): ?CacheItemInterface
    {
        $result = $this->getConnection()
            ->createQueryBuilder()
            ->select($this->columnKey, $this->columnValue)
            ->from($this->table)
            ->where($this->columnKey . ' = ?')
            ->setParameter(0, $key)
            ->setMaxResults(1)
            ->execute();

        if (false === $result instanceof Result) {
            return null;
        }

        if (false === $row = $result->fetchAssociative()) {
            return null;
        }

        $contents = $row[$this->columnValue] ?? null;

        if (false === is_string($contents)) {
            return null;
        }

        return $this->unserializeItem($key, $contents);
    }

    protected function removeItem(string $key): bool
    {
        try {
            $result = (bool) $this->getConnection()->delete($this->table, [$this->columnKey => $key]);
        } catch (Throwable $e) {
            $result = false;
        }

        return $result;
    }

    protected function saveItem(CacheItemInterface $item): bool
    {
        $key = $item->getKey();

        try {
            $this->removeItem($key);

            $result = (bool) $this->getConnection()->insert($this->table, [
                $this->columnKey => $key,
                $this->columnValue => serialize($item),
            ]);
        } catch (Throwable $e) {
            $result = false;
        }

        return $result;
    }

    public function clear(): bool
    {
        try {
            $result = $this->getConnection()
                ->createQueryBuilder()
                ->delete($this->table)
                ->execute();
        } catch (Throwable $e) {
            $result = false;
        }

        return (bool) $result;
    }
}
