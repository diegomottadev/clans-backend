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
                <h5>Listado de alumnos por idiomas, niveles y cursos</h5>
            </div>
        </div>
        @foreach ($list as $item)
            <p><strong>Idioma: </strong> {{ $item['idioma'] }}</p>
            <p><strong>Nivel: </strong> {{ $item['nivel'] }}</p>
            <p><strong>Curso: </strong>{{ $item['curso'] }}</p>
            <p><strong>Total de alumnos: </strong>{{ $item['num_estudiantes'] }}</p>
            <table class="table">
                <thead>
                    <tr>
                        <th style="text-align: center;">Nombre y Apellido</th>
                        <th style="text-align: center;">DNI</th>
                        <th style="text-align: center;">Telefono</th>
                        <th style="text-align: center;">Turno escolar/laboral</th>
                        <th style="text-align: center;">Horario ed.fisica</th>
                        <th style="text-align: center;">Edad</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($item['estudiantes'])> 0)
                    @foreach ($item['estudiantes'] as $estudiante)
                    <tr>
                        <td style="text-align: center;">{{$estudiante['nombreCompleto']}}</td>
                        <td style="text-align: center;">{{$estudiante['dni']}}</td>
                        <td style="text-align: center;">{{$estudiante['telefono']}}</td>
                        <td style="text-align: center;">{{$estudiante['turno_escolar']}}</td>
                        <td style="text-align: center;">{{$estudiante['horario_ed_fisica']}}</td>
                        <td style="text-align: center;">{{\Carbon\Carbon::parse($estudiante['fecha_nac'])->diffInYears(\Carbon\Carbon::now());}}</td>
                        <td>{!!$estudiante['observaciones']!!}</td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        @endforeach
    </div>
</body>
</html>
