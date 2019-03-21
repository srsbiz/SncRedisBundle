<?php

/*
 * This file is part of the SncRedisBundle package.
 *
 * (c) Henrik Westphal <henrik.westphal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snc\RedisBundle\DataCollector;

use Predis\Connection\ConnectionInterface;
use Snc\RedisBundle\Logger\RedisLogger;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RedisDataCollector
 */
class RedisDataCollector extends DataCollector
{
    /**
     * @var RedisLogger
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param RedisLogger $logger
     */
    public function __construct(RedisLogger $logger)
    {
        $this->logger = $logger;
        $this->reset();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'redis';
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $isIgBinaryExtensionAvailable = \extension_loaded('igbinary');
        $igBinaryExtensionVersion = null;

        if ($isIgBinaryExtensionAvailable) {
            $igBinaryExtensionVersion = \phpversion('igbinary');
        }

        $isRedisExtensionAvailable = \extension_loaded('redis');
        $redisExtensionVersion = null;

        if ($isRedisExtensionAvailable) {
            $redisExtensionVersion = \phpversion('redis');
        }

        $groupedCommands = $this->logger->getGroupedCommands();
        $adaptersInfo = [];
        $extensionVersion = null;
        $activeConnections = 0;
        $adapters = $this->logger->getAdapters();

        foreach ($adapters as $data) {
            $alias = $data['alias'];
            $isConnected = $data['isConnected'];
            $adapter = $data['adapter'];
            $isExtension = false;

            if ($adapter instanceof \Redis) {
                $isExtension = true;
            } else if ($adapter instanceof ConnectionInterface) {
                $isConnected = $adapter->isConnected(); // Override old value with fresh one.
            }

            $adaptersInfo[] = [
                'type' => $isExtension ? 'PHP extension' : 'External',
                'class' => \is_object($adapter) ? \get_class($adapter) : $adapter,
                'isConnected' => $isConnected,
                'isPersistentConnection' => $data['isPersistentConnection'],
                'alias' => $alias,
                'scheme' => $data['scheme'],
                'host' => $data['host'],
                'port' => $data['port'],
                'commands' => $alias
                    ? ($groupedCommands[$alias] ?? [])
                    : [],
            ];

            if ($isConnected) {
                $activeConnections++;
            }
        }

        $allCommands = [];

        foreach ($groupedCommands as $commands) {
            foreach ($commands as $command) {
                $order = $command['order'];
                $allCommands[$order] = $command;
            }
        }

        // Now sort all commands by the order of their executions.
        \ksort($allCommands);

        $this->data = [
            'isIgBinaryAvailable' => $isIgBinaryExtensionAvailable,
            'igBinaryExtensionVersion' => $igBinaryExtensionVersion,
            'isRedisExtensionAvailable' => $isRedisExtensionAvailable,
            'redisExtensionVersion' => $redisExtensionVersion,
            'adapters' => $adaptersInfo,
            'activeConnections' => $activeConnections,
            'groupedCommands' => $groupedCommands,
            'allCommands' => $allCommands,
            'totalCommands' => $this->logger->getTotalCommands(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->data = [
            'isIgBinaryAvailable' => false,
            'igBinaryExtensionVersion' => null,
            'isRedisExtensionAvailable' => false,
            'redisExtensionVersion' => null,
            'adapters' => [],
            'activeConnections' => 0,
            'groupedCommands' => [],
            'allCommands' => [],
        ];
    }

    /**
     * @return bool
     */
    public function isIgBinaryAvailable(): bool
    {
        return $this->data['isIgBinaryAvailable'];
    }

    /**
     * @return string|null
     */
    public function getIgBinaryExtensionVersion(): ?string
    {
        return $this->data['igBinaryExtensionVersion'];
    }

    /**
     * @return bool
     */
    public function isRedisExtensionAvailable(): bool
    {
        return $this->data['isRedisExtensionAvailable'];
    }

    /**
     * @return string|null
     */
    public function getRedisExtensionVersion(): ?string
    {
        return $this->data['redisExtensionVersion'];
    }

    /**
     * @return array
     */
    public function getAdapters(): array
    {
        return $this->data['adapters'];
    }

    /**
     * @return array
     */
    public function getGroupedCommands(): array
    {
        return $this->data['groupedCommands'];
    }

    /**
     * @return array
     */
    public function getAllCommands(): array
    {
        return $this->data['allCommands'];
    }

    /**
     * Returns the number of collected commands.
     *
     * @param string $connectionAlias
     * @return integer
     */
    public function getCommandCount(string $connectionAlias = '')
    {
        if ('' !== $connectionAlias) {
            return count($this->data['groupedCommands'][$connectionAlias]);
        }

        return $this->data['totalCommands'];
    }

    /**
     * Returns the number of failed commands.
     *
     * @param string $connectionAlias
     * @return integer
     */
    public function getErroredCommandsCount(string $connectionAlias = '')
    {
        if ('' !== $connectionAlias) {
            return count(array_filter($this->data['groupedCommands'][$connectionAlias], function ($command) {
                return $command['error'] !== false;
            }));
        }

        return count(array_filter($this->data['allCommands'], function ($command) {
            return $command['error'] !== false;
        }));
    }

    /**
     * Returns the execution time of all collected commands in seconds.
     *
     * @param string $connectionAlias
     * @return float
     */
    public function getTime(string $connectionAlias = '')
    {
        $time = 0;

        if ('' !== $connectionAlias) {
            foreach ($this->data['groupedCommands'][$connectionAlias] as $command) {
                $time += $command['executionMS'];
            }
        } else {
            foreach ($this->data['allCommands'] as $command) {
                $time += $command['executionMS'];
            }
        }

        return $time;
    }
}
