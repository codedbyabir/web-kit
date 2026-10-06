<?php
/**
 * Web Kit Author & Reviewer Widget.
 *
 * Minimalist, elegant widget displaying the post author and designated reviewer
 * side-by-side with circular avatars, labels ("Written By", "Reviewed By"), names,
 * and a subtle vertical divider line. Supports post meta from the Reviewer meta box
 * with a fallback user control in the widget settings.
 *
 * @package Web_Kit
 */

namespace WebKit\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use WebKit\Reviewer_Metabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Author_Reviewer extends Widget_Base {

	public function get_name() {
		return 'wk_author_reviewer';
	}

	public function get_title() {
		return esc_html__( 'Web Kit Author & Reviewer', 'web-kit' );
	}

	public function get_icon() {
		return 'eicon-user-circle-o';
	}

	public function get_categories() {
		return [ 'web-kit' ];
	}

	public function get_keywords() {
		return [ 'author', 'reviewer', 'reviewed by', 'written by', 'avatar', 'user', 'meta', 'post author', 'web kit' ];
	}

	public function get_style_depends() {
		return [ 'wk-author-reviewer' ];
	}

	/**
	 * Helper to get list of WordPress users for dropdowns.
	 *
	 * @return array
	 */
	protected function get_user_options() {
		$options = [
			'' => esc_html__( '— None / Select User —', 'web-kit' ),
		];

		$users = get_users( [
			'number'  => 150,
			'orderby' => 'display_name',
			'order'   => 'ASC',
			'fields'  => [ 'ID', 'display_name', 'user_email' ],
		] );

		if ( ! empty( $users ) && is_array( $users ) ) {
			foreach ( $users as $user ) {
				$options[ (string) $user->ID ] = sprintf( '%s (#%d)', $user->display_name, $user->ID );
			}
		}

		return $options;
	}

	protected function register_controls() {

		/* =========================================================
		 * CONTENT TAB — GENERAL
		 * ========================================================= */
		$this->start_controls_section(
			'wk_ar_section_general',
			[
				'label' => esc_html__( 'Display & Structure', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'wk_ar_show_author',
			[
				'label'        => esc_html__( 'Show Author', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'wk_ar_show_reviewer',
			[
				'label'        => esc_html__( 'Show Reviewer', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'wk_ar_show_divider',
			[
				'label'        => esc_html__( 'Show Vertical Divider', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'condition'    => [
					'wk_ar_show_author'   => 'yes',
					'wk_ar_show_reviewer' => 'yes',
				],
			]
		);

		$this->add_control(
			'wk_ar_link_items',
			[
				'label'        => esc_html__( 'Link to Author / User Archives', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * CONTENT TAB — AUTHOR
		 * ========================================================= */
		$this->start_controls_section(
			'wk_ar_section_author',
			[
				'label'     => esc_html__( 'Author', 'web-kit' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'wk_ar_show_author' => 'yes' ],
			]
		);

		$this->add_control(
			'wk_ar_author_label',
			[
				'label'       => esc_html__( 'Label Text', 'web-kit' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Written By', 'web-kit' ),
				'placeholder' => esc_html__( 'Written By', 'web-kit' ),
			]
		);

		$this->add_control(
			'wk_ar_author_source',
			[
				'label'   => esc_html__( 'Author Source', 'web-kit' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'current_post',
				'options' => [
					'current_post' => esc_html__( 'Current Post Author (Dynamic)', 'web-kit' ),
					'custom_user'  => esc_html__( 'Specific WordPress User', 'web-kit' ),
					'custom'       => esc_html__( 'Custom Name & Avatar', 'web-kit' ),
				],
			]
		);

		$this->add_control(
			'wk_ar_author_user',
			[
				'label'     => esc_html__( 'Select WordPress User', 'web-kit' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => $this->get_user_options(),
				'condition' => [ 'wk_ar_author_source' => 'custom_user' ],
			]
		);

		$this->add_control(
			'wk_ar_author_custom_name',
			[
				'label'       => esc_html__( 'Custom Author Name', 'web-kit' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'Boris Vance',
				'condition'   => [ 'wk_ar_author_source' => 'custom' ],
				'label_block' => true,
			]
		);

		$this->add_control(
			'wk_ar_author_custom_avatar',
			[
				'label'     => esc_html__( 'Custom Author Avatar', 'web-kit' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => [ 'wk_ar_author_source' => 'custom' ],
			]
		);

		$this->add_control(
			'wk_ar_author_custom_url',
			[
				'label'       => esc_html__( 'Custom Author URL', 'web-kit' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://example.com/author/boris-vance',
				'condition'   => [ 'wk_ar_author_source' => 'custom' ],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * CONTENT TAB — REVIEWER
		 * ========================================================= */
		$this->start_controls_section(
			'wk_ar_section_reviewer',
			[
				'label'     => esc_html__( 'Reviewer', 'web-kit' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => [ 'wk_ar_show_reviewer' => 'yes' ],
			]
		);

		$this->add_control(
			'wk_ar_reviewer_label',
			[
				'label'       => esc_html__( 'Label Text', 'web-kit' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Reviewed By', 'web-kit' ),
				'placeholder' => esc_html__( 'Reviewed By', 'web-kit' ),
			]
		);

		$this->add_control(
			'wk_ar_reviewer_source',
			[
				'label'   => esc_html__( 'Reviewer Source', 'web-kit' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'post_meta',
				'options' => [
					'post_meta'   => esc_html__( 'Post Meta (From Post Edit Screen)', 'web-kit' ),
					'custom_user' => esc_html__( 'Specific WordPress User', 'web-kit' ),
					'custom'      => esc_html__( 'Custom Name & Avatar', 'web-kit' ),
				],
			]
		);

		$this->add_control(
			'wk_ar_reviewer_fallback_user',
			[
				'label'       => esc_html__( 'Fallback Reviewer User', 'web-kit' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $this->get_user_options(),
				'description' => esc_html__( 'Used when no reviewer is chosen on the post edit screen or during editor preview.', 'web-kit' ),
				'condition'   => [ 'wk_ar_reviewer_source' => 'post_meta' ],
			]
		);

		$this->add_control(
			'wk_ar_reviewer_user',
			[
				'label'     => esc_html__( 'Select WordPress User', 'web-kit' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => $this->get_user_options(),
				'condition' => [ 'wk_ar_reviewer_source' => 'custom_user' ],
			]
		);

		$this->add_control(
			'wk_ar_reviewer_custom_name',
			[
				'label'       => esc_html__( 'Custom Reviewer Name', 'web-kit' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'Starc Nick Fury',
				'condition'   => [ 'wk_ar_reviewer_source' => 'custom' ],
				'label_block' => true,
			]
		);

		$this->add_control(
			'wk_ar_reviewer_custom_avatar',
			[
				'label'     => esc_html__( 'Custom Reviewer Avatar', 'web-kit' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => [ 'wk_ar_reviewer_source' => 'custom' ],
			]
		);

		$this->add_control(
			'wk_ar_reviewer_custom_url',
			[
				'label'       => esc_html__( 'Custom Reviewer URL', 'web-kit' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => 'https://example.com/reviewer/starc-fury',
				'condition'   => [ 'wk_ar_reviewer_source' => 'custom' ],
			]
		);

		$this->add_control(
			'wk_ar_reviewer_hide_empty',
			[
				'label'        => esc_html__( 'Hide If No Reviewer', 'web-kit' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Hides the reviewer item and divider on the front-end if no reviewer is selected on the post and no fallback user is set.', 'web-kit' ),
				'condition'    => [ 'wk_ar_reviewer_source' => 'post_meta' ],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — LAYOUT & CONTAINER
		 * ========================================================= */
		$this->start_controls_section(
			'wk_ar_section_style_layout',
			[
				'label' => esc_html__( 'Layout & Container', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'wk_ar_layout_direction',
			[
				'label'     => esc_html__( 'Layout Direction', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'row'    => [ 'title' => esc_html__( 'Inline / Horizontal', 'web-kit' ), 'icon' => 'eicon-ellipsis-h' ],
					'column' => [ 'title' => esc_html__( 'Stacked / Vertical', 'web-kit' ), 'icon' => 'eicon-ellipsis-v' ],
				],
				'default'   => 'row',
				'selectors' => [
					'{{WRAPPER}} .wk-author-reviewer-inner' => 'flex-direction: {{VALUE}};',
					'{{WRAPPER}} .wk-author-reviewer-inner' => 'align-items: {{VALUE}} === "column" ? "flex-start" : "center";',
				],
			]
		);

		$this->add_responsive_control(
			'wk_ar_alignment',
			[
				'label'     => esc_html__( 'Alignment', 'web-kit' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'flex-start'    => [ 'title' => esc_html__( 'Left', 'web-kit' ), 'icon' => 'eicon-text-align-left' ],
					'center'        => [ 'title' => esc_html__( 'Center', 'web-kit' ), 'icon' => 'eicon-text-align-center' ],
					'flex-end'      => [ 'title' => esc_html__( 'Right', 'web-kit' ), 'icon' => 'eicon-text-align-right' ],
					'space-between' => [ 'title' => esc_html__( 'Space Between', 'web-kit' ), 'icon' => 'eicon-text-align-justify' ],
				],
				'default'   => 'flex-start',
				'selectors' => [
					'{{WRAPPER}} .wk-author-reviewer-inner' => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .wk-author-reviewer-wrap'  => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_ar_items_gap',
			[
				'label'      => esc_html__( 'Gap Between Items', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 100 ],
				],
				'default'    => [ 'size' => 32, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-author-reviewer-inner' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_ar_container_padding',
			[
				'label'      => esc_html__( 'Container Padding', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', 'rem', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-author-reviewer-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'wk_ar_container_bg',
			[
				'label'     => esc_html__( 'Background Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wk-author-reviewer-wrap' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'wk_ar_container_border',
				'selector' => '{{WRAPPER}} .wk-author-reviewer-wrap',
			]
		);

		$this->add_responsive_control(
			'wk_ar_container_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-author-reviewer-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'wk_ar_container_shadow',
				'selector' => '{{WRAPPER}} .wk-author-reviewer-wrap',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — AVATAR
		 * ========================================================= */
		$this->start_controls_section(
			'wk_ar_section_style_avatar',
			[
				'label' => esc_html__( 'Avatars', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'wk_ar_avatar_size',
			[
				'label'      => esc_html__( 'Size', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 28, 'max' => 120 ],
				],
				'default'    => [ 'size' => 52, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-ar-avatar' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_ar_avatar_spacing',
			[
				'label'      => esc_html__( 'Spacing (Gap to Text)', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 4, 'max' => 40 ],
				],
				'default'    => [ 'size' => 14, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-ar-item' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_ar_avatar_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'default'    => [
					'top'      => '50',
					'right'    => '50',
					'bottom'   => '50',
					'left'     => '50',
					'unit'     => '%',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .wk-ar-avatar, {{WRAPPER}} .wk-ar-avatar img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'wk_ar_avatar_border',
				'selector' => '{{WRAPPER}} .wk-ar-avatar',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'wk_ar_avatar_box_shadow',
				'selector' => '{{WRAPPER}} .wk-ar-avatar',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — LABELS ("Written By" / "Reviewed By")
		 * ========================================================= */
		$this->start_controls_section(
			'wk_ar_section_style_label',
			[
				'label' => esc_html__( 'Meta Labels', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_ar_label_color',
			[
				'label'     => esc_html__( 'Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#64748B',
				'selectors' => [
					'{{WRAPPER}} .wk-ar-label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_ar_label_typography',
				'selector' => '{{WRAPPER}} .wk-ar-label',
			]
		);

		$this->add_responsive_control(
			'wk_ar_label_margin_bottom',
			[
				'label'      => esc_html__( 'Spacing Below Label', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 24 ],
				],
				'default'    => [ 'size' => 4, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-ar-label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — NAMES (Author / Reviewer Name)
		 * ========================================================= */
		$this->start_controls_section(
			'wk_ar_section_style_name',
			[
				'label' => esc_html__( 'Names', 'web-kit' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wk_ar_name_color',
			[
				'label'     => esc_html__( 'Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0F172A',
				'selectors' => [
					'{{WRAPPER}} .wk-ar-name, {{WRAPPER}} .wk-ar-name a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'wk_ar_name_hover_color',
			[
				'label'     => esc_html__( 'Hover Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563EB',
				'selectors' => [
					'{{WRAPPER}} .wk-ar-name a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'wk_ar_name_typography',
				'selector' => '{{WRAPPER}} .wk-ar-name',
			]
		);

		$this->end_controls_section();

		/* =========================================================
		 * STYLE TAB — DIVIDER
		 * ========================================================= */
		$this->start_controls_section(
			'wk_ar_section_style_divider',
			[
				'label'     => esc_html__( 'Divider Line', 'web-kit' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 'wk_ar_show_divider' => 'yes' ],
			]
		);

		$this->add_control(
			'wk_ar_divider_color',
			[
				'label'     => esc_html__( 'Color', 'web-kit' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E2E8F0',
				'selectors' => [
					'{{WRAPPER}} .wk-ar-divider' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_ar_divider_width',
			[
				'label'      => esc_html__( 'Thickness / Width', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 1, 'max' => 10 ],
				],
				'default'    => [ 'size' => 1, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-ar-divider' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_ar_divider_height',
			[
				'label'      => esc_html__( 'Height', 'web-kit' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [ 'min' => 10, 'max' => 100 ],
				],
				'default'    => [ 'size' => 36, 'unit' => 'px' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-ar-divider' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wk_ar_divider_margin',
			[
				'label'      => esc_html__( 'Margin / Spacing', 'web-kit' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .wk-ar-divider' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Resolves author data based on current post or widget controls.
	 *
	 * @param array $settings
	 * @return array|false
	 */
	protected function get_author_data( $settings ) {
		$source = $settings['wk_ar_author_source'];

		if ( 'custom' === $source ) {
			$name   = ! empty( $settings['wk_ar_author_custom_name'] ) ? $settings['wk_ar_author_custom_name'] : 'Boris Vance';
			$avatar = ! empty( $settings['wk_ar_author_custom_avatar']['url'] ) ? $settings['wk_ar_author_custom_avatar']['url'] : 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=160&auto=format&fit=crop&q=80';
			$url    = ! empty( $settings['wk_ar_author_custom_url']['url'] ) ? $settings['wk_ar_author_custom_url']['url'] : '';

			return [
				'name'   => $name,
				'avatar' => $avatar,
				'url'    => $url,
			];
		}

		$user_id = 0;

		if ( 'custom_user' === $source && ! empty( $settings['wk_ar_author_user'] ) ) {
			$user_id = absint( $settings['wk_ar_author_user'] );
		} else {
			// Dynamic current post author.
			$post_id = get_the_ID();
			if ( $post_id ) {
				$user_id = absint( get_post_field( 'post_author', $post_id ) );
			}

			// Fallback in editor if post has no author yet.
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}
		}

		if ( $user_id ) {
			$user = get_userdata( $user_id );
			if ( $user ) {
				return [
					'name'   => $user->display_name,
					'avatar' => get_avatar_url( $user_id, [ 'size' => 160 ] ),
					'url'    => get_author_posts_url( $user_id ),
				];
			}
		}

		// Clean default sample for live preview.
		return [
			'name'   => 'Boris Vance',
			'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=160&auto=format&fit=crop&q=80',
			'url'    => '#',
		];
	}

	/**
	 * Resolves reviewer data based on post meta, fallback user, or widget controls.
	 *
	 * @param array $settings
	 * @return array|false
	 */
	protected function get_reviewer_data( $settings ) {
		$source = $settings['wk_ar_reviewer_source'];

		if ( 'custom' === $source ) {
			$name   = ! empty( $settings['wk_ar_reviewer_custom_name'] ) ? $settings['wk_ar_reviewer_custom_name'] : 'Starc Nick Fury';
			$avatar = ! empty( $settings['wk_ar_reviewer_custom_avatar']['url'] ) ? $settings['wk_ar_reviewer_custom_avatar']['url'] : 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=160&auto=format&fit=crop&q=80';
			$url    = ! empty( $settings['wk_ar_reviewer_custom_url']['url'] ) ? $settings['wk_ar_reviewer_custom_url']['url'] : '';

			return [
				'name'   => $name,
				'avatar' => $avatar,
				'url'    => $url,
			];
		}

		$user_id = 0;

		if ( 'custom_user' === $source && ! empty( $settings['wk_ar_reviewer_user'] ) ) {
			$user_id = absint( $settings['wk_ar_reviewer_user'] );
		} else {
			// Read post meta from Reviewer_Metabox.
			$post_id = get_the_ID();
			if ( $post_id ) {
				$user_id = absint( get_post_meta( $post_id, Reviewer_Metabox::META_KEY, true ) );
			}

			// If no reviewer set on post, check widget fallback user control.
			if ( ! $user_id && ! empty( $settings['wk_ar_reviewer_fallback_user'] ) ) {
				$user_id = absint( $settings['wk_ar_reviewer_fallback_user'] );
			}
		}

		if ( $user_id ) {
			$user = get_userdata( $user_id );
			if ( $user ) {
				return [
					'name'   => $user->display_name,
					'avatar' => get_avatar_url( $user_id, [ 'size' => 160 ] ),
					'url'    => get_author_posts_url( $user_id ),
				];
			}
		}

		// If user requested to hide when empty and on frontend without reviewer.
		if ( 'yes' === $settings['wk_ar_reviewer_hide_empty'] && ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return false;
		}

		// Clean default sample for editor preview.
		return [
			'name'   => 'Starc Nick Fury',
			'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=160&auto=format&fit=crop&q=80',
			'url'    => '#',
		];
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$show_author   = 'yes' === $settings['wk_ar_show_author'];
		$show_reviewer = 'yes' === $settings['wk_ar_show_reviewer'];
		$show_divider  = 'yes' === $settings['wk_ar_show_divider'];
		$link_items    = 'yes' === $settings['wk_ar_link_items'];

		$author_data   = $show_author ? $this->get_author_data( $settings ) : false;
		$reviewer_data = $show_reviewer ? $this->get_reviewer_data( $settings ) : false;

		// If both are empty, nothing to render.
		if ( ! $author_data && ! $reviewer_data ) {
			return;
		}

		// Don't show divider if one of them is missing.
		$has_divider = $show_divider && $author_data && $reviewer_data;
		?>
		<div class="wk-author-reviewer-wrap">
			<div class="wk-author-reviewer-inner">

				<?php if ( $author_data ) : ?>
					<div class="wk-ar-item wk-ar-item-author">
						<div class="wk-ar-avatar">
							<?php if ( $link_items && ! empty( $author_data['url'] ) ) : ?>
								<a href="<?php echo esc_url( $author_data['url'] ); ?>" aria-label="<?php echo esc_attr( $author_data['name'] ); ?>">
									<img src="<?php echo esc_url( $author_data['avatar'] ); ?>" alt="<?php echo esc_attr( $author_data['name'] ); ?>" class="wk-ar-avatar-img" />
								</a>
							<?php else : ?>
								<img src="<?php echo esc_url( $author_data['avatar'] ); ?>" alt="<?php echo esc_attr( $author_data['name'] ); ?>" class="wk-ar-avatar-img" />
							<?php endif; ?>
						</div>

						<div class="wk-ar-info">
							<?php if ( ! empty( $settings['wk_ar_author_label'] ) ) : ?>
								<span class="wk-ar-label"><?php echo esc_html( $settings['wk_ar_author_label'] ); ?></span>
							<?php endif; ?>

							<span class="wk-ar-name">
								<?php if ( $link_items && ! empty( $author_data['url'] ) ) : ?>
									<a href="<?php echo esc_url( $author_data['url'] ); ?>">
										<?php echo esc_html( $author_data['name'] ); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html( $author_data['name'] ); ?>
								<?php endif; ?>
							</span>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $has_divider ) : ?>
					<div class="wk-ar-divider" aria-hidden="true"></div>
				<?php endif; ?>

				<?php if ( $reviewer_data ) : ?>
					<div class="wk-ar-item wk-ar-item-reviewer">
						<div class="wk-ar-avatar">
							<?php if ( $link_items && ! empty( $reviewer_data['url'] ) ) : ?>
								<a href="<?php echo esc_url( $reviewer_data['url'] ); ?>" aria-label="<?php echo esc_attr( $reviewer_data['name'] ); ?>">
									<img src="<?php echo esc_url( $reviewer_data['avatar'] ); ?>" alt="<?php echo esc_attr( $reviewer_data['name'] ); ?>" class="wk-ar-avatar-img" />
								</a>
							<?php else : ?>
								<img src="<?php echo esc_url( $reviewer_data['avatar'] ); ?>" alt="<?php echo esc_attr( $reviewer_data['name'] ); ?>" class="wk-ar-avatar-img" />
							<?php endif; ?>
						</div>

						<div class="wk-ar-info">
							<?php if ( ! empty( $settings['wk_ar_reviewer_label'] ) ) : ?>
								<span class="wk-ar-label"><?php echo esc_html( $settings['wk_ar_reviewer_label'] ); ?></span>
							<?php endif; ?>

							<span class="wk-ar-name">
								<?php if ( $link_items && ! empty( $reviewer_data['url'] ) ) : ?>
									<a href="<?php echo esc_url( $reviewer_data['url'] ); ?>">
										<?php echo esc_html( $reviewer_data['name'] ); ?>
									</a>
								<?php else : ?>
									<?php echo esc_html( $reviewer_data['name'] ); ?>
								<?php endif; ?>
							</span>
						</div>
					</div>
				<?php endif; ?>

			</div>
		</div>
		<?php
	}
}
