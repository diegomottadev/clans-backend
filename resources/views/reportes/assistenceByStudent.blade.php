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
                <h5>Asistencias por alumno</h5>
            </div>
        </div>
        <h3><strong>{{ $student->nombreCompleto }}</strong></h3>
        {!! isset($dateInit) && isset($dateFinish) ? "<h5><strong>Periodo del ".$dateInit. " al "  .$dateFinish. " </strong></h5>" : '' !!}
        {!! isset($month)  ? "<h5><strong>Periodo ".$month.  " </strong></h5>" : '' !!}
        <table class="table">
            <thead>
                <tr>
                    <th>Tipo de asistencia</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($countByTypeAssistence as $countAssistence)
                    <tr>
                        <td>{{ $countAssistence['typeAssistance'] }}</td>
                        <td>{{ $countAssistence['cant'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Curso</th>
                    <th>Tipo de asistencia</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($assistences as $key => $assistence)
                    <tr>
                        <td>{{ $assistence['date'] }}</td>
                        <td>{{ $assistence['course']['nombre'] }}</td>
                        <td>{{ $assistence['typeAssistance']['nameTypeAssistance']['nombre'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
