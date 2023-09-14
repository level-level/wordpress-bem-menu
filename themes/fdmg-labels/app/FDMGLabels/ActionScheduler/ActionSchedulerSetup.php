<?php

namespace FDMGLabels\ActionScheduler;

use ActionScheduler;

class ActionSchedulerSetup {

	public const GROUP = 'fdmg_labels';

	public function register_hooks(): void {

		// The hook must occur before 'plugins_loaded', or you can use 'plugins_loaded' with negative priority, like -10.
		// see: https://actionscheduler.org/usage/#load-order
		add_action( 'plugins_loaded', array( $this, 'require_plugin_file' ), -10 );

		// ActionScheduler_QueueRunner::init() is attached to 'init' with priority 1, so we need to run after that
		add_action( 'init', array( $this, 'disable_default_as_runner' ) );

		// Filter Action Scheduler batch size. Set LL_AS_QUEUE_BATCH_SIZE in wp-config.php to overwrite.
		add_filter( 'action_scheduler_queue_runner_batch_size', array( $this, 'increase_queue_batch_size' ) );

		// Filter Action Scheduler concurent batches. Set LL_AS_QUEUE_CONCURRENT_BATCHES in wp-config.php to overwrite.
		add_filter( 'action_scheduler_queue_runner_concurrent_batches', array( $this, 'increase_concurrent_batches' ) );

		// Filter Action Scheduler timeout period. Set LL_AS_TIMEOUT in wp-config.php to overwrite.
		add_filter( 'action_scheduler_timeout_period', array( $this, 'increase_timeout_period' ) );

		// Filter Action Scheduler failure period. Set LL_AS_FAILURE_PERIOD in wp-config.php to overwrite.
		add_filter( 'action_scheduler_failure_period', array( $this, 'increase_failure_period' ) );

		// Filter Action Scheduler queue runner time limit. Set LL_AS_TIME_LIMIT in wp-config.php to overwrite.
		add_filter( 'action_scheduler_queue_runner_time_limit', array( $this, 'increase_time_limit' ) );

	}

	public function require_plugin_file(): void {
		if ( ! empty( $this->get_plugin_file() ) && file_exists( $this->get_plugin_file() ) ) {
			require_once $this->get_plugin_file();
		}
	}

	public function get_plugin_file(): ?string {
		if ( defined( 'WP_PLUGIN_DIR' ) ) {
			return WP_PLUGIN_DIR . '/action-scheduler/action-scheduler.php';
		}
		return null;
	}

	/**
	 * Disable the default WP cron scheduler for Action Scheduler
	 * This means Action Scheduler should always be started by running a CLI command
	 * This can be done by adding the command `wp action-scheduler run` to a crontab every x minutes
	 */
	public function disable_default_as_runner(): void {
		if ( class_exists( 'ActionScheduler' ) ) {
			remove_action( 'action_scheduler_run_queue', array( ActionScheduler::runner(), 'run' ) );
		}
	}

	/**
	 * Action scheduler claims a batch of actions to process in each request. It keeps the batch
	 * fairly small (by default, 25) in order to prevent errors, like memory exhaustion.
	 *
	 * This method increases it so that more actions are processed in each queue, which speeds up the
	 * overall queue processing time due to latency in requests and the minimum 1 minute between each
	 * queue being processed.
	 *
	 * For more details, see: https://actionscheduler.org/perf/#increasing-batch-size
	 */
	public function increase_queue_batch_size( int $batch_size ): int {
		if ( ! defined( 'LL_AS_QUEUE_BATCH_SIZE' ) ) {
			return $batch_size;
		}
		return constant( 'LL_AS_QUEUE_BATCH_SIZE' );
	}

	/**
	 * Action scheduler processes queues of actions in parallel to speed up the processing of large numbers
	 * If each queue takes a long time, this will result in multiple PHP processes being used to process actions,
	 * which can prevent PHP processes being available to serve requests from visitors. This is why it defaults to
	 * only 5. However, on high volume sites, this can be increased to speed up the processing time for actions.
	 *
	 * This method increases the default so that more queues can be processed concurrently. Use with caution as doing
	 * this can take down your site completely depending on your PHP configuration.
	 *
	 * For more details, see: https://actionscheduler.org/perf/#increasing-concurrent-batches
	 */
	public function increase_concurrent_batches( int $concurrent_batches ): int {
		if ( ! defined( 'LL_AS_QUEUE_CONCURRENT_BATCHES' ) ) {
			return $concurrent_batches;
		}
		return constant( 'LL_AS_QUEUE_CONCURRENT_BATCHES' );
	}


	/**
	 * Action scheduler reset actions claimed for more than 5 minutes. If needed this can be increased here
	 */
	public function increase_timeout_period( int $timeout ): int {
		if ( ! defined( 'LL_AS_TIMEOUT' ) ) {
			return $timeout;
		}
		return constant( 'LL_AS_TIMEOUT' );
	}

	/**
	 * Action scheduler reset actions claimed for more than 5 minutes. If needed this can be increased here
	 */
	public function increase_failure_period( int $timeout ): int {
		if ( ! defined( 'LL_AS_FAILURE_PERIOD' ) ) {
			return $timeout;
		}
		return constant( 'LL_AS_FAILURE_PERIOD' );
	}

	/**
	 * Action Scheduler provides a default maximum of 30 seconds in which to process actions. Increase this to 120
	 * seconds for hosts like Pantheon which support such a long time limit, or if you know your PHP and Apache, Nginx
	 * or other web server configs support a longer time limit.
	 *
	 * Note, WP Engine only supports a maximum of 60 seconds - if using WP Engine, this will need to be decreased to 60.
	 */
	public function increase_time_limit( int $time_limit ): int {
		if ( ! defined( 'LL_AS_TIME_LIMIT' ) ) {
			return $time_limit;
		}
		return constant( 'LL_AS_TIME_LIMIT' );
	}

}

