<?php
namespace RS\Elementor;
if (!defined('ABSPATH')) exit;

use \Elementor\Widget_Base;
use \Elementor\Controls_Manager;

class Review_Widget extends Widget_Base {
    public function get_name() { return 'rs_review_slider'; }
    public function get_title() { return 'Review Slider'; }
    public function get_icon() { return 'eicon-posts'; }
    public function get_categories() { return ['general']; }

    protected function register_controls(){
        $this->start_controls_section('content_section', ['label'=>'Content']);
        $this->end_controls_section();
    }

    protected function render(){
        echo do_shortcode('[review_slider]');
    }
}
