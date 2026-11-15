document.addEventListener("DOMContentLoaded", function () {
    var statusColors = ["#fbc02d", "#7cb342", "#2e7d32", "#e57373"];
    Chart.defaults.font.family = "Inter, system-ui, sans-serif";
    Chart.defaults.color = "#5f6b5f";
    var valueLabels = {
        id: "valueLabels",
        afterDatasetsDraw: function (chart) {
            var ctx = chart.ctx;
            ctx.save();
            ctx.fillStyle = "#33402f";
            ctx.font = "600 12px Inter, system-ui, sans-serif";
            ctx.textBaseline = "middle";
            chart.getDatasetMeta(0).data.forEach(function (bar, i) {
                var v = chart.data.datasets[0].data[i];
                ctx.fillText(Number(v).toLocaleString(), bar.x + 8, bar.y);
            });
            ctx.restore();
        }
    };
    var statusCanvas = document.getElementById("statusChart");
    var cropCanvas = document.getElementById("cropChart");
    var sLabels = statusCanvas ? JSON.parse(statusCanvas.dataset.labels) : [];
    var sValues = statusCanvas ? JSON.parse(statusCanvas.dataset.values) : [];
    var cLabels = cropCanvas ? JSON.parse(cropCanvas.dataset.labels) : [];
    var cValues = cropCanvas ? JSON.parse(cropCanvas.dataset.values) : [];

    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: "doughnut",
            data: { labels: sLabels, datasets: [{ data: sValues, backgroundColor: statusColors, borderColor: "#ffffff", borderWidth: 3 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: "72%",
                plugins: { legend: { position: "bottom", labels: { usePointStyle: true, pointStyle: "circle", boxWidth: 8, padding: 18 } } } }
        });
    }
    if (cropCanvas) {
        new Chart(cropCanvas, {
            type: "bar",
            data: { labels: cLabels, datasets: [{ data: cValues, backgroundColor: "#2e7d32", borderRadius: 10, maxBarThickness: 8 }] },
            options: { indexAxis: "y", responsive: true, maintainAspectRatio: false, layout: { padding: { right: 50 } },
                plugins: { legend: { display: false } },
                scales: { x: { display: false, beginAtZero: true }, y: { grid: { display: false }, border: { display: false } } } },
            plugins: [valueLabels]
        });
    }
});
