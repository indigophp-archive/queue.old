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
	protected static $_defaults;

	/**
	 * Queue driver instance.
	 *
	 * @param	string			$queue		Queue name
	 * @param	mixed			$setup		Setup name or extra config
	 * @param	mixed			$config		Extra config array
	 * @return  Queue instance
	 */
	public static function instance($queue = 'default', $setup = null, $config = array())
	{
		if (array_key_exists($queue, static::$_instances))
		{
			return static::$_instances[$instance];
		}

		if(is_array($setup))
		{
			$config = \Arr::merge($setup, $config);
			$setup = null;
		}

		empty($setup) and $setup = \Config::get('queue.default_setup', 'default');
		is_string($setup) and $setup = \Config::get('queue.setups.'.$setup, array());

		$setup = \Arr::merge(static::$_defaults, $setup);
		$config = \Arr::merge($setup, $config);

		$class = '\\Queue\\Queue_' . ucfirst(strtolower($config['driver']));

		if( ! class_exists($class, true))
		{
			throw new \QueueException('Could not find Queue driver: ' . $config['driver']);
		}

		if(($config['restrict_queue'] === true && ! in_array($queue, $config['queue'])) && ! in_array('*', $config['queue']))
		{
			throw new \QueueException($queue . ' is not part of this setup.');
		}

		$driver = new $class($queue, $config);

		static::$_instances[$queue] =& $driver;

		return static::$_instances[$queue];
	}

	/**
	 * Init, config loading.
	 */
	public static function _init()
	{
		static::$_defaults = \Config::get('queue.defaults');
	}

	/**
	 * Push a job from static interface
	 * @param  string $job   Job name
	 * @param  array $args  Optional array of arguments
	 * @param  string $queue Optional queue name
	 * @return string        Job token
	 */
	public static function push($job, array $args = array(), $queue = 'default')
	{
		return static::instance($queue)->enqueue($job, $args);
	}

	/**
	 * class constructor
	 *
	 * @param	void
	 * @access	private
	 * @return	void
	 */
	final private function __construct() {}

}
