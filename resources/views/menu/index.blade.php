<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنيو - {{ $restaurant->name ?? 'المطعم' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <div class="container mx-auto p-6">
        <!-- رأس الصفحة -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">📋 إدارة المنيو والأطباق</h1>
            <a href="{{ route('menu-items.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow">
                + إضافة وجبة جديدة
            </a>
        </div>

        <!-- رسائل النجاح -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- جدول الوجبات -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 font-semibold">
                        <th class="p-4">اسم الوجبة</th>
                        <th class="p-4">السعر</th>
                        <th class="p-4">الوصف</th>
                        <th class="p-4 text-center">التحكم</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse($menuItems as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="p-4 font-bold text-gray-900">{{ $item->name }}</td>
                            <td class="p-4 font-semibold text-amber-700">{{ $item->price }} ₪</td>
                            <td class="p-4 text-gray-500 text-xs">{{ $item->description ?? 'لا يوجد وصف' }}</td>
                            <td class="p-4 text-center space-x-2 space-x-reverse">
                                <a href="{{ route('menu-items.edit', $item->id) }}" class="text-blue-600 hover:underline text-xs font-semibold">تعديل</a>
                                <form action="{{ route('menu-items.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('متأكد بدك تحذف هاي الوجبة؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-xs font-semibold">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-6 text-center text-gray-400">لا توجد وجبات مضافة حالياً في المنيو.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
