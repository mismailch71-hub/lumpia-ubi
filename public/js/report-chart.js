document.addEventListener("DOMContentLoaded", function () {
    const chartCanvas = document.getElementById('salesChart');
    
    if (!chartCanvas) return;

    // Ambil data JSON pendapatan yang disisipkan melalui atribut data
    const rawData = chartCanvas.getAttribute('data-monthly');
    const monthlyData = JSON.parse(rawData || '[]');

    const ctx = chartCanvas.getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: monthlyData,
                borderColor: '#7e22ce',
                backgroundColor: 'rgba(126, 34, 206, 0.1)',
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#7e22ce',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
});