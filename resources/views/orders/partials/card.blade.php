<div class="bg-gray-900 border border-gray-800 rounded-xl p-4 shadow-sm hover:shadow-md transition flex flex-col justify-between theme-card">
    <div>
        <!-- رقم الطلب وحالته + زر الحذف -->
        <div class="flex justify-between items-center mb-3">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-white" dir="ltr">#{{ $order->id }}</span>

                <!-- زر الحذف الذي يفتح نافذة التأكيد المنبثقة -->
                <button type="button" onclick="openDeleteModal('{{ route('orders.destroy', $order->id) }}')"
                    class="text-rose-500 hover:text-white bg-rose-500/10 hover:bg-rose-600 p-1.5 rounded-lg transition-all duration-200 border border-rose-500/20 shadow-sm flex items-center justify-center"
                    title="حذف الطلب">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>

            @php
                $statusLabels = [
                    'pending_acceptance' => 'قيد الانتظار',
                    'accepted' => 'تم القبول',
                    'preparing' => 'قيد التجهيز',
                    'ready' => 'جاهز للاستلام',
                    'completed' => 'مكتمل',
                    'cancelled' => 'ملغي'
                ];
            @endphp
            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-gray-800 text-gray-300">
                {{ $statusLabels[$order->status] ?? $order->status }}
            </span>
        </div>

        <!-- معلومات العميل مع شارة الـ VIP المشروطة -->
        <div class="text-xs text-gray-300 space-y-1.5 mb-3 bg-gray-950/60 p-2.5 rounded-lg border border-gray-800">
            <div class="flex justify-between items-center">
                <p><strong>👤 العميل:</strong> {{ optional($order->customer)->name ?? 'غير متوفر' }}</p>
                @if(optional($order->customer)->is_vip)
                    <span class="bg-amber-500/20 text-amber-400 text-[10px] px-1.5 py-0.5 rounded-md font-bold border border-amber-500/30">⭐ VIP</span>
                @endif
            </div>
            <p><strong>📞 الهاتف:</strong> <span dir="ltr">{{ optional($order->customer)->wa_phone_number ?? 'غير متوفر' }}</span></p>
        </div>

        <!-- محتويات الطلب (اسم الوجبة الحقيقي) -->
        <div class="mb-4">
            <p class="text-xs font-bold text-gray-300 mb-1.5">🛒 محتويات الطلب:</p>
            <div class="bg-amber-500/10 border border-amber-500/20 p-2.5 rounded-lg space-y-1.5 text-xs text-gray-200">
                <div class="flex justify-between items-center">
                    <span>• {{ !empty($order->payment_reference) ? $order->payment_reference : 'وجبة رئيسية' }} <span class="text-amber-400 font-semibold">(x1)</span></span>
                    <span class="font-bold text-amber-500">{{ $order->total_amount ?? 0 }} ₪</span>
                </div>
            </div>

            <!-- المجموع الكلي -->
            <div class="mt-2 flex justify-between items-center text-xs font-bold text-gray-200 px-1">
                <span>المجموع الكلي:</span>
                <span class="text-amber-400 text-sm">{{ $order->total_amount ?? 0 }} ₪</span>
            </div>
        </div>
    </div>

    <!-- زر تحديث الحالة -->
    <div>
        <label class="block text-[10px] font-semibold text-gray-400 mb-1">تغيير الحالة:</label>
        <form action="{{ route('orders.updateStatus', $order->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <select name="status" onchange="this.form.submit()" class="w-full bg-gray-950 border border-gray-700 text-gray-200 text-xs rounded-lg p-1.5 focus:ring-1 focus:ring-amber-500 focus:outline-none cursor-pointer">
                <option value="pending_acceptance" {{ $order->status == 'pending_acceptance' ? 'selected' : '' }}>قيد الانتظار</option>
                <option value="accepted" {{ $order->status == 'accepted' ? 'selected' : '' }}>تم القبول</option>
                <option value="preparing" {{ $order->status == 'preparing' ? 'selected' : '' }}>قيد التجهيز</option>
                <option value="ready" {{ $order->status == 'ready' ? 'selected' : '' }}>جاهز للاستلام</option>
                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>مكتمل</option>
                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>ملغي</option>
            </select>
        </form>
    </div>
</div>

<!-- نافذة التأكيد المنبثقة (Modal) الخاصة بالحذف -->
<div id="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm hidden">
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-2xl shadow-2xl max-w-sm w-full mx-4 text-center transform transition-all">
        <div class="w-12 h-12 rounded-full bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-500 text-xl mx-auto mb-4">
            ⚠️
        </div>
        <h3 class="text-white font-black text-base mb-1">حذف الطلب نهائياً</h3>
        <p class="text-gray-400 text-xs mb-6">هل أنت متأكد من رغبتك في حذف هذا الطلب؟ لا يمكن التراجع عن هذا الإجراء.</p>

        <div class="flex gap-3">
            <button type="button" onclick="closeDeleteModal()" class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 font-bold text-xs py-2.5 rounded-xl transition">
                إلغاء
            </button>
            <form id="deleteForm" method="POST" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs py-2.5 rounded-xl transition shadow-lg shadow-rose-600/20">
                    نعم، حذف
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    if (typeof openDeleteModal === 'undefined') {
        function openDeleteModal(actionUrl) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            form.action = actionUrl;
            modal.classList.remove('hidden');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
        }
    }
</script>
