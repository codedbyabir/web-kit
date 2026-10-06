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

	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		self::init_hooks();
	}

	/**
	 * Register frontend rendering filters for Section, Container, and Widget.
	 */
	public static function init_hooks() {
		static $hooked = false;
		if ( ! $hooked ) {
			add_filter( 'elementor/frontend/section/should_render', [ __CLASS__, 'filter_section_should_render' ], 10, 2 );
			add_filter( 'elementor/frontend/container/should_render', [ __CLASS__, 'filter_section_should_render' ], 10, 2 );
			add_filter( 'elementor/frontend/widget/should_render', [ __CLASS__, 'filter_widget_should_render' ], 10, 2 );
			$hooked = true;
		}
	}

	public function get_name() {
		return 'wk-faq';
	}

	public function get_title() {
		return esc_html__( 'Web Kit FAQ', 'web-kit' );
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
				'label' => esc_html__( 'FAQ Settings', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'wk_faq_source_note',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'This widget automatically displays the FAQs added to this post. If the post has no FAQs, you can enable custom FAQs in the "Custom FAQs" section below.', 'web-kit' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->add_control(
			'wk_faq_question_tag',
			[
				'label'   => esc_html__( 'Question HTML Tag', 'web-kit' ),
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
				'label'       => esc_html__( 'Message When No FAQs', 'web-kit' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'No FAQs have been added for this post yet.', 'web-kit' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'wk_faq_enable_schema',
			[
				'label'        => esc_html__( 'Output FAQ Schema (JSON-LD)', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Adds FAQPage structured data so search engines can potentially show these Q&As directly in results.', 'web-kit' ),
			]
		);

		$this->add_control(
			'wk_faq_hide_parent_section',
			[
				'label'        => esc_html__( 'Hide Section When Empty', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Automatically hide the parent Section or Container on the frontend when this post has no FAQs.', 'web-kit' ),
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * CONTENT TAB — CUSTOM FAQS
		 * ========================================================= */
		$this->start_controls_section(
			'wk_faq_section_custom_faqs',
			[
				'label' => esc_html__( 'Custom FAQs', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'wk_faq_custom_faq',
			[
				'label'        => esc_html__( 'Enable Custom FAQs', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Enable to provide custom FAQs when the post has no FAQs saved.', 'web-kit' ),
			]
		);

		$this->add_control(
			'wk_faq_custom_faq_behavior',
			[
				'label'       => esc_html__( 'Display Rule', 'web-kit' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'fallback' => esc_html__( 'Only if post has no FAQs (Fallback)', 'web-kit' ),
					'override' => esc_html__( 'Always show custom FAQs (Override post FAQs)', 'web-kit' ),
				],
				'default'     => 'fallback',
				'condition'   => [
					'wk_faq_custom_faq' => 'yes',
				],
				'description' => esc_html__( 'Choose whether custom FAQs display only when the post has no FAQs, or always override the post FAQs.', 'web-kit' ),
			]
		);

		$custom_faqs_repeater = new Repeater();

		$custom_faqs_repeater->add_control(
			'question',
			[
				'label'       => esc_html__( 'Question', 'web-kit' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'FAQ Question', 'web-kit' ),
				'placeholder' => esc_html__( 'Enter your question', 'web-kit' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
			]
		);

		$custom_faqs_repeater->add_control(
			'answer',
			[
				'label'       => esc_html__( 'Answer', 'web-kit' ),
				'type'        => Controls_Manager::WYSIWYG,
				'default'     => esc_html__( 'FAQ Answer', 'web-kit' ),
				'placeholder' => esc_html__( 'Enter your answer', 'web-kit' ),
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'wk_faq_custom_faqs',
			[
				'label'       => esc_html__( 'Custom FAQ Items', 'web-kit' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $custom_faqs_repeater->get_controls(),
				'default'     => [
					[
						'question' => esc_html__( 'What is your refund policy?', 'web-kit' ),
						'answer'   => esc_html__( 'We offer a full refund within 30 days of purchase if you are not completely satisfied.', 'web-kit' ),
					],
					[
						'question' => esc_html__( 'How can I contact support?', 'web-kit' ),
						'answer'   => esc_html__( 'You can reach our friendly support team 24/7 through our contact page or email.', 'web-kit' ),
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
				'label' => esc_html__( 'Title', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'wk_faq_show_title',
			[
				'label'        => esc_html__( 'Show Title', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'wk_faq_faq_title',
			[
				'label'       => esc_html__( 'Title Text', 'web-kit' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Frequently Asked Questions', 'web-kit' ),
				'placeholder' => esc_html__( 'Frequently Asked Questions', 'web-kit' ),
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
				'condition'   => [ 'wk_faq_show_title' => 'yes' ],
			]
		);

		$this->add_control(
			'wk_faq_title_tag',
			[
				'label'     => esc_html__( 'Title HTML Tag', 'web-kit' ),
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
				'label' => esc_html__( 'Layout', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'wk_faq_layout',
			[
				'label'   => esc_html__( 'Layout', 'web-kit' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'list' => [ 'title' => esc_html__( 'List', 'web-kit' ), 'icon' => 'eicon-editor-list-ul' ],
					'grid' => [ 'title' => esc_html__( 'Grid', 'web-kit' ), 'icon' => 'eicon-gallery-grid' ],
				],
				'default' => 'list',
				'toggle'  => false,
			]
		);

		$this->add_responsive_control(
			'wk_faq_columns',
			[
				'label'          => esc_html__( 'Columns', 'web-kit' ),
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
				'label'      => esc_html__( 'Gap', 'web-kit' ),
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
				'label'        => esc_html__( 'Divider Between FAQs', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => [ 'wk_faq_layout' => 'list' ],
				'description'  => esc_html__( 'List layout only. In Grid layout, use the Item style section\'s border instead.', 'web-kit' ),
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — TITLE
		 * ========================================================= */
		$this->start_controls_section(
			'wk_faq_section_style_title',
			[
				'label'     => esc_html__( 'Title', 'web-kit' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'wk_faq_show_title' => 'yes' ],
			]
		);

		$this->add_control(
			'wk_faq_title_color',
			[
				'label'     => esc_html__( 'Color', 'web-kit' ),
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
				'label'     => esc_html__( 'Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
				],
				'default'   => 'left',
				'selectors' => [ '{{WRAPPER}} .wk-faq-title' => 'text-align: {{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'wk_faq_title_spacing',
			[
				'label'      => esc_html__( 'Spacing Below', 'web-kit' ),
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
				'label' => esc_html__( 'Question', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_faq_question_color',
			[
				'label'     => esc_html__( 'Color', 'web-kit' ),
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
				'label'      => esc_html__( 'Spacing Below', 'web-kit' ),
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
				'label' => esc_html__( 'Answer', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_faq_answer_color',
			[
				'label'     => esc_html__( 'Color', 'web-kit' ),
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
				'label'     => esc_html__( 'List Spacing', 'web-kit' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'wk_faq_layout' => 'list' ],
			]
		);

		$this->add_responsive_control(
			'wk_faq_item_spacing',
			[
				'label'      => esc_html__( 'Space Between FAQs', 'web-kit' ),
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
				'label'     => esc_html__( 'Divider Color', 'web-kit' ),
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
				'label' => esc_html__( 'Item', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_faq_item_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .wk-faq-item' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_responsive_control(
			'wk_faq_item_padding',
			[
				'label'      => esc_html__( 'Padding', 'web-kit' ),
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
				'label'      => esc_html__( 'Border Radius', 'web-kit' ),
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
				'label'     => esc_html__( 'Hover State', 'web-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'wk_faq_hover_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .wk-faq-item:hover' => 'background-color: {{VALUE}};' ],
			]
		);

		$this->add_control(
			'wk_faq_hover_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'web-kit' ),
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
				'label'     => esc_html__( 'Border Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .wk-faq-item:hover' => 'border-color: {{VALUE}};' ],
				'condition' => [ 'wk_faq_item_border_border!' => [ '', 'none' ] ],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Whether we're rendering inside the Elementor editor (as opposed to
	 * the live front-end).
	 *
	 * @return bool
	 */
	private static function is_editor_mode() {
		return did_action( 'elementor/loaded' )
			&& isset( \Elementor\Plugin::$instance )
			&& \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	private function is_editor_edit_mode() {
		return self::is_editor_mode();
	}

	/**
	 * Get resolved FAQ items (combining dynamic post meta FAQs and custom FAQs).
	 *
	 * @param array $settings
	 * @return array
	 */
	public function get_resolved_items( $settings = [] ) {
		if ( empty( $settings ) ) {
			$settings = $this->get_settings_for_display();
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			$post_id = get_queried_object_id();
		}

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

		// If post FAQs are not enabled and custom FAQs are not enabled, return empty.
		if ( ! $post_faq_enabled && ! $custom_faq_enabled ) {
			return [];
		}

		// Determine which items to render.
		if ( $custom_faq_enabled && 'override' === $custom_behavior ) {
			return $custom_items;
		} elseif ( ! empty( $post_items ) ) {
			return $post_items;
		} elseif ( $custom_faq_enabled ) {
			return $custom_items;
		}

		return [];
	}

	/**
	 * Whether there are any valid FAQ items to display for the current post.
	 *
	 * @param array $settings
	 * @return bool
	 */
	public function has_items_to_render( $settings = [] ) {
		$items = $this->get_resolved_items( $settings );
		return ! empty( $items );
	}

	/**
	 * Hide the parent Section or Container on the frontend if it contains
	 * an FAQ widget that has no FAQs to display for the current post.
	 *
	 * @param bool                    $should_render
	 * @param \Elementor\Element_Base $element
	 * @return bool
	 */
	public static function filter_section_should_render( $should_render, $element ) {
		if ( ! $should_render ) {
			return false;
		}

		// Never hide in Elementor editor mode so editors can configure the page.
		if ( self::is_editor_mode() ) {
			return $should_render;
		}

		$faq_widgets = self::find_faq_widgets( $element );
		if ( empty( $faq_widgets ) ) {
			return $should_render;
		}

		// If this section/container contains other major unrelated widgets (e.g. post content, form), don't hide the whole section.
		if ( self::has_unrelated_content( $element ) ) {
			return $should_render;
		}

		// Check if any FAQ widget in this section/container has FAQs to display.
		foreach ( $faq_widgets as $widget ) {
			$settings    = $widget->get_settings_for_display();
			$hide_parent = isset( $settings['wk_faq_hide_parent_section'] ) ? $settings['wk_faq_hide_parent_section'] : 'yes';

			if ( 'yes' !== $hide_parent ) {
				// User explicitly disabled hiding the parent section for this widget.
				return $should_render;
			}

			if ( $widget->has_items_to_render( $settings ) ) {
				return $should_render;
			}
		}

		// None of the FAQ widgets have items to render, and parent hiding is enabled.
		return false;
	}

	/**
	 * Filter widget rendering on the frontend.
	 * Completely suppresses printing the Elementor widget wrapper if there are no FAQs to display.
	 *
	 * @param bool                    $should_render
	 * @param \Elementor\Element_Base $element
	 * @return bool
	 */
	public static function filter_widget_should_render( $should_render, $element ) {
		if ( ! $should_render ) {
			return false;
		}

		if ( self::is_editor_mode() ) {
			return $should_render;
		}

		if ( $element instanceof self || ( method_exists( $element, 'get_name' ) && 'wk-faq' === $element->get_name() ) ) {
			$settings = method_exists( $element, 'get_settings_for_display' ) ? $element->get_settings_for_display() : [];
			if ( method_exists( $element, 'has_items_to_render' ) && ! $element->has_items_to_render( $settings ) ) {
				return false;
			}
		}

		return $should_render;
	}

	/**
	 * Recursively find all 'wk-faq' widgets inside an Elementor element.
	 *
	 * @param \Elementor\Element_Base $element
	 * @return \WebKit\Widgets\Faq[]
	 */
	private static function find_faq_widgets( $element ) {
		$widgets = [];

		if ( $element instanceof self || ( method_exists( $element, 'get_name' ) && 'wk-faq' === $element->get_name() ) ) {
			$widgets[] = $element;
		}

		if ( method_exists( $element, 'get_children' ) ) {
			$children = $element->get_children();
			if ( is_array( $children ) ) {
				foreach ( $children as $child ) {
					$widgets = array_merge( $widgets, self::find_faq_widgets( $child ) );
				}
			}
		}

		return $widgets;
	}

	/**
	 * Get all widgets within an Elementor element tree recursively.
	 *
	 * @param \Elementor\Element_Base $element
	 * @return \Elementor\Widget_Base[]
	 */
	private static function get_all_widgets( $element ) {
		$widgets = [];

		if ( 'widget' === $element->get_type() ) {
			$widgets[] = $element;
		}

		if ( method_exists( $element, 'get_children' ) ) {
			$children = $element->get_children();
			if ( is_array( $children ) ) {
				foreach ( $children as $child ) {
					$widgets = array_merge( $widgets, self::get_all_widgets( $child ) );
				}
			}
		}

		return $widgets;
	}

	/**
	 * Check if an element contains any content unrelated to the FAQ widget.
	 *
	 * @param \Elementor\Element_Base $element
	 * @return bool
	 */
	private static function has_unrelated_content( $element ) {
		$settings = method_exists( $element, 'get_settings' ) ? $element->get_settings() : [];
		$css_classes = isset( $settings['_css_classes'] ) ? (string) $settings['_css_classes'] : '';
		if ( false !== strpos( $css_classes, 'wk-faq-section' ) || false !== strpos( $css_classes, 'faq-section' ) ) {
			return false;
		}

		$all_widgets = self::get_all_widgets( $element );

		// Common accessories in an FAQ section (headings, text descriptions, dividers, spacers, icons, images, buttons):
		$faq_accessory_widgets = [
			'wk-faq',
			'heading',
			'text-editor',
			'divider',
			'spacer',
			'icon',
			'image',
			'button',
		];

		foreach ( $all_widgets as $widget ) {
			$name = $widget->get_name();
			if ( ! in_array( $name, $faq_accessory_widgets, true ) ) {
				// Contains an unrelated widget (e.g. form, post content, loop, pricing table, etc.)
				return true;
			}
		}

		return false;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$post_id  = get_the_ID();
		if ( ! $post_id ) {
			$post_id = get_queried_object_id();
		}
		$items    = $this->get_resolved_items( $settings );

		// If no items are available:
		if ( empty( $items ) ) {
			// On the frontend, completely hide the widget block.
			if ( ! $this->is_editor_edit_mode() ) {
				return;
			}

			// In the Elementor editor, show a notice so editors can select and edit the widget.
			$post_faq_enabled   = $post_id && \WebKit\Settings::faq_enabled_for_post( $post_id );
			$custom_faq_val     = isset( $settings['wk_faq_custom_faq'] ) ? $settings['wk_faq_custom_faq'] : ( isset( $settings['custom_faq'] ) ? $settings['custom_faq'] : '' );
			$custom_faq_enabled = 'yes' === $custom_faq_val;

			if ( ! $post_faq_enabled && ! $custom_faq_enabled ) {
				printf(
					'<div class="wk-faq-empty">%s</div>',
					esc_html__( 'The FAQ feature is not enabled for this post type/taxonomy in Web Kit → Settings.', 'web-kit' )
				);
				return;
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
			if ( ! empty( $empty_message ) ) {
				printf(
					'<div class="wk-faq-empty">%s <span style="font-size: 11px; opacity: 0.7;">(%s)</span></div>',
					esc_html( $empty_message ),
					esc_html__( 'Hidden on live site because no FAQs are available for this post', 'web-kit' )
				);
			}
			return;
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

Faq::init_hooks();