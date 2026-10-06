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
		return esc_html__( 'Web Kit HTML Table', 'web-kit' );
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
			'wk_table_section_columns',
			[
				'label' => esc_html__( 'Columns', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$columns_repeater = new Repeater();

		$columns_repeater->add_control(
			'column_label',
			[
				'label'       => esc_html__( 'Column Title', 'web-kit' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Column', 'web-kit' ),
				'label_block' => true,
			]
		);

		$columns_repeater->add_control(
			'column_align',
			[
				'label'   => esc_html__( 'Text Align', 'web-kit' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'   => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
				],
				'default' => 'center',
				'toggle'  => false,
			]
		);

		$columns_repeater->add_control(
			'column_width',
			[
				'label'       => esc_html__( 'Width (%)', 'web-kit' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 100,
				'description' => esc_html__( 'Leave at 0 for auto/equal width.', 'web-kit' ),
			]
		);

		$this->add_control(
			'wk_table_columns',
			[
				'label'       => esc_html__( 'Table Columns', 'web-kit' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $columns_repeater->get_controls(),
				'default'     => [
					[ 'column_label' => esc_html__( 'Loan Type', 'web-kit' ), 'column_align' => 'left' ],
					[ 'column_label' => esc_html__( 'Best For', 'web-kit' ) ],
					[ 'column_label' => esc_html__( 'Loan Amount', 'web-kit' ) ],
					[ 'column_label' => esc_html__( 'Term', 'web-kit' ) ],
					[ 'column_label' => esc_html__( 'Funding Speed', 'web-kit' ) ],
					[ 'column_label' => esc_html__( 'Min. FICO', 'web-kit' ) ],
				],
				'title_field' => '{{{ column_label }}}',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * CONTENT TAB — ROWS
		 * ========================================================= */
		$this->start_controls_section(
			'wk_table_section_rows',
			[
				'label' => esc_html__( 'Rows', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$rows_repeater = new Repeater();

		for ( $i = 1; $i <= self::MAX_COLUMNS; $i++ ) {

			$rows_repeater->add_control(
				"cell_{$i}",
				[
					/* translators: %d: cell/column number */
					'label'       => sprintf( esc_html__( 'Cell %d', 'web-kit' ), $i ),
					'type'        => Controls_Manager::TEXTAREA,
					'rows'        => 2,
					'label_block' => true,
					'description' => 1 === $i ? esc_html__( 'Basic HTML allowed: <strong>, <br>, <em>.', 'web-kit' ) : '',
				]
			);

			$rows_repeater->add_control(
				"cell_{$i}_link",
				[
					/* translators: %d: cell/column number */
					'label'       => sprintf( esc_html__( 'Cell %d Link (optional)', 'web-kit' ), $i ),
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
					'label'       => sprintf( esc_html__( 'Cell %d Text Color (optional)', 'web-kit' ), $i ),
					'type'        => Controls_Manager::COLOR,
					'description' => esc_html__( 'Overrides the Body/Accent color from the Style tab for this cell only.', 'web-kit' ),
				]
			);

			$rows_repeater->add_control(
				"cell_{$i}_bg",
				[
					/* translators: %d: cell/column number */
					'label'       => sprintf( esc_html__( 'Cell %d Background Color (optional)', 'web-kit' ), $i ),
					'type'        => Controls_Manager::COLOR,
					'description' => esc_html__( 'Overrides the Row/Alternate Row background from the Style tab for this cell only.', 'web-kit' ),
				]
			);
		}

		$this->add_control(
			'wk_table_rows',
			[
				'label'       => esc_html__( 'Table Rows', 'web-kit' ),
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
			'wk_table_section_settings',
			[
				'label' => esc_html__( 'Table Settings', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'wk_table_column_count_mode',
			[
				'label'   => esc_html__( 'Column Count', 'web-kit' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'auto'   => esc_html__( 'Auto (from Columns repeater)', 'web-kit' ),
					'manual' => esc_html__( 'Manual override', 'web-kit' ),
				],
				'default' => 'auto',
			]
		);

		$this->add_control(
			'wk_table_manual_column_count',
			[
				'label'     => esc_html__( 'Number of Columns', 'web-kit' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => self::MAX_COLUMNS,
				'default'   => 6,
				'condition' => [ 'wk_table_column_count_mode' => 'manual' ],
			]
		);

		$this->add_control(
			'wk_table_responsive_scroll',
			[
				'label'        => esc_html__( 'Horizontal Scroll on Mobile', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'wk_table_sticky_header',
			[
				'label'        => esc_html__( 'Sticky Header Row', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'wk_table_first_col_accent',
			[
				'label'        => esc_html__( 'Accent-Style First Column', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Matches the reference design: bold, colored text in the left-most column.', 'web-kit' ),
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — HEADER
		 * ========================================================= */
		$this->start_controls_section(
			'wk_table_section_style_header',
			[
				'label' => esc_html__( 'Header Row', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_table_header_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1F3A5F',
				'selectors' => [ '{{WRAPPER}} .wk-table thead th' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'wk_table_header_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [ '{{WRAPPER}} .wk-table thead th' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_table_header_typography',
				'selector' => '{{WRAPPER}} .wk-table thead th',
			]
		);

		$this->add_responsive_control(
			'wk_table_header_padding',
			[
				'label'      => esc_html__( 'Padding', 'web-kit' ),
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
			'wk_table_section_style_body',
			[
				'label' => esc_html__( 'Body', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_table_body_bg_color',
			[
				'label'     => esc_html__( 'Row Background', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [ '{{WRAPPER}} .wk-table tbody td' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'wk_table_body_alt_bg_color',
			[
				'label'     => esc_html__( 'Alternate Row Background', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F7F9FC',
				'selectors' => [ '{{WRAPPER}} .wk-table tbody tr:nth-child(even) td' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'wk_table_body_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1F3A5F',
				'selectors' => [ '{{WRAPPER}} .wk-table tbody td' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'wk_table_accent_color',
			[
				'label'       => esc_html__( 'Accent Color', 'web-kit' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '#2E6FE0',
				'description' => esc_html__( 'Used for the accented first column and for linked cell values.', 'web-kit' ),
				'selectors'   => [
					'{{WRAPPER}} .wk-table tbody td.wk-first-col' => 'color: {{VALUE}};',
					'{{WRAPPER}} .wk-table tbody td a'            => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_table_body_typography',
				'selector' => '{{WRAPPER}} .wk-table tbody td',
			]
		);

		$this->add_responsive_control(
			'wk_table_body_padding',
			[
				'label'      => esc_html__( 'Padding', 'web-kit' ),
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
			'wk_table_section_style_border',
			[
				'label' => esc_html__( 'Borders & Container', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_table_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E5E9F0',
				'selectors' => [
					'{{WRAPPER}} .wk-table'    => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .wk-table td' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_table_border_width',
			[
				'label'     => esc_html__( 'Border Width', 'web-kit' ),
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
			'wk_table_table_radius',
			[
				'label'     => esc_html__( 'Corner Radius', 'web-kit' ),
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
		$mode = isset( $settings['wk_table_column_count_mode'] ) ? $settings['wk_table_column_count_mode'] : ( isset( $settings['column_count_mode'] ) ? $settings['column_count_mode'] : 'auto' );
		if ( 'manual' === $mode ) {
			$manual = isset( $settings['wk_table_manual_column_count'] ) ? $settings['wk_table_manual_column_count'] : ( isset( $settings['manual_column_count'] ) ? $settings['manual_column_count'] : 6 );
			return max( 1, (int) $manual );
		}
		return count( $columns );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$columns  = ! empty( $settings['wk_table_columns'] ) ? $settings['wk_table_columns'] : ( ! empty( $settings['columns'] ) ? $settings['columns'] : [] );
		$rows     = ! empty( $settings['wk_table_rows'] ) ? $settings['wk_table_rows'] : ( ! empty( $settings['rows'] ) ? $settings['rows'] : [] );

		$column_count = $this->get_column_count( $settings, $columns );

		if ( $column_count < 1 || empty( $rows ) ) {
			return;
		}

		$responsive_scroll = isset( $settings['wk_table_responsive_scroll'] ) ? $settings['wk_table_responsive_scroll'] : ( isset( $settings['responsive_scroll'] ) ? $settings['responsive_scroll'] : 'yes' );
		$sticky_header     = isset( $settings['wk_table_sticky_header'] ) ? $settings['wk_table_sticky_header'] : ( isset( $settings['sticky_header'] ) ? $settings['sticky_header'] : '' );
		$first_col_accent  = isset( $settings['wk_table_first_col_accent'] ) ? $settings['wk_table_first_col_accent'] : ( isset( $settings['first_col_accent'] ) ? $settings['first_col_accent'] : 'yes' );

		$wrap_classes = [ 'wk-table-wrap' ];
		if ( 'yes' === $responsive_scroll ) {
			$wrap_classes[] = 'wk-scroll';
		}

		$table_classes = [ 'wk-table' ];
		if ( 'yes' === $sticky_header ) {
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
								if ( 1 === $i && 'yes' === $first_col_accent ) {
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
