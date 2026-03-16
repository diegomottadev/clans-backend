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
                <h5>Asistencias por curso</h5>
            </div>
        </div>
        <p><strong>Idioma: </strong> {{ $languaje->nombre }}</p>
        <p><strong>Nivel: </strong> {{ $level->nombre }}</p>
        <p><strong>Curso: </strong>{{ $course->nombre }}</p>
        {!! $dateInit != "null" && $dateFinish !="null" ? "<h5><strong>Periodo del ".$dateInit. " al "  .$dateFinish. " </strong></h5>" : '' !!}
        {!! $month!="null"  ? "<h5><strong>Periodo ".$month.  " </strong></h5>" : '' !!}
        @foreach ($assistences as $key => $assitence)
            <div class="card">
                <div class="card-header">
                    <h5><strong>Fecha: </strong>{{ $assistences[$key]['date'] }}</h5>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Tipo de asistencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assistences[$key]['studentsAssisted'] as $key => $assistanceStudent)
                    <tr>
                        <td> @if ($assistanceStudent != null) {{ $assistanceStudent['name_complete'] }}@endif</td>
                        <td> @if ($assistanceStudent['pivot']['nameTypeAssistance'] != null) {{ $assistanceStudent['pivot']['nameTypeAssistance']['nombre'] }}@endif</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    </div>
</body>
</html>
