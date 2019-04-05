<?php

/*
 * This file is part of the SncRedisBundle package.
 *
 * (c) Henrik Westphal <henrik.westphal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snc\RedisBundle\Logger;

use Predis\Connection\ConnectionInterface;
use Predis\Connection\NodeConnectionInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Snc\RedisBundle\Client\Phpredis\Client;
use Snc\RedisBundle\Client\Phpredis\Client42;
use Snc\RedisBundle\Client\Phpredis\Client43;

/**
 * RedisLogger
 */
class RedisLogger
{
    /**
     * @var PsrLoggerInterface|null
     */
    protected $logger;

    /**
     * @var object[]
     */
    private $adapters = [];

    /**
     * @var int
     */
    protected $totalCommands = 0;

    /**
     * @var array
     */
    protected $groupedCommands = [];

    /**
     * Constructor.
     *
     * @param PsrLoggerInterface $logger A LoggerInterface instance
     */
    public function __construct($logger = null)
    {
        if (!$logger instanceof PsrLoggerInterface && null !== $logger) {
            throw new \InvalidArgumentException(\sprintf(
                'RedisLogger needs a PSR-3 LoggerInterface, "%s" was injected instead.',
                \is_object($logger) ? \get_class($logger) : \gettype($logger)
            ));
        }

        $this->logger = $logger;
    }

    /**
     * @param object $adapter
     * @param bool $isConnected
     */
    public function setIsConnected($adapter, bool $isConnected): void
    {
        $hash = \spl_object_hash($adapter);

        foreach ($this->adapters as $currentHash => $data) {
            if ($currentHash !== $hash) {
                continue;
            }

            $this->adapters[$hash]['isConnected'] = $isConnected;
        }
    }

    /**
     * @param object $adapter
     * @param bool $isConnected
     */
    public function setIsPersistentConnection($adapter, bool $isPersistentConnection): void
    {
        $hash = \spl_object_hash($adapter);

        foreach ($this->adapters as $currentHash => $data) {
            if ($currentHash !== $hash) {
                continue;
            }

            $this->adapters[$hash]['isPersistentConnection'] = $isPersistentConnection;
        }
    }

    /**
     * @param object $adapter
     * @param string|null $alias
     */
    public function setAlias($adapter, ?string $alias): void
    {
        $hash = \spl_object_hash($adapter);

        foreach ($this->adapters as $currentHash => $data) {
            if ($currentHash !== $hash) {
                continue;
            }

            $this->adapters[$hash]['alias'] = $alias;
        }
    }

    /**
     * @param object $adapter
     * @param string|null $scheme
     */
    public function setScheme($adapter, ?string $scheme): void
    {
        $hash = \spl_object_hash($adapter);

        foreach ($this->adapters as $currentHash => $data) {
            if ($currentHash !== $hash) {
                continue;
            }

            $this->adapters[$hash]['scheme'] = $scheme;
        }
    }

    /**
     * @param object $adapter
     * @param string|null $host
     */
    public function setHost($adapter, ?string $host): void
    {
        $hash = \spl_object_hash($adapter);

        foreach ($this->adapters as $currentHash => $data) {
            if ($currentHash !== $hash) {
                continue;
            }

            $this->adapters[$hash]['host'] = $host;
        }
    }

    /**
     * @param object $adapter
     * @param string|int|null $port
     */
    public function setPort($adapter, $port): void
    {
        $hash = \spl_object_hash($adapter);

        foreach ($this->adapters as $currentHash => $data) {
            if ($currentHash !== $hash) {
                continue;
            }

            $this->adapters[$hash]['port'] = $port;
        }
    }

    /**
     * @return object[]
     */
    public function getAdapters(): array
    {
        return $this->adapters;
    }

    /**
     * @param object $adapter
     */
    public function addAdapter($adapter): void
    {
        $alias = null;
        $isConnected = false;

        if ($adapter instanceof Client43 ||
            $adapter instanceof Client42 ||
            $adapter instanceof Client
        ) {
            $alias = $adapter->getAlias();
        } else if ($adapter instanceof ConnectionInterface) {
            $connection = $adapter->getConnection();

            if ($connection instanceof NodeConnectionInterface) {
                $alias = $connection->getParameters()->alias;
            }

            $isConnected = $connection->isConnected();
        }

        $this->adapters[\spl_object_hash($adapter)] = [
            'alias' => $alias,
            'adapter' => $adapter,
            'isConnected' => $isConnected,
            'isPersistentConnection' => false,
            'scheme' => null,
            'host' => null,
            'port' => null,
        ];
    }

    /**
     * Logs a command
     *
     * @param string      $command    Redis command
     * @param float       $duration   Duration in milliseconds
     * @param string      $connectionAlias Connection alias
     * @param bool|string $error      Error message or false if command was successful
     */
    public function logCommand($command, $duration, $connectionAlias, $error = false)
    {
        ++$this->totalCommands;

        if (null === $this->logger) {
            return;
        }

        if (!\array_key_exists($connectionAlias, $this->groupedCommands)) {
            $this->groupedCommands[$connectionAlias] = [];
        }

        $this->groupedCommands[$connectionAlias][] = [
            'order' => $this->totalCommands,
            'connectionAlias' => $connectionAlias,
            'cmd' => $command,
            'executionMS' => $duration,
            'error' => $error,
        ];

        if ($error) {
            $message = 'Command "' . $command . '" failed (' . $error . ')';

            $this->logger->error($message);
        } else {
            $this->logger->debug('Executing command "' . $command . '"');
        }
    }

    /**
     * Returns the number of logged groupedCommands.
     *
     * @return integer
     */
    public function getTotalCommands()
    {
        return $this->totalCommands;
    }

    /**
     * Returns an array of the logged groupedCommands.
     *
     * @return array
     */
    public function getGroupedCommands()
    {
        return $this->groupedCommands;
    }
}
