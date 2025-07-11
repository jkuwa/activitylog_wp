<?php
  // テーマサポート
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('automatic-feed-links');

  // --------- ファイル読み込み ---------
  function activityLog_script() {
    // font
    wp_enqueue_style('m-plus1c', 'https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;500&display=swap', array());
    wp_enqueue_style('montserrat', 'https://fonts.googleapis.com/css2?family=Montserrat+Alternates:wght@500&display=swap', array());
    wp_enqueue_style('zen-maru', 'https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@400;700&display=swap', array());

    // css
    wp_enqueue_style('reset', get_theme_file_uri('/css/ress.css'));
    wp_enqueue_style('my-style', get_theme_file_uri('/css/style.min.css'), array('reset'), '2.1.0');

    // JS
    wp_enqueue_script('full-calendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js', array(), false, true);
    wp_enqueue_script('chart', get_theme_file_uri('/js/chart.umd.js'), array(), '4.4.9', true);
    wp_enqueue_script('jquery');
    wp_enqueue_script('my-script', get_theme_file_uri('/js/main.js'), array('jquery'), false, true);
  }
  add_action('wp_enqueue_scripts', 'activityLog_script');


  // --------- Full Calendar ---------
  function create_log_archive() {
    // エンドポイント作成
    register_rest_route('custom/v1', '/post-dates', [
      'methods' => 'GET',
      'callback' => 'get_post_dates',
      'permission_callback' => '__return_true',   // 誰でも取得できる
    ]);
  };

  function get_post_dates($request) {
    $year = sanitize_text_field($request['year']);
    $month = sanitize_text_field($request['month']);

    $args = [
      'post_type' => 'log',
      'posts_per_page' => -1,
      'post_status' => 'publish',
      'date_query' => [
        [
          'year' => $year,
          'month' => $month,
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
  add_action('rest_api_init', 'create_log_archive');


  // --------- Chart.js---------
  // エンドポイント作成
  function create_chart() {
    register_rest_route('custom/v1', '/chart-data', [
      'methods' => 'GET',
      'callback' => 'get_chart_data',
      'permission_callback' => '__return_true',
    ]);
  };

  function get_chart_data($request) {
    $mode = sanitize_text_field($request['mode']);
    $year = sanitize_text_field($request['year']);
    $month = sanitize_text_field($request['month']);

    $args = [
      'post_type' => 'log',
      'posts_per_page' => -1,
      'post_status' => 'publish',
      'date_query' => ( $mode === 'month' )
        ? [['year' => $year, 'month' => $month,]]
        : ['year' => $year]
    ];

    $query = new WP_Query($args);
    $organizedData = [];
    $categoryTotals = [];
    $totalHours = 0;

    // 投稿データーからグラフデータ取得
    foreach( $query -> posts as $post ) {
      $id = $post -> ID;
      $postDate = new DateTime( get_the_date('Y-m-d', $post) );

      // この投稿のkey設定
      $postKey = ( $mode === 'month' )
        ? 'week ' . ceil( $postDate -> format('d') / 7 )
        : $postDate -> format('m');

      // データ抽出
      for ( $i=1; $i<=6; $i++) {
        $category = get_post_meta( $id, "category_0{$i}", true);
        $hours = floatval( get_post_meta( $id, "hours_0{$i}", true) );

        if (!$category || !$hours) continue;  // 空ならスキップ

        // カテゴリデータの初期化
        if ( empty($organizedData[$category]) ) {
          $organizedData[$category] = [];

          // mode別でkey作成
          if ( $mode === 'month' ) {
            $weeks = ceil( $postDate -> format('t') / 7 );
            for ( $w=1; $w<=$weeks; $w++) {
              $key = "week {$w}";
              $organizedData[$category][$key] = 0;
            }
          } else {
            for ( $m=1; $m<=12; $m++ ) {
              $key = str_pad($m, 2, '0', STR_PAD_LEFT);
              $organizedData[$category][$key] = 0;
            }
          }
        }

        // データ追加
        $organizedData[$category][$postKey] += $hours;   //グラフ用
        $totalHours += $hours;   // 合計表示用

        // ランキング用
        if ( empty($categoryTotals[$category]) ) {
          $categoryTotals[$category] = 0;
        }
        $categoryTotals[$category] += $hours;
      }
    }

    // --- 調整 ---
    // hours で降順ソート、上位3件取得
    arsort($categoryTotals);
    $topCategories = array_slice($categoryTotals, 0, 3);

    // 割合追加
    $topCategories = array_map(function($category, $hours) use ($totalHours) {
      return [
        'category' => $category,
        'hours' => $hours,
        'per' => round( ( $hours / $totalHours) * 100 ),
      ];
    }, array_keys($topCategories), array_values($topCategories));

    return rest_ensure_response([
      'data' => $organizedData,
      'total' => $totalHours,
      'ranking' => $topCategories,
    ]);
  }
  add_action('rest_api_init', 'create_chart');