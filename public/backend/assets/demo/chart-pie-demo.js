// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#292b2c';

// Student score distribution demo data
var ctx = document.getElementById("myPieChart");
var myPieChart = new Chart(ctx, {
  type: 'pie',
  data: {
    labels: ["Excellent", "Good", "Satisfactory", "Needs improvement"],
    datasets: [{
      data: [32, 41, 19, 8],
      backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545'],
    }],
  },
});
