<?php

namespace Queue;

class QueueException extends \FuelException {}

class Queue
{

	/**
	 * array of loaded instances
	 */
	protected static $_instances = array();

	/**
	 * Default config
	 * @var array
	 */
	protected static $_defaults = array(
		'driver'   => 'resque',
		'redis'    => '127.0.0.1:6379',
		'workers'  => 2,
		'interval' => 5,
		'blocking' => false,
		'prefix'   => 'fuel',
		'db'       => 0
	);

	/**
	 * Queue driver forge.
	 *
	 * @param	string			$queue		Queue name
	 * @param	mixed			$config		Extra config array or the driver
	 * @return  Queue instance
	 */
	public static function forge($queue = 'default', $config = array())
	{
		! is_array($config) && $config = array('driver' => $config);

		$config = \Arr::merge(static::$_defaults, \Config::get('queue', array()), $config);

		$class = '\\Queue\\Queue_' . ucfirst($config['driver']);

		if( ! class_exists($class, true))
		{
			throw new \FuelException('Could not find Queue driver: ' . $config['driver']);
		}

		$driver = new $class($queue, $config);

		static::$_instances[$queue] =& $driver;

		return static::$_instances[$queue];
	}

	/**
	 * Return a specific driver, or the default instance (is created if necessary)
	 *
	 * @param   string  $instance
	 * @return  Queue instance
	 */
	public static function instance($instance = 'default')
	{
		if ( ! array_key_exists($instance, static::$_instances))
		{
			return static::forge($instance);
		}

		return static::$_instances[$instance];
	}

	public static function enqueue($job, $args = null, $queue = 'default')
	{
		static::instance($queue)->enqueue($job, $args);
	}

}