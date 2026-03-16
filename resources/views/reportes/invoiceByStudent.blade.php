@extends('layouts.pdf')
@push('styles')
    <style>
        .fondo {
            font-size: 12px;
        }
        img {
            width: 50%;
            display: block;
            }
    </style>
@endpush
@section('content')
        <div class="row fondo">
            <div class="col-md-6">
                <div class="col-md-12">
                    <table>
                        <tbody>
                            <tr>
                                <td colspan="3">
                                    <img class="img" src="{!! $logoBase64 !!}" />
                                </td>
                                <td></td>
                                <td colspan="3">
                                    <img class="img" src="{!! $logoBase64 !!}" />
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <strong>Cuba 747 Primer Piso</strong>
                                </td>
                                <td></td>
                                <td colspan="3">
                                    <strong>Cuba 747 Primer Piso</strong>
                                </td>

                            </tr>
                            <tr>
                                <td colspan="3">
                                    <strong>Tel&eacute;fono 461250</strong>
                                </td>
                                <td></td>
                                <td colspan="3">
                                    <strong>Tel&eacute;fono 461250</strong>
                                </td>

                            </tr>
                            <tr>
                                <td colspan="3">
                                    <p><strong>Jard&iacute;n Amer&iacute;ca Misiones</strong></p>
                                </td>
                                <td></td>
                                <td colspan="3">
                                    <p><strong>Jard&iacute;n Amer&iacute;ca Misiones</strong></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Nro recibo</strong></td>
                                <td>{{ $id }}</td>
                                <td></td>
                                <td colspan="2"><strong>Nro recibo</strong></td>
                                <td>{{ $id }}</td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                                <td></td>
                                <td colspan="3"></td>

                            </tr>
                            <tr>
                                <td colspan="2"><strong>Alumno</strong></td>
                                <td><strong>{{ $student->nombreCompleto }}</strong></td>
                                <td></td>
                                <td colspan="2"><strong>Alumno</strong></td>
                                <td><strong>{{ $student->nombreCompleto }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="2"></td>
                                <td></td>
                                <td></td>
                                <td colspan="2"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Curso</strong></td>
                                <td>{{ $course->nombre }}</td>
                                <td></td>
                                <td colspan="2"><strong>Curso</strong></td>
                                <td>{{ $course->nombre }}</td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Mes</strong></td>
                                <td>{{ $month }}</td>
                                <td></td>
                                <td colspan="2"><strong>Mes</strong></td>
                                <td>{{ $month }}</td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Cuota</strong></td>
                                <td>{{ $fee }}</td>
                                <td></td>
                                <td colspan="2"><strong>Cuota</strong></td>
                                <td>{{ $fee }}</td>
                            </tr>
                            <tr>
                                <td colspan="2"><strong>Fecha cobro</strong></td>
                                <td>{{ $date }}</td>
                                <td></td>
                                <td colspan="2"><strong>Fecha cobro</strong></td>
                                <td>{{ $date }}</td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@endsection
