<?php get_header(); ?>

  <main class="l-main">
    <!-- ABOUT SECTION -->

    <?php
      $about = get_posts( array(
        'name' => 'about',
        'post_type' => 'page',
        'post_status' => array('private', 'publish'),
        'numberposts' => 1,
      ));

      if ($about):
        $about_page = $about[0];
        $about_id = $about_page -> ID;
    ?>

    <section class="p-about">
      <h2><?php echo esc_html( $about_page -> post_title ); ?></h2>

      <?php if (has_post_thumbnail($about_id)): ?>
        
      <figure><?php echo get_the_post_thumbnail($about_id, 'full'); ?></figure>

      <?php
        endif;
        echo apply_filters('the_content', $about_page -> post_content);
      endif;
      ?>
    </section>

    <div class="p-contents js-container">
      <div class="p-contents__tabs" role="tablist" aria-label="活動記録タブ">
        <div class="c-inner">
          <button id="tab_log" class="c-button--tab js-tabBtn is-current" type="button" role="tab" aria-selected="true" aria-controls="log" tabindex="0" data-tab="log">log</button>
          <button id="tab_report" class="c-button--tab js-tabBtn" type="button" role="tab" aria-selected="false" aria-controls="report" tabindex="-1" data-tab="report">report</button>
        </div>
      </div>

      <!-- LOG SECTION -->
      <section id="log" class="c-content p-log js-tabContent is-open" role="tabpanel" aria-labelledby="tab_log" tabindex="0" data-tab="log">
        <div class="c-inner">
          <!-- CALENDAR -->
          <div class="p-calendar js-calendar"></div>

          <!-- ARCHIVE -->
          <ul class="p-log__archive js-archive"></ul>
        </div>

        <a href="#log" class="c-button--top js-topBtn">log top</a>
      </section>

      <!-- REPORT SECTION -->
      <div id="report" class="c-content p-report js-tabContent" role="tabpanel" aria-labelledby="tab_report" tabindex="0" data-tab="report">
        <div class="c-inner">
          <div class="p-report__head">
            <fieldset>
              <label><input type="radio" name="mode" value="month" checked>月間</label>
              <label><input type="radio" name="mode" value="year">年間</label>
            </fieldset>
  
            <div class="p-report__select">
              <!-- <label for="month">select month: </label> -->
              <input type="month" id="month" class="js-select is-open">
              <select name="year" class="js-select">
                <option value="2025" selected>2025年</option>
                <option value="2024">2024年</option>
              </select>
              <button class="c-button--select js-setBtn">select</button>
            </div>
          </div>
  
          <div class="p-graph">
  
            <section class="p-graph__weekly">
              <h2>活動時間</h2>
              <div class="p-graph__content">
                <canvas class="js-chart" aria-label="月の学習時間グラフ"></canvas>
              </div>
              <dl>
                <dt>合計</dt>
                <dd class="c-hours js-total"></dd>
              </dl>
            </section>
            
            <section class="p-graph__ranking">

              <div class="c-blowing">
                <svg width="444" height="352" viewBox="0 0 444 352" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M364.702 69.8139C445.642 106.079 461.935 206.991 421.465 271.637M3.62791 225.912C31.2617 411.5 359.972 353.102 409.377 286.879M1 204.888C1 26.5 274.302 19.3581 326.86 46.6884C333.167 49.968 353.586 16.7163 360.5 2C360.5 2 365.802 37.2442 358 42.5" stroke="#246286"/>
                </svg>
              </div>

              <h2>カテゴリー別<span>TOP</span><span>3</span></h2>
              <table class="p-table">
                <tbody class="js-table"></tbody>
              </table>

            </section>
          </div>
        </div>
      </div>

      <div class="p-loader js-loader">
        <div class="p-loader__wrapper">

          <div class="p-loader__bg"></div>
            
          <p>
            <span class="c-loader -l">L</span><span class="c-loader -o">o</span><span class="c-loader -a">a</span><span class="c-loader -d">d</span><span class="c-loader -i">i</span><span class="c-loader -n">n</span><span class="c-loader -g">g</span><span class="c-loader -dot">...</span>
          </p>
        </div>
      </div>

    </div>
  </main>

<?php get_footer(); ?>