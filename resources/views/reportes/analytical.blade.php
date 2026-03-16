<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="pragma" content="no-cache" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ config('app.name', 'Instituto Clans') }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }
        .page-content {
            padding: 140px 80px 90px 80px;
        }
        .card {
            border: none;
            margin-bottom: 5px;
        }
        .card-header {
            background-color: #2e7d32;
            color: #fff;
            padding: 6px 14px;
            border-radius: 6px;
        }
        .card-header h5 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 12px;
        }
        .table th, .table td {
            border: 1px solid #dee2e6;
            padding: 5px 10px;
            background-color: rgba(255,255,255,0.92);
        }
        .table th {
            background-color: #e8f5e9;
            font-weight: 700;
            color: #1a3320;
        }
        h3 {
            font-size: 16px;
            margin: 5px 0;
            color: #1a3320;
        }
        p {
            font-size: 12px;
            margin: 3px 0;
        }
        h5 {
            font-size: 13px;
            margin: 3px 0;
        }
        .observations-content {
            font-size: 12px;
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            min-height: 40px;
            background-color: rgba(255,255,255,0.92);
        }
    </style>
</head>
<body>
    <div class="page-content">
        <div class="card">
            <div class="card-header">
                <h5>Informe Analítico</h5>
            </div>
        </div>

        <h3><strong>{{ $student->nombreCompleto }}</strong></h3>
        <p><strong>Curso: </strong>{{ $course->nombre }}</p>
        {!! $dateInit != "null" && $dateFinish !="null" ? "<p><strong>Período del ".$dateInit. " al "  .$dateFinish. " </strong></p>" : '' !!}

        <br>
        <div class="card">
            <div class="card-header">
                <h5>Desempeño en clases</h5>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <tbody>
                    <tr>
                        <td><strong>Oralidad</strong></td>
                        <td>{{ $result["oralidad"] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Participación</strong></td>
                        <td>{{ $result["participacion"] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Cumplimiento de tareas</strong></td>
                        <td>{{ $result["cumplimiento"] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Evaluaciones</h5>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <tbody>
                    <tr>
                        <td><strong>Unidades evaluadas</strong></td>
                        <td>{{ $result["units"] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Listening</strong></td>
                        <td>{{ $result['scoreFinal'][0]["listening"] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Vocabulary</strong></td>
                        <td>{{ $result['scoreFinal'][0]["vocabulary"] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Language Focus</strong></td>
                        <td>{{ $result['scoreFinal'][0]["languajeFocus"] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Reading</strong></td>
                        <td>{{ $result['scoreFinal'][0]["reading"] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Writing</strong></td>
                        <td>{{ $result['scoreFinal'][0]["writing"] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Oral Exam</strong></td>
                        <td>{{ $result['scoreFinal'][0]["oralExam"] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Tareas</h5>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <tbody>
                    <tr>
                        <td><strong>Asignadas</strong></td>
                        <td>{{ $result['scoreFinalPractice'][0]["assigned"] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Entregas</strong></td>
                        <td>{{ $result['scoreFinalPractice'][0]['delivered'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Pendientes</strong></td>
                        <td>{{ $result['scoreFinalPractice'][0]['pending'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Asistencias</h5>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Tipo de asistencia</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($result['assistences'] as $countAssistence)
                        <tr>
                            <td>{{ $countAssistence['typeAssistance'] }}</td>
                            <td>{{ $countAssistence['cant'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($result['observations']!="null")
        <div class="card">
            <div class="card-header">
                <h5>Observaciones</h5>
            </div>
        </div>
        <div class="observations-content">
            {!! $result['observations'] !!}
        </div>
        @endif
    </div>
</body>
</html>
