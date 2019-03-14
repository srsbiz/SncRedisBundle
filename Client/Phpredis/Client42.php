<?php

/*
 * This file is part of the SncRedisBundle package.
 *
 * (c) Henrik Westphal <henrik.westphal@gmail.com>
 * (c) Yassine Khial <yassine.khial@blablacar.com>
 * (c) Pierre Boudelle <pierre.boudelle@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Snc\RedisBundle\Client\Phpredis;

use Redis;
use Snc\RedisBundle\Logger\RedisLogger;

/**
 * PHP Redis client with logger.
 *
 * @author Henrik Westphal <henrik.westphal@gmail.com>
 * @author Yassine Khial <yassine.khial@blablacar.com>
 * @author Pierre Boudelle <pierre.boudelle@gmail.com>
 */
class Client42 extends Redis
{
    /**
     * @var RedisLogger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $alias;

    /**
     * Constructor.
     *
     * @param array       $parameters List of parameters (only `alias` key is handled)
     * @param RedisLogger $logger     A RedisLogger instance
     */
    public function __construct(array $parameters = array(), RedisLogger $logger = null)
    {
        parent::__construct();

        $this->logger = $logger;
        $this->alias = isset($parameters['alias']) ? $parameters['alias'] : '';
    }

    /**
     * Proxy function.
     *
     * @param string $name      A command name
     * @param array  $arguments Lit of command arguments
     *
     * @throws \RuntimeException If no Redis instance is defined
     *
     * @return mixed
     */
    private function call($name, array $arguments = array())
    {
        $startTime = microtime(true);
        $result = call_user_func_array("parent::$name", $arguments);
        $duration = (microtime(true) - $startTime) * 1000;

        if (null !== $this->logger) {
            $this->logger->logCommand($this->getCommandString($name, $arguments), $duration, $this->alias, false);
        }

        return $result;
    }

    /**
     * Returns a string representation of the given command including arguments.
     *
     * @param string $command   A command name
     * @param array  $arguments List of command arguments
     *
     * @return string
     */
    private function getCommandString($command, array $arguments)
    {
        $list = array();
        $this->flatten($arguments, $list);

        return trim(strtoupper($command).' '.implode(' ', $list));
    }

    /**
     * Flatten arguments to single dimension array.
     *
     * @param array $arguments An array of command arguments
     * @param array $list      Holder of results
     */
    private function flatten($arguments, array &$list)
    {
        foreach ($arguments as $key => $item) {
            if (!is_numeric($key)) {
                $list[] = $key;
            }

            if (is_scalar($item)) {
                $list[] = strval($item);
            } elseif (null === $item) {
                $list[] = '<null>';
            } else {
                $this->flatten($item, $list);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function ping()
    {
        return $this->call('ping', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->call('get', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $opts = NULL)
    {
        return $this->call('set', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function setex($key, $expire, $value)
    {
        return $this->call('setEx', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function setnx($key, $value)
    {
        return $this->call('setNx', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function del($key, ...$other_keys)
    {
        return $this->call('del', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key, ...$other_keys)
    {
        return $this->call('delete', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function multi($mode = Redis::MULTI)
    {
        return $this->call('multi', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function exec()
    {
        return $this->call('exec', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function discard()
    {
        return $this->call('discard', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function watch($key, ...$other_keys)
    {
        return $this->call('watch', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function unwatch()
    {
        return $this->call('unwatch', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe(array $channels)
    {
        return $this->call('subscribe', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function psubscribe(array $patterns)
    {
        return $this->call('psubscribe', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function publish($channel, $message)
    {
        return $this->call('publish', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function pubsub($cmd, ...$args)
    {
        return $this->call('pubsub', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function exists($key, ...$other_keys)
    {
        return $this->call('exists', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function incr($key)
    {
        return $this->call('incr', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function incrByFloat($key, $value)
    {
        return $this->call('incrByFloat', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function incrBy($key, $value)
    {
        return $this->call('incrBy', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function decr($key)
    {
        return $this->call('decr', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function decrBy($key, $value)
    {
        return $this->call('decrBy', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(array $keys)
    {
        return $this->call('getMultiple', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lPush($key, $value)
    {
        return $this->call('lPush', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function rPush($key, $value)
    {
        return $this->call('rPush', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lPushx($key, $value)
    {
        return $this->call('lPushx', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function rPushx($key, $value)
    {
        return $this->call('rPushx', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lPop($key)
    {
        return $this->call('lPop', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function rPop($key)
    {
        return $this->call('rPop', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function blPop($key, $timeout_or_key, ...$extra_args)
    {
        return $this->call('blPop', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function brPop($key, $timeout_or_key, ...$extra_args)
    {
        return $this->call('brPop', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lLen($key)
    {
        return $this->call('lLen', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lSize($key)
    {
        return $this->call('lSize', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lindex($key, $index)
    {
        return $this->call('lIndex', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lGet($key, $index)
    {
        return $this->call('lGet', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lSet($key, $index, $value)
    {
        return $this->call('lSet', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lrange($key, $start, $end)
    {
        return $this->call('lRange', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lGetRange($key, $start, $end)
    {
        return $this->call('lGetRange', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lTrim($key, $start, $stop)
    {
        return $this->call('lTrim', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function listTrim($key, $start, $stop)
    {
        return $this->call('listTrim', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lRem($key, $value, $count)
    {
        return $this->call('lRem', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lRemove($key, $value, $count)
    {
        return $this->call('lRemove', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lInsert($key, $position, $pivot, $value)
    {
        return $this->call('lInsert', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sAdd($key, $value1, $value2 = null, $valueN = null)
    {
        return $this->call('sAdd', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function srem($key, $member, ...$other_members)
    {
        return $this->call('sRem', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sRemove($key, $member, ...$other_members)
    {
        return $this->call('sRemove', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sMove($srcKey, $dstKey, $member)
    {
        return $this->call('sMove', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sIsMember($key, $value)
    {
        return $this->call('sIsMember', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sContains($key, $value)
    {
        return $this->call('sContains', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sCard($key)
    {
        return $this->call('sCard', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sPop($key)
    {
        return $this->call('sPop', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sRandMember($key, $count = null)
    {
        return $this->call('sRandMember', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sInter($key, ...$other_keys)
    {
        return $this->call('sInter', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sInterStore($dst, $key, ...$other_keys)
    {
        return $this->call('sInterStore', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sUnion($key, ...$other_keys)
    {
        return $this->call('sUnion', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sUnionStore($dst, $key, ...$other_keys)
    {
        return $this->call('sUnionStore', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sDiff($key, ...$other_keys)
    {
        return $this->call('sDiff', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sDiffStore($dst, $key, ...$other_keys)
    {
        return $this->call('sDiffStore', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sMembers($key)
    {
        return $this->call('sMembers', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sGetMembers($key)
    {
        return $this->call('sGetMembers', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sScan($key, &$iterator, $pattern = null, $count = null)
    {
        return $this->call('sScan', array($key, &$iterator, $pattern, $count));
    }

    /**
     * {@inheritdoc}
     */
    public function getSet($key, $value)
    {
        return $this->call('getSet', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function randomKey()
    {
        return $this->call('randomKey', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function move($key, $dbindex)
    {
        return $this->call('move', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function rename($srcKey, $dstKey)
    {
        return $this->call('rename', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function renameKey($srcKey, $dstKey)
    {
        return $this->call('renameKey', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function renameNx($srcKey, $dstKey)
    {
        return $this->call('renameNx', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function expire($key, $ttl)
    {
        return $this->call('expire', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function pExpire($key, $ttl)
    {
        return $this->call('pExpire', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function setTimeout($key, $ttl)
    {
        return $this->call('setTimeout', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function expireAt($key, $timestamp)
    {
        return $this->call('expireAt', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function pExpireAt($key, $timestamp)
    {
        return $this->call('pExpireAt', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function keys($pattern)
    {
        return $this->call('keys', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys($pattern)
    {
        return $this->call('getKeys', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function dbSize()
    {
        return $this->call('dbSize', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function bgrewriteaof()
    {
        return $this->call('bgrewriteaof', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function slaveof($host = '127.0.0.1', $port = 6379)
    {
        return $this->call('slaveof', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function object($string = '', $key = '')
    {
        return $this->call('object', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        return $this->call('save', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function bgsave()
    {
        return $this->call('bgsave', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function lastSave()
    {
        return $this->call('lastSave', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function wait($numSlaves, $timeout)
    {
        return $this->call('wait', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function type($key)
    {
        return $this->call('type', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function append($key, $value)
    {
        return $this->call('append', [$key, $value]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRange($key, $start, $end)
    {
        return $this->call('getRange', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function substr($key, $start, $end)
    {
        return $this->call('substr', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function setRange($key, $offset, $value)
    {
        return $this->call('setRange', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function strlen($key)
    {
        return $this->call('strlen', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function bitpos($key, $bit, $start = NULL, $end = NULL)
    {
        return $this->call('bitpos', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function getBit($key, $offset)
    {
        return $this->call('getBit', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function setBit($key, $offset, $value)
    {
        return $this->call('setBit', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function bitCount($key)
    {
        return $this->call('bitCount', [$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function bitop($operation, $ret_key, $key, ...$other_keys)
    {
        return $this->call('bitOp', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function flushDB($async = NULL)
    {
        return $this->call('flushDB', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function flushAll($async = NULL)
    {
        return $this->call('flushAll', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function sort($key, $option = null)
    {
        return $this->call('sort', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function info($option = NULL)
    {
        return $this->call('info', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function resetStat()
    {
        return $this->call('resetStat', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function ttl($key)
    {
        return $this->call('ttl', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function pttl($key)
    {
        return $this->call('pttl', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function persist($key)
    {
        return $this->call('persist', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function mset(array $array)
    {
        return $this->call('mset', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function mget(array $array)
    {
        return $this->call('mget', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function msetnx(array $array)
    {
        return $this->call('msetnx', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function rpoplpush($srcKey, $dstKey)
    {
        return $this->call('rpoplpush', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function brpoplpush($src, $dst, $timeout)
    {
        return $this->call('brpoplpush', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zAdd($key, $score1, $value1, $score2 = null, $value2 = null, $scoreN = null, $valueN = null)
    {
        return $this->call('zAdd', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zRange($key, $start, $end, $withscores = null)
    {
        return $this->call('zRange', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zRem($key, $member, ...$other_members)
    {
        return $this->call('zRem', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zDelete($key, $member, ...$other_members)
    {
        return $this->call('zDelete', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zRevRange($key, $start, $end, $withscore = null)
    {
        return $this->call('zRevRange', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zRangeByScore($key, $start, $end, array $options = array())
    {
        return $this->call('zRangeByScore', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zRevRangeByScore($key, $start, $end, array $options = array())
    {
        return $this->call('zRevRangeByScore', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zRangeByLex($key, $min, $max, $offset = null, $limit = null)
    {
        return $this->call('zRangeByLex', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zRevRangeByLex($key, $min, $max, $offset = null, $limit = null)
    {
        return $this->call('zRevRangeByLex', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zCount($key, $start, $end)
    {
        return $this->call('zCount', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zRemRangeByScore($key, $start, $end)
    {
        return $this->call('zRemRangeByScore', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zDeleteRangeByScore($key, $start, $end)
    {
        return $this->call('zDeleteRangeByScore', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zRemRangeByRank($key, $start, $end)
    {
        return $this->call('zRemRangeByRank', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zDeleteRangeByRank($key, $start, $end)
    {
        return $this->call('zDeleteRangeByRank', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zCard($key)
    {
        return $this->call('zCard', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zSize($key)
    {
        return $this->call('zSize', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zScore($key, $member)
    {
        return $this->call('zScore', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zRank($key, $member)
    {
        return $this->call('zRank', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zRevRank($key, $member)
    {
        return $this->call('zRevRank', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zIncrBy($key, $value, $member)
    {
        return $this->call('zIncrBy', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zUnion($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM')
    {
        return $this->call('zUnion', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zInter($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM')
    {
        return $this->call('zInter', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function zScan($key, &$iterator, $pattern = null, $count = null)
    {
        return $this->call('zScan', array($key, &$iterator, $pattern, $count));
    }

    /**
     * {@inheritdoc}
     */
    public function hSet($key, $member, $value)
    {
        return $this->call('hSet', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function hSetNx($key, $member, $value)
    {
        return $this->call('hSetNx', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function hGet($key, $member)
    {
        return $this->call('hGet', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function hLen($key)
    {
        return $this->call('hLen', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function hDel($key, $member, ...$other_members)
    {
        return $this->call('hDel', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function hKeys($key)
    {
        return $this->call('hKeys', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function hVals($key)
    {
        return $this->call('hVals', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function hGetAll($key)
    {
        return $this->call('hGetAll', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function hExists($key, $member)
    {
        return $this->call('hExists', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function hIncrBy($key, $member, $value)
    {
        return $this->call('hIncrBy', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function hIncrByFloat($key, $member, $value)
    {
        return $this->call('hIncrByFloat', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function hMset($key, array $pairs)
    {
        return $this->call('hMset', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function hMget($key, array $keys)
    {
        return $this->call('hMGet', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function hScan($key, &$iterator, $pattern = null, $count = null)
    {
        return $this->call('hScan', array($key, &$iterator, $pattern, $count));
    }

    /**
     * {@inheritdoc}
     */
    public function config($cmd, $key, $value = NULL)
    {
        return $this->call('config', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate($script, $args = array(), $numKeys = 0)
    {
        return $this->call('evaluate', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function evalSha($script_sha, $args = NULL, $num_keys = NULL)
    {
        return $this->call('evalSha', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function evaluateSha($scriptSha, $args = array(), $numKeys = 0)
    {
        return $this->call('evaluateSha', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function script($cmd, ...$args)
    {
        return $this->call('script', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function getLastError()
    {
        return $this->call('getLastError', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function clearLastError()
    {
        return $this->call('clearLastError', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function dump($key)
    {
        return $this->call('dump', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function restore($key, $ttl, $value)
    {
        return $this->call('restore', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function migrate($host, $port, $key, $db, $timeout, $copy = false, $replace = false)
    {
        return $this->call('migrate', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function time()
    {
        return $this->call('time', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function scan(&$iterator, $pattern = null, $count = null)
    {
        return $this->call('scan', array(&$iterator, $pattern, $count));
    }

    /**
     * {@inheritdoc}
     */
    public function pfAdd($key, array $elements)
    {
        return $this->call('pfAdd', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function pfCount($key)
    {
        return $this->call('pfCount', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function pfMerge($destkey, array $sourcekeys)
    {
        return $this->call('pfMerge', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function rawcommand($cmd, ...$args)
    {
        return $this->call('rawCommand', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function getMode()
    {
        return $this->call('getMode', func_get_args());
    }
}
