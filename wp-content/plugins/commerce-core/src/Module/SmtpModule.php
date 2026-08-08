<?php
/**
 * SMTP Module — configure WordPress mail to use SMTP via env vars.
 *
 * Reads SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASSWORD, SMTP_FROM_EMAIL,
 * SMTP_FROM_NAME from environment variables. If SMTP_HOST is not set,
 * the module does nothing (WordPress falls back to PHP mail()).
 *
 * @package CommerceMaster\Core\Module
 */

declare(strict_types=1);

namespace CommerceMaster\Core\Module;

class SmtpModule implements ModuleInterface {

	public function register(): void {
		// No early hooks needed.
	}

	public function boot(): void {
		// Only configure if SMTP_HOST is set.
		$host = getenv('SMTP_HOST' );
		if ( ! $host || '' === $host ) {
			return;
		}

		add_action('phpmailer_init', array($this, 'configure_phpmailer' ) );
		add_filter('wp_mail_from', array($this, 'filter_from_email' ) );
		add_filter('wp_mail_from_name', array($this, 'filter_from_name' ) );
	}

	public function activate(): void {
		// No activation tasks.
	}

	public function get_id(): string {
		return 'smtp';
	}

	/**
	 * Configure PHPMailer to use SMTP.
	 *
	 * @param \PHPMailer $phpmailer PHPMailer instance passed by reference.
	 */
	public function configure_phpmailer( $phpmailer ): void {
		$host     = getenv('SMTP_HOST' );
		$port     = getenv('SMTP_PORT' ) ?: '587';
		$user     = getenv('SMTP_USER' );
		$password = getenv('SMTP_PASSWORD' );

		if ( ! $host || ! $user || ! $password ) {
			return;
		}

		$phpmailer->isSMTP();
		$phpmailer->Host     = $host;
		$phpmailer->Port     = (int) $port;
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = $user;
		$phpmailer->Password = $password;

		// Port 465 = SSL, port 587/25 = TLS.
		$phpmailer->SMTPSecure = 465 === (int) $port ? 'ssl' : 'tls';
	}

	/**
	 * Filter the from email address.
	 *
	 * @param string $email Default email from WordPress.
	 * @return string
	 */
	public function filter_from_email( $email ): string {
		$from = getenv('SMTP_FROM_EMAIL' );
		if ( $from && '' !== $from ) {
			return $from;
		}
		return $email;
	}

	/**
	 * Filter the from name.
	 *
	 * @param string $name Default name from WordPress.
	 * @return string
	 */
	public function filter_from_name( $name ): string {
		$from_name = getenv('SMTP_FROM_NAME' );
		if ( $from_name && '' !== $from_name ) {
			return $from_name;
		}
		return $name;
	}
}
