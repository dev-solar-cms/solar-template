<?php
/**
 * Created: 2026-09-28 10:15 CEST
 * Role: Minimal WC_Product stand-in for the theme's PHP unit tests.
 * Author: David ROMERA <d.romera.11@gmail.com>
 * Purpose: Same spirit as bootstrap.php's own WordPress function stand-ins — a plain, mutable data
 *          holder covering only the methods actually called by the Product-domain classes under test
 *          (never a full WooCommerce install here). Real functional behaviour against a live
 *          WooCommerce install is verified manually in Docker for every step (see RELEASE.md).
 *
 * @package Solar_Template
 */

if ( class_exists( 'WC_Product', false ) ) {
	return;
}

/**
 * Minimal stand-in for WooCommerce's WC_Product.
 */
class WC_Product {

	/**
	 * @var int
	 */
	protected int $id;

	/**
	 * @var float|string
	 */
	protected $price = 0;

	/**
	 * @var bool
	 */
	protected bool $in_stock = true;

	/**
	 * @var array<string, mixed>
	 */
	protected array $meta = array();

	/**
	 * @var int
	 */
	protected int $image_id = 0;

	/**
	 * @var float|string
	 */
	protected $regular_price = '';

	/**
	 * @var bool
	 */
	protected bool $on_sale = false;

	/**
	 * @var string
	 */
	protected string $name = '';

	/**
	 * @var bool
	 */
	protected bool $visible = true;

	/**
	 * @param int $id Product ID.
	 */
	public function __construct( int $id = 0 ) {
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * @return float|string
	 */
	public function get_price() {
		return $this->price;
	}

	/**
	 * @param float|string $price New price.
	 * @return void
	 */
	public function set_price( $price ): void {
		$this->price = $price;
	}

	/**
	 * @return bool
	 */
	public function is_in_stock(): bool {
		return $this->in_stock;
	}

	/**
	 * @return bool
	 */
	public function is_purchasable(): bool {
		return true;
	}

	/**
	 * @return bool
	 */
	public function is_visible(): bool {
		return $this->visible;
	}

	/**
	 * @param string $key    Meta key.
	 * @param bool   $single Ignored (this stand-in only ever stores a single value per key).
	 * @return mixed
	 */
	public function get_meta( string $key, bool $single = true ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- stand-in mirrors the real method's signature.
		return $this->meta[ $key ] ?? '';
	}

	/**
	 * @param string $key   Meta key.
	 * @param mixed  $value Meta value.
	 * @return void
	 */
	public function update_meta_data( string $key, $value ): void {
		$this->meta[ $key ] = $value;
	}

	/**
	 * @return void
	 */
	public function save(): void {}

	/**
	 * @return int
	 */
	public function get_image_id(): int {
		return $this->image_id;
	}

	/**
	 * @return float|string
	 */
	public function get_regular_price() {
		return $this->regular_price;
	}

	/**
	 * @return bool
	 */
	public function is_on_sale(): bool {
		return $this->on_sale;
	}

	/**
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Test-only helper — not part of the real WC_Product API.
	 *
	 * @param float|string $price Price to set directly (bypasses set_price(), used in fixture setup).
	 * @return void
	 */
	public function solar_template_test_set_price( $price ): void {
		$this->price = $price;
	}

	/**
	 * Test-only helper — not part of the real WC_Product API.
	 *
	 * @param int $image_id Featured image attachment ID.
	 * @return void
	 */
	public function solar_template_test_set_image_id( int $image_id ): void {
		$this->image_id = $image_id;
	}

	/**
	 * Test-only helper — not part of the real WC_Product API.
	 *
	 * @param float|string $regular_price Regular (pre-sale) price.
	 * @return void
	 */
	public function solar_template_test_set_regular_price( $regular_price ): void {
		$this->regular_price = $regular_price;
	}

	/**
	 * Test-only helper — not part of the real WC_Product API.
	 *
	 * @param bool $on_sale Whether the product is on sale.
	 * @return void
	 */
	public function solar_template_test_set_on_sale( bool $on_sale ): void {
		$this->on_sale = $on_sale;
	}

	/**
	 * Test-only helper — not part of the real WC_Product API.
	 *
	 * @param string $name Product name.
	 * @return void
	 */
	public function solar_template_test_set_name( string $name ): void {
		$this->name = $name;
	}

	/**
	 * Test-only helper — not part of the real WC_Product API.
	 *
	 * @param bool $visible Catalog visibility state.
	 * @return void
	 */
	public function solar_template_test_set_visible( bool $visible ): void {
		$this->visible = $visible;
	}

	/**
	 * Test-only helper — not part of the real WC_Product API.
	 *
	 * @param bool $in_stock Stock state to set directly.
	 * @return void
	 */
	public function solar_template_test_set_in_stock( bool $in_stock ): void {
		$this->in_stock = $in_stock;
	}

	/**
	 * Test-only helper — not part of the real WC_Product API.
	 *
	 * @param string $key   Meta key.
	 * @param mixed  $value Meta value.
	 * @return void
	 */
	public function solar_template_test_set_meta( string $key, $value ): void {
		$this->meta[ $key ] = $value;
	}
}
