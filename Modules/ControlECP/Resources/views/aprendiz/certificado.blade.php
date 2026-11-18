@extends('controlecp::components.layouts.dashboard')

@push('styles')
<style>
    .apr-cert-header { padding:1.35rem 1.5rem; border-radius:20px; background:linear-gradient(120deg,#001A29,#00324D); color:#fff; }
    .apr-cert-header h1 { color:#fff; }
    .apr-cert-header p { color:#c7d4da; }
    .apr-cert-card { overflow:hidden; border:1px solid #e0ece4; border-radius:16px; background:#fff; box-shadow:0 2px 10px rgba(0,26,41,.05); }
    .apr-cert-kpi { min-height:125px; padding:1.1rem 1.25rem; border:1px solid; border-radius:16px; background:#fff; box-shadow:0 2px 10px rgba(0,26,41,.05); transition:transform .18s ease, box-shadow .18s ease; }
    .apr-cert-kpi:hover { transform:translateY(-2px); box-shadow:0 8px 22px rgba(0,26,41,.1); }
    .apr-cert-kpi-label { color:#475569; font-size:.9rem; font-weight:600; }
    .apr-cert-kpi-value { margin-top:.4rem; font-size:2rem; font-weight:800; line-height:1; }
    .apr-cert-kpi-icon { display:flex; width:42px; height:42px; align-items:center; justify-content:center; border-radius:50%; font-size:1rem; }
    .apr-cert-card-header { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem; padding:1.1rem 1.25rem; border-bottom:1px solid #edf1f3; }
    .apr-cert-moment { display:flex; align-items:center; gap:.75rem; padding:.9rem 1.1rem; border-bottom:1px solid #edf1f3; }
    .apr-cert-moment:last-child { border-bottom:0; }
    .apr-cert-moment-icon { display:grid; width:38px; height:38px; flex:0 0 38px; place-items:center; border-radius:12px; }
    .apr-cert-moment--done .apr-cert-moment-icon { background:#e9f8ef; color:#168653; }
    .apr-cert-moment--pending .apr-cert-moment-icon { background:#fff4d6; color:#a16207; }
    .apr-cert-status { display:inline-flex; align-items:center; gap:.35rem; padding:.3rem .65rem; border:1px solid transparent; border-radius:999px; font-size:.72rem; font-weight:800; white-space:nowrap; }
    .apr-cert-status--done { border-color:#a7e4c5; background:#d1fae5; color:#168653; }
    .apr-cert-status--pending { border-color:#f8e3b2; background:#fff7e6; color:#a16207; }
    .apr-cert-download { display:inline-flex; align-items:center; justify-content:center; gap:.5rem; min-height:42px; border:1px solid #39A900; border-radius:999px; background:#39A900; color:#fff; font-weight:700; text-decoration:none; }
    .apr-cert-download:hover { border-color:#318f00; background:#318f00; color:#fff; }
    .apr-cert-download-disabled { display:inline-flex; align-items:center; gap:.5rem; min-height:42px; padding:.5rem 1rem; border:1px solid #d5dde3; border-radius:999px; background:#f1f5f9; color:#64748b; font-size:.86rem; font-weight:700; }
    .apr-cert-empty { padding:3rem 1.5rem; border:1px solid #e0ece4; border-radius:16px; background:#fff; text-align:center; }
    .apr-cert-empty-icon { display:grid; width:72px; height:72px; place-items:center; margin:0 auto 1rem; border-radius:50%; background:#fff7e6; color:#c47a00; font-size:1.8rem; }
    .apr-cert-details { display:inline-flex; min-height:40px; align-items:center; gap:.45rem; padding:.45rem .9rem; border:1px solid #cfe2d2; border-radius:999px; background:#f4faef; color:#287a13; font-size:.86rem; font-weight:700; }
    .apr-cert-details:hover { border-color:#39A900; background:#e9f8e4; color:#21650f; }
    .apr-cert-modal .modal-content { overflow:hidden; border:0; border-radius:20px; box-shadow:0 24px 70px rgba(0,26,41,.24); }
    .apr-cert-modal .modal-header { gap:.85rem; padding:1.15rem 1.35rem; border:0; border-bottom:3px solid #39A900; color:#fff; background:linear-gradient(120deg,#001A29,#00324D); }
    .apr-cert-modal .modal-title { color:#fff; }
    .apr-cert-modal .modal-header p { color:#c7d4da; }
    .apr-cert-modal .modal-header .btn-close { filter:brightness(0) invert(1); }
    .apr-cert-modal .modal-body { max-height:65vh; overflow-y:auto; padding:1.25rem; background:#f7faf8; }
    @media (max-width:575px) {
        .apr-cert-kpi { min-height:110px; }
    }
</style>
@endpush

@section('dashboard-content')
<div class="container-fluid px-3 py-3">
    <header class="apr-cert-header mb-4">
        <div class="small fw-semibold mb-1" style="color:#62E31D">Control ECP · Aprendiz</div>
        <h1 class="h3 fw-bold mb-1"><i class="fas fa-award me-2" style="color:#62E31D" aria-hidden="true"></i>Mi certificado</h1>
        <p class="mb-0 small">Consulta el avance de los momentos de tu ficha y descarga la constancia cuando completes el proceso.</p>
    </header>

    @php
        $constanciasDisponibles = $records->where('completo', true)->count();
        $momentosCertificados = $records->sum('completados');
        $momentosPendientes = $records->sum(fn ($record) => $record['total'] - $record['completados']);
    @endphp
    <div class="row g-3 mb-4" aria-label="Resumen de certificación">
        <div class="col-12 col-md-4">
            <div class="apr-cert-kpi h-100" style="border-color:rgba(2,132,199,.35)">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <span class="apr-cert-kpi-label">Constancias disponibles</span>
                    <span class="apr-cert-kpi-icon" style="background:#e0f2fe;color:#0284c7"><i class="fas fa-file-pdf" aria-hidden="true"></i></span>
                </div>
                <div class="apr-cert-kpi-value" style="color:#0284c7">{{ $constanciasDisponibles }}</div>
                <div class="small text-muted mt-2">Una por ficha al certificar sus tres momentos</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="apr-cert-kpi h-100" style="border-color:rgba(22,163,74,.35)">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <span class="apr-cert-kpi-label">Momentos certificados</span>
                    <span class="apr-cert-kpi-icon" style="background:#dcfce7;color:#16a34a"><i class="fas fa-circle-check" aria-hidden="true"></i></span>
                </div>
                <div class="apr-cert-kpi-value" style="color:#16a34a">{{ $momentosCertificados }}</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="apr-cert-kpi h-100" style="border-color:rgba(245,158,11,.4)">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <span class="apr-cert-kpi-label">Momentos pendientes</span>
                    <span class="apr-cert-kpi-icon" style="background:#fff7e6;color:#c47a00"><i class="fas fa-hourglass-half" aria-hidden="true"></i></span>
                </div>
                <div class="apr-cert-kpi-value" style="color:#c47a00">{{ $momentosPendientes }}</div>
            </div>
        </div>
    </div>

    @if($records->isEmpty())
        <div class="apr-cert-empty">
            <div class="apr-cert-empty-icon"><i class="fas fa-id-card" aria-hidden="true"></i></div>
            <h2 class="h5 fw-bold">No encontramos una ficha asociada a tu usuario</h2>
            <p class="text-muted mb-0">Solicita al administrador que revise tu inscripción para consultar el avance de certificación.</p>
        </div>
    @else
        <div class="d-flex flex-column gap-4">
            @foreach($records as $record)
                <section class="apr-cert-card">
                    <div class="apr-cert-card-header">
                        <div>
                            <div class="small text-muted">Ficha {{ $record['course']->code }}</div>
                            <h2 class="h5 fw-bold mb-1">{{ $record['course']->program?->name ?: 'Programa de formación' }}</h2>
                            <div class="small text-muted">{{ $record['completados'] }} de {{ $record['total'] }} momentos certificados</div>
                        </div>
                        <button class="apr-cert-details" type="button" data-bs-toggle="modal" data-bs-target="#modalCertificado{{ $record['course']->id }}">
                            <i class="fas fa-eye" aria-hidden="true"></i>Ver avance
                        </button>
                    </div>
                </section>

                <div class="modal fade apr-cert-modal" id="modalCertificado{{ $record['course']->id }}" tabindex="-1" aria-labelledby="modalCertificadoTitulo{{ $record['course']->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <span class="apr-cert-moment-icon" style="background:rgba(98,227,29,.14);color:#8df05d">
                                    <i class="fas fa-award" aria-hidden="true"></i>
                                </span>
                                <div class="flex-grow-1">
                                    <h3 class="modal-title h5 fw-bold mb-0" id="modalCertificadoTitulo{{ $record['course']->id }}">Avance de certificación</h3>
                                    <p class="mb-0 small">Ficha {{ $record['course']->code }} · {{ $record['course']->program?->name ?: 'Programa de formación' }}</p>
                                </div>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                    <span class="fw-semibold">Momentos certificados</span>
                                    <span class="apr-cert-status {{ $record['completo'] ? 'apr-cert-status--done' : 'apr-cert-status--pending' }}">
                                        <i class="fas {{ $record['completo'] ? 'fa-check-circle' : 'fa-hourglass-half' }}" aria-hidden="true"></i>
                                        {{ $record['completados'] }} de {{ $record['total'] }}
                                    </span>
                                </div>
                                @forelse($record['momentos'] as $momento)
                                    <div class="apr-cert-moment {{ $momento['certificada'] ? 'apr-cert-moment--done' : 'apr-cert-moment--pending' }} bg-white rounded-3 mb-2">
                                        <span class="apr-cert-moment-icon">
                                            <i class="fas {{ $momento['certificada'] ? 'fa-circle-check' : 'fa-hourglass-half' }}" aria-hidden="true"></i>
                                        </span>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">Momento {{ $momento['orden'] }} · {{ $momento['nombre'] }}</div>
                                            @if($momento['certificada'])
                                                <div class="small text-muted">Certificado el {{ \Illuminate\Support\Carbon::parse($momento['fecha'])->timezone('America/Bogota')->format('d/m/Y') }}</div>
                                            @else
                                                <div class="small text-muted">Aún no cuenta con una certificación registrada.</div>
                                            @endif
                                        </div>
                                        @if($momento['certificada'])
                                            <span class="apr-cert-status apr-cert-status--done"><i class="fas fa-check" aria-hidden="true"></i>Certificado</span>
                                        @else
                                            <span class="apr-cert-status apr-cert-status--pending"><i class="fas fa-clock" aria-hidden="true"></i>Pendiente</span>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-muted">Aún no hay momentos configurados para consultar.</div>
                                @endforelse
                            </div>
                            <div class="modal-footer">
                                @if($record['completo'])
                                    <a class="apr-cert-download px-3" href="{{ route('controlecp.aprendiz.certificado.descargar', $record['course']->id) }}">
                                        <i class="fas fa-file-pdf" aria-hidden="true"></i>Descargar constancia PDF
                                    </a>
                                @else
                                    <span class="apr-cert-download-disabled"><i class="fas fa-lock" aria-hidden="true"></i>Constancia no disponible</span>
                                @endif
                                <button class="btn btn-outline-secondary rounded-pill px-3" type="button" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
