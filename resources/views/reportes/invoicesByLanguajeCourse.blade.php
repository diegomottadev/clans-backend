<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="pragma" content="no-cache" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ config('app.name', 'Instituto Clans') }}</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; }
        .page-content { padding: 140px 80px 90px 80px; }
        .card { border: none; margin-bottom: 5px; }
        .card-header { background-color: #2e7d32; color: #fff; padding: 6px 14px; border-radius: 6px; }
        .card-header h5 { margin: 0; font-size: 14px; font-weight: 700; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 12px; }
        .table th, .table td { border: 1px solid #dee2e6; padding: 5px 10px; background-color: rgba(255,255,255,0.92); }
        .table th { background-color: #e8f5e9; font-weight: 700; color: #1a3320; }
        h3 { font-size: 16px; margin: 5px 0; color: #1a3320; }
        p { font-size: 12px; margin: 3px 0; }
        h5 { font-size: 13px; margin: 3px 0; }
    </style>
</head>
<body>
    <div class="page-content">
        <div class="card">
            <div class="card-header">
                <h5>Cobros por idioma y curso</h5>
            </div>
        </div>
        @if($courses !== null)
        @else
            <h3>Curso <strong>{{ $course->nombre }}</strong></h3>
            <h3>Nivel <strong>{{ $course->level->nombre }}</strong></h3>
            <h3>Idioma <strong>{{ $course->level->languaje->nombre }}</strong></h3>
        @endif
        {!! isset($dateInit) && isset($dateFinish) ? "<h5><strong>Periodo del ".$dateInit. " al "  .$dateFinish. " </strong></h5>" : '' !!}
        @php
            $hasTable = [];
        @endphp
        @foreach ($courses as $key => $course)
            @php
            $hasInvoices = false;
            foreach ($invoices as $invoice) {
                if ($invoice['studentCourse']['course']['nombre'] === $course->nombre) {
                    $hasInvoices = true;
                    break;
                }
            }
            @endphp
            @if ($hasInvoices && empty($hasTable[$course->nombre]))
                <h3>Curso <strong>{{ $course->nombre }}</strong></h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Fecha de pago</th>
                            <th>Mes</th>
                            <th>Cuota</th>
                            <th>Curso</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $key => $invoice)
                            @if($invoice['studentCourse']['course']['nombre'] === $course->nombre)
                                <tr>
                                    <td>{{ $invoice['student']['nombreCompleto'] }}</td>
                                    <td>{{ $invoice['dateIssue'] }}</td>
                                    <td>{{ $invoice['fee'] }}</td>
                                    <td>{{ $invoice['month'] }}</td>
                                    <td>{{ $invoice['studentCourse']['course']['nombre'] }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
                @php
                $hasTable[$course->nombre] = true;
                @endphp
            @endif
        @endforeach
    </div>
</body>
</html>
