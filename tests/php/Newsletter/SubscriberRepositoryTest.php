<?php
/**
 * Created: 2026-09-25 12:30 CEST
 * Role: Unit test for Solar_Template\Newsletter\SubscriberRepository.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Verify subscription and de-duplication behaviour against an in-memory `$wpdb` double
 *          (no real WordPress/MySQL install available here).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Newsletter;

use PHPUnit\Framework\TestCase;
use Solar_Template\Newsletter\SubscriberRepository;

/**
 * @covers \Solar_Template\Newsletter\SubscriberRepository
 */
final class SubscriberRepositoryTest extends TestCase {

	/**
	 * A new email address is recorded and reported as subscribed afterwards.
	 *
	 * @return void
	 */
	public function test_subscribe_records_a_new_email_address(): void {
		$repository = new SubscriberRepository( new FakeWpdb() );

		$this->assertFalse( $repository->is_subscribed( 'jane@example.test' ) );
		$this->assertTrue( $repository->subscribe( 'jane@example.test' ) );
		$this->assertTrue( $repository->is_subscribed( 'jane@example.test' ) );
	}

	/**
	 * Subscribing the same email address twice returns false the second time, without erroring.
	 *
	 * @return void
	 */
	public function test_subscribe_returns_false_for_an_already_subscribed_email_address(): void {
		$repository = new SubscriberRepository( new FakeWpdb() );

		$this->assertTrue( $repository->subscribe( 'jane@example.test' ) );
		$this->assertFalse( $repository->subscribe( 'jane@example.test' ) );
	}
}
