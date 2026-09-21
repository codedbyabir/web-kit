<?php
/**
 * Web Kit - FAQ Widget
 *
 * Reads the FAQ items saved on the current post (see Faq_Metabox /
 * "_wk_faq_items" post meta) and renders them as a plain, always-expanded
 * list. No post picker - it always reflects whatever post the page/template
 * is currently rendering for, so the same widget works correctly across a
 * single-post template applied to many posts.
 *
 * @package Web_Kit
 */

namespace WebKit\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class Faq extends Widget_Base {

	public function get_name() {
		return 'wk-faq';
	}

	public function get_title() {
		return __( 'Web Kit FAQ', 'web-kit' );
	}

	public function get_icon() {
		return 'eicon-help-o';
	}

	public function get_categories() {
		return [ 'web-kit' ];
	}

	public function get_keywords() {
		return [ 'faq', 'frequently asked questions', 'questions', 'schema' ];
	}

	/**
	 * Style handle registered in includes/class-wk-plugin.php via
	 * wp_register_style(). Elementor enqueues it only on pages/previews
	 * where this widget is actually used.
	 */
	public function get_style_depends() {
		return [ 'wk-faq' ];
	}

	protected function register_controls() {

		/* =========================================================
		 * CONTENT TAB — SETTINGS
		 * ========================================================= */
		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'FAQ Settings', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'source_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'This widget automatically shows the FAQs added in the "FAQs" box on this post\'s edit screen (Posts only, for now).', 'web-kit' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->add_control(
			'question_tag',
			[
				'label'   => __( 'Question HTML Tag', 'web-kit' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'h2'  => 'H2',
					'h3'  => 'H3',
					'h4'  => 'H4',
					'h5'  => 'H5',
					'div' => 'div',
				],
				'default' => 'h3',
			]
		);

		$this->add_control(
			'show_divider',
			[
				'label'        => __( 'Divider Between FAQs', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'empty_message',
			[
				'label'       => __( 'Message When No FAQs', 'web-kit' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'No FAQs have been added for this post yet.', 'web-kit' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'enable_schema',
			[
				'label'        => __( 'Output FAQ Schema (JSON-LD)', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'Adds FAQPage structured data so search engines can potentially show these Q&As directly in results.', 'web-kit' ),
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — QUESTION
		 * ========================================================= */
		$this->start_controls_section(
			'section_style_question',
			[
				'label' => __( 'Question', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'question_color',
			[
				'label'     => __( 'Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1F3A5F',
				'selectors' => [ '{{WRAPPER}} .wk-faq-question' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'question_typography',
				'selector' => '{{WRAPPER}} .wk-faq-question',
				'fields_options' => [
					'font_size' => [ 'default' => [ 'unit' => 'px', 'size' => 18 ] ],
					'font_weight' => [ 'default' => '600' ],
				],
			]
		);

		$this->add_responsive_control(
			'question_spacing',
			[
				'label'      => __( 'Spacing Below', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'default'    => [ 'size' => 8, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-faq-question' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — ANSWER
		 * ========================================================= */
		$this->start_controls_section(
			'section_style_answer',
			[
				'label' => __( 'Answer', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'answer_color',
			[
				'label'     => __( 'Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4A5568',
				'selectors' => [ '{{WRAPPER}} .wk-faq-answer' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'answer_typography',
				'selector' => '{{WRAPPER}} .wk-faq-answer',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — LIST / SPACING
		 * ========================================================= */
		$this->start_controls_section(
			'section_style_list',
			[
				'label' => __( 'List', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'item_spacing',
			[
				'label'      => __( 'Space Between FAQs', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
				'default'    => [ 'size' => 24, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-faq-item' => 'padding-bottom: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .wk-faq-item:last-child' => 'padding-bottom: 0; margin-bottom: 0;',
				],
			]
		);

		$this->add_control(
			'divider_color',
			[
				'label'     => __( 'Divider Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E5E9F0',
				'condition' => [ 'show_divider' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .wk-faq-has-divider .wk-faq-item' => 'border-bottom-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$post_id  = get_the_ID();
		$items    = $post_id ? get_post_meta( $post_id, '_wk_faq_items', true ) : [];

		if ( ! is_array( $items ) ) {
			$items = [];
		}

		// Drop any fully-empty rows defensively (shouldn't normally occur;
		// the meta box already skips them on save).
		$items = array_values(
			array_filter(
				$items,
				function ( $item ) {
					$q = isset( $item['question'] ) ? trim( $item['question'] ) : '';
					$a = isset( $item['answer'] ) ? trim( wp_strip_all_tags( $item['answer'] ) ) : '';
					return '' !== $q || '' !== $a;
				}
			)
		);

		if ( empty( $items ) ) {
			if ( ! empty( $settings['empty_message'] ) ) {
				printf( '<div class="wk-faq-empty">%s</div>', esc_html( $settings['empty_message'] ) );
			}
			return;
		}

		$tag = in_array( $settings['question_tag'], [ 'h2', 'h3', 'h4', 'h5', 'div' ], true )
			? $settings['question_tag']
			: 'h3';

		$list_classes = [ 'wk-faq-list' ];
		if ( 'yes' === $settings['show_divider'] ) {
			$list_classes[] = 'wk-faq-has-divider';
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', $list_classes ) ); ?>">
			<?php foreach ( $items as $item ) : ?>
				<div class="wk-faq-item">
					<<?php echo esc_attr( $tag ); ?> class="wk-faq-question">
						<?php echo esc_html( $item['question'] ); ?>
					</<?php echo esc_attr( $tag ); ?>>
					<div class="wk-faq-answer">
						<?php echo wp_kses_post( $item['answer'] ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php

		if ( 'yes' === $settings['enable_schema'] ) {
			$this->render_schema( $items );
		}
	}

	/**
	 * Outputs FAQPage JSON-LD for the given items.
	 *
	 * @param array $items
	 */
	private function render_schema( array $items ) {
		$entities = [];

		foreach ( $items as $item ) {
			$question = isset( $item['question'] ) ? wp_strip_all_tags( $item['question'] ) : '';
			$answer   = isset( $item['answer'] ) ? wp_strip_all_tags( $item['answer'] ) : '';

			if ( '' === trim( $question ) || '' === trim( $answer ) ) {
				continue;
			}

			$entities[] = [
				'@type'          => 'Question',
				'name'           => $question,
				'acceptedAnswer' => [
					'@type' => 'Answer',
					'text'  => $answer,
				],
			];
		}

		if ( empty( $entities ) ) {
			return;
		}

		$schema = [
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
		];

		printf(
			'<script type="application/ld+json">%s</script>',
			wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON, not HTML.
		);
	}
}
