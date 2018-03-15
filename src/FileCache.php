<?php

namespace rdx\librarything;

use Exception;

class FileCache {

	public $dir = '';
	public $ttl = 0;

	/**
	 * Dependency constructor
	 */
	public function __construct( $dir, $ttl ) {
		$this->dir = rtrim($dir, '\\/');
		$this->ttl = $ttl;

		@mkdir($dir);
		@chmod($dir, 0777);
	}

	/**
	 *
	 */
	public function retrieve( $name, callable $callback ) {
		$file = $this->getFile($name);

		// Must get live data
		if ( !file_exists($file) ) {
			$data = $callback();
			$this->store($name, $data);
			return $data;
		}

		// Try to get live data, or fall back to old cache
		if ( filemtime($file) + $this->ttl < time() ) {
			try {
				$data = $callback();
				$this->store($name, $data);
				return $data;
			}
			catch ( Exception $ex ) {
				return $this->decode($file);
			}
		}

		// Use cached data
		return $this->decode($file);
	}

	/**
	 *
	 */
	public function store( $name, $data, $touch = true ) {
		$file = $this->getFile($name);

		return $this->encode($file, $data, $touch);
	}

	/**
	 *
	 */
	protected function decode( $file ) {
		return unserialize(file_get_contents($file));
	}

	/**
	 *
	 */
	protected function encode( $file, $data, $touch = true ) {
		$mtime = file_exists($file) ? filemtime($file) : 0;

		@touch($file);
		@chmod($file, 0666);

		$put = file_put_contents($file, serialize($data));

		if ( $mtime && !$touch ) {
			touch($file, $mtime);
		}

		return $put;
	}

	/**
	 *
	 */
	protected function getFile( $name ) {
		return $this->dir . '/' . preg_replace('#[^\w\-]#i', '', $name) . '.bin';
	}

}
