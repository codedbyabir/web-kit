<?php
/**
 * Web Kit - Post Content Widget
 *
 * Full-featured post content widget designed specifically to style all
 * WordPress Gutenberg block elements with pixel-perfect control:
 * - Paragraphs (typography, colors, margins, paddings, links, drop caps)
 * - Headings (global controls + granular individual H1–H6 overrides)
 * - Images (borders, radius, box shadows, margins, paddings, captions)
 * - Lists (bullet/number marker colors, list styles, spacing, indents)
 * - Tables (complete suite: headers, body cells, alternating/striped rows,
 *   row hover, first-column accents, borders, border-radius, responsive
 *   horizontal scroll, footers, captions, and layout modes)
 * - Blockquotes, Code/Pre blocks, Separators, and Gutenberg Buttons
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
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class Post_Content extends Widget_Base {

	public function get_name() {
		return 'wk-post-content';
	}

	public function get_title() {
		return esc_html__( 'Web Kit Post Content', 'web-kit' );
	}

	public function get_icon() {
		return 'eicon-post-content';
	}

	public function get_categories() {
		return [ 'web-kit' ];
	}

	public function get_keywords() {
		return [ 'post content', 'content', 'gutenberg', 'table', 'heading', 'paragraph', 'image', 'list', 'the content' ];
	}

	public function get_style_depends() {
		return [ 'wk-post-content' ];
	}

	protected function register_controls() {

		/* =========================================================
		 * CONTENT TAB — POST CONTENT SETTINGS
		 * ========================================================= */
		$this->start_controls_section(
			'wk_pc_section_content',
			[
				'label' => esc_html__( 'Post Content', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'wk_pc_content_source',
			[
				'label'   => esc_html__( 'Content Source', 'web-kit' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'current_post' => esc_html__( 'Current Post (Default)', 'web-kit' ),
					'sample'       => esc_html__( 'Preview Sample Gutenberg Content', 'web-kit' ),
					'custom'       => esc_html__( 'Custom Content', 'web-kit' ),
				],
				'default' => 'current_post',
			]
		);

		$this->add_control(
			'wk_pc_preview_sample_fallback',
			[
				'label'        => esc_html__( 'Sample Content in Editor', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Shows rich Gutenberg sample elements (headings, tables, lists, images) in the Elementor editor if current post has no content.', 'web-kit' ),
				'condition'    => [ 'wk_pc_content_source' => 'current_post' ],
			]
		);

		$this->add_control(
			'wk_pc_custom_content',
			[
				'label'       => esc_html__( 'Custom Content', 'web-kit' ),
				'type'        => Controls_Manager::WYSIWYG,
				'default'     => esc_html__( '<p>Enter your custom post content here.</p>', 'web-kit' ),
				'condition'   => [ 'wk_pc_content_source' => 'custom' ],
				'label_block' => true,
			]
		);

		$this->add_control(
			'wk_pc_table_responsive_scroll',
			[
				'label'        => esc_html__( 'Responsive Table Scroll', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Enables smooth horizontal scrolling for wide tables on smaller mobile screens.', 'web-kit' ),
			]
		);

		$this->add_control(
			'wk_pc_table_header_sticky',
			[
				'label'        => esc_html__( 'Sticky Table Header', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Keeps the header row visible at the top during scrolling.', 'web-kit' ),
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — PARAGRAPH & BODY
		 * ========================================================= */
		$this->start_controls_section(
			'wk_pc_section_style_paragraph',
			[
				'label' => esc_html__( 'Paragraph & Body', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_pc_body_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#334155',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content, {{WRAPPER}} .wk-post-content p' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_body_typography',
				'selector' => '{{WRAPPER}} .wk-post-content, {{WRAPPER}} .wk-post-content p',
			]
		);

		$this->add_responsive_control(
			'wk_pc_paragraph_align',
			[
				'label'     => esc_html__( 'Text Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center'  => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'   => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
					'justify' => [ 'title' => esc_html__( 'Justified', 'web-kit' ), 'icon' => 'eicon-text-align-justify' ],
				],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content p' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_paragraph_margin',
			[
				'label'      => esc_html__( 'Paragraph Margin', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content p' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_paragraph_padding',
			[
				'label'      => esc_html__( 'Paragraph Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content p' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_paragraph_spacing',
			[
				'label'      => esc_html__( 'Spacing Below Paragraph', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content p' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		/* Links */
		$this->add_control(
			'wk_pc_heading_links',
			[
				'label'     => esc_html__( 'Inline Links', 'web-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->start_controls_tabs( 'wk_pc_tabs_links' );

		$this->start_controls_tab(
			'wk_pc_tab_links_normal',
			[ 'label' => esc_html__( 'Normal', 'web-kit' ) ]
		);

		$this->add_control(
			'wk_pc_link_color',
			[
				'label'     => esc_html__( 'Link Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563eb',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_link_decoration',
			[
				'label'     => esc_html__( 'Text Decoration', 'web-kit' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''          => esc_html__( 'Default', 'web-kit' ),
					'none'      => esc_html__( 'None', 'web-kit' ),
					'underline' => esc_html__( 'Underline', 'web-kit' ),
				],
				'default'   => 'underline',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content a' => 'text-decoration: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_link_font_weight',
			[
				'label'     => esc_html__( 'Font Weight', 'web-kit' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''    => esc_html__( 'Inherit', 'web-kit' ),
					'400' => '400',
					'500' => '500',
					'600' => '600',
					'700' => '700',
				],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content a' => 'font-weight: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'wk_pc_tab_links_hover',
			[ 'label' => esc_html__( 'Hover', 'web-kit' ) ]
		);

		$this->add_control(
			'wk_pc_link_hover_color',
			[
				'label'     => esc_html__( 'Hover Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1d4ed8',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_link_hover_decoration',
			[
				'label'     => esc_html__( 'Hover Decoration', 'web-kit' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''          => esc_html__( 'Default', 'web-kit' ),
					'none'      => esc_html__( 'None', 'web-kit' ),
					'underline' => esc_html__( 'Underline', 'web-kit' ),
				],
				'default'   => 'underline',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content a:hover' => 'text-decoration: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		/* Drop Caps */
		$this->add_control(
			'wk_pc_heading_drop_cap',
			[
				'label'     => esc_html__( 'Drop Cap', 'web-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'wk_pc_drop_cap_color',
			[
				'label'     => esc_html__( 'Drop Cap Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wk-post-content .has-drop-cap:not(:focus):first-letter' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_drop_cap_size',
			[
				'label'      => esc_html__( 'Drop Cap Font Size', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [ 'px' => [ 'min' => 20, 'max' => 100 ] ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content .has-drop-cap:not(:focus):first-letter' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — HEADINGS
		 * ========================================================= */
		$this->start_controls_section(
			'wk_pc_section_style_headings',
			[
				'label' => esc_html__( 'Headings (H1 – H6)', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_pc_headings_global_heading',
			[
				'label' => esc_html__( 'All Headings (Global)', 'web-kit' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'wk_pc_headings_color',
			[
				'label'     => esc_html__( 'Headings Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content h1, {{WRAPPER}} .wk-post-content h2, {{WRAPPER}} .wk-post-content h3, {{WRAPPER}} .wk-post-content h4, {{WRAPPER}} .wk-post-content h5, {{WRAPPER}} .wk-post-content h6' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_headings_typography',
				'selector' => '{{WRAPPER}} .wk-post-content h1, {{WRAPPER}} .wk-post-content h2, {{WRAPPER}} .wk-post-content h3, {{WRAPPER}} .wk-post-content h4, {{WRAPPER}} .wk-post-content h5, {{WRAPPER}} .wk-post-content h6',
			]
		);

		$this->add_responsive_control(
			'wk_pc_headings_align',
			[
				'label'     => esc_html__( 'Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center'  => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'   => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
					'justify' => [ 'title' => esc_html__( 'Justified', 'web-kit' ), 'icon' => 'eicon-text-align-justify' ],
				],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content h1, {{WRAPPER}} .wk-post-content h2, {{WRAPPER}} .wk-post-content h3, {{WRAPPER}} .wk-post-content h4, {{WRAPPER}} .wk-post-content h5, {{WRAPPER}} .wk-post-content h6' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_headings_margin',
			[
				'label'      => esc_html__( 'Headings Margin', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content h1, {{WRAPPER}} .wk-post-content h2, {{WRAPPER}} .wk-post-content h3, {{WRAPPER}} .wk-post-content h4, {{WRAPPER}} .wk-post-content h5, {{WRAPPER}} .wk-post-content h6' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_headings_padding',
			[
				'label'      => esc_html__( 'Headings Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content h1, {{WRAPPER}} .wk-post-content h2, {{WRAPPER}} .wk-post-content h3, {{WRAPPER}} .wk-post-content h4, {{WRAPPER}} .wk-post-content h5, {{WRAPPER}} .wk-post-content h6' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		/* Individual Headings Override Toggle */
		$this->add_control(
			'wk_pc_individual_headings',
			[
				'label'        => esc_html__( 'Customize Individual Headings', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'separator'    => 'before',
				'description'  => esc_html__( 'Fine-tune colors, typography, margins, and paddings for H1 through H6 independently.', 'web-kit' ),
			]
		);

		/* Loop H1 to H6 */
		for ( $h = 1; $h <= 6; $h++ ) {
			$tag = "h{$h}";

			$this->add_control(
				"wk_pc_{$tag}_heading",
				[
					/* translators: %d: heading level */
					'label'     => sprintf( esc_html__( 'Heading %d (H%d)', 'web-kit' ), $h, $h ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => [ 'wk_pc_individual_headings' => 'yes' ],
				]
			);

			$this->add_control(
				"wk_pc_{$tag}_color",
				[
					'label'     => esc_html__( 'Color', 'web-kit' ),
					'type'      => Controls_Manager::COLOR,
					'condition' => [ 'wk_pc_individual_headings' => 'yes' ],
					'selectors' => [
						"{{WRAPPER}} .wk-post-content {$tag}" => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name'      => "wk_pc_{$tag}_typography",
					'selector'  => "{{WRAPPER}} .wk-post-content {$tag}",
					'condition' => [ 'wk_pc_individual_headings' => 'yes' ],
				]
			);

			$this->add_responsive_control(
				"wk_pc_{$tag}_align",
				[
					'label'     => esc_html__( 'Alignment', 'web-kit' ),
					'type'      => Controls_Manager::CHOOSE,
					'options'   => [
						'left'    => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
						'center'  => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
						'right'   => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
						'justify' => [ 'title' => esc_html__( 'Justified', 'web-kit' ), 'icon' => 'eicon-text-align-justify' ],
					],
					'condition' => [ 'wk_pc_individual_headings' => 'yes' ],
					'selectors' => [
						"{{WRAPPER}} .wk-post-content {$tag}" => 'text-align: {{VALUE}};',
					],
				]
			);

			$this->add_responsive_control(
				"wk_pc_{$tag}_margin",
				[
					'label'      => esc_html__( 'Margin', 'web-kit' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', 'rem', '%' ],
					'condition'  => [ 'wk_pc_individual_headings' => 'yes' ],
					'selectors'  => [
						"{{WRAPPER}} .wk-post-content {$tag}" => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				"wk_pc_{$tag}_padding",
				[
					'label'      => esc_html__( 'Padding', 'web-kit' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', 'em', 'rem', '%' ],
					'condition'  => [ 'wk_pc_individual_headings' => 'yes' ],
					'selectors'  => [
						"{{WRAPPER}} .wk-post-content {$tag}" => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);
		}

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — IMAGES
		 * ========================================================= */
		$this->start_controls_section(
			'wk_pc_section_style_images',
			[
				'label' => esc_html__( 'Images', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'wk_pc_image_align',
			[
				'label'     => esc_html__( 'Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
				],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content figure.wp-block-image, {{WRAPPER}} .wk-post-content .wp-caption' => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .wk-post-content figure.wp-block-image img, {{WRAPPER}} .wk-post-content img' => 'display: inline-block;',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_image_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range'      => [
					'%'  => [ 'min' => 10, 'max' => 100 ],
					'px' => [ 'min' => 50, 'max' => 1600 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content img' => 'max-width: {{SIZE}}{{UNIT}}; height: auto;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'wk_pc_image_border',
				'selector' => '{{WRAPPER}} .wk-post-content img',
			]
		);

		$this->add_responsive_control(
			'wk_pc_image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'wk_pc_image_box_shadow',
				'selector' => '{{WRAPPER}} .wk-post-content img',
			]
		);

		$this->add_responsive_control(
			'wk_pc_image_margin',
			[
				'label'      => esc_html__( 'Image Margin', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content figure.wp-block-image, {{WRAPPER}} .wk-post-content img' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_image_padding',
			[
				'label'      => esc_html__( 'Image Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		/* Image Caption */
		$this->add_control(
			'wk_pc_heading_caption',
			[
				'label'     => esc_html__( 'Caption', 'web-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'wk_pc_caption_color',
			[
				'label'     => esc_html__( 'Caption Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content figcaption, {{WRAPPER}} .wk-post-content .wp-caption-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_caption_typography',
				'selector' => '{{WRAPPER}} .wk-post-content figcaption, {{WRAPPER}} .wk-post-content .wp-caption-text',
			]
		);

		$this->add_responsive_control(
			'wk_pc_caption_align',
			[
				'label'     => esc_html__( 'Caption Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
				],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content figcaption, {{WRAPPER}} .wk-post-content .wp-caption-text' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_caption_margin',
			[
				'label'      => esc_html__( 'Caption Spacing (Margin)', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content figcaption, {{WRAPPER}} .wk-post-content .wp-caption-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — VIDEOS & EMBEDS
		 * ========================================================= */
		$this->start_controls_section(
			'wk_pc_section_style_videos',
			[
				'label' => esc_html__( 'Videos & Embeds', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'wk_pc_video_align',
			[
				'label'     => esc_html__( 'Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
				],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content figure.wp-block-video, {{WRAPPER}} .wk-post-content figure.wp-block-embed, {{WRAPPER}} .wk-post-content figure.is-type-video' => 'text-align: {{VALUE}};',
					'{{WRAPPER}} .wk-post-content figure.wp-block-video video, {{WRAPPER}} .wk-post-content figure.wp-block-embed .wp-block-embed__wrapper, {{WRAPPER}} .wk-post-content video' => 'display: inline-block;',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_video_width',
			[
				'label'      => esc_html__( 'Width', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px', 'vw' ],
				'range'      => [
					'%'  => [ 'min' => 10, 'max' => 100 ],
					'px' => [ 'min' => 100, 'max' => 1600 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content figure.wp-block-video video, {{WRAPPER}} .wk-post-content figure.wp-block-embed .wp-block-embed__wrapper, {{WRAPPER}} .wk-post-content video' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_video_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px', 'vw' ],
				'range'      => [
					'%'  => [ 'min' => 10, 'max' => 100 ],
					'px' => [ 'min' => 200, 'max' => 1600 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content figure.wp-block-video, {{WRAPPER}} .wk-post-content figure.wp-block-embed, {{WRAPPER}} .wk-post-content figure.wp-block-video video, {{WRAPPER}} .wk-post-content figure.wp-block-embed .wp-block-embed__wrapper, {{WRAPPER}} .wk-post-content video' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_video_aspect_ratio',
			[
				'label'   => esc_html__( 'Aspect Ratio', 'web-kit' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''       => esc_html__( 'Default / Native', 'web-kit' ),
					'16 / 9' => esc_html__( '16:9 (Widescreen)', 'web-kit' ),
					'4 / 3'  => esc_html__( '4:3 (Standard)', 'web-kit' ),
					'1 / 1'  => esc_html__( '1:1 (Square)', 'web-kit' ),
					'9 / 16' => esc_html__( '9:16 (Vertical / Reels)', 'web-kit' ),
					'21 / 9' => esc_html__( '21:9 (Ultrawide)', 'web-kit' ),
				],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content figure.wp-block-video video, {{WRAPPER}} .wk-post-content figure.wp-block-embed iframe, {{WRAPPER}} .wk-post-content figure.wp-block-embed .wp-block-embed__wrapper, {{WRAPPER}} .wk-post-content video' => 'aspect-ratio: {{VALUE}}; width: 100%; height: auto;',
				],
			]
		);

		$this->add_control(
			'wk_pc_video_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wk-post-content figure.wp-block-video video, {{WRAPPER}} .wk-post-content figure.wp-block-embed .wp-block-embed__wrapper, {{WRAPPER}} .wk-post-content video' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'wk_pc_video_border',
				'selector' => '{{WRAPPER}} .wk-post-content figure.wp-block-video video, {{WRAPPER}} .wk-post-content figure.wp-block-embed iframe, {{WRAPPER}} .wk-post-content figure.wp-block-embed .wp-block-embed__wrapper, {{WRAPPER}} .wk-post-content video',
			]
		);

		$this->add_responsive_control(
			'wk_pc_video_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content figure.wp-block-video video, {{WRAPPER}} .wk-post-content figure.wp-block-embed iframe, {{WRAPPER}} .wk-post-content figure.wp-block-embed .wp-block-embed__wrapper, {{WRAPPER}} .wk-post-content video' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'wk_pc_video_box_shadow',
				'selector' => '{{WRAPPER}} .wk-post-content figure.wp-block-video video, {{WRAPPER}} .wk-post-content figure.wp-block-embed iframe, {{WRAPPER}} .wk-post-content figure.wp-block-embed .wp-block-embed__wrapper, {{WRAPPER}} .wk-post-content video',
			]
		);

		$this->add_responsive_control(
			'wk_pc_video_margin',
			[
				'label'      => esc_html__( 'Margin', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content figure.wp-block-video, {{WRAPPER}} .wk-post-content figure.wp-block-embed, {{WRAPPER}} .wk-post-content video' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		/* Video Caption */
		$this->add_control(
			'wk_pc_heading_video_caption',
			[
				'label'     => esc_html__( 'Caption', 'web-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'wk_pc_video_caption_color',
			[
				'label'     => esc_html__( 'Caption Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content figure.wp-block-video figcaption, {{WRAPPER}} .wk-post-content figure.wp-block-embed figcaption' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_video_caption_typography',
				'selector' => '{{WRAPPER}} .wk-post-content figure.wp-block-video figcaption, {{WRAPPER}} .wk-post-content figure.wp-block-embed figcaption',
			]
		);

		$this->add_responsive_control(
			'wk_pc_video_caption_align',
			[
				'label'     => esc_html__( 'Caption Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
				],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content figure.wp-block-video figcaption, {{WRAPPER}} .wk-post-content figure.wp-block-embed figcaption' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_video_caption_margin',
			[
				'label'      => esc_html__( 'Caption Spacing (Margin)', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content figure.wp-block-video figcaption, {{WRAPPER}} .wk-post-content figure.wp-block-embed figcaption' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — LISTS
		 * ========================================================= */
		$this->start_controls_section(
			'wk_pc_section_style_lists',
			[
				'label' => esc_html__( 'Lists (UL / OL)', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_pc_list_marker_color',
			[
				'label'     => esc_html__( 'Bullet / Number Marker Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563eb',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content li::marker, {{WRAPPER}} .wk-post-content ul li::marker, {{WRAPPER}} .wk-post-content ol li::marker' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_list_marker_size',
			[
				'label'      => esc_html__( 'Marker Size', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'range'      => [
					'px' => [ 'min' => 6, 'max' => 60 ],
					'em' => [ 'min' => 0.4, 'max' => 3, 'step' => 0.05 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content li::marker, {{WRAPPER}} .wk-post-content ul li::marker, {{WRAPPER}} .wk-post-content ol li::marker' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_ul_style_type',
			[
				'label'     => esc_html__( 'Unordered List Marker Style', 'web-kit' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'disc'   => esc_html__( 'Disc (Default)', 'web-kit' ),
					'circle' => esc_html__( 'Circle', 'web-kit' ),
					'square' => esc_html__( 'Square', 'web-kit' ),
					'none'   => esc_html__( 'None', 'web-kit' ),
				],
				'default'   => 'disc',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content ul' => 'list-style-type: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_ul_marker_size',
			[
				'label'      => esc_html__( 'Bullet Size (UL Override)', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'range'      => [
					'px' => [ 'min' => 6, 'max' => 60 ],
					'em' => [ 'min' => 0.4, 'max' => 3, 'step' => 0.05 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content ul li::marker' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_ol_style_type',
			[
				'label'     => esc_html__( 'Ordered List Numbering Style', 'web-kit' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'decimal'              => esc_html__( 'Decimal (1, 2, 3)', 'web-kit' ),
					'decimal-leading-zero' => esc_html__( 'Leading Zero (01, 02, 03)', 'web-kit' ),
					'lower-roman'          => esc_html__( 'Lower Roman (i, ii, iii)', 'web-kit' ),
					'upper-roman'          => esc_html__( 'Upper Roman (I, II, III)', 'web-kit' ),
					'lower-alpha'          => esc_html__( 'Lower Alpha (a, b, c)', 'web-kit' ),
					'upper-alpha'          => esc_html__( 'Upper Alpha (A, B, C)', 'web-kit' ),
					'none'                 => esc_html__( 'None', 'web-kit' ),
				],
				'default'   => 'decimal',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content ol' => 'list-style-type: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_ol_marker_size',
			[
				'label'      => esc_html__( 'Number Size (OL Override)', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'range'      => [
					'px' => [ 'min' => 6, 'max' => 60 ],
					'em' => [ 'min' => 0.4, 'max' => 3, 'step' => 0.05 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content ol li::marker' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_list_text_color',
			[
				'label'     => esc_html__( 'List Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#334155',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content ul, {{WRAPPER}} .wk-post-content ol, {{WRAPPER}} .wk-post-content li' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_list_typography',
				'selector' => '{{WRAPPER}} .wk-post-content ul, {{WRAPPER}} .wk-post-content ol, {{WRAPPER}} .wk-post-content li',
			]
		);

		$this->add_responsive_control(
			'wk_pc_list_item_spacing',
			[
				'label'      => esc_html__( 'Space Between Items', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'default'    => [ 'size' => 8, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_list_padding_left',
			[
				'label'      => esc_html__( 'List Indentation (Padding Left)', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
				'default'    => [ 'size' => 24, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content ul, {{WRAPPER}} .wk-post-content ol' => 'padding-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_list_margin',
			[
				'label'      => esc_html__( 'List Margin', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content ul, {{WRAPPER}} .wk-post-content ol' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_nested_list_indent',
			[
				'label'      => esc_html__( 'Nested List Indent', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
				'default'    => [ 'size' => 20, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content ul ul, {{WRAPPER}} .wk-post-content ol ol, {{WRAPPER}} .wk-post-content ul ol, {{WRAPPER}} .wk-post-content ol ul' => 'padding-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — TABLES (ALL POSSIBLE CONTROLS)
		 * ========================================================= */
		$this->start_controls_section(
			'wk_pc_section_style_table',
			[
				'label' => esc_html__( 'Tables (Complete Suite)', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		/* --- Table Layout & Box --- */
		$this->add_control(
			'wk_pc_table_heading_general',
			[
				'label' => esc_html__( 'General Table & Container', 'web-kit' ),
				'type'  => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'wk_pc_table_layout',
			[
				'label'     => esc_html__( 'Table Layout', 'web-kit' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'auto'  => esc_html__( 'Auto (fits content)', 'web-kit' ),
					'fixed' => esc_html__( 'Fixed (equal column distribution)', 'web-kit' ),
				],
				'default'   => 'auto',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table' => 'table-layout: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_table_width',
			[
				'label'      => esc_html__( 'Table Width', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px', 'vw' ],
				'range'      => [
					'%'  => [ 'min' => 20, 'max' => 100 ],
					'px' => [ 'min' => 200, 'max' => 1800 ],
				],
				'default'    => [ 'size' => 100, 'unit' => '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content table' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_table_max_width',
			[
				'label'      => esc_html__( 'Max Width', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range'      => [
					'%'  => [ 'min' => 20, 'max' => 100 ],
					'px' => [ 'min' => 200, 'max' => 2000 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content table' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_table_align',
			[
				'label'     => esc_html__( 'Table Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
				],
				'default'   => 'center',
				'selectors_dictionary' => [
					'left'   => 'margin-left: 0; margin-right: auto;',
					'center' => 'margin-left: auto; margin-right: auto;',
					'right'  => 'margin-left: auto; margin-right: 0;',
				],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table' => '{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'wk_pc_table_bg_color',
			[
				'label'     => esc_html__( 'Table Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_table_margin',
			[
				'label'      => esc_html__( 'Table Outer Margin', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content table, {{WRAPPER}} .wk-post-content .wp-block-table, {{WRAPPER}} .wk-post-content .wk-table-scroll-wrap' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'wk_pc_table_outer_border',
				'selector' => '{{WRAPPER}} .wk-post-content table',
			]
		);

		$this->add_responsive_control(
			'wk_pc_table_border_radius',
			[
				'label'      => esc_html__( 'Table Border Radius', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content table, {{WRAPPER}} .wk-post-content .wp-block-table' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'wk_pc_table_box_shadow',
				'selector' => '{{WRAPPER}} .wk-post-content table, {{WRAPPER}} .wk-post-content .wp-block-table',
			]
		);

		$this->add_control(
			'wk_pc_table_border_collapse',
			[
				'label'     => esc_html__( 'Border Collapse', 'web-kit' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'collapse' => esc_html__( 'Collapse (Merged borders)', 'web-kit' ),
					'separate' => esc_html__( 'Separate (Cell spacing enabled)', 'web-kit' ),
				],
				'default'   => 'collapse',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table' => 'border-collapse: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_table_border_spacing',
			[
				'label'      => esc_html__( 'Border Spacing', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 30 ] ],
				'default'    => [ 'size' => 4, 'unit' => 'px' ],
				'condition'  => [ 'wk_pc_table_border_collapse' => 'separate' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content table' => 'border-spacing: {{SIZE}}{{UNIT}};',
				],
			]
		);

		/* --- Table Header --- */
		$this->add_control(
			'wk_pc_table_heading_th',
			[
				'label'     => esc_html__( 'Header Row (TH)', 'web-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'wk_pc_th_bg_color',
			[
				'label'     => esc_html__( 'Header Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table thead th, {{WRAPPER}} .wk-post-content table thead tr' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_th_text_color',
			[
				'label'     => esc_html__( 'Header Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table thead th' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_th_typography',
				'selector' => '{{WRAPPER}} .wk-post-content table thead th',
			]
		);

		$this->add_responsive_control(
			'wk_pc_th_align',
			[
				'label'     => esc_html__( 'Header Text Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center'  => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'   => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
					'justify' => [ 'title' => esc_html__( 'Justify', 'web-kit' ), 'icon' => 'eicon-text-align-justify' ],
				],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table thead th' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_th_valign',
			[
				'label'     => esc_html__( 'Vertical Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'top'    => [ 'title' => esc_html__( 'Top', 'web-kit' ), 'icon' => 'eicon-v-align-top' ],
					'middle' => [ 'title' => esc_html__( 'Middle', 'web-kit' ), 'icon' => 'eicon-v-align-middle' ],
					'bottom' => [ 'title' => esc_html__( 'Bottom', 'web-kit' ), 'icon' => 'eicon-v-align-bottom' ],
				],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table thead th' => 'vertical-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_th_padding',
			[
				'label'      => esc_html__( 'Header Cell Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'default'    => [
					'top'      => '14',
					'right'    => '16',
					'bottom'   => '14',
					'left'     => '16',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content table thead th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'wk_pc_th_border',
				'selector' => '{{WRAPPER}} .wk-post-content table thead th',
			]
		);

		/* --- Table Body Cells --- */
		$this->add_control(
			'wk_pc_table_heading_td',
			[
				'label'     => esc_html__( 'Body Cells (TD)', 'web-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'wk_pc_td_bg_color',
			[
				'label'     => esc_html__( 'Cell Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody td, {{WRAPPER}} .wk-post-content table td' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_td_text_color',
			[
				'label'     => esc_html__( 'Cell Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#334155',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody td, {{WRAPPER}} .wk-post-content table td' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_td_typography',
				'selector' => '{{WRAPPER}} .wk-post-content table tbody td, {{WRAPPER}} .wk-post-content table td',
			]
		);

		$this->add_responsive_control(
			'wk_pc_td_align',
			[
				'label'     => esc_html__( 'Cell Text Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'    => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center'  => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'   => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
					'justify' => [ 'title' => esc_html__( 'Justify', 'web-kit' ), 'icon' => 'eicon-text-align-justify' ],
				],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody td, {{WRAPPER}} .wk-post-content table td' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_td_valign',
			[
				'label'     => esc_html__( 'Vertical Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'top'    => [ 'title' => esc_html__( 'Top', 'web-kit' ), 'icon' => 'eicon-v-align-top' ],
					'middle' => [ 'title' => esc_html__( 'Middle', 'web-kit' ), 'icon' => 'eicon-v-align-middle' ],
					'bottom' => [ 'title' => esc_html__( 'Bottom', 'web-kit' ), 'icon' => 'eicon-v-align-bottom' ],
				],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody td, {{WRAPPER}} .wk-post-content table td' => 'vertical-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_td_padding',
			[
				'label'      => esc_html__( 'Cell Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'default'    => [
					'top'      => '12',
					'right'    => '16',
					'bottom'   => '12',
					'left'     => '16',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content table tbody td, {{WRAPPER}} .wk-post-content table td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'wk_pc_td_border',
				'selector' => '{{WRAPPER}} .wk-post-content table tbody td, {{WRAPPER}} .wk-post-content table td',
			]
		);

		$this->add_control(
			'wk_pc_td_link_color',
			[
				'label'     => esc_html__( 'Cell Link Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563eb',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody td a, {{WRAPPER}} .wk-post-content table td a' => 'color: {{VALUE}}; font-weight: 500;',
				],
			]
		);

		$this->add_control(
			'wk_pc_td_link_hover_color',
			[
				'label'     => esc_html__( 'Cell Link Hover Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1d4ed8',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody td a:hover, {{WRAPPER}} .wk-post-content table td a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		/* --- Alternating / Striped Rows --- */
		$this->add_control(
			'wk_pc_table_heading_stripes',
			[
				'label'     => esc_html__( 'Alternating / Striped Rows', 'web-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'wk_pc_table_striped_enable',
			[
				'label'        => esc_html__( 'Enable Alternating Rows', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'wk_pc_table_striped_even_bg',
			[
				'label'     => esc_html__( 'Even Row Background', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F8FAFC',
				'condition' => [ 'wk_pc_table_striped_enable' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody tr:nth-child(even) td' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_table_striped_even_color',
			[
				'label'     => esc_html__( 'Even Row Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 'wk_pc_table_striped_enable' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody tr:nth-child(even) td' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_table_striped_odd_bg',
			[
				'label'     => esc_html__( 'Odd Row Background', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'condition' => [ 'wk_pc_table_striped_enable' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody tr:nth-child(odd) td' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_table_striped_odd_color',
			[
				'label'     => esc_html__( 'Odd Row Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 'wk_pc_table_striped_enable' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody tr:nth-child(odd) td' => 'color: {{VALUE}};',
				],
			]
		);

		/* --- Row Hover --- */
		$this->add_control(
			'wk_pc_table_heading_hover',
			[
				'label'     => esc_html__( 'Row Hover State', 'web-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'wk_pc_table_hover_enable',
			[
				'label'        => esc_html__( 'Enable Row Hover Effect', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'wk_pc_table_hover_bg',
			[
				'label'     => esc_html__( 'Hover Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#EFF6FF',
				'condition' => [ 'wk_pc_table_hover_enable' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody tr:hover td' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'wk_pc_table_hover_color',
			[
				'label'     => esc_html__( 'Hover Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 'wk_pc_table_hover_enable' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody tr:hover td' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_table_hover_transition',
			[
				'label'      => esc_html__( 'Hover Transition (ms)', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 600 ] ],
				'default'    => [ 'size' => 150, 'unit' => 'px' ],
				'condition'  => [ 'wk_pc_table_hover_enable' => 'yes' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content table tbody td' => 'transition: background-color {{SIZE}}ms ease, color {{SIZE}}ms ease;',
				],
			]
		);

		/* --- First Column Accent --- */
		$this->add_control(
			'wk_pc_table_heading_first_col',
			[
				'label'     => esc_html__( 'First Column (Accent Column)', 'web-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'wk_pc_table_first_col_enable',
			[
				'label'        => esc_html__( 'Custom First Column Style', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Highlights the first column (e.g. feature names or row headers in comparison tables).', 'web-kit' ),
			]
		);

		$this->add_control(
			'wk_pc_table_first_col_bg',
			[
				'label'     => esc_html__( 'First Column Background', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 'wk_pc_table_first_col_enable' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody tr td:first-child' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_table_first_col_color',
			[
				'label'     => esc_html__( 'First Column Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0f172a',
				'condition' => [ 'wk_pc_table_first_col_enable' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody tr td:first-child' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_table_first_col_font_weight',
			[
				'label'     => esc_html__( 'First Column Font Weight', 'web-kit' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''    => esc_html__( 'Default', 'web-kit' ),
					'500' => '500',
					'600' => '600 (Semi-bold)',
					'700' => '700 (Bold)',
				],
				'default'   => '600',
				'condition' => [ 'wk_pc_table_first_col_enable' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody tr td:first-child' => 'font-weight: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_table_first_col_align',
			[
				'label'     => esc_html__( 'First Column Text Align', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
				],
				'condition' => [ 'wk_pc_table_first_col_enable' => 'yes' ],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tbody tr td:first-child' => 'text-align: {{VALUE}};',
				],
			]
		);

		/* --- Table Footer --- */
		$this->add_control(
			'wk_pc_table_heading_tfoot',
			[
				'label'     => esc_html__( 'Table Footer (TFOOT)', 'web-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'wk_pc_tfoot_bg_color',
			[
				'label'     => esc_html__( 'Footer Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F1F5F9',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tfoot td, {{WRAPPER}} .wk-post-content table tfoot th' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_tfoot_text_color',
			[
				'label'     => esc_html__( 'Footer Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#475569',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table tfoot td, {{WRAPPER}} .wk-post-content table tfoot th' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_tfoot_typography',
				'selector' => '{{WRAPPER}} .wk-post-content table tfoot td, {{WRAPPER}} .wk-post-content table tfoot th',
			]
		);

		$this->add_responsive_control(
			'wk_pc_tfoot_padding',
			[
				'label'      => esc_html__( 'Footer Cell Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content table tfoot td, {{WRAPPER}} .wk-post-content table tfoot th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'wk_pc_tfoot_border',
				'selector' => '{{WRAPPER}} .wk-post-content table tfoot td, {{WRAPPER}} .wk-post-content table tfoot th',
			]
		);

		/* --- Table Caption --- */
		$this->add_control(
			'wk_pc_table_heading_caption',
			[
				'label'     => esc_html__( 'Table Caption', 'web-kit' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'wk_pc_table_caption_side',
			[
				'label'     => esc_html__( 'Caption Position', 'web-kit' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'bottom' => esc_html__( 'Bottom', 'web-kit' ),
					'top'    => esc_html__( 'Top', 'web-kit' ),
				],
				'default'   => 'bottom',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table caption' => 'caption-side: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_table_caption_color',
			[
				'label'     => esc_html__( 'Caption Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table caption, {{WRAPPER}} .wk-post-content .wp-block-table figcaption' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_table_caption_typography',
				'selector' => '{{WRAPPER}} .wk-post-content table caption, {{WRAPPER}} .wk-post-content .wp-block-table figcaption',
			]
		);

		$this->add_responsive_control(
			'wk_pc_table_caption_align',
			[
				'label'     => esc_html__( 'Caption Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'   => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center' => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'right'  => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
				],
				'selectors' => [
					'{{WRAPPER}} .wk-post-content table caption, {{WRAPPER}} .wk-post-content .wp-block-table figcaption' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_table_caption_padding',
			[
				'label'      => esc_html__( 'Caption Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content table caption, {{WRAPPER}} .wk-post-content .wp-block-table figcaption' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — BLOCKQUOTES
		 * ========================================================= */
		$this->start_controls_section(
			'wk_pc_section_style_quotes',
			[
				'label' => esc_html__( 'Blockquotes', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_pc_quote_text_color',
			[
				'label'     => esc_html__( 'Quote Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1e293b',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content blockquote, {{WRAPPER}} .wk-post-content .wp-block-quote, {{WRAPPER}} .wk-post-content blockquote p' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_quote_typography',
				'selector' => '{{WRAPPER}} .wk-post-content blockquote, {{WRAPPER}} .wk-post-content .wp-block-quote, {{WRAPPER}} .wk-post-content blockquote p',
			]
		);

		$this->add_control(
			'wk_pc_quote_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F8FAFC',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content blockquote, {{WRAPPER}} .wk-post-content .wp-block-quote' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_quote_border_color',
			[
				'label'     => esc_html__( 'Border Left Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563eb',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content blockquote, {{WRAPPER}} .wk-post-content .wp-block-quote' => 'border-left-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_quote_border_width',
			[
				'label'      => esc_html__( 'Border Left Width', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 20 ] ],
				'default'    => [ 'size' => 4, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content blockquote, {{WRAPPER}} .wk-post-content .wp-block-quote' => 'border-left-width: {{SIZE}}{{UNIT}}; border-left-style: solid;',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_quote_padding',
			[
				'label'      => esc_html__( 'Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'default'    => [
					'top'      => '16',
					'right'    => '24',
					'bottom'   => '16',
					'left'     => '24',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content blockquote, {{WRAPPER}} .wk-post-content .wp-block-quote' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_quote_margin',
			[
				'label'      => esc_html__( 'Margin', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content blockquote, {{WRAPPER}} .wk-post-content .wp-block-quote' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_quote_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content blockquote, {{WRAPPER}} .wk-post-content .wp-block-quote' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_quote_cite_color',
			[
				'label'     => esc_html__( 'Author / Cite Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748b',
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content blockquote cite, {{WRAPPER}} .wk-post-content .wp-block-quote cite' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_quote_cite_typography',
				'selector' => '{{WRAPPER}} .wk-post-content blockquote cite, {{WRAPPER}} .wk-post-content .wp-block-quote cite',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — CODE & PREFORMATTED
		 * ========================================================= */
		$this->start_controls_section(
			'wk_pc_section_style_code',
			[
				'label' => esc_html__( 'Code & Preformatted', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_pc_code_color',
			[
				'label'     => esc_html__( 'Code Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F8FAFC',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content pre, {{WRAPPER}} .wk-post-content .wp-block-code, {{WRAPPER}} .wk-post-content pre code' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_code_bg_color',
			[
				'label'     => esc_html__( 'Code Block Background', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1E293B',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content pre, {{WRAPPER}} .wk-post-content .wp-block-code' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_inline_code_bg',
			[
				'label'     => esc_html__( 'Inline Code Background', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F1F5F9',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content p code, {{WRAPPER}} .wk-post-content li code' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_inline_code_color',
			[
				'label'     => esc_html__( 'Inline Code Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0F172A',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content p code, {{WRAPPER}} .wk-post-content li code' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_code_typography',
				'selector' => '{{WRAPPER}} .wk-post-content pre, {{WRAPPER}} .wk-post-content code',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'wk_pc_code_border',
				'selector' => '{{WRAPPER}} .wk-post-content pre, {{WRAPPER}} .wk-post-content .wp-block-code',
			]
		);

		$this->add_responsive_control(
			'wk_pc_code_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content pre, {{WRAPPER}} .wk-post-content .wp-block-code' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_code_padding',
			[
				'label'      => esc_html__( 'Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content pre, {{WRAPPER}} .wk-post-content .wp-block-code' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — SEPARATOR / DIVIDER
		 * ========================================================= */
		$this->start_controls_section(
			'wk_pc_section_style_separator',
			[
				'label' => esc_html__( 'Separator / Divider (HR)', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_pc_separator_color',
			[
				'label'     => esc_html__( 'Divider Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E2E8F0',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content hr, {{WRAPPER}} .wk-post-content hr.wp-block-separator' => 'border-top-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_separator_style',
			[
				'label'     => esc_html__( 'Style', 'web-kit' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'solid'  => esc_html__( 'Solid', 'web-kit' ),
					'dashed' => esc_html__( 'Dashed', 'web-kit' ),
					'dotted' => esc_html__( 'Dotted', 'web-kit' ),
					'double' => esc_html__( 'Double', 'web-kit' ),
				],
				'default'   => 'solid',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content hr, {{WRAPPER}} .wk-post-content hr.wp-block-separator' => 'border-top-style: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_separator_thickness',
			[
				'label'      => esc_html__( 'Thickness', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [ 'px' => [ 'min' => 1, 'max' => 12 ] ],
				'default'    => [ 'size' => 1, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content hr, {{WRAPPER}} .wk-post-content hr.wp-block-separator' => 'border-top-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_separator_width',
			[
				'label'      => esc_html__( 'Width', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range'      => [
					'%'  => [ 'min' => 10, 'max' => 100 ],
					'px' => [ 'min' => 50, 'max' => 1200 ],
				],
				'default'    => [ 'size' => 100, 'unit' => '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content hr, {{WRAPPER}} .wk-post-content hr.wp-block-separator' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_separator_margin',
			[
				'label'      => esc_html__( 'Margin (Spacing)', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content hr, {{WRAPPER}} .wk-post-content hr.wp-block-separator' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — GUTENBERG BUTTONS
		 * ========================================================= */
		$this->start_controls_section(
			'wk_pc_section_style_buttons',
			[
				'label' => esc_html__( 'Gutenberg Buttons', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_btn_typography',
				'selector' => '{{WRAPPER}} .wk-post-content .wp-block-button__link',
			]
		);

		$this->start_controls_tabs( 'wk_pc_tabs_btn' );

		$this->start_controls_tab(
			'wk_pc_tab_btn_normal',
			[ 'label' => esc_html__( 'Normal', 'web-kit' ) ]
		);

		$this->add_control(
			'wk_pc_btn_color',
			[
				'label'     => esc_html__( 'Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content .wp-block-button__link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_btn_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563EB',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content .wp-block-button__link' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'wk_pc_btn_border',
				'selector' => '{{WRAPPER}} .wk-post-content .wp-block-button__link',
			]
		);

		$this->add_responsive_control(
			'wk_pc_btn_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content .wp-block-button__link' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'wk_pc_btn_box_shadow',
				'selector' => '{{WRAPPER}} .wk-post-content .wp-block-button__link',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'wk_pc_tab_btn_hover',
			[ 'label' => esc_html__( 'Hover', 'web-kit' ) ]
		);

		$this->add_control(
			'wk_pc_btn_hover_color',
			[
				'label'     => esc_html__( 'Hover Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content .wp-block-button__link:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_btn_hover_bg_color',
			[
				'label'     => esc_html__( 'Hover Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1D4ED8',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content .wp-block-button__link:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_btn_hover_border_color',
			[
				'label'     => esc_html__( 'Hover Border Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wk-post-content .wp-block-button__link:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'wk_pc_btn_hover_box_shadow',
				'selector' => '{{WRAPPER}} .wk-post-content .wp-block-button__link:hover',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_responsive_control(
			'wk_pc_btn_padding',
			[
				'label'      => esc_html__( 'Button Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem' ],
				'separator'  => 'before',
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content .wp-block-button__link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — CUSTOM HIGHLIGHT BOX (.wk-highlight-box)
		 * ========================================================= */
		$this->start_controls_section(
			'wk_pc_section_style_highlight_box',
			[
				'label' => esc_html__( 'Highlight Box (.wk-highlight-box)', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_pc_highlight_box_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F8FAFC',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content .wk-highlight-box' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_highlight_box_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#334155',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content .wk-highlight-box, {{WRAPPER}} .wk-post-content .wk-highlight-box p' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_highlight_box_typography',
				'selector' => '{{WRAPPER}} .wk-post-content .wk-highlight-box, {{WRAPPER}} .wk-post-content .wk-highlight-box p',
			]
		);

		$this->add_responsive_control(
			'wk_pc_highlight_box_padding',
			[
				'label'      => esc_html__( 'Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'default'    => [
					'top'      => '20',
					'right'    => '24',
					'bottom'   => '20',
					'left'     => '24',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content .wk-highlight-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_highlight_box_margin',
			[
				'label'      => esc_html__( 'Margin', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'default'    => [
					'top'      => '24',
					'right'    => '0',
					'bottom'   => '24',
					'left'     => '0',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content .wk-highlight-box' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'wk_pc_highlight_box_border',
				'selector' => '{{WRAPPER}} .wk-post-content .wk-highlight-box',
			]
		);

		$this->add_responsive_control(
			'wk_pc_highlight_box_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'      => '8',
					'right'    => '8',
					'bottom'   => '8',
					'left'     => '8',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content .wk-highlight-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'wk_pc_highlight_box_box_shadow',
				'selector' => '{{WRAPPER}} .wk-post-content .wk-highlight-box',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — CUSTOM HIGHLIGHT BOX V2 (.wk-highlight-box-v2)
		 * ========================================================= */
		$this->start_controls_section(
			'wk_pc_section_style_highlight_box_v2',
			[
				'label' => esc_html__( 'Highlight Box V2 (.wk-highlight-box-v2)', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_pc_highlight_box_v2_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F1F5F9',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content .wk-highlight-box-v2' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_pc_highlight_box_v2_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#334155',
				'selectors' => [
					'{{WRAPPER}} .wk-post-content .wk-highlight-box-v2, {{WRAPPER}} .wk-post-content .wk-highlight-box-v2 p' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_pc_highlight_box_v2_typography',
				'selector' => '{{WRAPPER}} .wk-post-content .wk-highlight-box-v2, {{WRAPPER}} .wk-post-content .wk-highlight-box-v2 p',
			]
		);

		$this->add_responsive_control(
			'wk_pc_highlight_box_v2_padding',
			[
				'label'      => esc_html__( 'Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'default'    => [
					'top'      => '20',
					'right'    => '24',
					'bottom'   => '20',
					'left'     => '24',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content .wk-highlight-box-v2' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_pc_highlight_box_v2_margin',
			[
				'label'      => esc_html__( 'Margin', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'default'    => [
					'top'      => '24',
					'right'    => '0',
					'bottom'   => '24',
					'left'     => '0',
					'unit'     => 'px',
					'isLinked' => false,
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content .wk-highlight-box-v2' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'wk_pc_highlight_box_v2_border',
				'selector' => '{{WRAPPER}} .wk-post-content .wk-highlight-box-v2',
			]
		);

		$this->add_responsive_control(
			'wk_pc_highlight_box_v2_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'      => '8',
					'right'    => '8',
					'bottom'   => '8',
					'left'     => '8',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-post-content .wk-highlight-box-v2' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'wk_pc_highlight_box_v2_box_shadow',
				'selector' => '{{WRAPPER}} .wk-post-content .wk-highlight-box-v2',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Returns full sample Gutenberg block content so users can visually preview
	 * and adjust all headings, paragraphs, lists, media, and tables in the Elementor editor.
	 *
	 * @return string
	 */
	protected function get_sample_content() {
		return '
		<h1 class="wp-block-heading">Web Kit Post Content Widget (H1)</h1>
		<p class="has-drop-cap">This is a sample post content paragraph demonstrating the Gutenberg typographic flow. Web Kit provides full Elementor styling controls over every native WordPress block, including <a href="#">interactive inline links</a>, bold text (<strong>strong</strong>), and italics (<em>emphasis</em>).</p>

		<h2 class="wp-block-heading">Heading Level 2: Section Overview (H2)</h2>
		<p>Customize margins, paddings, colors, and font styles across all headings collectively or individually level-by-level from H1 down to H6.</p>

		<h3 class="wp-block-heading">Heading Level 3: Media & Captions (H3)</h3>
		<figure class="wp-block-image size-large aligncenter">
			<img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1000&auto=format&fit=crop&q=80" alt="Web Development Workspace" />
			<figcaption>Sample responsive figure demonstrating border, radius, shadow, and caption styling.</figcaption>
		</figure>

		<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio aligncenter">
			<div class="wp-block-embed__wrapper">
				<iframe title="Sample Video Embed" width="100%" height="340" src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			</div>
			<figcaption>Sample responsive video embed with customizable aspect ratio, borders, radius, shadow, and caption styling.</figcaption>
		</figure>

		<h4 class="wp-block-heading">Heading Level 4: Lists & Nesting (H4)</h4>
		<ul class="wp-block-list">
			<li>Unordered list item showing customized bullet marker colors.</li>
			<li>Second list item with multiple sub-levels:
				<ul>
					<li>Nested unordered item with adjustable left indentation.</li>
					<li>Another nested list entry verifying styling hierarchy.</li>
				</ul>
			</li>
			<li>Third main bullet point with custom item spacing.</li>
		</ul>

		<ol class="wp-block-list">
			<li>Ordered step one with numbered marker styling.</li>
			<li>Ordered step two demonstrating line height and margins.</li>
			<li>Ordered step three with full typography support.</li>
		</ol>

		<h2 class="wp-block-heading">Complete Gutenberg Table Suite</h2>
		<p>The table below showcases headers, body cells, alternating zebra stripes, hover effects, accent columns, footers, and responsive scrolling:</p>

		<figure class="wp-block-table is-style-stripes">
			<table class="has-fixed-layout">
				<thead>
					<tr>
						<th>Feature / Block</th>
						<th>Basic Styling</th>
						<th>Pro Styling</th>
						<th>Enterprise Controls</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Headings (H1 to H6)</td>
						<td>Color & Margin</td>
						<td>Full Typography</td>
						<td>Individual H1–H6 Overrides</td>
					</tr>
					<tr>
						<td>Paragraphs & Links</td>
						<td>Standard Text</td>
						<td>Hover & Drop Caps</td>
						<td>Full Spacing & Dimensions</td>
					</tr>
					<tr>
						<td>Images & Media</td>
						<td>Responsive Width</td>
						<td>Border & Radius</td>
						<td>Shadows, Captions & Align</td>
					</tr>
					<tr>
						<td>Lists (UL & OL)</td>
						<td>Bullet Types</td>
						<td>Marker Colors</td>
						<td>Nested Indents & Spacing</td>
					</tr>
					<tr>
						<td>Gutenberg Tables</td>
						<td>Borders & Headings</td>
						<td>Stripes & Hover</td>
						<td>All Possible Controls</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td>Summary Status</td>
						<td>Essential</td>
						<td>Enhanced</td>
						<td>Complete Suite</td>
					</tr>
				</tfoot>
				<caption>Comparison Table: Visualizing All Table Controls</caption>
			</table>
		</figure>

		<div class="wk-highlight-box">
			<h4 class="wp-block-heading" style="margin-top:0;">Pro Tip: Custom Highlight Box (.wk-highlight-box)</h4>
			<p>Any block, group, or container in WordPress with the CSS class <code>.wk-highlight-box</code> will automatically receive these customizable padding, margin, border, background color, and border-radius styles.</p>
		</div>

		<div class="wk-highlight-box-v2">
			<h4 class="wp-block-heading" style="margin-top:0;">Notice: Custom Highlight Box V2 (.wk-highlight-box-v2)</h4>
			<p>Any block, group, or container in WordPress with the CSS class <code>.wk-highlight-box-v2</code> can be styled independently with its own padding, margin, borders, colors, and shadows.</p>
		</div>

		<blockquote class="wp-block-quote">
			<p>“Good design is making something intelligible and memorable. Great design is making something memorable and meaningful.”</p>
			<cite>Dieter Rams</cite>
		</blockquote>

		<pre class="wp-block-code"><code>// Web Kit Post Content Widget
add_action( "elementor/widgets/register", function( $manager ) {
    $manager->register( new \\WebKit\\Widgets\\Post_Content() );
} );</code></pre>

		<hr class="wp-block-separator is-style-wide" />

		<div class="wp-block-buttons">
			<div class="wp-block-button"><a class="wp-block-button__link" href="#">Discover Web Kit</a></div>
		</div>
		';
	}

	protected function render() {
		static $rendering = false;

		// Prevent infinite loop if the_content filter calls this widget again.
		if ( $rendering ) {
			return;
		}

		$settings = $this->get_settings_for_display();
		$source   = ! empty( $settings['wk_pc_content_source'] ) ? $settings['wk_pc_content_source'] : 'current_post';

		$is_editor = false;
		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) ) {
			$is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
		}

		$content = '';

		if ( 'custom' === $source ) {
			$raw_content = ! empty( $settings['wk_pc_custom_content'] ) ? $settings['wk_pc_custom_content'] : '';
			$content     = apply_filters( 'the_content', $raw_content );
		} elseif ( 'sample' === $source ) {
			$content = $this->get_sample_content();
		} else {
			// 'current_post'
			$post = get_post();

			if ( $post && ! post_password_required( $post->ID ) ) {
				$rendering = true;

				$post_content = $post->post_content;

				// If post content is empty in editor (or sample fallback requested)
				if ( empty( trim( $post_content ) ) && $is_editor && 'yes' === ( $settings['wk_pc_preview_sample_fallback'] ?? 'yes' ) ) {
					$content = $this->get_sample_content();
				} else {
					// Remove Elementor frontend content filter to prevent recursive widget rendering
					if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
						\Elementor\Plugin::$instance->frontend->remove_content_filter();
					}

					$content = apply_filters( 'the_content', $post_content );

					if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
						\Elementor\Plugin::$instance->frontend->add_content_filter();
					}
				}

				$rendering = false;
			} else {
				if ( $post && post_password_required( $post->ID ) ) {
					echo get_the_password_form( $post->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					return;
				}
				if ( $is_editor ) {
					$content = $this->get_sample_content();
				}
			}
		}

		if ( empty( trim( $content ) ) && $is_editor ) {
			$content = $this->get_sample_content();
		}

		// Table scroll wrapper for standalone tables not wrapped in wp-block-table
		if ( 'yes' === ( $settings['wk_pc_table_responsive_scroll'] ?? 'yes' ) && ! empty( $content ) ) {
			// Ensure raw tables get wrapped in a scroll container if not already in figure.wp-block-table
			$content = preg_replace_callback(
				'/(<figure[^>]*class="[^"]*wp-block-table[^"]*"[^>]*>.*?<\/figure>)|(<table\b[^>]*>.*?<\/table>)/is',
				function ( $matches ) {
					// If already matched a figure wp-block-table, leave untouched
					if ( ! empty( $matches[1] ) ) {
						return $matches[1];
					}
					// Standalone table: wrap in wk-table-scroll-wrap
					return '<div class="wk-table-scroll-wrap">' . $matches[2] . '</div>';
				},
				$content
			);
		}

		$wrapper_classes = [ 'wk-post-content' ];

		if ( 'yes' === ( $settings['wk_pc_table_responsive_scroll'] ?? 'yes' ) ) {
			$wrapper_classes[] = 'wk-has-table-scroll';
		}

		if ( 'yes' === ( $settings['wk_pc_table_header_sticky'] ?? '' ) ) {
			$wrapper_classes[] = 'wk-table-sticky-head';
		}

		printf(
			'<div class="%1$s">%2$s</div>',
			esc_attr( implode( ' ', $wrapper_classes ) ),
			$content // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
}
