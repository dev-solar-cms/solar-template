<?php
/**
 * Created: 2026-09-26 09:40 CEST
 * Role: Unit test for Solar_Template\Checkout\StepIndicator.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Assert the done/active/future state computed for each of the tunnel's 5 steps, for every
 *          possible current step, plus the defensive fallback for an unknown step value.
 *
 * @package Solar_Template
 */

namespace Solar_Template\Tests\Checkout;

use PHPUnit\Framework\TestCase;
use Solar_Template\Checkout\StepIndicator;

final class StepIndicatorTest extends TestCase {

	/**
	 * Every step before the current one is done, the current one is active, every step after it is
	 * future.
	 *
	 * @return void
	 */
	public function test_marks_steps_relative_to_the_current_one(): void {
		$steps = StepIndicator::steps( 'shipping' );

		$this->assertSame(
			array( 'cart', 'login', 'shipping', 'payment', 'confirmation' ),
			array_column( $steps, 'key' )
		);
		$this->assertSame( array( 1, 2, 3, 4, 5 ), array_column( $steps, 'number' ) );
		$this->assertSame(
			array( 'done', 'done', 'active', 'future', 'future' ),
			array_column( $steps, 'state' )
		);
	}

	/**
	 * The very first step has no step marked done.
	 *
	 * @return void
	 */
	public function test_first_step_has_no_done_step(): void {
		$steps = StepIndicator::steps( 'cart' );

		$this->assertSame(
			array( 'active', 'future', 'future', 'future', 'future' ),
			array_column( $steps, 'state' )
		);
	}

	/**
	 * The very last step marks every previous step done.
	 *
	 * @return void
	 */
	public function test_last_step_marks_every_previous_step_done(): void {
		$steps = StepIndicator::steps( 'confirmation' );

		$this->assertSame(
			array( 'done', 'done', 'done', 'done', 'active' ),
			array_column( $steps, 'state' )
		);
	}

	/**
	 * An unrecognized step value degrades to every step being future rather than raising an error.
	 *
	 * @return void
	 */
	public function test_unknown_step_falls_back_to_every_step_future(): void {
		$steps = StepIndicator::steps( 'not-a-real-step' );

		$this->assertSame(
			array( 'future', 'future', 'future', 'future', 'future' ),
			array_column( $steps, 'state' )
		);
	}
}
