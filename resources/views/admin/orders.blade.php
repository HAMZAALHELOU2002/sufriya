<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة طلبات مطعم حمزة 🍔</title>
    <!-- استدعاء بوتستراب للتصميم -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- استدعاء مكتبة Toastify للإشعارات المرئية -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body class="bg-light">

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h1 class="fw-bold text-danger">🍔 لوحة تحكم طلبات مطعم حمزة</h1>
                <p class="text-muted">الطلبات الواردة عبر بوت الواتساب (تحديث تلقائي وتنصت للطلبات الجديدة)</p>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>رقم الطلب</th>
                                <th>رقم المطعم</th>
                                <th>رقم الزبون</th>
                                <th>نوع الاستلام</th>
                                <th>المبلغ الإجمالي</th>
                                <th>الحالة</th>
                                <th>وقت الطلب</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>#{{ $order->id }}</td>
                                    <td>{{ $order->restaurant_id ?? '-' }}</td>
                                    <td class="fw-bold">{{ $order->customer_id ?? '-' }}</td>
                                    <td>{{ $order->fulfillment_type ?? '-' }}</td>
                                    <td>{{ $order->total_amount ?? 0 }} شيكل</td>
                                    <td>
                                        <span class="badge bg-warning text-dark">{{ $order->status }}</span>
                                    </td>
                                    <td>{{ $order->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">لا توجد طلبات جديدة حتى الآن...</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- استدعاء مكتبة Toastify للإشعارات -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        // حفظ أحدث ID للطلب الموجود حالياً في الصفحة
        let lastOrderId = {{ $orders->first()?->id ?? 0 }};

        // دالة لتوليد صوت تنبيه (Beep) باستخدام المتصفح مباشرة بدون ملفات خارجية
        function playBeep() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();

                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(800, audioCtx.currentTime); // حدة الصوت
                gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);

                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);

                oscillator.start();
                oscillator.stop(audioCtx.currentTime + 0.3); // مدة الصوت 0.3 ثانية
            } catch (e) {
                console.log('Audio context not allowed yet');
            }
        }

        // فحص وجود طلبات جديدة كل 5 ثوانٍ عبر جلب الصفحة في الخلفية (AJAX)
        setInterval(async () => {
            try {
                let response = await fetch(window.location.href);
                let html = await response.text();
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, 'text/html');

                // استخراج جدول الطلبات الجديد وتحديث الصفحة محلياً أو إعادة تحميلها إذا اختلف الوضع
                let newTableBody = doc.querySelector('tbody').innerHTML;
                let currentTableBody = document.querySelector('tbody').innerHTML;

                if (newTableBody !== currentTableBody) {
                    // استخراج الـ ID الأول الجديد
                    let firstRow = doc.querySelector('tbody tr');
                    if (firstRow) {
                        let idText = firstRow.querySelector('td')?.innerText;
                        let newId = parseInt(idText?.replace('#', '')) || 0;

                        if (newId > lastOrderId) {
                            // تشغيل التنبيه الصوتي
                            playBeep();

                            // إظهار إشعار مرئي
                            Toastify({
                                text: "🚨 وصل طلب جديد لمطعم حمزة!",
                                duration: 5000,
                                gravity: "top",
                                position: "left",
                                backgroundColor: "#dc3545",
                            }).showToast();

                            lastOrderId = newId;
                        }
                    }
                    // تحديث الجدول تلقائياً بالبيانات الجديدة
                    document.querySelector('tbody').innerHTML = newTableBody;
                }
            } catch (error) {
                console.error('خطأ في جلب التحديثات:', error);
            }
        }, 5000);
    </script>
</body>
</html>
