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
      <h2 class="js-target"><?php echo esc_html( $about_page -> post_title ); ?></h2>

      <?php if (has_post_thumbnail($about_id)): ?>
        
      <figure class="js-target"><?php echo get_the_post_thumbnail($about_id, 'full'); ?></figure>

      <?php endif; ?>
      <div class="js-target">
      <?php
        echo apply_filters('the_content', $about_page -> post_content);
      ?>
      </div>
        
    <?php endif; ?>
    </section>

    <div class="p-contents js-target">
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
          <div class="p-calendar js-calendar js-target"></div>

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
              <input type="month" id="month" class="js-select is-open">
              <select name="year" class="js-select">
                <option value="2025" selected>2025年</option>
                <option value="2024">2024年</option>
              </select>
              <button class="c-button--select js-setBtn">select</button>
            </div>
          </div>
  
          <section class="p-graph">
            <h2>activity time</h2>

            <div class="p-graph__contents">
              <div class="p-graph__time">
                <canvas class="js-chart" aria-label="学習時間グラフ"></canvas>
              </div>

              <div class="p-graph__ranking">
                <table class="p-table">
                  <tbody class="js-table"></tbody>
                </table>
                <dl>
                  <dt>合計</dt>
                  <dd class="c-hours js-total"></dd>
                </dl>
              </div>
            </div>
          </section>
        </div>
      </div>

      <div class="p-loader js-loader">
        <div class="p-loader__wrapper">  
          <p>
            <span class="c-loader">L</span><span class="c-loader">o</span><span class="c-loader">a</span><span class="c-loader">d</span><span class="c-loader">i</span><span class="c-loader">n</span><span class="c-loader">g</span><span class="c-loader">...</span>
          </p>
          <div class="p-loader__book"></div>
        </div>
      </div>

    </div>
  </main>

<?php get_footer(); ?>