<?php
/**
 * Created: 2026-09-26 19:20 CEST
 * Role: Unit test for Solar_Template\Account\SupportRequestRepository.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Verify create/lookup/count behaviour against an in-memory `$wpdb` double (no real
 *          WordPress/MySQL install available here).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Account;

use PHPUnit\Framework\TestCase;
use Solar_Template\Account\SupportRequestRepository;

/**
 * @covers \Solar_Template\Account\SupportRequestRepository
 */
final class SupportRequestRepositoryTest extends TestCase {

	/**
	 * A newly created request is returned by for_user(), newest first, and starts "open".
	 *
	 * @return void
	 */
	public function test_create_and_list_for_user(): void {
		$repository = new SupportRequestRepository( new FakeWpdb() );

		$repository->create(
			array(
				'user_id'        => 1,
				'order_id'       => 42,
				'request_type'   => 'defective',
				'subject'        => 'Broken item',
				'message'        => 'The item arrived broken.',
				'attachment_url' => '',
			)
		);
		$repository->create(
			array(
				'user_id'        => 1,
				'order_id'       => null,
				'request_type'   => 'other',
				'subject'        => 'Question',
				'message'        => 'Just a question.',
				'attachment_url' => '',
			)
		);

		$requests = $repository->for_user( 1 );

		$this->assertCount( 2, $requests );
		$this->assertSame( 'Question', $requests[0]->subject );
		$this->assertSame( 'open', $requests[0]->status );
	}

	/**
	 * active_count_for() only counts the given user's own open requests.
	 *
	 * @return void
	 */
	public function test_active_count_is_scoped_to_the_user(): void {
		$repository = new SupportRequestRepository( new FakeWpdb() );

		$repository->create(
			array(
				'user_id'        => 1,
				'order_id'       => null,
				'request_type'   => 'other',
				'subject'        => 'A',
				'message'        => 'A',
				'attachment_url' => '',
			)
		);
		$repository->create(
			array(
				'user_id'        => 2,
				'order_id'       => null,
				'request_type'   => 'other',
				'subject'        => 'B',
				'message'        => 'B',
				'attachment_url' => '',
			)
		);

		$this->assertSame( 1, $repository->active_count_for( 1 ) );
		$this->assertSame( 1, $repository->active_count_for( 2 ) );
		$this->assertSame( 0, $repository->active_count_for( 3 ) );
	}
}
