// currency format
function currencyFormat(amount, type = "icon", decimals = 2) {
    let symbol = $("#currency_symbol").val();
    let position = $("#currency_position").val();
    let code = $("#currency_code").val();

    let formatted_amount = formattedAmount(amount, decimals);

    // Apply currency format based on the position and type
    if (type === "icon" || type === "symbol") {
        if (position === "right") {
            return formatted_amount + symbol;
        } else {
            return symbol + formatted_amount;
        }
    } else {
        if (position === "right") {
            return formatted_amount + " " + code;
        } else {
            return code + " " + formatted_amount;
        }
    }
}
// Format the amount
function formattedAmount(amount, decimals) {
    return Number.isInteger(+amount)
        ? parseInt(amount)
        : (+amount).toFixed(decimals);
}



function formatNumber(num) {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
  } else if (num >= 1000) {
    return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
  }
  return num;
}

function renderArrow(selector, direction) {
  let profitIcon = `<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M14 5L8.38636 12.125L5.43182 8.375L1 14" stroke="#009F5E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M10 5H14V9.5" stroke="#009F5E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>`;
  let lossIcon = `<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M1 14L6.61364 6.875L9.56818 10.625L14 5" stroke="#F89100" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M5 14H1V9.5" stroke="#F89100" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
  `;

  let parentDiv = $(selector).closest('div');
  parentDiv.removeClass('profit-amount loss-amount');

  let iconHtml = "";
  if (direction === "up") {
    iconHtml = profitIcon;
    parentDiv.addClass('profit-amount');
  } else if (direction === "down") {
    iconHtml = lossIcon;
    parentDiv.addClass('loss-amount');
  } else {
    iconHtml = profitIcon;
    parentDiv.addClass('profit-amount');
  }
  $(selector).html(iconHtml);
}

getDashboardData();
function getDashboardData() {
  var url = $("#get-dashboard").val();
  $.ajax({
    type: "GET",
    url: url,
    dataType: "json",
    success: function (res) {
      $("#total_landlord").text(res.total_landlord);
      $("#last_month_total_landlord").text(formatNumber(res.last_month_total_landlord));
      $("#total_tenant").text(res.total_tenant);
      $("#last_month_total_tenant").text(formatNumber(res.last_month_total_tenant));
      $("#total_subscribed").text(res.total_subscribed);
      $("#last_month_total_subscribed").text(formatNumber(res.last_month_total_subscribed));

      // Render arrows
      renderArrow("#landlord_arrow", res.landlord_arrow);
      renderArrow("#tenant_arrow", res.tenant_arrow);
      renderArrow("#subscribed_arrow", res.subscribed_arrow);
    },
  });
}



function adminAccountChartYear(year = new Date().getFullYear()) {
  const url = $('#admin-get-account-data').val() + '?year=' + year;

  $.ajax({
    type: "GET",
    url: url,
    dataType: "json",
    success: function (res) {
      income = res.total_income;
      expense = res.total_expense;
      totalBalance = res.total_balance;

      updateAccountingChart(totalBalance, income, expense);

      // Update totals in Blade (sum over arrays)
      $('.income_value').text(`${currencyFormat(income.reduce((a, b) => a + b, 0))}`);
      $('.expense_value').text(`${currencyFormat(expense.reduce((a, b) => a + b, 0))}`);
      $('.total_balance').text(`${currencyFormat(totalBalance.reduce((a, b) => a + b, 0))}`);
    },
    error: function (err) {
      console.error("Error fetching data:", err);
    }
  });
}

// Initial chart load with the current year data
adminAccountChartYear();

$('.admin-account-chart-date').on('change', function () {
  const year = $(this).val();
  adminAccountChartYear(year);
});

function updateAccountingChart(balanceData, incomeData, expenseData) {
  const maxDataValue = Math.max(...balanceData, ...incomeData, ...expenseData);

  const roundedMax = maxDataValue > 0 ? Math.ceil(maxDataValue / 2000) * 2000 : 2000;

  let stepSize = Math.ceil(roundedMax / 9 / 100) * 100;

  if (stepSize < 100) stepSize = 100;

  accountingChart.data.datasets[0].data = balanceData;
  accountingChart.data.datasets[1].data = incomeData;
  accountingChart.data.datasets[2].data = expenseData;

   accountingChart.options.scales.y.max = roundedMax;
  accountingChart.options.scales.y.ticks.stepSize = stepSize;

  accountingChart.update();
}

// income and expense chart ------------------------>
const months = ['Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'];

let verticalLinePlugin = {
  id: 'verticalLinePlugin',
  afterDraw: chart => {
    if (chart.tooltip?._active?.length) {
      const ctx = chart.ctx;
      const x = chart.tooltip._active[0].element.x;
      const topY = chart.scales.y.top;
      const bottomY = chart.scales.y.bottom;

      ctx.save();
      ctx.beginPath();
      ctx.setLineDash([5, 5]);
      ctx.moveTo(x, topY);
      ctx.lineTo(x, bottomY);
      ctx.strokeStyle = '#8e44ec';
      ctx.stroke();
      ctx.restore();
    }
  }
};

const ctx = document.getElementById('incomeExpenseChart').getContext('2d');
const accountingChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: months,
    datasets: [{
      label: 'Total Balance',
      data: new Array(12).fill(0),
      borderColor: '#8e44ec',
      backgroundColor: 'rgba(142, 68, 236, 0.1)',
      pointBorderColor: '#8e44ec',
      pointBackgroundColor: '#fff',
      pointRadius: 5,
      pointHoverRadius: 7,
      borderWidth: 2,
      fill: true,
      tension: 0
    },
    {
      label: 'Income',
      data: new Array(12).fill(1),
      borderColor: '#2ecc71',
      backgroundColor: 'rgba(46, 204, 113, 0.1)',
      hidden: true // <-- hide from chart line but still shows in tooltip
    },
    {
      label: 'Expense',
      data: new Array(12).fill(2),
      borderColor: '#e74c3c',
      backgroundColor: 'rgba(231, 76, 60, 0.1)',
      hidden: true
    }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        enabled: true,
        callbacks: {
          title: context => {
            const idx = context[0].dataIndex;
            return `${months[idx]} - Total Balance: ${totalBalance[idx]}`;
          },
          label: context => {
            const idx = context.dataIndex;
            const datasets = context.chart.data.datasets;
            const incomeValue = datasets[1].data[idx];
            const expenseValue = datasets[2].data[idx];
            return `Income: $${incomeValue}\nExpense: $${expenseValue}`;
          }
        },
        backgroundColor: '#fff',
        titleColor: '#000',
        borderWidth: 1,
        titleFont: { weight: 'bold' },
        padding: 10,
        displayColors: false
      }
    },
    interaction: {
      mode: 'index',
      intersect: false
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: value => '$' + (value / 1000) + 'k'
        },
        max: 4000,
        stepSize: 500
      },
      x: {
        grid: {
          display: false
        }
      }
    }
  },
  plugins: [verticalLinePlugin]
});



$(document).ready(function () {
  updatePropertyChart();
});

$(".property-chart").on("change", function () {
  let year = $(this).val();
  updatePropertyChart(year);
});

function updatePropertyChart(year = new Date().getFullYear()) {
  var url = $("#get-property-data").val();

  $.ajax({
    type: "GET",
    url: url + "?year=" + year,
    dataType: "json",
    success: function (res) {
      propertyStatusChart.data.datasets[0].data = [
        res.pending || 0.000001,
        res.approved || 0.000001,
        res.rejected || 0.000001,
      ];
      propertyStatusChart.update();

      $(".total_property").text(res.total_property || 0);
      $(".property_approved").text(res.approved || 0);
      $(".property_pending").text(res.pending || 0);
      $(".property_rejected").text(res.rejected || 0);
    },
  });
}
const ctxOverview = document.getElementById('overviewChart').getContext('2d');

const propertyStatusChart = new Chart(ctxOverview, {
  type: 'doughnut',
  data: {
    labels: ['Pending', 'Approved', 'Rejected'],
    datasets: [{
      data: [0.000001, 0.000001, 0.000001],
      backgroundColor: ['#FFA726', '#28A745', '#6900D1'],
      borderWidth: 0,
      borderRadius: 16,
      spacing: 10,
      cutout: '80%',
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: false,
      },
      tooltip: {
        enabled: true,
        backgroundColor: '#ffffff', // White background
        titleColor: '#000000',      // Black title text
        bodyColor: '#000000',       // Black body text
        borderColor: '#e0e0e0',
        callbacks: {
          label: function (context) {
            const label = context.label || '';
            const value = context.raw;
            return `${label}: ${value}`;
          }
        }
      }
    }
  }
});


$(document).ready(function () {
  var $tabContainer = $('.change-tab');
  var withdraw_url = $tabContainer.data("withdraw-url");
  var refund_url = $tabContainer.data("refund-url");

  // Set initial link based on active tab
  if ($('.custom-tab.active').data('bs-target') === '#tab1') {
    $('.change-view').attr('href', withdraw_url);
  } else {
    $('.change-view').attr('href', refund_url);
  }

  // When clicking individual tabs
  $('.custom-tab').on('click', function () {
    $('.custom-tab').removeClass('active');
    $(this).addClass('active');

    var target = $(this).data('bs-target');
    if (target === '#tab1') {
      $('.change-view').attr('href', withdraw_url);
    } else {
      $('.change-view').attr('href', refund_url);
    }
  });
});
