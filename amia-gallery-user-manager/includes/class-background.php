<?php
/**
 * Lightweight background job queue.
 *
 * @package AMIA_Gallery_User_Manager
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
class AGUM_Background {
	public static function enqueue( $type, $payload = array() ) {
		global $wpdb;
		$wpdb->insert( AGUM_DB::jobs_table(), array( 'job_type' => sanitize_key( $type ), 'status' => 'pending', 'payload' => wp_json_encode( $payload ), 'created_at' => current_time( 'mysql' ) ), array( '%s','%s','%s','%s' ) );
		if ( ! wp_next_scheduled( 'agum_process_jobs' ) ) { wp_schedule_single_event( time() + 30, 'agum_process_jobs' ); }
		return (int) $wpdb->insert_id;
	}
	public static function init() { add_action( 'agum_process_jobs', array( __CLASS__, 'process' ) ); }
	public static function process( $limit = 5 ) {
		global $wpdb; $table = AGUM_DB::jobs_table();
		$jobs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s ORDER BY created_at ASC LIMIT %d", 'pending', absint( $limit ) ) );
		foreach ( $jobs as $job ) {
			$wpdb->update( $table, array( 'status' => 'running', 'started_at' => current_time( 'mysql' ), 'attempts' => (int) $job->attempts + 1 ), array( 'id' => $job->id ), array( '%s','%s','%d' ), array( '%d' ) );
			AGUM_Logger::log( 'background_job', 'Processed ' . $job->job_type, null, json_decode( $job->payload, true ) ?: array() );
			$wpdb->update( $table, array( 'status' => 'complete', 'completed_at' => current_time( 'mysql' ) ), array( 'id' => $job->id ), array( '%s','%s' ), array( '%d' ) );
		}
	}
	public static function cleanup() {
		global $wpdb; $table = AGUM_DB::jobs_table();
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE status = %s AND completed_at < %s", 'complete', gmdate( 'Y-m-d H:i:s', time() - WEEK_IN_SECONDS ) ) );
	}
}
