<?php

  // --------- ファイル読み込み ---------
  function activityLog_script() {
    // font
    wp_enqueue_style('m-plus1c', 'https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;500&display=swap', array());
    wp_enqueue_style('montserrat', 'https://fonts.googleapis.com/css2?family=Montserrat+Alternates:wght@500&display=swap', array());
    wp_enqueue_style('zen-maru', 'https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@400;700&display=swap', array());

    // css
    wp_enqueue_style('reset', get_theme_file_uri('/css/ress.css'));
    wp_enqueue_style('my-style', get_theme_file_uri('/css/style.min.css'), array('reset'));

    // JS
    wp_enqueue_script('full-calendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js', array(), false, true);
    wp_enqueue_script('chart', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js', array(), false, true);
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.7.1.min.js', array('chart'), false, true);
    wp_enqueue_script('my-script', get_theme_file_uri('/js/main.js'), array('jquery'), false, true);
  }
  add_action('wp_enqueue_scripts', 'activityLog_script');