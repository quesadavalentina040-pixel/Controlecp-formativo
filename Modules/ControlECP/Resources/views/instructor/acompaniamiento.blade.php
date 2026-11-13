@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .acom-hero{padding:1.5rem 1.75rem;color:#fff;background:linear-gradient(120deg,#001A29,#00324D);border-radius:22px}
    .acom-card{border:0;border-radius:18px;box-shadow:0 5px 20px rgba(0,26,41,.07)}
    .acom-kpi-card{background:#fff;border:2px solid transparent;border-radius:14px;transition:transform .2s ease,box-shadow .2s ease}
    .acom-kpi-card:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,26,41,.08)}
    .acom-kpi-label{color:#475569;font-size:.95rem;font-weight:500}
    .acom-kpi-value{font-size:1.7rem;font-weight:800;line-height:1;margin-top:.15rem}
    .acom-kpi-subtitle{color:#64748b;font-size:.9rem}
    .acom-kpi-icon{width:38px;height:38px;flex:0 0 38px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;font-size:1rem}
    .acom-kpi-row{margin-right:0;margin-left:0}
    .acom-filters{padding:1rem 1.15rem;background:#001A29;border-radius:18px;box-shadow:0 5px 20px rgba(0,26,41,.08)}
    .acom-filter-grid{display:grid;grid-template-columns:minmax(160px,1fr) minmax(220px,1.35fr) minmax(220px,1fr) auto;align-items:end;gap:.65rem}
    .acom-filter-field{min-width:0}
    .acom-filter-field label{display:block;margin:0 0 .3rem;color:#fff;font-size:.78rem;font-weight:700}
    .acom-filter-field .form-control,.acom-filter-field .form-select{width:100%;min-width:0;height:44px;border:1px solid #dce5eb;border-radius:10px;background:#fff;color:#001A29;box-shadow:none}
    .acom-filter-actions{display:flex;gap:.55rem}
    .acom-filter-submit,.acom-filter-reset{height:44px;display:inline-flex;align-items:center;justify-content:center;border-radius:10px;font-weight:700;white-space:nowrap}
    .acom-filter-submit{min-width:112px;padding:0 1rem;border:1px solid #39A900;background:#39A900;color:#fff}
    .acom-filter-submit:hover{border-color:#2e8800;background:#2e8800;color:#fff}
    .acom-filter-reset{width:46px;border:1px solid #fff;background:#fff;color:#001A29;text-decoration:none}
    .acom-filter-reset:hover{background:#edf2f4;color:#001A29}
    .btn.acom-green-button{border-color:#39A900;background:#39A900;color:#fff}
    .btn.acom-green-button:hover,.btn.acom-green-button:focus{border-color:#2e8800;background:#2e8800;color:#fff}
    .btn.acom-category-button{border:1px solid #fff;background:#fff;color:#168b52}
    .btn.acom-category-button:hover,.btn.acom-category-button:focus{border-color:#e8f5ee;background:#e8f5ee;color:#126b40}
    .acom-action-button{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;padding:0;border:1px solid #c9e5ff;border-radius:10px;background:#eef7ff;color:#0878c9}
    .acom-action-button:hover,.acom-action-button:focus{border-color:#9bd0ff;background:#dceeff;color:#0566ad}
    .acom-action-button--green{border-color:#c8ead4;background:#eef9f1;color:#198754}
    .acom-action-button--green:hover,.acom-action-button--green:focus{border-color:#a6d9b8;background:#dff3e5;color:#146c43}
    .acom-action-button--yellow{border-color:#f1e4b7;background:#fffdf4;color:#a87919}
    .acom-action-button--yellow:hover,.acom-action-button--yellow:focus{border-color:#e7d59b;background:#fbf4df;color:#896313}
    .acom-action-button--sessions{border-color:#c8ead4;background:#eef9f1;color:#198754}
    .acom-action-button--sessions:hover,.acom-action-button--sessions:focus{border-color:#a6d9b8;background:#dff3e5;color:#146c43}
    .acom-action-button--red{border-color:#ffc9c9;background:#fff1f1;color:#dc3545}
    .acom-action-button--red:hover,.acom-action-button--red:focus{border-color:#f5a8a8;background:#ffe2e2;color:#b02a37}
    .acom-action-button--muted{border-color:#d6dde4;background:#f1f5f9;color:#64748b}
    .acom-action-button--muted:hover,.acom-action-button--muted:focus{border-color:#b9c5d1;background:#e2e8f0;color:#475569}
    .acom-action-button--purple{border-color:#e2d5ff;background:#f5f0ff;color:#7c3aed}
    .acom-action-button--purple:hover,.acom-action-button--purple:focus{border-color:#cdb8ff;background:#ece3ff;color:#6d28d9}
    .acom-action-button--pink{border-color:#f7c4df;background:#fff0f7;color:#d63384}
    .acom-action-button--pink:hover,.acom-action-button--pink:focus{border-color:#ed9fc7;background:#ffe0ef;color:#b0256a}
    .acom-status-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.28rem .7rem;border:1px solid #9edcba;border-radius:999px;background:#e2f5eb;color:#16834f;font-size:.78rem;font-weight:700;line-height:1.2;white-space:nowrap}
    .acom-status-badge--inactive{border-color:#d6dde4;background:#f1f5f9;color:#64748b}
    .acom-modal-text{overflow-wrap:anywhere;word-break:break-word}
    .acom-detail-modal{overflow:hidden;border:0;border-radius:18px;box-shadow:0 18px 55px rgba(0,26,41,.2)}
    .acom-detail-modal .modal-header{padding:1.1rem 1.35rem;border:0;background:linear-gradient(120deg,#001A29,#00324D);color:#fff}
    .acom-detail-modal .modal-header .btn-close{filter:brightness(0) invert(1);opacity:.85}
    .acom-detail-modal .modal-body{padding:1.35rem}
    .acom-add-sessions-modal{overflow:hidden;border:0;border-radius:20px;box-shadow:0 20px 60px rgba(0,26,41,.24)}
    .acom-add-sessions-modal .modal-header{padding:1rem 1.35rem;border-bottom:1px solid #e8eef2;background:#fff}
    .acom-add-sessions-modal .modal-title{display:flex;align-items:center;gap:.7rem;color:#00324d}
    .acom-add-sessions-modal .acom-add-sessions-icon{display:inline-flex;width:38px;height:38px;align-items:center;justify-content:center;border-radius:12px;background:#edf7fc;color:#0878c9}
    .acom-add-sessions-modal .modal-body{padding:1.35rem;background:#fbfcfd}
    .acom-add-sessions-modal .acom-add-sessions-field{margin-bottom:1.15rem}
    .acom-add-sessions-modal .acom-add-sessions-field:last-child{margin-bottom:0}
    .acom-add-sessions-modal .form-label{margin-bottom:.45rem;color:#36566a;font-size:.88rem;font-weight:700}
    .acom-add-sessions-modal .form-control{border-color:#dce5eb;border-radius:10px;padding:.65rem .8rem;color:#183b4e}
    .acom-add-sessions-modal .form-control:focus{border-color:#9bd0ff;box-shadow:0 0 0 .2rem rgba(8,120,201,.12)}
    .acom-add-sessions-modal .modal-footer{padding:.9rem 1.35rem 1.15rem;border-top:1px solid #edf1f4;background:#fff}
    .acom-add-sessions-modal .acom-add-sessions-submit{padding:.6rem 1rem;border:1px solid #0878c9;border-radius:10px;background:#0878c9;color:#fff;font-weight:700}
    .acom-add-sessions-modal .acom-add-sessions-submit:hover,.acom-add-sessions-modal .acom-add-sessions-submit:focus{border-color:#0566ad;background:#0566ad;color:#fff}
    @media(max-width:575.98px){.acom-add-sessions-modal .modal-body{padding:1rem}.acom-add-sessions-modal .modal-footer{padding:.85rem 1rem 1rem}.acom-add-sessions-modal .acom-add-sessions-submit{width:100%}}
    .acom-close-modal{overflow:hidden;border:0;border-radius:20px;box-shadow:0 20px 60px rgba(0,26,41,.24)}
    .acom-close-modal .modal-header{padding:1rem 1.35rem;border-bottom:1px solid #e8eef2;background:#fff}
    .acom-close-modal .modal-title{display:flex;align-items:center;gap:.7rem;color:#00324d}
    .acom-close-modal .acom-close-icon{display:inline-flex;width:38px;height:38px;align-items:center;justify-content:center;border-radius:12px;background:#fff4e3;color:#b66a12}
    .acom-close-modal .modal-body{padding:1.25rem 1.35rem;background:#fbfcfd}
    .acom-close-modal .acom-close-notice{display:flex;align-items:flex-start;gap:.65rem;margin-bottom:1.15rem;padding:.8rem .9rem;border:1px solid #f4d5a5;border-radius:12px;background:#fff8ed;color:#805719;font-size:.86rem;line-height:1.5}
    .acom-close-modal .acom-close-notice i{margin-top:.15rem;color:#c47b1a}
    .acom-close-modal .form-label{margin-bottom:.45rem;color:#36566a;font-size:.88rem;font-weight:700}
    .acom-close-modal .form-control,.acom-close-modal .form-select{border-color:#dce5eb;border-radius:10px;padding:.65rem .8rem;color:#183b4e}
    .acom-close-modal .form-control:focus,.acom-close-modal .form-select:focus{border-color:#9bd0ff;box-shadow:0 0 0 .2rem rgba(8,120,201,.12)}
    .acom-close-modal .acom-close-field{margin-bottom:1.1rem}
    .acom-close-modal .acom-close-field:last-child{margin-bottom:0}
    .acom-close-modal .modal-footer{padding:.9rem 1.35rem 1.15rem;border-top:1px solid #edf1f4;background:#fff}
    .acom-close-modal .acom-close-submit{padding:.6rem 1rem;border:1px solid #198754;border-radius:10px;background:#198754;color:#fff;font-weight:700}
    .acom-close-modal .acom-close-submit:hover,.acom-close-modal .acom-close-submit:focus{border-color:#146c43;background:#146c43;color:#fff}
    @media(max-width:575.98px){.acom-close-modal .modal-body{padding:1rem}.acom-close-modal .modal-footer{padding:.85rem 1rem 1rem}.acom-close-modal .acom-close-submit{width:100%}}
    .acom-session-detail{overflow:hidden;border:0;border-radius:18px;box-shadow:0 18px 55px rgba(0,26,41,.2)}
    .acom-session-detail .modal-header{padding:1.1rem 1.35rem;border:0;background:linear-gradient(120deg,#001A29,#00324D);color:#fff}
    .acom-session-detail .modal-header .btn-close{filter:brightness(0) invert(1);opacity:.85}
    .acom-session-detail .modal-body{padding:1.35rem}
    .acom-sessions-modal{overflow:hidden;border:0;border-radius:20px;box-shadow:0 20px 60px rgba(0,26,41,.24)}
    .acom-sessions-modal .modal-header{padding:1rem 1.35rem;border-bottom:1px solid #e8eef2;background:#fff}
    .acom-sessions-modal .modal-title{display:flex;align-items:center;gap:.65rem;color:#00324d}
    .acom-sessions-modal .acom-sessions-title-icon{display:inline-flex;width:38px;height:38px;align-items:center;justify-content:center;border-radius:12px;background:#eef9f1;color:#198754}
    .acom-sessions-modal .modal-body{padding:1.25rem 1.35rem;background:#fbfcfd}
    .acom-sessions-modal .acom-sessions-table-wrap{overflow:hidden;border:1px solid #e2eaf0;border-radius:14px;background:#fff}
    .acom-sessions-modal .table{margin:0}
    .acom-sessions-modal .table thead th{padding:.8rem 1rem;border-bottom:1px solid #dfe7ed;background:#f3f7f9;color:#36566a;font-size:.78rem;font-weight:750;letter-spacing:.025em}
    .acom-sessions-modal .table tbody td{padding:.75rem 1rem;border-color:#edf1f4}
    .acom-sessions-modal .table tbody tr:last-child td{border-bottom:0}
    .acom-sessions-modal .table-hover tbody tr:hover>*{background:#f8fbfd}
    .acom-session-status--scheduled{display:inline-flex;align-items:center;padding:.34rem .72rem;border:1px solid #f1e5b9;border-radius:999px;background:#fff9e8;color:#856719;font-size:.78rem;font-weight:700;line-height:1.2}
    .acom-sessions-modal .acom-action-button--yellow{border-color:#f1e4b7;background:#fffdf4;color:#a87919}
    .acom-sessions-modal .acom-action-button--yellow:hover,.acom-sessions-modal .acom-action-button--yellow:focus{border-color:#e7d59b;background:#fbf4df;color:#896313}
    .acom-sessions-modal .btn-outline-primary,.acom-sessions-modal .btn-outline-success{padding:.4rem .8rem;font-weight:600}
    .acom-sessions-modal .btn-outline-primary{border-color:#b9d9ee;color:#176b9b}
    .acom-sessions-modal .btn-outline-primary:hover{background:#edf7fc;color:#12587f}
    .acom-sessions-modal .btn-outline-success{border-color:#b9dfc8;color:#198754}
    .acom-sessions-modal .btn-outline-success:hover{background:#eff9f2;color:#146c43}
    .acom-sessions-modal .btn-close{padding:.65rem;border-radius:50%;background-color:#f1f5f7;opacity:1}
    @media(max-width:575.98px){.acom-sessions-modal .modal-header{flex-wrap:wrap;gap:.75rem}.acom-sessions-modal .modal-header>.d-flex{width:100%;margin-left:0!important;flex-wrap:wrap}.acom-sessions-modal .modal-body{padding:1rem}}
    .acom-registered-sessions-dialog{width:calc(100% - 2rem);max-width:1280px}
    .acom-registered-sessions-dialog .acom-session-detail{max-height:calc(100vh - 2rem)}
    .acom-registered-sessions-dialog .modal-body{overflow-x:hidden}
    @media(min-width:1400px){.acom-registered-sessions-dialog{max-width:1400px}}
    @media(max-width:767.98px){.acom-registered-sessions-dialog{width:calc(100% - 1rem);margin:.5rem auto}.acom-registered-sessions-dialog .modal-body{padding:1rem}}
    .acom-session-detail-card{height:100%;padding:1rem;border:1px solid #e6edf2;border-radius:12px;background:#f8fafc}
    .acom-session-detail-label{display:block;margin-bottom:.35rem;color:#64748b;font-size:.76rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
    .acom-session-detail-text{margin:0;color:#334155;line-height:1.6;overflow-wrap:anywhere;white-space:pre-wrap}
    .acom-session-evidence{display:block;width:100%;max-height:220px;object-fit:contain;border-radius:10px;background:#f1f5f9}
    .acom-upload-modal{overflow:hidden;border:0;border-radius:20px;box-shadow:0 18px 55px rgba(0,26,41,.2)}
    .acom-upload-modal .modal-header{align-items:flex-start;padding:1.35rem 1.5rem .75rem;border:0}
    .acom-upload-modal .modal-body{padding:.5rem 1.5rem 1rem}
    .acom-upload-title{margin:0;color:#001a29;font-size:1.2rem;font-weight:800}
    .acom-upload-icon{color:#b99a50}
    .acom-upload-subtitle{margin:.15rem 0 0;color:#64748b;font-size:.9rem}
    .acom-upload-type{position:absolute;width:1px;height:1px;opacity:0}
    .acom-upload-type-card{display:flex;min-height:74px;align-items:center;gap:.65rem;padding:.75rem .9rem;border:1px solid #d6dde4;border-radius:10px;cursor:pointer;transition:border-color .15s ease,background-color .15s ease}
    .acom-upload-type-card:has(.acom-upload-type:checked){border-color:#eadcae;background:#fffdf4}
    .acom-upload-type:focus-visible + .acom-upload-type-content{outline:2px solid #eadcae;outline-offset:3px;border-radius:4px}
    .acom-upload-type-content{display:flex;min-width:0;align-items:center;gap:.65rem}
    .acom-upload-type-icon{color:#168b52;font-size:1.05rem}
    .acom-upload-type-copy{display:flex;flex-direction:column;line-height:1.35}
    .acom-upload-type-copy strong{color:#183b4e;font-size:.9rem}
    .acom-upload-type-copy span{color:#7b8794;font-size:.78rem}
    .acom-upload-field-label{margin-bottom:.45rem;color:#183b4e;font-size:.9rem;font-weight:700}
    .acom-upload-modal .form-control:focus{border-color:#e8d99f;box-shadow:0 0 0 .2rem rgba(232,217,159,.28)}
    .acom-upload-submit{border-color:#eadcae;background:#fff7dc;color:#765a12}
    .acom-upload-submit:hover,.acom-upload-submit:focus{border-color:#decd91;background:#f8edc8;color:#60480d}
    .acom-detail-name{margin:0 0 1.15rem;color:#00324D;font-size:1.12rem;font-weight:800;line-height:1.4;overflow-wrap:anywhere}
    .acom-detail-item{height:100%;padding:.85rem 1rem;border:1px solid #e6edf2;border-radius:12px;background:#f8fafc}
    .acom-detail-label{display:block;margin-bottom:.3rem;color:#64748b;font-size:.76rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
    .acom-detail-value{color:#183b4e;font-weight:600;overflow-wrap:anywhere}
    .acom-detail-progress{height:7px;margin-top:.55rem;border-radius:99px;background:#e2e8f0}
    .acom-detail-motive{padding:1rem;border:1px solid #e3edf3;border-radius:12px;background:#f5f9fc}
    .acom-detail-motive-text{margin:0;color:#334155;line-height:1.6;overflow-wrap:anywhere;word-break:break-word;white-space:pre-wrap}
    .acom-tabs{display:inline-flex;gap:.25rem;padding:.25rem;border:1px solid #dce5eb;border-radius:999px;background:#f8fafc}
    .acom-tabs .nav-link{padding:.55rem 1.25rem;border-radius:999px;color:#001A29;font-weight:700;white-space:nowrap}
    .acom-tabs .nav-link.active{background:#39A900;color:#fff}
    @media(max-width:575px){.acom-tabs{display:flex;width:100%}.acom-tabs .nav-link{flex:1;padding:.55rem .6rem;text-align:center}}
    @media(max-width:991px){.acom-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.acom-filter-actions{grid-column:1/-1;justify-content:flex-end}}
    @media(max-width:575px){.acom-filter-grid{grid-template-columns:minmax(0,1fr)}.acom-filter-actions{grid-column:auto}.acom-filter-submit{flex:1}}
    .acom-calendar{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:2px;overflow:hidden;border-radius:14px;background:#dce5eb}
    .acom-calendar-heading{padding:.7rem .35rem;background:#001A29;color:#fff;text-align:center;font-size:.75rem;font-weight:700;text-transform:uppercase}
    .acom-calendar-day{display:flex;min-width:0;min-height:102px;flex-direction:column;gap:.2rem;padding:.45rem;background:#fff}
    .acom-calendar-day.is-outside{background:#eaf1f6;color:#718096}
    .acom-calendar-day-top{display:flex;align-items:center;justify-content:space-between;min-height:26px;margin-bottom:.1rem}
    .acom-calendar-day-number{display:inline-flex;width:24px;height:24px;align-items:center;justify-content:center;border-radius:50%;font-size:.82rem;font-weight:700}
    .acom-calendar-day.is-today .acom-calendar-day-number{background:#39A900;color:#fff;box-shadow:0 0 8px rgba(57,169,0,.3)}
    .acom-calendar-today-label{padding:.1rem .35rem;border-radius:5px;background:#168b52;color:#fff;font-size:.62rem;font-weight:700}
    .acom-calendar-indicator{display:flex;min-width:0;align-items:center;gap:.3rem;margin-top:.25rem;padding:.28rem .4rem;border-radius:999px;font-size:.65rem;font-weight:700;line-height:1.2}
    .acom-calendar-indicator.sem-neutro{background:#e2e8f0;color:#475569}
    .acom-calendar-indicator.sem-rojo{background:#fee2e2;color:#b91c1c}
    .acom-calendar-indicator.sem-verde{background:#dcfce7;color:#166534}
    .acom-calendar-indicator.sem-amarillo{background:#fef3c7;color:#92400e}
    .acom-calendar-dot{width:6px;height:6px;flex:0 0 6px;border-radius:50%;background:#94a3b8}
    .sem-rojo .acom-calendar-dot{background:#dc2626}
    .sem-verde .acom-calendar-dot{background:#16a34a}
    .sem-amarillo .acom-calendar-dot{background:#f59e0b}
    @media(max-width:767px){.acom-calendar{gap:1px}.acom-calendar-heading{padding:.5rem .15rem;font-size:.62rem}.acom-calendar-day{min-height:78px;padding:.25rem}.acom-calendar-day-top{min-height:22px}.acom-calendar-day-number{width:21px;height:21px;font-size:.72rem}.acom-calendar-today-label{font-size:.52rem}.acom-calendar-indicator{gap:.2rem;padding:.22rem .28rem;font-size:.55rem}}
</style>
@endpush

@section('dashboard-content')
@php
    $formatSessionTime = static function ($time) {
        $hour = (int) substr($time, 0, 2);

        return sprintf('%d:%s', $hour % 12 ?: 12, substr($time, 3, 2));
    };
@endphp
<div class="container-fluid px-3 py-3">
    <header class="acom-hero mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="small fw-bold text-uppercase mb-2" style="color:#62E31D;letter-spacing:.1em">Control ECP · Instructor</div>
            <h1 class="h3 fw-bold mb-1"><i class="fas fa-hand-holding-heart me-2" style="color:#62E31D"></i>Acompañamiento pedagógico</h1>
            <p class="mb-0 text-white-50">Gestiona sesiones y registros reservados, visibles únicamente para ti.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn acom-category-button rounded-pill px-4" type="button" data-bs-toggle="modal" data-bs-target="#crearCategoriaAcompanamiento"><i class="fas fa-plus me-2"></i>Crear categoría</button>
            <button class="btn acom-green-button rounded-pill px-4" type="button" data-bs-toggle="modal" data-bs-target="#nuevoAcompanamiento"><i class="fas fa-plus me-2"></i>Nuevo acompañamiento</button>
        </div>
    </header>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="row g-3 mb-4 acom-kpi-row">
        @foreach([
            ['Activos',$stats['activos'],'#0284c7','#e0f2fe','rgba(2,132,199,.35)','Acompañamientos activos'],
            ['Sesiones realizadas',$stats['realizadas'],'#16a34a','#dcfce7','rgba(22,163,74,.35)','Sesiones completadas'],
            ['Pendientes',$stats['pendientes'],'#f59e0b','#fef3c7','rgba(245,158,11,.4)','Por realizar'],
            ['No asistió',$stats['no_asistio'],'#dc2626','#fee2e2','rgba(220,38,38,.35)','Ausencias registradas'],
        ] as [$label,$value,$color,$softColor,$borderColor,$subtitle])
            <div class="col-6 col-xl-3">
                <div class="acom-kpi-card h-100 p-3" style="border-color:{{ $borderColor }};">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="acom-kpi-label">{{ $label }}</span>
                        <span class="acom-kpi-icon" style="background:{{ $softColor }};color:{{ $color }};">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                    </div>
                    <div class="acom-kpi-value" style="color:{{ $color }};">{{ $value }}</div>
                    <div class="acom-kpi-subtitle">{{ $subtitle }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('controlecp.instructor.acompaniamiento') }}" class="acom-filters mb-4">
        <input type="hidden" name="mes" value="{{ $month->format('Y-m') }}">
        <div class="acom-filter-grid">
            <div class="acom-filter-field">
                <label for="filtroFechaAcompanamiento">Fecha</label>
                <input class="form-control" id="filtroFechaAcompanamiento" type="date" name="fecha" value="{{ request('fecha') }}">
            </div>
            <div class="acom-filter-field">
                <label for="filtroAprendizAcompanamiento">Nombre del aprendiz</label>
                <input class="form-control" id="filtroAprendizAcompanamiento" type="search" name="aprendiz" value="{{ request('aprendiz') }}" placeholder="Buscar aprendiz">
            </div>
            <div class="acom-filter-field">
                <label for="filtroEstadoAcompanamiento">Estado de sesión</label>
                <select class="form-select" id="filtroEstadoAcompanamiento" name="estado_color">
                    <option value="">Todos los estados</option>
                    <option value="verde" @selected(request('estado_color') === 'verde')>Verde · Realizada</option>
                    <option value="amarillo" @selected(request('estado_color') === 'amarillo')>Amarillo · Próxima</option>
                    <option value="rojo" @selected(request('estado_color') === 'rojo')>Rojo · Atrasada o no asistió</option>
                </select>
            </div>
            <div class="acom-filter-actions">
                <button class="btn acom-filter-submit" type="submit"><i class="fas fa-magnifying-glass me-1"></i>Buscar</button>
                <a class="acom-filter-reset" href="{{ route('controlecp.instructor.acompaniamiento', ['mes' => $month->format('Y-m')]) }}" title="Limpiar filtros" aria-label="Limpiar filtros"><i class="fas fa-rotate-left"></i></a>
            </div>
        </div>
    </form>

    <ul class="nav acom-tabs mb-3" role="tablist" aria-label="Secciones de acompañamiento">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-calendario" data-bs-toggle="tab" data-bs-target="#panel-calendario" type="button" role="tab" aria-controls="panel-calendario" aria-selected="true">Calendario de sesiones</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-mis-acompanamientos" data-bs-toggle="tab" data-bs-target="#panel-mis-acompanamientos" type="button" role="tab" aria-controls="panel-mis-acompanamientos" aria-selected="false">Mis acompañamientos</button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active show" id="panel-calendario" role="tabpanel" aria-labelledby="tab-calendario" tabindex="0">
    <section class="card acom-card mb-4" id="calendario">
        <div class="card-header bg-white border-0 p-3 px-4 d-flex justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-3">
                <a class="btn btn-sm btn-outline-secondary rounded-circle" style="width:34px;height:34px" href="{{ route('controlecp.instructor.acompaniamiento', array_merge(request()->query(), ['mes' => $month->copy()->subMonth()->format('Y-m')])) }}#calendario" aria-label="Mes anterior"><i class="fas fa-chevron-left"></i></a>
                <h2 class="h6 fw-bold mb-0 text-center" style="min-width:150px">{{ $month->format('F Y') }}</h2>
                <a class="btn btn-sm btn-outline-secondary rounded-circle" style="width:34px;height:34px" href="{{ route('controlecp.instructor.acompaniamiento', array_merge(request()->query(), ['mes' => $month->copy()->addMonth()->format('Y-m')])) }}#calendario" aria-label="Mes siguiente"><i class="fas fa-chevron-right"></i></a>
            </div>
            <a class="btn btn-sm acom-green-button rounded-pill px-3" href="{{ route('controlecp.instructor.acompaniamiento', array_merge(request()->query(), ['mes' => now()->format('Y-m')])) }}#calendario"><i class="fas fa-calendar-day me-1"></i>Hoy</a>
        </div>
        <div class="card-body p-3 pt-0">
            <div class="acom-calendar">
                @foreach(['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $weekday)<div class="acom-calendar-heading">{{ $weekday }}</div>@endforeach
                @foreach($days as $day)
                    <div class="acom-calendar-day {{ !$day['in_month'] ? 'is-outside' : '' }} {{ $day['date']->isToday() ? 'is-today' : '' }}" data-calendario-fecha="{{ $day['date']->toDateString() }}">
                        <div class="acom-calendar-day-top">
                            <span class="acom-calendar-day-number">{{ $day['date']->day }}</span>
                            @if($day['date']->isToday())<span class="acom-calendar-today-label">Hoy</span>@endif
                        </div>
                        @foreach($day['session_groups'] as $sessionColor => $sessions)
                            <button class="acom-calendar-indicator sem-{{ $sessionColor }} border-0 text-start acom-calendar-event" type="button" data-bs-toggle="modal" data-bs-target="#detalleSesiones{{ $day['date']->format('Ymd') }}{{ $sessionColor }}">
                                <span class="acom-calendar-dot"></span>
                                {{ $sessions->count() }} {{ $sessions->count() === 1 ? 'sesión' : 'sesiones' }}
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </div>
            @foreach($days as $day)
                @foreach($day['session_groups'] as $sessionColor => $sessions)
                    <div class="modal fade" id="detalleSesiones{{ $day['date']->format('Ymd') }}{{ $sessionColor }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <h2 class="modal-title h5 fw-bold">Sesiones del {{ $day['date']->format('d/m/Y') }}</h2>
                                        <span class="badge rounded-pill bg-{{ $sessionColor === 'verde' ? 'success' : ($sessionColor === 'amarillo' ? 'warning text-dark' : ($sessionColor === 'rojo' ? 'danger' : 'secondary')) }}">{{ $sessions->count() }} {{ $sessions->count() === 1 ? 'sesión' : 'sesiones' }}</span>
                                    </div>
                                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    @foreach($sessions as $session)
                                        <article class="border rounded-3 p-3 {{ !$loop->first ? 'mt-3' : '' }}">
                                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                                <div>
                                                    <h3 class="h6 fw-bold mb-1">Sesión #{{ $session->numero }} · {{ $session->acompanamiento?->aprendiz?->person?->full_name ?? $session->acompanamiento?->aprendiz?->nickname ?? 'Aprendiz' }}</h3>
                                                    <div class="small text-muted">{{ $session->fecha?->format('d/m/Y') }} · {{ $formatSessionTime($session->hora_inicio) }}–{{ $formatSessionTime($session->hora_fin) }}</div>
                                                </div>
                                                <span class="badge text-bg-{{ $sessionColor === 'verde' ? 'success' : ($sessionColor === 'amarillo' ? 'warning' : ($sessionColor === 'rojo' ? 'danger' : 'secondary')) }}">{{ $session->estado }}</span>
                                            </div>
                                            @if($session->motivo)
                                                <p class="small mt-3 mb-0"><strong>Motivo:</strong> {{ $session->motivo }}</p>
                                            @endif
                                            @if($session->observaciones || $session->avances || $session->compromisos)
                                                <div class="small mt-3">
                                                    @if($session->observaciones)<p class="mb-2"><strong>Observaciones:</strong> {{ $session->observaciones }}</p>@endif
                                                    @if($session->avances)<p class="mb-2"><strong>Avances:</strong> {{ $session->avances }}</p>@endif
                                                    @if($session->compromisos)<p class="mb-0"><strong>Compromisos:</strong> {{ $session->compromisos }}</p>@endif
                                                </div>
                                            @endif
                                            @if($session->historial->isNotEmpty())
                                                <details class="small mt-3">
                                                    <summary class="fw-semibold">Información anterior y cambios</summary>
                                                    @foreach($session->historial->sortByDesc('created_at') as $event)
                                                        <div class="border-top mt-2 pt-2">
                                                            <strong>{{ $event->accion }}</strong> · {{ $event->created_at?->copy()->timezone('America/Bogota')->format('d/m/Y g:i A') ?? '—' }}
                                                            @if($event->motivo)<div><strong>Motivo del cambio:</strong> {{ $event->motivo }}</div>@endif
                                                            @foreach(['antes' => 'Antes del cambio', 'despues' => 'Después del cambio'] as $snapshotKey => $snapshotLabel)
                                                                @php
                                                                    $snapshot = $event->datos[$snapshotKey] ?? [];
                                                                @endphp
                                                                @if($snapshot)
                                                                    <div class="mt-1"><strong>{{ $snapshotLabel }}:</strong>
                                                                        {{ $snapshot['fecha'] ?? '—' }} · {{ isset($snapshot['hora_inicio']) ? $formatSessionTime($snapshot['hora_inicio']) : '—' }}–{{ isset($snapshot['hora_fin']) ? $formatSessionTime($snapshot['hora_fin']) : '—' }} · {{ $snapshot['estado'] ?? '—' }}
                                                                    </div>
                                                                    @foreach(['observaciones' => 'Observaciones', 'avances' => 'Avances', 'compromisos' => 'Compromisos', 'motivo' => 'Motivo'] as $field => $label)
                                                                        @if(!empty($snapshot[$field]))<div><strong>{{ $label }}:</strong> {{ $snapshot[$field] }}</div>@endif
                                                                    @endforeach
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                </details>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </section>
        </div>

        <div class="tab-pane" id="panel-mis-acompanamientos" role="tabpanel" aria-labelledby="tab-mis-acompanamientos" tabindex="0">
    <section class="card acom-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr><th>Nombre aprendiz</th><th>Categoría</th><th>Fecha de inicio</th><th>Progreso</th><th>Estado</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
            @forelse($acompanamientos as $acompanamiento)
                @php
                    $learnerPerson = $acompanamiento->aprendiz?->person;
                    $doneCount = $acompanamiento->sesiones->where('estado','Realizada')->count();
                    $noShowCount = $acompanamiento->sesiones->where('estado','No asistio')->count();
                    $pendingCount = $acompanamiento->sesiones->where('estado','Programada')->count();
                    $progress = $acompanamiento->sesiones_planeadas ? (int) round($doneCount * 100 / $acompanamiento->sesiones_planeadas) : 0;
                    $sesionesFiltradas = request()->filled('fecha') || request()->filled('estado_color');
                @endphp
                <tr id="acompanamiento{{ $acompanamiento->id }}">
                    <td class="fw-semibold">{{ $learnerPerson?->full_name ?? $acompanamiento->aprendiz?->nickname ?? 'Aprendiz' }}</td>
                    <td>{{ $acompanamiento->categoria }}</td>
                    <td>{{ $acompanamiento->fecha_inicio?->format('d/m/Y') }}</td>
                    <td style="min-width:150px">
                        <div class="progress" style="height:7px"><div class="progress-bar bg-success" style="width:{{ min(100,$progress) }}%"></div></div>
                        <span class="small text-muted">{{ min(100,$progress) }}% · {{ $doneCount }}/{{ $acompanamiento->sesiones_planeadas }}</span>
                    </td>
                    <td><span class="acom-status-badge {{ $acompanamiento->estado === 'Activo' ? '' : 'acom-status-badge--inactive' }}"><i class="fas fa-circle-check" aria-hidden="true"></i>{{ $acompanamiento->estado }}</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn acom-action-button" type="button" title="Ver acompañamiento" aria-label="Ver acompañamiento" data-bs-toggle="modal" data-bs-target="#verAcompanamiento{{ $acompanamiento->id }}"><i class="fas fa-eye" aria-hidden="true"></i></button>
                            <button class="btn acom-action-button acom-action-button--sessions" type="button" title="Sesiones" aria-label="Sesiones" data-bs-toggle="modal" data-bs-target="#sesionesAcompanamiento{{ $acompanamiento->id }}"><i class="fas fa-users" aria-hidden="true"></i></button>
                            <button class="btn acom-action-button acom-action-button--pink" type="button" title="Detalle de sesiones registradas" aria-label="Detalle de sesiones registradas" data-bs-toggle="modal" data-bs-target="#detalleSesionesRegistradas{{ $acompanamiento->id }}"><i class="fas fa-clipboard-check" aria-hidden="true"></i></button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-5">No tienes acompañamientos registrados.</td></tr>
            @endforelse
                    </tbody>
                </table>
            </div>
            @if ($acompanamientos->hasPages())
                <nav aria-label="Paginación de acompañamientos">
                    <ul class="pagination justify-content-center">
                        @if ($acompanamientos->onFirstPage())
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&lsaquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $acompanamientos->previousPageUrl() }}" aria-label="Página anterior">&lsaquo;</a></li>
                        @endif
                        @php
                                $ultimaPaginaMostrada = 0;
                            @endphp
                        @for ($pagina = 1; $pagina <= $acompanamientos->lastPage(); $pagina++)
                            @if ($pagina === 1 || $pagina === $acompanamientos->lastPage() || abs($pagina - $acompanamientos->currentPage()) <= 1)
                                @if ($ultimaPaginaMostrada > 0 && $pagina > $ultimaPaginaMostrada + 1)
                                    <li class="page-item disabled"><span class="page-link">…</span></li>
                                @endif
                                <li class="page-item {{ $pagina === $acompanamientos->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $acompanamientos->url($pagina) }}" @if($pagina === $acompanamientos->currentPage()) aria-current="page" @endif>{{ $pagina }}</a>
                                </li>
                                @php
                                    $ultimaPaginaMostrada = $pagina;
                                @endphp
                            @endif
                        @endfor
                        @if ($acompanamientos->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $acompanamientos->nextPageUrl() }}" aria-label="Página siguiente">&rsaquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&rsaquo;</span></li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    </section>
            @foreach($acompanamientos as $acompanamiento)
                @php
                    $learnerPerson = $acompanamiento->aprendiz?->person;
                    $learnerFicha = $learnerPerson ? $aprendicesPorPersona->get($learnerPerson->id) : null;
                    $doneCount = $acompanamiento->sesiones->where('estado','Realizada')->count();
                    $noShowCount = $acompanamiento->sesiones->where('estado','No asistio')->count();
                    $pendingCount = $acompanamiento->sesiones->where('estado','Programada')->count();
                    $progress = $acompanamiento->sesiones_planeadas ? (int) round($doneCount * 100 / $acompanamiento->sesiones_planeadas) : 0;
                    $sesionesFiltradas = request()->filled('fecha') || request()->filled('estado_color');
                @endphp
                <div class="modal fade" id="verAcompanamiento{{ $acompanamiento->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered"><div class="modal-content acom-detail-modal">
                        <div class="modal-header"><h2 class="modal-title h5">Acompañamiento</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
                        <div class="modal-body">
                            <h3 class="acom-detail-name">{{ $learnerPerson?->full_name ?? $acompanamiento->aprendiz?->nickname ?? 'Aprendiz' }}</h3>
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6"><div class="acom-detail-item"><span class="acom-detail-label">Categoría</span><div class="acom-detail-value">{{ $acompanamiento->categoria }}</div></div></div>
                                <div class="col-sm-6"><div class="acom-detail-item"><span class="acom-detail-label">Fecha de inicio</span><div class="acom-detail-value">{{ $acompanamiento->fecha_inicio?->format('d/m/Y') ?? '—' }}</div></div></div>
                                <div class="col-sm-6"><div class="acom-detail-item"><span class="acom-detail-label">Estado</span><span class="acom-status-badge {{ $acompanamiento->estado === 'Activo' ? '' : 'acom-status-badge--inactive' }}"><i class="fas fa-circle-check" aria-hidden="true"></i>{{ $acompanamiento->estado }}</span></div></div>
                                <div class="col-sm-6"><div class="acom-detail-item"><span class="acom-detail-label">Progreso</span><div class="acom-detail-value">{{ min(100,$progress) }}% <span class="small text-muted">({{ $doneCount }}/{{ $acompanamiento->sesiones_planeadas }} sesiones)</span></div><div class="progress acom-detail-progress"><div class="progress-bar bg-success" style="width:{{ min(100,$progress) }}%"></div></div></div></div>
                            </div>
                            <div class="acom-detail-motive"><span class="acom-detail-label">Motivo del acompañamiento</span><p class="acom-detail-motive-text">{{ $acompanamiento->motivo }}</p></div>
                            @if($acompanamiento->resumen_final)
                                <div class="acom-detail-motive mt-3"><span class="acom-detail-label">Resumen final</span><p class="acom-detail-motive-text">{{ $acompanamiento->resumen_final }}</p><div class="small mt-2"><strong>Objetivo:</strong> {{ $acompanamiento->objetivo_cumplido ? 'Cumplido' : 'No cumplido' }}</div></div>
                            @endif
                        </div>
                    </div></div>
                </div>
                <div class="modal fade" id="detalleSesionesRegistradas{{ $acompanamiento->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable acom-registered-sessions-dialog"><div class="modal-content acom-session-detail">
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title h5 fw-bold mb-1">Sesiones registradas</h2>
                                <div class="small opacity-75">{{ $learnerPerson?->full_name ?? $acompanamiento->aprendiz?->nickname ?? 'Aprendiz' }}</div>
                            </div>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            @php
                                $completedSessions = $acompanamiento->sesiones->where('estado', 'Realizada');
                            @endphp
                            @forelse($completedSessions as $completedSession)
                                <section class="acom-session-detail-card mb-3">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                        <h3 class="h6 fw-bold mb-0">Sesión #{{ $completedSession->numero }}</h3>
                                        <span class="small text-muted">{{ $completedSession->fecha?->format('d/m/Y') }} · {{ $formatSessionTime($completedSession->hora_inicio) }}–{{ $formatSessionTime($completedSession->hora_fin) }}</span>
                                    </div>
                                    @if($completedSession->registrada_en)
                                        <p class="small text-muted"><i class="fas fa-clock me-1" aria-hidden="true"></i>Registrada el {{ $completedSession->registrada_en->copy()->timezone('America/Bogota')->format('d/m/Y g:i a') }}</p>
                                    @endif
                                    <div class="row g-2">
                                        <div class="col-md-4"><span class="acom-session-detail-label">Observaciones</span><p class="acom-session-detail-text">{{ $completedSession->observaciones ?: 'Sin observaciones registradas.' }}</p></div>
                                        <div class="col-md-4"><span class="acom-session-detail-label">Avances</span><p class="acom-session-detail-text">{{ $completedSession->avances ?: 'Sin avances registrados.' }}</p></div>
                                        <div class="col-md-4"><span class="acom-session-detail-label">Compromisos</span><p class="acom-session-detail-text">{{ $completedSession->compromisos ?: 'Sin compromisos registrados.' }}</p></div>
                                    </div>
                                    @if($completedSession->evidencias->isNotEmpty())
                                        <div class="border-top mt-3 pt-3">
                                            <span class="acom-session-detail-label">Evidencias</span>
                                            <div class="row g-2">
                                                @foreach($completedSession->evidencias as $evidencia)
                                                    <div class="col-sm-6">
                                                        @if($evidencia->tipo === 'foto')
                                                            <a href="{{ $evidencia->url }}" target="_blank" rel="noopener noreferrer"><img class="acom-session-evidence" src="{{ $evidencia->url }}" alt="Evidencia sesión #{{ $completedSession->numero }}"></a>
                                                        @else
                                                            <a href="{{ $evidencia->enlace }}" target="_blank" rel="noopener noreferrer"><i class="fas fa-video me-1" aria-hidden="true"></i>Ver video</a>
                                                        @endif
                                                        @if($evidencia->descripcion)<p class="small text-muted mt-2 mb-0">{{ $evidencia->descripcion }}</p>@endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </section>
                            @empty
                                <div class="text-center text-muted py-4"><i class="far fa-clipboard fa-2x mb-2" aria-hidden="true"></i><p class="mb-0">Aún no hay sesiones finalizadas ni registros para mostrar.</p></div>
                            @endforelse
                        </div>
                    </div></div>
                </div>
                <div class="modal fade" id="sesionesAcompanamiento{{ $acompanamiento->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable"><div class="modal-content acom-sessions-modal">
                        <div class="modal-header">
                            <h2 class="modal-title h5 fw-bold"><span class="acom-sessions-title-icon"><i class="fas fa-calendar-check" aria-hidden="true"></i></span>Sesiones</h2>
                            <div class="d-flex align-items-center gap-2 ms-auto">
                                @if($acompanamiento->estado === 'Activo')
                                    <button class="btn btn-sm btn-outline-primary rounded-pill" type="button" data-bs-toggle="modal" data-bs-target="#agregarSesiones{{ $acompanamiento->id }}">Agregar sesiones</button>
                                    <button class="btn btn-sm btn-outline-success rounded-pill" type="button" data-bs-toggle="modal" data-bs-target="#cerrarAcompanamiento{{ $acompanamiento->id }}">Cerrar acompañamiento</button>
                                @endif
                                <button class="btn-close ms-1" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                        </div>
                        <div class="modal-body">
                            @if($acompanamiento->estado !== 'Activo' && $acompanamiento->resumen_final)
                                <div class="alert alert-light border"><strong>Resumen final:</strong> {{ $acompanamiento->resumen_final }} <span class="ms-2">Objetivo {{ $acompanamiento->objetivo_cumplido ? 'cumplido' : 'no cumplido' }}.</span></div>
                            @endif
                            <div class="table-responsive acom-sessions-table-wrap">
                                    <table class="table table-sm table-hover align-middle">
                                        <thead class="table-light"><tr><th>Sesión</th><th>Fecha y hora</th><th>Estado</th><th>Acciones</th></tr></thead>
                                        <tbody>
                                        @foreach($acompanamiento->sesiones_visibles as $session)
                                            @php
                                                $sessionColor = $session->estado === 'Realizada'
                                                    ? 'success'
                                                    : ($session->estado === 'Programada' && $session->fecha->lt(today())
                                                        ? 'danger'
                                                        : ($session->estado === 'Programada' ? 'warning' : 'secondary'));
                                            @endphp
                                            <tr id="sesion{{ $session->id }}">
                                                <td>#{{ $session->numero }}</td>
                                                <td>{{ $session->fecha?->format('d/m/Y') }}<div class="small text-muted">{{ $formatSessionTime($session->hora_inicio) }}–{{ $formatSessionTime($session->hora_fin) }}</div></td>
                                                <td>
                                                    @if($session->estado === 'Realizada')
                                                        <span class="acom-status-badge"><i class="fas fa-circle-check" aria-hidden="true"></i>Finalizada</span>
                                                    @elseif($sessionColor === 'warning')
                                                        <span class="acom-session-status--scheduled">{{ $session->estado }}</span>
                                                    @else
                                                        <span class="badge text-bg-{{ $sessionColor }}">{{ $session->estado }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                                    @if($acompanamiento->estado === 'Activo' && $session->estado === 'Programada')
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @if(!$session->fecha->isFuture())
                                                                <button class="btn acom-action-button acom-action-button--green" type="button" title="Registrar sesión" aria-label="Registrar sesión" data-bs-toggle="modal" data-bs-target="#registrarSesion{{ $session->id }}"><i class="fas fa-check" aria-hidden="true"></i></button>
                                                                <button class="btn acom-action-button acom-action-button--red" type="button" title="No asistió" aria-label="No asistió" data-bs-toggle="modal" data-bs-target="#noAsistio{{ $session->id }}"><i class="fas fa-user-xmark" aria-hidden="true"></i></button>
                                                            @endif
                                                            <button class="btn acom-action-button" type="button" title="Reprogramar sesión" aria-label="Reprogramar sesión" data-bs-toggle="modal" data-bs-target="#reprogramarSesion{{ $session->id }}"><i class="fas fa-calendar-days" aria-hidden="true"></i></button>
                                                            <button class="btn acom-action-button acom-action-button--muted" type="button" title="Cancelar sesión" aria-label="Cancelar sesión" data-bs-toggle="modal" data-bs-target="#cancelarSesion{{ $session->id }}"><i class="fas fa-ban" aria-hidden="true"></i></button>
                                                            <button class="btn acom-action-button acom-action-button--yellow" type="button" title="Agregar evidencia" aria-label="Agregar evidencia" data-bs-toggle="modal" data-bs-target="#agregarEvidenciaSesion{{ $session->id }}"><i class="fas fa-cloud-arrow-up" aria-hidden="true"></i></button>
                                                        </div>
                                                    @elseif($session->estado === 'Realizada')
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <button class="btn acom-action-button acom-action-button--pink" type="button" title="Ver detalle de la sesión" aria-label="Ver detalle de la sesión" data-bs-toggle="modal" data-bs-target="#verSesionRealizada{{ $session->id }}"><i class="fas fa-eye" aria-hidden="true"></i></button>
                                                            @if($acompanamiento->estado === 'Activo')
                                                                <button class="btn acom-action-button" type="button" title="Editar registro" aria-label="Editar registro" data-bs-toggle="modal" data-bs-target="#corregirSesion{{ $session->id }}"><i class="fas fa-pen" aria-hidden="true"></i></button>
                                                                <button class="btn acom-action-button acom-action-button--yellow" type="button" title="Agregar evidencia" aria-label="Agregar evidencia" data-bs-toggle="modal" data-bs-target="#agregarEvidenciaSesion{{ $session->id }}"><i class="fas fa-cloud-arrow-up" aria-hidden="true"></i></button>
                                                            @endif
                                                        </div>
                                                    @else —
                                                    @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                            </div>
                        </div>
                    </div></div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="modal fade" id="crearCategoriaAcompanamiento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content">
            <form id="formCrearCategoriaAcompanamiento" action="{{ route('controlecp.instructor.acompaniamiento.categorias.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title h5">Crear categoría</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label" for="nombreNuevaCategoriaAcompanamiento">Nombre de la categoría *</label>
                    <input class="form-control" id="nombreNuevaCategoriaAcompanamiento" type="text" name="nombre" maxlength="100" required>
                    <div class="small text-danger mt-1" id="errorCategoriaAcompanamiento" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary rounded-pill" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn acom-green-button rounded-pill" id="guardarCategoriaAcompanamiento" type="submit">Guardar categoría</button>
                </div>
            </form>
        </div></div>
    </div>

    <div class="modal fade" id="nuevoAcompanamiento" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content">
        <form method="POST" action="{{ route('controlecp.instructor.acompaniamiento.store') }}" class="acompanamiento-horario-form">@csrf
            <div class="modal-header"><h2 class="modal-title h5">Nuevo acompañamiento</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Aprendiz *</label><select class="form-select" name="aprendiz_id" required><option value="">Selecciona un aprendiz</option>@foreach($aprendices as $learner)<option value="{{ $learner->id }}">{{ $learner->person?->full_name ?? $learner->nickname }} · {{ $learner->person?->document_number ?? $learner->email }}</option>@endforeach</select></div>
                    <div class="col-md-6">
                        <label class="form-label" for="categoriaAcompanamiento">Categoría *</label>
                        <select class="form-select" id="categoriaAcompanamiento" name="categoria" required>
                            <option value="">Selecciona una categoría</option>
                            @foreach(['Psicológica', 'Espiritual', 'Pedagógica'] as $defaultCategory)
                                <option value="{{ $defaultCategory }}">{{ $defaultCategory }}</option>
                            @endforeach
                            @foreach($categoriasAcompanamiento as $category)
                                @if(!in_array(mb_strtolower($category->nombre), ['psicológica', 'espiritual', 'pedagógica'], true))
                                    <option value="{{ $category->nombre }}">{{ $category->nombre }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Fecha de inicio *</label><input class="form-control" type="date" name="fecha_inicio" min="{{ today()->toDateString() }}" required></div>
                    <div class="col-md-6"><label class="form-label">Hora de inicio *</label><input class="form-control" type="time" name="hora_inicio" value="08:00" min="08:00" max="14:00" step="60" required></div>
                    <div class="col-md-6"><label class="form-label">Duración del acompañamiento (horas) *</label><select class="form-select" name="duracion_horas" required>@for($hours = 1; $hours <= 12; $hours++)<option value="{{ $hours }}" @selected($hours === 1)>{{ $hours }} {{ $hours === 1 ? 'hora' : 'horas' }}</option>@endfor</select></div>
                    <div class="col-md-6"><label class="form-label">Cada cuántos días *</label><input class="form-control" type="number" name="intervalo_dias" min="1" max="365" value="7" required></div>
                    <div class="col-md-6"><label class="form-label">Número de sesiones *</label><input class="form-control" type="number" name="sesiones_planeadas" min="1" max="100" value="6" required></div>
                    <div class="col-12"><label class="form-label">Motivo del acompañamiento *</label><textarea class="form-control" name="motivo" rows="3" minlength="5" maxlength="3000" required></textarea></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-outline-secondary rounded-pill" type="button" data-bs-dismiss="modal">Cancelar</button><button class="btn acom-green-button rounded-pill">Guardar acompañamiento</button></div>
        </form>
    </div></div></div>

    @foreach($acompanamientos as $acompanamiento)
        @foreach($acompanamiento->sesiones as $session)
            @if($acompanamiento->estado === 'Activo')
                <div class="modal fade" id="agregarEvidenciaSesion{{ $session->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered"><div class="modal-content acom-upload-modal">
                        <form method="POST" action="{{ route('controlecp.instructor.acompaniamiento.sesiones.evidencias.store', $session->id) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header">
                                <div>
                                    <h2 class="acom-upload-title"><i class="fas fa-upload me-2 acom-upload-icon" aria-hidden="true"></i>Subir evidencia</h2>
                                    <p class="acom-upload-subtitle">{{ $acompanamiento->aprendiz?->person?->full_name ?? $acompanamiento->aprendiz?->nickname ?? 'Aprendiz' }} · Sesión #{{ $session->numero }}</p>
                                </div>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3 mb-4">
                                    <div class="col-sm-6">
                                        <label class="acom-upload-type-card" for="tipoFotoSesion{{ $session->id }}">
                                            <input class="acom-upload-type" id="tipoFotoSesion{{ $session->id }}" type="radio" name="tipo" value="foto" checked required>
                                            <span class="acom-upload-type-content"><i class="fas fa-image acom-upload-type-icon" aria-hidden="true"></i><span class="acom-upload-type-copy"><strong>Foto</strong><span>JPG o PNG, máx. 5 MB</span></span></span>
                                        </label>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="acom-upload-type-card" for="tipoVideoSesion{{ $session->id }}">
                                            <input class="acom-upload-type" id="tipoVideoSesion{{ $session->id }}" type="radio" name="tipo" value="video">
                                            <span class="acom-upload-type-content"><i class="fas fa-video text-info" aria-hidden="true"></i><span class="acom-upload-type-copy"><strong>Enlace de video</strong><span>YouTube, Drive, etc.</span></span></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3 acom-evidence-photo"><label class="form-label acom-upload-field-label" for="archivoEvidenciaSesion{{ $session->id }}">Archivo de foto (JPG/PNG) <span class="text-danger">*</span></label><input class="form-control" id="archivoEvidenciaSesion{{ $session->id }}" type="file" name="archivo" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required></div>
                                <div class="mb-3 acom-evidence-video d-none"><label class="form-label acom-upload-field-label" for="enlaceEvidenciaSesion{{ $session->id }}">Enlace de video <span class="text-danger">*</span></label><input class="form-control" id="enlaceEvidenciaSesion{{ $session->id }}" type="url" name="enlace" maxlength="500"></div>
                                <div><label class="form-label acom-upload-field-label" for="descripcionEvidenciaSesion{{ $session->id }}">Descripción (opcional)</label><textarea class="form-control" id="descripcionEvidenciaSesion{{ $session->id }}" name="descripcion" rows="2" maxlength="1000" placeholder="Describe brevemente esta evidencia..."></textarea></div>
                            </div>
                            <div class="modal-footer border-top-0 pt-0 px-4 pb-4"><button class="btn btn-outline-secondary rounded-pill px-4" type="button" data-bs-dismiss="modal">Cancelar</button><button class="btn acom-upload-submit rounded-pill px-4"><i class="fas fa-upload me-1" aria-hidden="true"></i>Subir evidencia</button></div>
                        </form>
                    </div></div>
                </div>
            @endif
            @if($acompanamiento->estado === 'Activo' && $session->estado === 'Programada')
                @if(!$session->fecha->isFuture())
                    <div class="modal fade" id="registrarSesion{{ $session->id }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST" action="{{ route('controlecp.instructor.acompaniamiento.sesiones.registrar',$session->id) }}">@csrf
                        <div class="modal-header"><h2 class="modal-title h5">Registrar sesión #{{ $session->numero }}</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body"><p class="small text-muted">Aprendiz: {{ $acompanamiento->aprendiz?->person?->full_name }} · {{ $session->fecha?->format('d/m/Y') }} {{ $formatSessionTime($session->hora_inicio) }}</p>
                            <div class="mb-3"><label class="form-label">Observaciones *</label><textarea class="form-control" name="observaciones" rows="2" required minlength="3" maxlength="5000"></textarea></div>
                            <div class="mb-3"><label class="form-label">Avances *</label><textarea class="form-control" name="avances" rows="2" required minlength="3" maxlength="5000"></textarea></div>
                            <div><label class="form-label">Compromisos *</label><textarea class="form-control" name="compromisos" rows="2" required minlength="3" maxlength="5000"></textarea></div>
                        </div><div class="modal-footer"><button class="btn btn-success">Guardar registro</button></div>
                    </form></div></div></div>
                    <div class="modal fade" id="noAsistio{{ $session->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="{{ route('controlecp.instructor.acompaniamiento.sesiones.no-asistio',$session->id) }}">@csrf
                        <div class="modal-header"><h2 class="modal-title h5">Marcar “No asistió”</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body"><label class="form-label">Motivo *</label><textarea class="form-control" name="motivo" required minlength="3" maxlength="1000"></textarea></div>
                        <div class="modal-footer"><button class="btn btn-danger">Confirmar inasistencia</button></div>
                    </form></div></div></div>
                @endif
                <div class="modal fade" id="reprogramarSesion{{ $session->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="{{ route('controlecp.instructor.acompaniamiento.sesiones.reprogramar',$session->id) }}" class="acompanamiento-horario-form">@csrf @method('PUT')
                    <div class="modal-header"><h2 class="modal-title h5">Reprogramar sesión #{{ $session->numero }}</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                    @php
                        $startMinutes = ((int) substr($session->hora_inicio, 0, 2) * 60) + (int) substr($session->hora_inicio, 3, 2);
                        $endMinutes = ((int) substr($session->hora_fin, 0, 2) * 60) + (int) substr($session->hora_fin, 3, 2);
                        $sessionDurationHours = max(1, intdiv($endMinutes - $startMinutes, 60));
                    @endphp
                    <div class="modal-body"><div class="mb-3"><label class="form-label">Nueva fecha</label><input class="form-control" type="date" name="fecha" min="{{ today()->toDateString() }}" value="{{ $session->fecha?->toDateString() }}" required></div><div class="mb-3"><label class="form-label">Hora de inicio</label><input class="form-control" type="time" name="hora_inicio" value="{{ substr($session->hora_inicio,0,5) }}" min="08:00" max="14:00" step="60" required></div><div class="mb-3"><label class="form-label">Duración (horas)</label><select class="form-select" name="duracion_horas" required>@for($hours = 1; $hours <= 12; $hours++)<option value="{{ $hours }}" @selected($hours === $sessionDurationHours)>{{ $hours }} {{ $hours === 1 ? 'hora' : 'horas' }}</option>@endfor</select></div><div><label class="form-label">Motivo *</label><textarea class="form-control" name="motivo" required minlength="3" maxlength="1000"></textarea></div></div>
                    <div class="modal-footer"><button class="btn btn-primary">Guardar cambio</button></div>
                </form></div></div></div>
                <div class="modal fade" id="cancelarSesion{{ $session->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="{{ route('controlecp.instructor.acompaniamiento.sesiones.cancelar',$session->id) }}">@csrf @method('PUT')
                    <div class="modal-header"><h2 class="modal-title h5">Cancelar sesión #{{ $session->numero }}</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body"><label class="form-label">Motivo *</label><textarea class="form-control" name="motivo" required minlength="3" maxlength="1000"></textarea></div><div class="modal-footer"><button class="btn btn-secondary">Confirmar cancelación</button></div>
                </form></div></div></div>
            @elseif($session->estado === 'Realizada')
                <div class="modal fade" id="verSesionRealizada{{ $session->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"><div class="modal-content acom-session-detail">
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title h5 fw-bold mb-1">Sesión #{{ $session->numero }} finalizada</h2>
                                <div class="small opacity-75">{{ $acompanamiento->aprendiz?->person?->full_name ?? $acompanamiento->aprendiz?->nickname ?? 'Aprendiz' }}</div>
                            </div>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                <span class="acom-status-badge"><i class="fas fa-circle-check" aria-hidden="true"></i>Sesión finalizada</span>
                                <span class="small text-muted"><i class="fas fa-calendar-days me-1" aria-hidden="true"></i>{{ $session->fecha?->format('d/m/Y') }} · {{ $formatSessionTime($session->hora_inicio) }}–{{ $formatSessionTime($session->hora_fin) }}</span>
                                @if($session->registrada_en)
                                    <span class="small text-muted"><i class="fas fa-clock me-1" aria-hidden="true"></i>Registrada el {{ $session->registrada_en->copy()->timezone('America/Bogota')->format('d/m/Y g:i a') }}</span>
                                @endif
                            </div>
                            <div class="row g-3">
                                <div class="col-12"><div class="acom-session-detail-card"><span class="acom-session-detail-label">Observaciones</span><p class="acom-session-detail-text">{{ $session->observaciones ?: 'Sin observaciones registradas.' }}</p></div></div>
                                <div class="col-md-6"><div class="acom-session-detail-card"><span class="acom-session-detail-label">Avances</span><p class="acom-session-detail-text">{{ $session->avances ?: 'Sin avances registrados.' }}</p></div></div>
                                <div class="col-md-6"><div class="acom-session-detail-card"><span class="acom-session-detail-label">Compromisos</span><p class="acom-session-detail-text">{{ $session->compromisos ?: 'Sin compromisos registrados.' }}</p></div></div>
                                @php
                                    $sessionCorrections = $acompanamiento->historial
                                        ->where('sesion_id', $session->id)
                                        ->where('accion', 'Registro corregido');
                                @endphp
                                @if($sessionCorrections->isNotEmpty())
                                    <div class="col-12">
                                        <div class="acom-session-detail-card">
                                            <span class="acom-session-detail-label">Historial de correcciones</span>
                                            @foreach($sessionCorrections as $correction)
                                                <div class="border-top pt-3 mt-3">
                                                    <div class="small fw-semibold text-dark">{{ $correction->created_at?->copy()->timezone('America/Bogota')->format('d/m/Y g:i a') ?? '—' }} · {{ $correction->usuario?->nickname ?? $correction->usuario?->full_name ?? 'Instructor' }}</div>
                                                    <p class="small text-muted mb-2">Motivo: {{ $correction->motivo }}</p>
                                                    @foreach(['observaciones' => 'Observaciones', 'avances' => 'Avances', 'compromisos' => 'Compromisos'] as $field => $label)
                                                        @if(isset($correction->datos['antes'][$field]) || isset($correction->datos['despues'][$field]))
                                                            <div class="small mb-2"><strong>{{ $label }}:</strong> {{ $correction->datos['antes'][$field] ?? '—' }} <i class="fas fa-arrow-right mx-1 text-muted" aria-hidden="true"></i> {{ $correction->datos['despues'][$field] ?? '—' }}</div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if($session->evidencias->isNotEmpty())
                                    <div class="col-12">
                                        <div class="acom-session-detail-card">
                                            <span class="acom-session-detail-label">Evidencias</span>
                                            <div class="row g-3">
                                                @foreach($session->evidencias as $evidencia)
                                                    <div class="col-sm-6">
                                                        @if($evidencia->tipo === 'foto')
                                                            <a href="{{ $evidencia->url }}" target="_blank" rel="noopener noreferrer"><img class="acom-session-evidence" src="{{ $evidencia->url }}" alt="Evidencia de sesión"></a>
                                                        @else
                                                            <a class="btn acom-action-button acom-action-button--yellow" href="{{ $evidencia->enlace }}" target="_blank" rel="noopener noreferrer"><i class="fas fa-video me-1" aria-hidden="true"></i>Ver video</a>
                                                        @endif
                                                        @if($evidencia->descripcion)<p class="small text-muted mt-2 mb-0">{{ $evidencia->descripcion }}</p>@endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div></div>
                </div>
            @endif
            @if($acompanamiento->estado === 'Activo' && $session->estado === 'Realizada')
                <div class="modal fade" id="corregirSesion{{ $session->id }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST" action="{{ route('controlecp.instructor.acompaniamiento.sesiones.corregir',$session->id) }}">@csrf @method('PUT')
                    <div class="modal-header"><h2 class="modal-title h5">Corregir registro de sesión #{{ $session->numero }}</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body"><p class="small text-muted">La versión actual se guardará en el historial reservado.</p><div class="mb-3"><label class="form-label">Observaciones *</label><textarea class="form-control" name="observaciones" rows="2" required minlength="3" maxlength="5000">{{ $session->observaciones }}</textarea></div><div class="mb-3"><label class="form-label">Avances *</label><textarea class="form-control" name="avances" rows="2" required minlength="3" maxlength="5000">{{ $session->avances }}</textarea></div><div class="mb-3"><label class="form-label">Compromisos *</label><textarea class="form-control" name="compromisos" rows="2" required minlength="3" maxlength="5000">{{ $session->compromisos }}</textarea></div><div><label class="form-label">Motivo de corrección *</label><textarea class="form-control" name="motivo" required minlength="3" maxlength="1000"></textarea></div></div>
                    <div class="modal-footer"><button class="btn btn-warning">Guardar corrección</button></div>
                </form></div></div></div>
            @endif
        @endforeach
        @if($acompanamiento->estado === 'Activo')
            <div class="modal fade" id="agregarSesiones{{ $acompanamiento->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered"><div class="modal-content acom-add-sessions-modal">
                    <form method="POST" action="{{ route('controlecp.instructor.acompaniamiento.sesiones.agregar',$acompanamiento->id) }}">@csrf
                        <div class="modal-header">
                            <h2 class="modal-title h5 fw-bold"><span class="acom-add-sessions-icon"><i class="fas fa-calendar-plus" aria-hidden="true"></i></span>Agregar sesiones</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="acom-add-sessions-field">
                                <label class="form-label" for="cantidadSesiones{{ $acompanamiento->id }}">Cantidad adicional</label>
                                <input class="form-control" id="cantidadSesiones{{ $acompanamiento->id }}" type="number" name="cantidad" min="1" max="100" value="1" required>
                            </div>
                            <div class="acom-add-sessions-field">
                                <label class="form-label" for="motivoSesiones{{ $acompanamiento->id }}">Motivo <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="motivoSesiones{{ $acompanamiento->id }}" name="motivo" rows="3" required minlength="3" maxlength="1000"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn acom-add-sessions-submit" type="submit"><i class="fas fa-bell me-2" aria-hidden="true"></i>Agregar y avisar al aprendiz</button>
                        </div>
                    </form>
                </div></div>
            </div>
            <div class="modal fade" id="cerrarAcompanamiento{{ $acompanamiento->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered"><div class="modal-content acom-close-modal">
                    <form method="POST" action="{{ route('controlecp.instructor.acompaniamiento.cerrar',$acompanamiento->id) }}" onsubmit="return confirm('¿Confirmas cerrar este acompañamiento? No se podrán agregar ni cambiar sesiones después.')">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h2 class="modal-title h5 fw-bold"><span class="acom-close-icon"><i class="fas fa-lock" aria-hidden="true"></i></span>Cerrar acompañamiento</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="acom-close-notice"><i class="fas fa-circle-exclamation" aria-hidden="true"></i><span>Sesiones pendientes: <strong>{{ $acompanamiento->sesiones->where('estado','Programada')->count() }}</strong>. Antes del cierre debes registrar, marcar inasistencia o cancelar todas las pendientes.</span></div>
                            <div class="acom-close-field">
                                <label class="form-label" for="resumenFinal{{ $acompanamiento->id }}">Resumen final <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="resumenFinal{{ $acompanamiento->id }}" name="resumen_final" rows="3" required minlength="10" maxlength="5000"></textarea>
                            </div>
                            <div class="acom-close-field">
                                <label class="form-label" for="objetivoCumplido{{ $acompanamiento->id }}">¿Se cumplió el objetivo? <span class="text-danger">*</span></label>
                                <select class="form-select" id="objetivoCumplido{{ $acompanamiento->id }}" name="objetivo_cumplido" required>
                                    <option value="">Selecciona</option>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn acom-close-submit" type="submit"><i class="fas fa-check me-2" aria-hidden="true"></i>Confirmar cierre</button>
                        </div>
                    </form>
                </div></div>
            </div>
        @endif
    @endforeach
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const categoryForm = document.getElementById('formCrearCategoriaAcompanamiento');
        if (!categoryForm) return;

        const categorySelect = document.getElementById('categoriaAcompanamiento');
        const errorMessage = document.getElementById('errorCategoriaAcompanamiento');
        const categoryModal = document.getElementById('crearCategoriaAcompanamiento');
        const categoryButton = categoryForm.querySelector('button[type="submit"]');

        categoryForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            errorMessage.textContent = '';
            categoryButton.disabled = true;

            try {
                const response = await fetch(categoryForm.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(categoryForm),
                });
                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.errors?.nombre?.[0] || result.message || 'No se pudo crear la categoría.');
                }

                let option = Array.from(categorySelect.options).find(
                    (item) => item.value.toLocaleLowerCase() === result.nombre.toLocaleLowerCase()
                );
                if (!option) {
                    option = new Option(result.nombre, result.nombre);
                    categorySelect.add(option);
                }
                categorySelect.value = option.value;
                categoryForm.reset();
                bootstrap.Modal.getOrCreateInstance(categoryModal).hide();
            } catch (error) {
                errorMessage.textContent = error.message || 'No se pudo crear la categoría.';
            } finally {
                categoryButton.disabled = false;
            }
        });
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('.modal.show [data-bs-toggle="modal"][data-bs-target]');
        if (!trigger) return;

        const parentModal = trigger.closest('.modal.show');
        if (!parentModal || !parentModal.id.startsWith('sesionesAcompanamiento')) return;

        const targetModal = document.querySelector(trigger.dataset.bsTarget);
        if (!targetModal || !targetModal.classList.contains('modal')) return;

        event.preventDefault();
        event.stopImmediatePropagation();
        targetModal.addEventListener('hidden.bs.modal', () => {
            bootstrap.Modal.getOrCreateInstance(parentModal).show();
        }, { once: true });
        parentModal.addEventListener('hidden.bs.modal', () => {
            bootstrap.Modal.getOrCreateInstance(targetModal).show();
        }, { once: true });
        bootstrap.Modal.getOrCreateInstance(parentModal).hide();
    }, true);

    document.addEventListener('change', (event) => {
        const typeInput = event.target.closest('.acom-upload-type');
        if (!typeInput) return;

        const form = typeInput.closest('form');
        const isPhoto = typeInput.value === 'foto';
        const photoWrap = form.querySelector('.acom-evidence-photo');
        const videoWrap = form.querySelector('.acom-evidence-video');
        photoWrap.classList.toggle('d-none', !isPhoto);
        videoWrap.classList.toggle('d-none', isPhoto);
        photoWrap.querySelector('input').required = isPhoto;
        videoWrap.querySelector('input').required = !isPhoto;
    });

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.acompanamiento-horario-form').forEach((form) => {
            const startInput = form.querySelector('input[name="hora_inicio"]');
            const durationSelect = form.querySelector('select[name="duracion_horas"]');
            if (!startInput || !durationSelect) return;

            const limitDuration = () => {
                if (!startInput.value) return;
                const [hours, minutes] = startInput.value.split(':').map(Number);
                const maxDuration = Math.floor((15 * 60 - (hours * 60 + minutes)) / 60);
                Array.from(durationSelect.options).forEach((option) => {
                    option.disabled = Number(option.value) > maxDuration;
                });
                if (maxDuration > 0 && Number(durationSelect.value) > maxDuration) {
                    durationSelect.value = String(maxDuration);
                }
            };

            startInput.addEventListener('input', limitDuration);
            startInput.addEventListener('change', limitDuration);
            limitDuration();
        });
    });

    @if(session('abrir_sesiones'))
        document.addEventListener('DOMContentLoaded', () => {
            const sessionsModal = document.getElementById('sesionesAcompanamiento{{ session('abrir_sesiones') }}');
            if (sessionsModal) bootstrap.Modal.getOrCreateInstance(sessionsModal).show();
        });
    @endif
</script>
@endpush
