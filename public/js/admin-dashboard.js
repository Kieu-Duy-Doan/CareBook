/**
 * Script chịu trách nhiệm khởi tạo và vẽ các biểu đồ thống kê trên trang Admin Dashboard.
 * Lắng nghe sự kiện DOMContentLoaded để đảm bảo HTML đã load xong.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Kiểm tra xem dữ liệu từ backend (truyền qua thẻ <script> trong view) có tồn tại không
    if (!window.DashboardData) return;

    // Bộ màu chuẩn cho các phần tử trên biểu đồ tròn
    const brandColors = [
        '#0ea5e9', '#14b8a6', '#6366f1', '#f59e0b', 
        '#ec4899', '#8b5cf6', '#10b981', '#f43f5e'
    ];
    
    // Bóc tách dữ liệu từ object toàn cục do Blade truyền sang
    const { trendLabels, trendData, pieLabels, pieData } = window.DashboardData;

    // 1. Gán màu sắc ngẫu nhiên cho các chấm chú thích (Legend) của biểu đồ tròn
    pieLabels.forEach((label, i) => {
        const legendDot = document.getElementById('legend-color-' + i);
        if (legendDot) {
            legendDot.style.backgroundColor = brandColors[i % brandColors.length];
        }
    });

    /**
     * Hàm vẽ biểu đồ đường (Line Chart) bằng HTML5 Canvas thuần
     * @param {string} canvasId ID của thẻ <canvas>
     * @param {Array} labels Mảng nhãn trục X (Ví dụ: ['T2', 'T3', ...])
     * @param {Array} data Mảng giá trị trục Y
     */
    function drawLineChart(canvasId, labels, data) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        
        const parent = canvas.parentElement;
        const dpr = window.devicePixelRatio || 1; // Hỗ trợ màn hình Retina siêu nét
        const rect = parent.getBoundingClientRect();

        // Đặt kích thước thực tế cho Canvas dựa trên thẻ cha
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;

        const ctx = canvas.getContext('2d');
        ctx.scale(dpr, dpr);

        const width = rect.width;
        const height = rect.height;
        // Lề xung quanh biểu đồ
        const padding = { top: 30, right: 30, bottom: 40, left: 40 };

        const chartWidth = width - padding.left - padding.right;
        const chartHeight = height - padding.top - padding.bottom;

        // Tìm giá trị cao nhất để chia tỷ lệ trục Y, tối thiểu là 5
        const maxVal = Math.max(...data, 5);
        const minVal = 0;

        ctx.clearRect(0, 0, width, height);
        
        // --- Bước 1: Vẽ các đường kẻ ngang (Lưới/Grid) ---
        ctx.beginPath();
        ctx.strokeStyle = '#f3f4f6';
        ctx.lineWidth = 1;
        const gridRows = 5;
        for (let i = 0; i <= gridRows; i++) {
            const y = padding.top + chartHeight - (i * chartHeight / gridRows);
            ctx.moveTo(padding.left, y);
            ctx.lineTo(width - padding.right, y);
            
            // Vẽ các con số trên trục Y (Bên trái)
            ctx.fillStyle = '#9ca3af';
            ctx.font = '10px sans-serif';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'middle';
            const labelVal = Math.round(minVal + (i * (maxVal - minVal) / gridRows));
            ctx.fillText(labelVal, padding.left - 10, y);
        }
        ctx.stroke();
        
        // Nếu không có dữ liệu thì dừng lại (chỉ vẽ lưới)
        if (data.length === 0) return;

        // Khoảng cách giữa 2 điểm liên tiếp trên trục X
        const stepX = chartWidth / (data.length > 1 ? data.length - 1 : 1);
        
        // --- Bước 2: Vẽ mảng màu nền mờ nhạt (Gradient) đổ xuống từ đường kẻ ---
        const gradient = ctx.createLinearGradient(0, padding.top, 0, height - padding.bottom);
        gradient.addColorStop(0, 'rgba(14, 165, 233, 0.2)'); // Màu xanh nhạt trên cùng
        gradient.addColorStop(1, 'rgba(14, 165, 233, 0)');   // Trong suốt ở dưới đáy

        ctx.beginPath();
        ctx.moveTo(padding.left, padding.top + chartHeight);
        for (let i = 0; i < data.length; i++) {
            const x = padding.left + i * stepX;
            const y = padding.top + chartHeight - ((data[i] - minVal) / (maxVal - minVal) * chartHeight);
            ctx.lineTo(x, y);
        }
        ctx.lineTo(padding.left + (data.length - 1) * stepX, padding.top + chartHeight);
        ctx.fillStyle = gradient;
        ctx.fill();
        
        // --- Bước 3: Vẽ đường nét Line chính (Màu xanh đậm) ---
        ctx.beginPath();
        ctx.strokeStyle = '#0ea5e9';
        ctx.lineWidth = 3;
        ctx.lineJoin = 'round';
        ctx.lineCap = 'round';
        for (let i = 0; i < data.length; i++) {
            const x = padding.left + i * stepX;
            const y = padding.top + chartHeight - ((data[i] - minVal) / (maxVal - minVal) * chartHeight);
            if (i === 0) ctx.moveTo(x, y);
            else ctx.lineTo(x, y);
        }
        ctx.stroke();
        
        // --- Bước 4: Vẽ các điểm nhấn (Dots) và số liệu (Labels) ---
        for (let i = 0; i < data.length; i++) {
            const x = padding.left + i * stepX;
            const y = padding.top + chartHeight - ((data[i] - minVal) / (maxVal - minVal) * chartHeight);
            
            // Vẽ hình tròn (Điểm)
            ctx.beginPath();
            ctx.fillStyle = '#ffffff';
            ctx.strokeStyle = '#0ea5e9';
            ctx.lineWidth = 2;
            ctx.arc(x, y, 4, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
            
            // Vẽ nhãn ngày/tháng trên trục X (Bên dưới)
            ctx.fillStyle = '#6b7280';
            ctx.font = '11px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';
            ctx.fillText(labels[i], x, padding.top + chartHeight + 10);
            
            // Vẽ số liệu nhỏ ở trên mỗi điểm (chỉ hiển thị nếu > 0)
            if (data[i] > 0) {
                ctx.fillStyle = '#1f2937';
                ctx.font = 'bold 10px sans-serif';
                ctx.fillText(data[i], x, y - 15);
            }
        }
    }

    /**
     * Hàm vẽ biểu đồ tròn rỗng giữa (Donut Chart) bằng HTML5 Canvas thuần
     * Dùng để thể hiện cấu trúc % các chuyên khoa
     */
    function drawDonutChart(canvasId, data, colors) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        const dpr = window.devicePixelRatio || 1;
        const rect = canvas.parentElement.getBoundingClientRect();

        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;

        const ctx = canvas.getContext('2d');
        ctx.scale(dpr, dpr);

        // Đặt tâm của biểu đồ tròn ở giữa canvas
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        // Bán kính biểu đồ
        const radius = Math.min(centerX, centerY) - 10;
        // Độ dày của viền biểu đồ Donut
        const thickness = 20;

        // Tổng tất cả các giá trị để tính phần trăm
        const total = data.reduce((sum, val) => sum + val, 0);
        if (total === 0) return;

        // Góc bắt đầu là góc 12h (-90 độ)
        let startAngle = -Math.PI / 2;

        // Vẽ từng mảng (slice) của biểu đồ
        for (let i = 0; i < data.length; i++) {
            // Tính toán góc của mảng hiện tại
            const sliceAngle = (data[i] / total) * 2 * Math.PI;
            const endAngle = startAngle + sliceAngle;

            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, startAngle, endAngle);
            ctx.lineWidth = thickness;
            ctx.strokeStyle = colors[i % colors.length];
            ctx.stroke();
            
            // Cập nhật góc bắt đầu cho mảng tiếp theo
            startAngle = endAngle;
        }
    }

    // --- Khởi tạo: Gọi các hàm vẽ sau khi định nghĩa xong ---
    drawLineChart('trendChart', trendLabels, trendData);
    drawDonutChart('specialtyChart', pieData, brandColors);

    // Xử lý sự kiện Resize Window: Khi thu phóng cửa sổ, tiến hành vẽ lại toàn bộ 
    // để biểu đồ đạt chuẩn Responsive và không bị vỡ ảnh.
    window.addEventListener('resize', () => {
        drawLineChart('trendChart', trendLabels, trendData);
        drawDonutChart('specialtyChart', pieData, brandColors);
    });
});
