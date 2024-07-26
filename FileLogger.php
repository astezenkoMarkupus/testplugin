<?php

namespace TestPlugin;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class FileLogger implements LoggerInterface {
	private $logger;

	public function __construct( LoggerInterface $logger = null ) {
		$this->logger = $logger;
	}

	public function emergency( \Stringable|string $message, array $context = [] ): void {
		$this->log( LogLevel::EMERGENCY, $message, $context );
	}

	public function alert( \Stringable|string $message, array $context = [] ): void {
		$this->log( LogLevel::ALERT, $message, $context );
	}

	public function critical( \Stringable|string $message, array $context = [] ): void {
		$this->log( LogLevel::CRITICAL, $message, $context );
	}

	public function error( \Stringable|string $message, array $context = [] ): void {
		$this->log( LogLevel::ERROR, $message, $context );
	}

	public function warning( \Stringable|string $message, array $context = [] ): void {
		$this->log( LogLevel::WARNING, $message, $context );
	}

	public function notice( \Stringable|string $message, array $context = [] ): void {
		$this->log( LogLevel::NOTICE, $message, $context );
	}

	public function info( \Stringable|string $message, array $context = [] ): void {
		$this->log( LogLevel::INFO, $message, $context );
	}

	public function debug( \Stringable|string $message, array $context = [] ): void {
		$this->log( LogLevel::DEBUG, $message, $context );
	}

	public function log( $level, \Stringable|string $message, array $context = [] ): void {
		$dateFormatted = ( new \DateTime() )->format( 'd.m.Y H:i:s' );
		$message       = sprintf( '[%s] %s: %s%s', $dateFormatted, $level, $message, PHP_EOL );

		file_put_contents( plugin_dir_path( __DIR__ ) . 'testplugin/logs/errors.log', $message, FILE_APPEND );
	}
}
