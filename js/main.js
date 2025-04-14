'use strict';

$(function() {
  // --------------------
  // タブ
  // --------------------
  $(".js-tabBtn").on('click', function() {
    if ( $(this).hasClass('is-current') ) {
      return;
    }
    // 選択中のタブを外す
    $(".js-tabBtn").removeClass('is-current').attr({
      'aria-selected': 'false',
      'tabindex': '-1',
    });
    $(".js-tabContent").removeClass('is-open');

    // タブの切り替え
    $(this).addClass('is-current').attr({
      'aria-selected': 'true',
      'tabindex': '0'
    });

    // コンテンツを表示
    const data = $(this).data('tab');
    const targetContent = $(`.js-tabContent[data-tab="${data}"]`);
    targetContent.addClass('is-open');
  });

  // キーボード操作
  $(".js-tabBtn").keydown(function(e) {
    if ( e.which === 37 ) {
      e.preventDefault();
      const prevTab = $(this).prev('.js-tabBtn');

      if ( prevTab.length ) {
        prevTab.focus();
      }
    } else if ( e.which === 39 ) {
      e.preventDefault();
      const nextTab = $(this).next('.js-tabBtn');

      if ( nextTab.length ) {
        nextTab.focus();
      }
    }
  });
});

  // グローバル変数
  let myChart;

{
  // --------------------
  // FullCalendar
  // --------------------
  document.addEventListener('DOMContentLoaded', () => {
    const calendarEL = document.querySelector(".js-calendar");
    const dayNames = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

    const calendar = new FullCalendar.Calendar(calendarEL, {
      initialView: 'dayGridMonth',
      // ツールバー
      headerToolbar: {
        start: 'prevYear, prev',
        center: 'title',
        end: 'next, nextYear'
      },
      dayHeaderContent: (arg) => {
        return dayNames[arg.date.getDay()]
      },
      // カレンダー
      firstDay: 1,
      contentHeight: 'auto',
      // イベント
      events: [
        {
          title: '2025.03.18',
          start: '2025-03-18',
          end: '2025-03-18'
        },
        {
          title: '2025.03.20',
          start: '2025-03-20',
          end: '2025-03-20'
        }
      ],
      eventDisplay: 'list-item',
    });
    calendar.render();
  });


  // --------------------
  // Chart
  // --------------------
  const ctx = document.querySelector(".js-chart");

  // ---------- form 切り替え ----------
  const mode = document.querySelectorAll("input[name='mode']");
  const input = document.querySelectorAll(".js-select");

  mode.forEach( (radio) => {
    radio.addEventListener('change', function() {
      input.forEach( (e) => {
        e.classList.toggle('is-open');
      });
    });
  });

  // selectボタンで実行
  document.querySelector(".js-setBtn").addEventListener('click', function() {
    fetchData();
    document.querySelector(".js-loader").classList.add('is-appeared');
  });
  
  // GASからデータ取得
  async function fetchData() {
    const modeSelect = document.querySelector('input[name="mode"]:checked').value;
    const monthValue = document.querySelector("#month").value;
    const yearValue = document.querySelector('select[name="year"]').value;

    let url = 'https://script.google.com/macros/s/AKfycbyXMcDbSBdCo01GwgduTl1f1EF_DppS45kpm2ACimXZHgmYZwz5IyXzGsQ5wvjH5NCo/exec';
    let title = '';

    if ( modeSelect === 'month' ) {
      const [year, month] = monthValue.split("-");
      url += `?mode=month&year=${year}&month=${month}`;
      title = `${year}/${month}`;

    } else if ( modeSelect === 'year' ) {
      url += `?mode=year&year=${yearValue}`;
      title = yearValue + '年';
    }   

    try {
      const response = await fetch (url);   // 非同期でデータ取得
      const data = await response.json();   // JSON読み取り

      const organizedData = data.data;
      const totalHours = data.totalHours;
      const categoryRanking = data.categoryRanking;

      updateChart(organizedData, title, categoryRanking);
      setTotal(totalHours);

    } catch (err) {
      console.log("データ取得エラー: " + err);
      alert("データ取得に失敗しました");
    }
  }

  // ---------- グラフ描画 ----------
  function updateChart(data, title, ranking) {
    // dateKeyを取得
    const dataArr = Object.values(data);
    const allDateKeys = [...new Set( dataArr.flatMap( item => Object.keys(item)))];
    allDateKeys.sort( (a, b) => a.localeCompare(b, undefined, {numeric: true}) );   // 順に並べる
    const labels = allDateKeys;

    // カテゴリ名取得
    const categories = Object.keys(data);

    // データセット
    const datasets = categories.map( category => {
      return {
        label: category,
        data: labels.map( dateKey => {
          return data[category][dateKey] ? data[category][dateKey] : 0;
        })
      };
    });


    // ---------- Chart.js 設定 ----------
    // グラフ初期化
    if (myChart) {
      myChart.destroy();
    }
    
    const mainColor = '#246286';
    let animationStarted = false;
    Chart.defaults.font.family = "'M PLUS Rounded 1c', 'sans-serif'";

    myChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: datasets
      },
      options: {
        plugins: {
          title: {
            display: true,
            text: title,
            position: 'bottom',
            color: mainColor,
            font: {
              size: 14,
              weight: 500
            },
            padding: {top: 16}
          },
          tooltip: {
            backgroundColor: '#fff',
            titleColor: mainColor,
            titleFont: {
              size: 14,
              weight: 400
            },
            bodyColor: mainColor,
            borderColor: mainColor,
            borderWidth: 1
          }
        },
        scales: {
          x: {
            stacked: true,
            ticks: {
              font: {
                size: 12
              }
            },
            grid: {
              display: false
            }
          },
          y: {
            stacked: true,
            beginAtZero: true,
            border: {
              dash: [2, 2]
            },
            title: {
              display: true,
              text: 'hours',
              font: {
                size: 14
              },
              padding: 0
            }
          }
        },
        barPercentage: 0.5,
        responsive: true,
        maintainAspectRatio: false,
        animation: {
          onProgress: () => {
            if ( !animationStarted ) {
              animationStarted = true;
              document.querySelector(".js-loader").classList.remove('is-appeared');
            }
          }
        }
      }
    });

    if ( !datasets.length ) {
      document.querySelector(".js-loader").classList.remove('is-appeared');
    }
    
    // ---------- カテゴリランキング作成 ---------
    // 対応する色を取得
    const topCategories = ranking.map( item => {
      const categoryName = item.category;
      const obj = datasets.find( item => item.label === categoryName );
      const itemColor = obj.backgroundColor;
      item.color = itemColor;
      return item;
    });

    // ランキング出力
    const categoryRanking = document.querySelector(".js-table");
    categoryRanking.innerHTML = '';
    topCategories.forEach( (item, index) => {
      const category = item.category;
      const hours = item.hours;
      const percentage = item.percentage;
      const color = item.color;
      const number = index + 1;
      let text = '';

      text += '<tr class="js-ranking' + number + '">';
      text += '<th><span>0' + number + '</span></th>';
      text += '<td class="p-table__category">' + category + '</td>';
      text += '<td class="c-hours p-table__hours">' + hours + '</td>';
      text += '<td class="p-table__per">' + percentage + '</td>';
      text += '</tr>';
      
      // HTML作成
      categoryRanking.insertAdjacentHTML('beforeend', text);

      // スタイル指定
      const tableHead = '.js-ranking' + number + ' > th';
      document.querySelector(tableHead).style.setProperty('--cat-color', color);
    });
  }


  // ---------- canvasサイズ指定 ---------
  function setCanvasHeight() {
    const bp = 768;
    if ( window.innerWidth < bp ) {
      ctx.style.height = '340px';
    } else {
      ctx.style.height = '420px';
    }
  }


  // 初回実行
  setCanvasHeight();
  // リサイズ時実行
  window.addEventListener('resize', setCanvasHeight);


  // ページ読み込み時にグラフ表示
  document.addEventListener('DOMContentLoaded', () => {
    const today = new Date();
    const year = today.getFullYear();
    const month = ("0" + (today.getMonth() + 1)).slice(-2);
    const currentMonth = `${year}-${month}`;

    // input にセット
    const monthInput = document.querySelector("#month");
    monthInput.value = currentMonth;

    fetchData();
  });


  // ---------- Total ----------
  function setTotal(value) {
    const total = document.querySelector(".js-total");
    total.textContent = value;
  }
}