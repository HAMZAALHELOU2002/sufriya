@extends('layouts.admin')

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-3 align-items-center">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold text-dark">
          <i class="fas fa-layer-group text-primary ml-2"></i> إدارة أقسام المنيو
        </h1>
      </div>
      <div class="col-sm-6 text-left">
        <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm shadow-sm font-weight-bold">
          <i class="fas fa-plus-circle ml-1"></i> إضافة قسم جديد
        </a>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <!-- رسائل النجاح أو الخطأ -->
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show shadow-sm text-right" role="alert">
        <i class="fas fa-check-circle ml-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show shadow-sm text-right" role="alert">
        <i class="fas fa-exclamation-circle ml-1"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    @endif

    <div class="row">
      @forelse($categories as $category)
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="card category-card shadow-sm border-0 rounded-lg overflow-hidden">
            <div class="card-body p-4 bg-white position-relative">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge badge-primary px-3 py-2 font-weight-bold rounded-pill">
                  <i class="fas fa-utensils ml-1"></i> ترتيب: {{ $category->sort_order }}
                </span>
                <span class="text-{{ $category->is_active ? 'success' : 'secondary' }} font-weight-bold small">
                  <i class="fas fa-check-circle ml-1"></i> {{ $category->is_active ? 'مفعل' : 'معطل' }}
                </span>
              </div>
              <h4 class="font-weight-bold text-dark mb-2">{{ $category->name }}</h4>
              <p class="text-muted small mb-3">{{ $category->description ?? 'لا يوجد وصف مختصر.' }}</p>

              <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                <span class="text-secondary font-weight-bold small">
                  <i class="fas fa-hamburger text-warning ml-1"></i> قسم مطعم
                </span>
                <div class="d-flex align-items-center">
                  <!-- زر الانتقال لصفحة التعديل -->
                  <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-outline-info btn-sm action-btn mx-1" title="تعديل القسم">
                    <i class="fas fa-edit"></i>
                  </a>

                  <!-- زر الحذف -->
                  <form action="{{ route('categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا القسم؟');" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm action-btn" title="حذف القسم">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12 text-center py-5">
          <div class="card shadow-sm border-0 p-5 bg-white">
            <div class="text-muted font-weight-bold">لا توجد أقسام مضافة حتى الآن. انقر على زر "إضافة قسم جديد" للبدء!</div>
          </div>
        </div>
      @endforelse
    </div>

  </div>
</section>
@endsection
