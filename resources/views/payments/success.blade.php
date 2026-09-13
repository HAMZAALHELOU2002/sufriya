<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نجاح الدفع - مطعم حمزة</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 500px;">
        <div class="card shadow-sm text-center">
            <div class="card-body">
                <div class="mb-3 text-success fs-1">✅</div>
                <h3 class="card-title text-success mb-3">تم الدفع بنجاح!</h3>
                <p class="text-muted">شكراً لك، تم تأكيد الطلب رقم <strong>#{{ $order->id }}</strong> وجاري تحضيره الآن.</p>
                <hr>
                <a href="{{ route('orders.index') }}" class="btn btn-primary mt-3">العودة للوحة التحكم</a>
            </div>
        </div>
    </div>
</body>
</html>
