<?php
/**
 * Created: 2026-09-26 09:00 CEST
 * Role: Sales tunnel step indicator data (Solar_Template\Checkout).
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Compute the ordered list of the tunnel's 5 steps (cart, login, shipping, payment,
 *          confirmation) with each one's done/active/future state relative to a given current
 *          step, for template-parts/checkout/step-indicator.php to render — the same data feeds
 *          every override template of the tunnel (cart.php, the checkout page's login/shipping/
 *          payment sections, thankyou.php).
 *
 * @package Solar_Template
 */

namespace Solar_Template\Checkout;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds the step indicator's view model.
 */
final class StepIndicator {

	/**
	 * Ordered step keys, matching the design handoff's 5-step tunnel.
	 *
	 * @var string[]
	 */
	private const STEPS = array( 'cart', 'login', 'shipping', 'payment', 'confirmation' );

	/**
	 * Returns every step's view model (key, number, label, state), in order.
	 *
	 * A step already passed is `done`, the step matching $current_step is `active`, every step after
	 * it is `future`. Falls back to treating every step as `future` (no active step) if $current_step
	 * is not one of self::STEPS, rather than raising an error over an unexpected value.
	 *
	 * @param string $current_step One of self::STEPS.
	 * @return array<int, array{key: string, number: int, label: string, state: string}>
	 */
	public static function steps( string $current_step ): array {
		$current_index = array_search( $current_step, self::STEPS, true );
		$labels        = self::labels();
		$steps         = array();

		foreach ( self::STEPS as $index => $key ) {
			if ( false === $current_index ) {
				$state = 'future';
			} elseif ( $index < $current_index ) {
				$state = 'done';
			} elseif ( $index === $current_index ) {
				$state = 'active';
			} else {
				$state = 'future';
			}

			$steps[] = array(
				'key'    => $key,
				'number' => $index + 1,
				'label'  => $labels[ $key ],
				'state'  => $state,
			);
		}

		return $steps;
	}

	/**
	 * Returns the translated label for each step key.
	 *
	 * @return array<string, string>
	 */
	private static function labels(): array {
		return array(
			'cart'         => __( 'Cart', 'solar-template' ),
			'login'        => __( 'Login', 'solar-template' ),
			'shipping'     => __( 'Shipping', 'solar-template' ),
			'payment'      => __( 'Payment', 'solar-template' ),
			'confirmation' => __( 'Confirmation', 'solar-template' ),
		);
	}
}
