<?php
/**
 * Web Kit - HTML Table Widget
 *
 * Repeater-driven comparison/data table rendered as plain HTML (no JS table
 * library). Columns are defined in one repeater (auto column count), rows
 * are defined in a second repeater with up to MAX_COLUMNS cell fields per
 * row - only the first N cells (N = number of columns) are ever rendered.
 *
 * @package Web_Kit
 */

namespace WebKit\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;

class Html_Table extends Widget_Base {

	/**
	 * Hard ceiling on how many cell fields we generate per row in the editor.
	 * Raise this if you need more than 10 columns; each unit adds several
	 * controls per row item (cell, link, color, bg) to the editor, so keep
	 * it sane.
	 */
	const MAX_COLUMNS = 10;

	public function get_name() {
		return 'wk-html-table';
	}

	public function get_title() {
		return __( 'Web Kit HTML Table', 'web-kit' );
	}

	public function get_icon() {
		return 'eicon-table';
	}

	public function get_categories() {
		return [ 'web-kit' ];
	}

	public function get_keywords() {
		return [ 'table', 'comparison', 'pricing table', 'repeater', 'data table' ];
	}

	/**
	 * Style handle registered in includes/class-wk-plugin.php via
	 * wp_register_style(). Elementor enqueues it only on pages/previews
	 * where this widget is actually used.
	 */
	public function get_style_depends() {
		return [ 'wk-table' ];
	}

	protected function register_controls() {

		/* =========================================================
		 * CONTENT TAB — COLUMNS
		 * ========================================================= */
		$this->start_controls_section(
			'section_columns',
			[
				'label' => __( 'Columns', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$columns_repeater = new Repeater();

		$columns_repeater->add_control(
			'column_label',
			[
				'label'       => __( 'Column Title', 'web-kit' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Column', 'web-kit' ),
				'label_block' => true,
			]
		);

		$columns_repeater->add_control(
			'column_align',
			[
				'label'   => __( 'Text Align', 'web-kit' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'   => [ 'title' => __( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => __( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => __( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
				],
				'default' => 'center',
				'toggle'  => false,
			]
		);

		$columns_repeater->add_control(
			'column_width',
			[
				'label'       => __( 'Width (%)', 'web-kit' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 100,
				'description' => __( 'Leave at 0 for auto/equal width.', 'web-kit' ),
			]
		);

		$this->add_control(
			'columns',
			[
				'label'       => __( 'Table Columns', 'web-kit' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $columns_repeater->get_controls(),
				'default'     => [
					[ 'column_label' => __( 'Loan Type', 'web-kit' ), 'column_align' => 'left' ],
					[ 'column_label' => __( 'Best For', 'web-kit' ) ],
					[ 'column_label' => __( 'Loan Amount', 'web-kit' ) ],
					[ 'column_label' => __( 'Term', 'web-kit' ) ],
					[ 'column_label' => __( 'Funding Speed', 'web-kit' ) ],
					[ 'column_label' => __( 'Min. FICO', 'web-kit' ) ],
				],
				'title_field' => '{{{ column_label }}}',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * CONTENT TAB — ROWS
		 * ========================================================= */
		$this->start_controls_section(
			'section_rows',
			[
				'label' => __( 'Rows', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$rows_repeater = new Repeater();

		for ( $i = 1; $i <= self::MAX_COLUMNS; $i++ ) {

			$rows_repeater->add_control(
				"cell_{$i}",
				[
					/* translators: %d: cell/column number */
					'label'       => sprintf( __( 'Cell %d', 'web-kit' ), $i ),
					'type'        => Controls_Manager::TEXTAREA,
					'rows'        => 2,
					'label_block' => true,
					'description' => 1 === $i ? __( 'Basic HTML allowed: <strong>, <br>, <em>.', 'web-kit' ) : '',
				]
			);

			$rows_repeater->add_control(
				"cell_{$i}_link",
				[
					/* translators: %d: cell/column number */
					'label'       => sprintf( __( 'Cell %d Link (optional)', 'web-kit' ), $i ),
					'type'        => Controls_Manager::URL,
					'placeholder' => 'https://your-link.com',
					'label_block' => true,
					'dynamic'     => [ 'active' => true ],
				]
			);

			$rows_repeater->add_control(
				"cell_{$i}_color",
				[
					/* translators: %d: cell/column number */
					'label'       => sprintf( __( 'Cell %d Text Color (optional)', 'web-kit' ), $i ),
					'type'        => Controls_Manager::COLOR,
					'description' => __( 'Overrides the Body/Accent color from the Style tab for this cell only.', 'web-kit' ),
				]
			);

			$rows_repeater->add_control(
				"cell_{$i}_bg",
				[
					/* translators: %d: cell/column number */
					'label'       => sprintf( __( 'Cell %d Background Color (optional)', 'web-kit' ), $i ),
					'type'        => Controls_Manager::COLOR,
					'description' => __( 'Overrides the Row/Alternate Row background from the Style tab for this cell only.', 'web-kit' ),
				]
			);
		}

		$this->add_control(
			'rows',
			[
				'label'       => __( 'Table Rows', 'web-kit' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rows_repeater->get_controls(),
				'default'     => [
					[
						'cell_1' => 'Short-Term Business Loan',
						'cell_2' => 'Bridging cash gaps, urgent inventory purchases',
						'cell_3' => '$10K – $500K',
						'cell_4' => '3 – 24 months',
						'cell_5' => '24–48 hours',
						'cell_6' => '650+',
					],
				],
				'title_field' => '{{{ cell_1 }}}',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * CONTENT TAB — TABLE SETTINGS
		 * ========================================================= */
		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Table Settings', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'column_count_mode',
			[
				'label'   => __( 'Column Count', 'web-kit' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'auto'   => __( 'Auto (from Columns repeater)', 'web-kit' ),
					'manual' => __( 'Manual override', 'web-kit' ),
				],
				'default' => 'auto',
			]
		);

		$this->add_control(
			'manual_column_count',
			[
				'label'     => __( 'Number of Columns', 'web-kit' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => self::MAX_COLUMNS,
				'default'   => 6,
				'condition' => [ 'column_count_mode' => 'manual' ],
			]
		);

		$this->add_control(
			'responsive_scroll',
			[
				'label'        => __( 'Horizontal Scroll on Mobile', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'sticky_header',
			[
				'label'        => __( 'Sticky Header Row', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'first_col_accent',
			[
				'label'        => __( 'Accent-Style First Column', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'Matches the reference design: bold, colored text in the left-most column.', 'web-kit' ),
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — HEADER
		 * ========================================================= */
		$this->start_controls_section(
			'section_style_header',
			[
				'label' => __( 'Header Row', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'header_bg_color',
			[
				'label'     => __( 'Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1F3A5F',
				'selectors' => [ '{{WRAPPER}} .wk-table thead th' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'header_text_color',
			[
				'label'     => __( 'Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [ '{{WRAPPER}} .wk-table thead th' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'header_typography',
				'selector' => '{{WRAPPER}} .wk-table thead th',
			]
		);

		$this->add_responsive_control(
			'header_padding',
			[
				'label'      => __( 'Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-table thead th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — BODY
		 * ========================================================= */
		$this->start_controls_section(
			'section_style_body',
			[
				'label' => __( 'Body', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'body_bg_color',
			[
				'label'     => __( 'Row Background', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [ '{{WRAPPER}} .wk-table tbody td' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'body_alt_bg_color',
			[
				'label'     => __( 'Alternate Row Background', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F7F9FC',
				'selectors' => [ '{{WRAPPER}} .wk-table tbody tr:nth-child(even) td' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'body_text_color',
			[
				'label'     => __( 'Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1F3A5F',
				'selectors' => [ '{{WRAPPER}} .wk-table tbody td' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'accent_color',
			[
				'label'       => __( 'Accent Color', 'web-kit' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '#2E6FE0',
				'description' => __( 'Used for the accented first column and for linked cell values.', 'web-kit' ),
				'selectors'   => [
					'{{WRAPPER}} .wk-table tbody td.wk-first-col' => 'color: {{VALUE}};',
					'{{WRAPPER}} .wk-table tbody td a'            => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'body_typography',
				'selector' => '{{WRAPPER}} .wk-table tbody td',
			]
		);

		$this->add_responsive_control(
			'body_padding',
			[
				'label'      => __( 'Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-table tbody td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — BORDERS / CONTAINER
		 * ========================================================= */
		$this->start_controls_section(
			'section_style_border',
			[
				'label' => __( 'Borders & Container', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'border_color',
			[
				'label'     => __( 'Border Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E5E9F0',
				'selectors' => [
					'{{WRAPPER}} .wk-table'    => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .wk-table td' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'border_width',
			[
				'label'     => __( 'Border Width', 'web-kit' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 5 ] ],
				'default'   => [ 'size' => 1, 'unit' => 'px' ],
				'selectors' => [
					'{{WRAPPER}} .wk-table'    => 'border-width: {{SIZE}}{{UNIT}}; border-style: solid;',
					'{{WRAPPER}} .wk-table td' => 'border-right-width: {{SIZE}}{{UNIT}}; border-bottom-width: {{SIZE}}{{UNIT}}; border-style: solid;',
				],
			]
		);

		$this->add_control(
			'table_radius',
			[
				'label'     => __( 'Corner Radius', 'web-kit' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'selectors' => [
					'{{WRAPPER}} .wk-table-wrap' => 'border-radius: {{SIZE}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Resolve how many columns to actually render.
	 */
	private function get_column_count( array $settings, array $columns ) {
		if ( 'manual' === $settings['column_count_mode'] ) {
			return max( 1, (int) $settings['manual_column_count'] );
		}
		return count( $columns );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$columns  = ! empty( $settings['columns'] ) ? $settings['columns'] : [];
		$rows     = ! empty( $settings['rows'] ) ? $settings['rows'] : [];

		$column_count = $this->get_column_count( $settings, $columns );

		if ( $column_count < 1 || empty( $rows ) ) {
			return;
		}

		$wrap_classes = [ 'wk-table-wrap' ];
		if ( 'yes' === $settings['responsive_scroll'] ) {
			$wrap_classes[] = 'wk-scroll';
		}

		$table_classes = [ 'wk-table' ];
		if ( 'yes' === $settings['sticky_header'] ) {
			$table_classes[] = 'wk-sticky-head';
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', $wrap_classes ) ); ?>">
			<table class="<?php echo esc_attr( implode( ' ', $table_classes ) ); ?>">
				<?php if ( ! empty( $columns ) ) : ?>
					<thead>
						<tr>
							<?php
							foreach ( $columns as $index => $col ) :
								if ( $index >= $column_count ) {
									break;
								}
								$align       = ! empty( $col['column_align'] ) ? $col['column_align'] : 'center';
								$width_style = ! empty( $col['column_width'] ) ? ' width:' . (float) $col['column_width'] . '%;' : '';
								?>
								<th style="text-align:<?php echo esc_attr( $align ); ?>;<?php echo esc_attr( $width_style ); ?>">
									<?php echo esc_html( $col['column_label'] ); ?>
								</th>
							<?php endforeach; ?>
						</tr>
					</thead>
				<?php endif; ?>
				<tbody>
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<?php
							for ( $i = 1; $i <= $column_count; $i++ ) :
								$cell_key  = "cell_{$i}";
								$link_key  = "cell_{$i}_link";
								$color_key = "cell_{$i}_color";
								$bg_key    = "cell_{$i}_bg";
								$content   = isset( $row[ $cell_key ] ) ? $row[ $cell_key ] : '';
								$align     = isset( $columns[ $i - 1 ]['column_align'] ) ? $columns[ $i - 1 ]['column_align'] : 'center';
								$color     = ! empty( $row[ $color_key ] ) ? $row[ $color_key ] : '';
								$bg        = ! empty( $row[ $bg_key ] ) ? $row[ $bg_key ] : '';

								$cell_classes = [];
								if ( 1 === $i && 'yes' === $settings['first_col_accent'] ) {
									$cell_classes[] = 'wk-first-col';
								}

								$cell_style = 'text-align:' . $align . ';';
								if ( $color ) {
									// Cascades to the cell's <a> too (see CSS: .wk-table tbody td a inherits color unless overridden).
									$cell_style .= 'color:' . $color . ';';
								}
								if ( $bg ) {
									// Inline background wins over the stylesheet's row/alt-row background rules for this one cell.
									$cell_style .= 'background-color:' . $bg . ';';
								}
								?>
								<td
									class="<?php echo esc_attr( implode( ' ', $cell_classes ) ); ?>"
									style="<?php echo esc_attr( $cell_style ); ?>"
								>
									<?php
									$inner = wp_kses_post( $content );
									if ( ! empty( $row[ $link_key ]['url'] ) ) {
										// Inline color (if set) beats the stylesheet's accent-color rule on
										// "td a", since that rule targets the anchor directly.
										$link_style = $color ? ' style="color:' . esc_attr( $color ) . ';"' : '';
										printf(
											'<a href="%1$s"%2$s%3$s>%4$s</a>',
											esc_url( $row[ $link_key ]['url'] ),
											! empty( $row[ $link_key ]['is_external'] ) ? ' target="_blank" rel="noopener"' : '',
											$link_style, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_attr() above.
											$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already passed through wp_kses_post().
										);
									} else {
										echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already passed through wp_kses_post().
									}
									?>
								</td>
							<?php endfor; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
