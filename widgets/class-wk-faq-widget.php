<?php
/**
 * Web Kit - FAQ Widget
 *
 * Reads the FAQ items saved on the current post (see Faq_Metabox /
 * "_wk_faq_items" post meta) and renders them either as a plain,
 * always-expanded list, or as a grid of cards. No post picker - it always
 * reflects whatever post the page/template is currently rendering for, so
 * the same widget works correctly across a single-post template applied to
 * many posts.
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
use Elementor\Group_Control_Border;

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
		return [ 'faq', 'frequently asked questions', 'questions', 'schema', 'grid', 'custom faq', 'repeater' ];
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
			'wk_faq_section_settings',
			[
				'label' => __( 'FAQ Settings', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'wk_faq_source_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'This widget automatically displays the FAQs added to this post. If the post has no FAQs, you can enable custom FAQs in the "Custom FAQs" section below.', 'web-kit' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->add_control(
			'wk_faq_question_tag',
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
			'wk_faq_empty_message',
			[
				'label'       => __( 'Message When No FAQs', 'web-kit' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'No FAQs have been added for this post yet.', 'web-kit' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'wk_faq_enable_schema',
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
		 * CONTENT TAB — CUSTOM FAQS
		 * ========================================================= */
		$this->start_controls_section(
			'wk_faq_section_custom_faqs',
			[
				'label' => __( 'Custom FAQs', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'wk_faq_custom_faq',
			[
				'label'        => __( 'Enable Custom FAQs', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => __( 'Enable to provide custom FAQs when the post has no FAQs saved.', 'web-kit' ),
			]
		);

		$this->add_control(
			'wk_faq_custom_faq_behavior',
			[
				'label'       => __( 'Display Rule', 'web-kit' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'fallback' => __( 'Only if post has no FAQs (Fallback)', 'web-kit' ),
					'override' => __( 'Always show custom FAQs (Override post FAQs)', 'web-kit' ),
				],
				'default'     => 'fallback',
				'condition'   => [
					'wk_faq_custom_faq' => 'yes',
				],
				'description' => __( 'Choose whether custom FAQs display only when the post has no FAQs, or always override the post FAQs.', 'web-kit' ),
			]
		);

		$custom_faqs_repeater = new Repeater();

		$custom_faqs_repeater->add_control(
			'question',
			[
				'label'       => __( 'Question', 'web-kit' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'FAQ Question', 'web-kit' ),
				'placeholder' => __( 'Enter your question', 'web-kit' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$custom_faqs_repeater->add_control(
			'answer',
			[
				'label'       => __( 'Answer', 'web-kit' ),
				'type'        => Controls_Manager::WYSIWYG,
				'default'     => __( 'FAQ Answer', 'web-kit' ),
				'placeholder' => __( 'Enter your answer', 'web-kit' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'wk_faq_custom_faqs',
			[
				'label'       => __( 'Custom FAQ Items', 'web-kit' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $custom_faqs_repeater->get_controls(),
				'default'     => [
					[
						'question' => __( 'What is your refund policy?', 'web-kit' ),
						'answer'   => __( 'We offer a full refund within 30 days of purchase if you are not completely satisfied.', 'web-kit' ),
					],
					[
						'question' => __( 'How can I contact support?', 'web-kit' ),
						'answer'   => __( 'You can reach our friendly support team 24/7 through our contact page or email.', 'web-kit' ),
					],
				],
				'title_field' => '{{{ question }}}',
				'condition'   => [
					'wk_faq_custom_faq' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * CONTENT TAB — TITLE
		 * ========================================================= */
		$this->start_controls_section(
			'wk_faq_section_title',
			[
				'label' => __( 'Title', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'wk_faq_show_title',
			[
				'label'        => __( 'Show Title', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'wk_faq_faq_title',
			[
				'label'       => __( 'Title Text', 'web-kit' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Frequently Asked Questions', 'web-kit' ),
				'placeholder' => __( 'Frequently Asked Questions', 'web-kit' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
				'condition'   => [ 'wk_faq_show_title' => 'yes' ],
			]
		);

		$this->add_control(
			'wk_faq_title_tag',
			[
				'label'     => __( 'Title HTML Tag', 'web-kit' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'h1'  => 'H1',
					'h2'  => 'H2',
					'h3'  => 'H3',
					'h4'  => 'H4',
					'h5'  => 'H5',
					'div' => 'div',
				],
				'default'   => 'h2',
				'condition' => [ 'wk_faq_show_title' => 'yes' ],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * CONTENT TAB — LAYOUT
		 * ========================================================= */
		$this->start_controls_section(
			'wk_faq_section_layout',
			[
				'label' => __( 'Layout', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'wk_faq_layout',
			[
				'label'   => __( 'Layout', 'web-kit' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'list' => [ 'title' => __( 'List', 'web-kit' ), 'icon' => 'eicon-editor-list-ul' ],
					'grid' => [ 'title' => __( 'Grid', 'web-kit' ), 'icon' => 'eicon-gallery-grid' ],
				],
				'default' => 'list',
				'toggle'  => false,
			]
		);

		$this->add_responsive_control(
			'wk_faq_columns',
			[
				'label'          => __( 'Columns', 'web-kit' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 1,
				'max'            => 6,
				'default'        => 2,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'condition'      => [ 'wk_faq_layout' => 'grid' ],
				'selectors'      => [
					'{{WRAPPER}} .wk-faq-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				],
			]
		);

		$this->add_responsive_control(
			'wk_faq_grid_gap',
			[
				'label'      => __( 'Gap', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
				'default'    => [ 'size' => 20, 'unit' => 'px' ],
				'condition'  => [ 'wk_faq_layout' => 'grid' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-faq-grid' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'wk_faq_show_divider',
			[
				'label'        => __( 'Divider Between FAQs', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => [ 'wk_faq_layout' => 'list' ],
				'description'  => __( 'List layout only. In Grid layout, use the Item style section\'s border instead.', 'web-kit' ),
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — TITLE
		 * ========================================================= */
		$this->start_controls_section(
			'wk_faq_section_style_title',
			[
				'label'     => __( 'Title', 'web-kit' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'wk_faq_show_title' => 'yes' ],
			]
		);

		$this->add_control(
			'wk_faq_title_color',
			[
				'label'     => __( 'Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1F3A5F',
				'selectors' => [ '{{WRAPPER}} .wk-faq-title' => 'color: {{VALUE}};' ],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'            => 'wk_faq_title_typography',
				'selector'        => '{{WRAPPER}} .wk-faq-title',
				'fields_options'  => [
					'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 26 ] ],
					'font_weight' => [ 'default' => '700' ],
				],
			]
		);

		$this->add_responsive_control(
			'wk_faq_title_alignment',
			[
				'label'     => __( 'Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => __( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => __( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => __( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
				],
				'default'   => 'left',
				'selectors' => [ '{{WRAPPER}} .wk-faq-title' => 'text-align: {{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'wk_faq_title_spacing',
			[
				'label'      => __( 'Spacing Below', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
				'default'    => [ 'size' => 20, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-faq-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — QUESTION
		 * ========================================================= */
		$this->start_controls_section(
			'wk_faq_section_style_question',
			[
				'label' => __( 'Question', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_faq_question_color',
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
				'name'            => 'wk_faq_question_typography',
				'selector'        => '{{WRAPPER}} .wk-faq-question',
				'fields_options'  => [
					'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 18 ] ],
					'font_weight' => [ 'default' => '600' ],
				],
			]
		);

		$this->add_responsive_control(
			'wk_faq_question_spacing',
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
			'wk_faq_section_style_answer',
			[
				'label' => __( 'Answer', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_faq_answer_color',
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
				'name'     => 'wk_faq_answer_typography',
				'selector' => '{{WRAPPER}} .wk-faq-answer',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — LIST SPACING (list layout only)
		 * ========================================================= */
		$this->start_controls_section(
			'wk_faq_section_style_list',
			[
				'label'     => __( 'List Spacing', 'web-kit' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'wk_faq_layout' => 'list' ],
			]
		);

		$this->add_responsive_control(
			'wk_faq_item_spacing',
			[
				'label'      => __( 'Space Between FAQs', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
				'default'    => [ 'size' => 24, 'unit' => 'px' ],
				'selectors'  => [
					// Scoped to :not(.wk-faq-grid) so this can never bleed into
					// Grid layout even if the value stays set after switching.
					'{{WRAPPER}} .wk-faq-list:not(.wk-faq-grid) .wk-faq-item' => 'padding-bottom: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .wk-faq-list:not(.wk-faq-grid) .wk-faq-item:last-child' => 'padding-bottom: 0; margin-bottom: 0;',
				],
			]
		);

		$this->add_control(
			'wk_faq_divider_color',
			[
				'label'     => __( 'Divider Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E5E9F0',
				'condition' => [ 'wk_faq_show_divider' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .wk-faq-has-divider .wk-faq-item' => 'border-bottom-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — ITEM (padding / border / radius / hover)
		 * Applies in both layouts; most useful for Grid "cards", but
		 * works standalone in List layout too.
		 * ========================================================= */
		$this->start_controls_section(
			'wk_faq_section_style_item',
			[
				'label' => __( 'Item', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_faq_item_bg_color',
			[
				'label'     => __( 'Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .wk-faq-item' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'wk_faq_item_padding',
			[
				'label'      => __( 'Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-faq-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'wk_faq_item_border',
				'selector' => '{{WRAPPER}} .wk-faq-item',
			]
		);

		$this->add_responsive_control(
			'wk_faq_item_border_radius',
			[
				'label'      => __( 'Border Radius', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 60 ],
					'%'  => [ 'min' => 0, 'max' => 50 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-faq-item' => 'border-radius: {{SIZE}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_control(
			'wk_faq_hover_heading',
			[
				'label'     => __( 'Hover State', 'web-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'wk_faq_hover_bg_color',
			[
				'label'     => __( 'Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .wk-faq-item:hover' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'wk_faq_hover_text_color',
			[
				'label'     => __( 'Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wk-faq-item:hover .wk-faq-question' => 'color: {{VALUE}};',
					'{{WRAPPER}} .wk-faq-item:hover .wk-faq-answer'   => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_faq_hover_border_color',
			[
				'label'     => __( 'Border Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .wk-faq-item:hover' => 'border-color: {{VALUE}};' ],
				'condition' => [ 'wk_faq_item_border_border!' => [ '', 'none' ] ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Whether we're rendering inside the Elementor editor (as opposed to
	 * the live front-end), so the "feature not enabled" notice only shows
	 * to the person building the page, never to visitors.
	 */
	private function is_editor_edit_mode() {
		return did_action( 'elementor/loaded' )
			&& isset( \Elementor\Plugin::$instance )
			&& \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$post_id  = get_the_ID();

		$custom_faq_val     = isset( $settings['wk_faq_custom_faq'] ) ? $settings['wk_faq_custom_faq'] : ( isset( $settings['custom_faq'] ) ? $settings['custom_faq'] : '' );
		$custom_faq_enabled = 'yes' === $custom_faq_val;
		$custom_behavior    = isset( $settings['wk_faq_custom_faq_behavior'] ) ? $settings['wk_faq_custom_faq_behavior'] : ( isset( $settings['custom_faq_behavior'] ) ? $settings['custom_faq_behavior'] : 'fallback' );

		// Prepare custom items from repeater.
		$custom_items_raw = ! empty( $settings['wk_faq_custom_faqs'] ) && is_array( $settings['wk_faq_custom_faqs'] )
			? $settings['wk_faq_custom_faqs']
			: ( ! empty( $settings['custom_faqs'] ) && is_array( $settings['custom_faqs'] ) ? $settings['custom_faqs'] : [] );

		$custom_items = [];
		if ( $custom_faq_enabled && ! empty( $custom_items_raw ) ) {
			$custom_items = array_values(
				array_filter(
					$custom_items_raw,
					function ( $item ) {
						$q = isset( $item['question'] ) ? trim( $item['question'] ) : '';
						$a = isset( $item['answer'] ) ? trim( wp_strip_all_tags( $item['answer'] ) ) : '';
						return '' !== $q || '' !== $a;
					}
				)
			);
		}

		// Prepare post items from post meta.
		$post_items       = [];
		$post_faq_enabled = $post_id && \WebKit\Settings::faq_enabled_for_post( $post_id );

		if ( $post_faq_enabled ) {
			$raw_post_items = get_post_meta( $post_id, '_wk_faq_items', true );
			if ( is_array( $raw_post_items ) ) {
				$post_items = array_values(
					array_filter(
						$raw_post_items,
						function ( $item ) {
							$q = isset( $item['question'] ) ? trim( $item['question'] ) : '';
							$a = isset( $item['answer'] ) ? trim( wp_strip_all_tags( $item['answer'] ) ) : '';
							return '' !== $q || '' !== $a;
						}
					)
				);
			}
		}

		// If post FAQs are not enabled and custom FAQs are not enabled, handle editor notice.
		if ( ! $post_faq_enabled && ! $custom_faq_enabled ) {
			if ( $this->is_editor_edit_mode() ) {
				printf(
					'<div class="wk-faq-empty">%s</div>',
					esc_html__( 'The FAQ feature is not enabled for this post type/taxonomy in Web Kit → Settings.', 'web-kit' )
				);
			}
			return;
		}

		// Determine which items to render.
		if ( $custom_faq_enabled && 'override' === $custom_behavior ) {
			$items = $custom_items;
		} elseif ( ! empty( $post_items ) ) {
			$items = $post_items;
		} elseif ( $custom_faq_enabled ) {
			$items = $custom_items;
		} else {
			$items = [];
		}

		$show_title        = isset( $settings['wk_faq_show_title'] ) ? $settings['wk_faq_show_title'] : ( isset( $settings['show_title'] ) ? $settings['show_title'] : 'yes' );
		$faq_title         = isset( $settings['wk_faq_faq_title'] ) ? $settings['wk_faq_faq_title'] : ( isset( $settings['faq_title'] ) ? $settings['faq_title'] : '' );
		$title_tag_setting = isset( $settings['wk_faq_title_tag'] ) ? $settings['wk_faq_title_tag'] : ( isset( $settings['title_tag'] ) ? $settings['title_tag'] : 'h2' );

		if ( 'yes' === $show_title && ! empty( $faq_title ) ) {
			$title_tag = in_array( $title_tag_setting, [ 'h1', 'h2', 'h3', 'h4', 'h5', 'div' ], true )
				? $title_tag_setting
				: 'h2';

			printf(
				'<%1$s class="wk-faq-title">%2$s</%1$s>',
				esc_attr( $title_tag ),
				esc_html( $faq_title )
			);
		}

		$empty_message = isset( $settings['wk_faq_empty_message'] ) ? $settings['wk_faq_empty_message'] : ( isset( $settings['empty_message'] ) ? $settings['empty_message'] : '' );

		if ( empty( $items ) ) {
			if ( ! empty( $empty_message ) ) {
				printf( '<div class="wk-faq-empty">%s</div>', esc_html( $empty_message ) );
			}
			return;
		}

		$question_tag_setting = isset( $settings['wk_faq_question_tag'] ) ? $settings['wk_faq_question_tag'] : ( isset( $settings['question_tag'] ) ? $settings['question_tag'] : 'h3' );
		$tag                  = in_array( $question_tag_setting, [ 'h2', 'h3', 'h4', 'h5', 'div' ], true )
			? $question_tag_setting
			: 'h3';

		$layout_setting       = isset( $settings['wk_faq_layout'] ) ? $settings['wk_faq_layout'] : ( isset( $settings['layout'] ) ? $settings['layout'] : 'list' );
		$is_grid              = 'grid' === $layout_setting;

		$show_divider_setting = isset( $settings['wk_faq_show_divider'] ) ? $settings['wk_faq_show_divider'] : ( isset( $settings['show_divider'] ) ? $settings['show_divider'] : 'yes' );

		$list_classes = [ 'wk-faq-list' ];
		if ( $is_grid ) {
			$list_classes[] = 'wk-faq-grid';
		} elseif ( 'yes' === $show_divider_setting ) {
			$list_classes[] = 'wk-faq-has-divider';
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', $list_classes ) ); ?>">
			<?php foreach ( $items as $item ) : ?>
				<?php
				$answer_html = isset( $item['answer'] ) ? $item['answer'] : '';
				if ( '' !== $answer_html && false === strpos( $answer_html, '<p>' ) && false === strpos( $answer_html, '<br' ) ) {
					$answer_html = wpautop( $answer_html );
				}
				?>
				<div class="wk-faq-item">
					<<?php echo esc_attr( $tag ); ?> class="wk-faq-question">
						<?php echo esc_html( $item['question'] ); ?>
					</<?php echo esc_attr( $tag ); ?>>
					<div class="wk-faq-answer">
						<?php echo wp_kses_post( $answer_html ); ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php

		$enable_schema = isset( $settings['wk_faq_enable_schema'] ) ? $settings['wk_faq_enable_schema'] : ( isset( $settings['enable_schema'] ) ? $settings['enable_schema'] : 'yes' );
		if ( 'yes' === $enable_schema ) {
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