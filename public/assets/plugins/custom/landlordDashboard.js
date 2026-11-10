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
            $("#total_tenant").text(res.total_tenant);
            $("#last_month_total_tenant").text(res.last_month_total_tenant);
            $("#total_property").text(res.total_property);
            $("#last_month_total_property").text(res.last_month_total_property);
            $("#total_withdraw").text(res.total_withdraw);
            $("#last_month_total_withdraw").text(res.last_month_total_withdraw);


            renderArrow("#tenant_arrow", res.tenant_arrow);
            renderArrow("#property_arrow", res.property_arrow);
            renderArrow("#withdraw_arrow", res.withdraw_arrow);
        },
    });
}


// Property Overview chart--------------
$(document).ready(function () {
    updatePropertyChart();
  });

  $(".property-chart").on("change", function () {
    let year = $(this).val();
    updatePropertyChart(year);
  });

  function updatePropertyChart(year = new Date().getFullYear()) {
    var url = $("#landlord-property-data").val();

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

  const propertyStatusChart =  new Chart(ctxOverview, {
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
          backgroundColor: '#ffffff',
          titleColor: '#000000',
          bodyColor: '#000000',
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

  function landlordAccountChartYear(year = new Date().getFullYear()) {
    const url = $('#landlord-account-data').val() + '?year=' + year;

    $.ajax({
      type: "GET",
      url: url,
      dataType: "json",
      success: function (res) {
        income = res.total_income;
        expense = res.total_expense;
        totalBalance = res.total_balance;

        updateAccountingChart(income, expense);

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
  landlordAccountChartYear();

  $('.landlord-account-chart-date').on('change', function () {
    const year = $(this).val();
    landlordAccountChartYear(year);
  });

  function updateAccountingChart(income, expense) {
    const maxDataValue = Math.max(...income, ...expense);

    const roundedMax = maxDataValue > 0 ? Math.ceil(maxDataValue / 2000) * 2000 : 2000;

    let stepSize = Math.ceil(roundedMax / 9 / 100) * 100;

    if (stepSize < 100) stepSize = 100;

    accountingChart.data.datasets[0].data = income;
    accountingChart.data.datasets[1].data = expense;

     accountingChart.options.scales.y.max = roundedMax;
    accountingChart.options.scales.y.ticks.stepSize = stepSize;

    accountingChart.update();
  }



// Earning Statistic chart
let hoveredIndex = null;

const ctx = document.getElementById("incomeExpenseChart").getContext("2d");

const data = {
    labels: [
        "Jan",
        "Feb",
        "March",
        "April",
        "May",
        "June",
        "July",
        "Aug",
        "Sept",
        "Oct",
        "Nov",
        "Dec",
    ],
    datasets: [
        {
            label: "Income",
            data: new Array(12).fill(0),
            backgroundColor: "#6200EA",
            borderRadius: 1,
            barPercentage: 1,
            categoryPercentage: 0.4,
        },
        {
            label: "Expense",
            data: new Array(12).fill(1),
            backgroundColor: "#FF900C",
            borderRadius: 1,
            barPercentage: 1,
            categoryPercentage: 0.4,
        },
    ],
};

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        tooltip: {
            backgroundColor: "#fff",
            titleColor: "#000",
            bodyColor: "#000",
            borderColor: "#ccc",
            borderWidth: 1,
            cornerRadius: 4,
        },
        legend: {
            display: false,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value) => `${value / 1000}k`,
            },
            grid: {
                color: "#eee",
            },
        },
        x: {
            grid: {
                display: false,
            },
        },
    },
    onHover: (event, chartElement) => {
        if (chartElement.length > 0) {
            hoveredIndex = chartElement[0].index;
        } else {
            hoveredIndex = null;
        }
        accountingChart.draw();
    },
};

const hoverHighlightPlugin = {
    id: "hoverHighlight",
    beforeDraw(accountingChart) {
        if (hoveredIndex === null) return;

        const ctx = accountingChart.ctx;
        const xAxis = accountingChart.scales.x;
        const chartArea = accountingChart.chartArea;

        const barWidth = xAxis.getPixelForTick(1) - xAxis.getPixelForTick(0);
        const x = xAxis.getPixelForTick(hoveredIndex) - barWidth / 2;
        const y = chartArea.top;
        const width = barWidth;
        const height = chartArea.bottom - chartArea.top;
        const radius = 8;

        ctx.save();
        ctx.fillStyle = "rgba(128, 0, 255, 0.08)";
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.lineTo(x + width - radius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
        ctx.lineTo(x + width, y + height - radius);
        ctx.quadraticCurveTo(
            x + width,
            y + height,
            x + width - radius,
            y + height
        );
        ctx.lineTo(x + radius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
        ctx.lineTo(x, y + radius);
        ctx.quadraticCurveTo(x, y, x + radius, y);
        ctx.closePath();
        ctx.fill();
        ctx.restore();
    },
};

const accountingChart = new Chart(ctx, {
    type: "bar",
    data,
    options,
    plugins: [hoverHighlightPlugin],
});

// 👇 Add this to activate tooltip on chart area hover
document
    .getElementById("incomeExpenseChart")
    .addEventListener("mousemove", function (event) {
        const points = accountingChart.getElementsAtEventForMode(
            event,
            "nearest",
            { intersect: false },
            true
        );

        if (points.length) {
            const point = points[0];
            accountingChart.setActiveElements([point]);
            accountingChart.tooltip.setActiveElements([point], {
                x: event.offsetX,
                y: event.offsetY,
            });
            accountingChart.update();
        } else {
            accountingChart.setActiveElements([]);
            accountingChart.tooltip.setActiveElements([], {});
            accountingChart.update();
        }
    });


$(document).ready(function() {
    var $tabContainer = $('.change-tab');
    var application_url = $tabContainer.data("application-url");
    var maintenance_url = $tabContainer.data("maintenance-url");

    // Set initial link based on active tab
    if ($('.custom-tab.active').data('bs-target') === '#tab1') {
        $('.change-view').attr('href', application_url);
    } else {
        $('.change-view').attr('href', maintenance_url);
    }

    // When clicking individual tabs
    $('.custom-tab').on('click', function() {
        $('.custom-tab').removeClass('active');
        $(this).addClass('active');

        var target = $(this).data('bs-target');
        if (target === '#tab1') {
            $('.change-view').attr('href', application_url);
        } else {
            $('.change-view').attr('href', maintenance_url);
        }
    });
});
