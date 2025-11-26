<?php
namespace RS\Elementor;
if (!defined('ABSPATH')) exit;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;

class Review_Widget extends Widget_Base {
    public function get_name() { return 'rs_review_slider'; }
    public function get_title() { return __('Review Slider', 'review-slider'); }
    public function get_icon() { return 'eicon-posts'; }
    public function get_categories() { return ['general']; }

    protected function register_controls(){
        $this->start_controls_section('content_section', ['label' => __('Slider Settings', 'review-slider')]);

        $this->add_responsive_control(
            'slides_to_show',
            [
                'label' => __('Slides to Show', 'review-slider'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 10,
                'devices' => ['desktop', 'tablet', 'mobile'],
                'desktop_default' => 3,
                'tablet_default' => 2,
                'mobile_default' => 1,
            ]
        );

        $this->add_control(
            'autoplay_speed',
            [
                'label' => __('Autoplay Speed (ms)', 'review-slider'),
                'type' => Controls_Manager::NUMBER,
                'default' => 3000,
                'description' => __('Set to 0 to disable autoplay.', 'review-slider'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render(){
        $settings = $this->get_settings_for_display();
        $shortcode_atts = [
            'autoplay_speed' => $settings['autoplay_speed'],
            'slides_to_show' => $settings['slides_to_show'],
            'slides_to_show_tablet' => $settings['slides_to_show_tablet'],
            'slides_to_show_mobile' => $settings['slides_to_show_mobile'],
        ];
        $atts_str = '';
        foreach($shortcode_atts as $key => $value) {
            if (!empty($value)) {
                $atts_str .= " {$key}='" . esc_attr($value) . "'";
            }
        }
        echo do_shortcode('[review_slider' . $atts_str . ']');
    }

    public function get_style_depends() {
        return [ 'rs-frontend' ];
    }
}
