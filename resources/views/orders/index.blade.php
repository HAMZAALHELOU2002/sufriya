<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - طلبات المطعم المباشرة</title>
    <!-- مكتبة Bootstrap 5 للتصميم -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .container {
            flex: 1;
        }

        .order-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .badge-pending_acceptance {
            background-color: #ffc107;
            color: #000;
        }

        .badge-preparing {
            background-color: #0d6efd;
            color: #fff;
        }

        .badge-completed {
            background-color: #198754;
            color: #fff;
        }

        .badge-cancelled {
            background-color: #dc3545;
            color: #fff;
        }

        footer {
            background-color: #ffffff;
            border-top: 1px solid #dee2e6;
            margin-top: auto;
        }
    </style>
</head>

<body class="p-4">
    <div class="container">
        <!-- هيدر الصفحة -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h2>🛒 لوحة إدارة طلبات: {{ $restaurant->name ?? 'المطعم' }}</h2>
                <p class="text-muted mb-0">متابعة الطلبات الواردة من بوت الواتساب لحظياً</p>
            </div>
            <div class="text-end">
                <span class="badge bg-success p-2 fs-6 mb-1">⚡ متصل مباشر (Live)</span>
                <div class="text-muted small" id="last-update">جاري التحديث...</div>
            </div>
        </div>

        <!-- حاوية الطلبات -->
        <div class="row" id="orders-container">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2">جاري تحميل الطلبات...</p>
            </div>
        </div>
    </div>

    <!-- تذيل الصفحة -->
    <footer class="text-center py-3 mt-4 text-muted small">
        جميع الحقوق محفوظة &copy; <span id="current-year"></span> | نظام بوت الواتساب الذكي 🚀
    </footer>

    <!-- جافاسكريبت -->
   <!-- جافاسكريبت -->
    <script>
        document.getElementById('current-year').innerText = new Date().getFullYear();

        function loadOrders() {
            fetch('/api/orders/live')
                .then(response => response.json())
                .then(data => {
                    let container = document.getElementById('orders-container');

                    if (!data || data.length === 0) {
                        container.innerHTML =
                            `<div class="col-12 text-center py-5"><p class="text-muted fs-5">لا توجد طلبات حتى الآن...</p></div>`;
                        updateTimestamp();
                        return;
                    }

                    let html = '';
                    data.forEach(order => {
                        let itemsHtml = '';
                        if (order.items && order.items.length > 0) {
                            order.items.forEach(item => {
                                let itemName = item.menu_item ? item.menu_item.name : (item.name || 'وجبة محذوفة');
                                let itemPrice = item.price ?? item.total_price ?? item.unit_price ?? '0.000';
                                itemsHtml +=
                                    `<li class="mb-1">${itemName} (×${item.quantity}) - <span class="text-success">${itemPrice} ₪</span></li>`;
                            });
                        }

                        let customerName = (order.customer && order.customer.name) ? order.customer.name : (order.customer_name || 'غير معروف');
                        let customerPhone = (order.customer && order.customer.wa_phone_number) ? order.customer.wa_phone_number : (order.wa_phone_number || '-');

                        html += `
                        <div class="col-md-4 mb-4">
                            <div class="card order-card p-3 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="fw-bold mb-0">طلب #${order.id}</h5>
                                    <span class="badge badge-${order.status} px-3 py-2">${order.status}</span>
                                </div>
                                <div class="mb-2 text-secondary small">
                                    <div>العميل: <strong class="text-dark">${customerName}</strong></div>
                                    <div>الهاتف: <strong class="text-dark">${customerPhone}</strong></div>
                                </div>
                                <hr class="my-2">
                                <div class="flex-grow-1">
                                    <p class="fw-bold mb-1 text-muted small">محتويات الطلب:</p>
                                    <ul class="ps-3 mb-3">${itemsHtml}</ul>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                    <span class="fs-5 fw-bold text-primary">${order.total_amount ?? '0.00'} ₪</span>
                                    <select class="form-select form-select-sm w-auto" onchange="updateStatus(${order.id}, this.value)">
                                        <option value="pending_acceptance" ${order.status === 'pending_acceptance' ? 'selected' : ''}>قيد الانتظار</option>
                                        <option value="preparing" ${order.status === 'preparing' ? 'selected' : ''}>جاري التحضير</option>
                                        <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>مكتمل</option>
                                        <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>ملغى</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    `;
                    });
                    container.innerHTML = html;
                    updateTimestamp();
                })
                .catch(err => console.error('خطأ في جلب الطلبات:', err));
        }

        function updateTimestamp() {
            let now = new Date();
            let timeString = now.toLocaleTimeString('ar-EG');
            document.getElementById('last-update').innerText = "آخر تحديث: " + timeString;
        }

        function updateStatus(orderId, newStatus) {
            fetch(`/api/orders/${orderId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' || data.success) {
                        loadOrders();
                    }
                })
                .catch(err => console.error('خطأ في تحديث الحالة:', err));
        }

        loadOrders();
        setInterval(loadOrders, 3000);
    </script>
