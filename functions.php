<?php
  // テーマサポート
  add_theme_support('post-thumbnails');

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


  // --------- 投稿データをスプシに送信 ---------
  function send_log_data_to_sheet($post_id) {

    $post = get_post($post_id);
    if ($post -> post_type !== 'log') return;

    $date = get_the_date('Y-m-d', $post_id);
    $log_data = [];

    for ($i =1; $i <= 6; $i++) {
      $category = get_post_meta($post_id, "category_0{$i}", true);
      $hours = get_post_meta($post_id, "hours_0{$i}", true);

      if ( $category && $hours ) {
        $log_data[] = [
          'category' => $category,
          'hours' => $hours
        ];
      }
    }

    if ( empty($log_data) ) return;

    $data = [
      'date' => $date,
      'log' => $log_data
    ];

    $json = wp_json_encode($data);
    $url = 'https://script.google.com/macros/s/AKfycbxOQ_9cM_y_SMrZ-yr0GIZp5xBVTTyhnMGObCCdx7TJCRy184F6xkN_ZZeLqMDiHGvGAQ/exec';

    $response = wp_remote_post($url, [
      'body' => $json,
      'headers' => [
        'Content-Type' => 'application/json',
        'Content-Length' => strlen($json)
      ],
      'method' => 'POST'
    ]);

    if ( is_wp_error($response) ) {
      error_log("Error sending data to Google Sheets:" . $response -> get_error_message());
    } else {
      error_log("Success! Sending data: {$json} | Response: " . wp_remote_retrieve_body($response));
    }
  }
  add_action('acf/save_post', 'send_log_data_to_sheet');


  // --------- Full Calendar ---------
  add_action('rest_api_init', function() {
    // エンドポイント作成
    register_rest_route('custom/v1', '/post-dates', [
      'methods' => 'GET',
      'callback' => 'get_post_dates',
      'permission_callback' => '__return_true',   // 誰でも取得できる
    ]);
  });

  function get_post_dates($request) {
    $month = sanitize_text_field($request['month']);
    $start = $month . '-01';   // 月の1日
    $end = date('Y-m-t', strtotime($start));   // 月の最終日

    $args = [
      'post_type' => 'log',
      'posts_per_page' => -1,
      'post_status' => 'publish',
      'date_query' => [
        [
          'after' => $start,
          'before' => $end,
          'inclusive' => true,   // afterとbeforeに指定した日も含める
        ]
      ]
    ];

    $query = new WP_Query($args);
    $posts = [];

    // error_log('取得した投稿数：' . $query -> found_posts);

    foreach ( $query -> posts as $post ) {
      $id = $post -> ID;
      $fields = [];
      // error_log( print_r($id, true));

      for ($i=1; $i<=6; $i++) {
        $cat = get_post_meta( $id, "category_0{$i}", true);
        $content = get_post_meta( $id, "content_0{$i}", true);
        $hours = get_post_meta( $id, "hours_0{$i}", true);

        if ( !empty($cat) ) {
          $fields[] = [
            'category' => $cat,
            'content' => $content,
            'hours' => $hours,
          ];
        }
      }

      $posts[] = [
        'id' => $id,
        'title' => get_the_title($post),
        'date' => get_the_date('Y-m-d', $post),
        'content' => apply_filters('the_content', $post -> post_content),
        'fields' => $fields,
      ];
    }

    // error_log( print_r($posts, true));
    return rest_ensure_response($posts);
  }

