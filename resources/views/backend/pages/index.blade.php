@extends('backend.layout.app')

@section('title', 'Fahrzeugliste')

@section('content')
    <div class="service">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-striped" id="datatable">
                            <thead class="thead-light">
                            <tr>
                                <th>Vorschaubild</th>
                                <th>Name</th>
                                <th>Hersteller</th>
                                <th>Model</th>
                                <th>Beschreibung</th>
                                <th class="text-center">Personen</th>
                                <th>Aktion</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($campers as $camper)
                                <tr>
                                    <td>
                                        <div id="carousel-{{ $camper->id }}" class="carousel slide">
                                            <div class="carousel-inner"style=" max-width:300px">
                                                @foreach($camper->images as $image)
                                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                                        <img class="d-block" src="{{ $image->getImage() }}" alt="Campers slide">
                                                    </div>
                                                @endforeach
                                            </div>
                                            <a class="carousel-control-prev" href="#carousel-{{ $camper->id }}" role="button" data-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="sr-only">Zurück</span>
                                            </a>
                                            <a class="carousel-control-next" href="#carousel-{{ $camper->id }}" role="button" data-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="sr-only">Weiter</span>
                                            </a>
                                        </div>
                                    </td>
                                    <td><a href="{{route('camper.show', $camper->id)}}">{{$camper->name}}</a></td>
                                    <td>{{$camper->manufacturer}}</td>
                                    <td>{{$camper->model}}</td>
                                    <td>{{Str::of($camper->description)->limit(35)}}</td>
                                    <td class="text-center">{{$camper->max_person}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a class="btn btn-outline-secondary mt-3" title="Fahrzeug bearbeiten" href="{{ route('admin.edit', $camper->id) }}" role="button"><i class="fa fa-pencil"></i> </a>
                                            <a class="btn btn-outline-danger mt-3" title="Fahrzeug löschen"  href="javascript:deleteResource('{{ route('admin.delete', $camper->id, false) }}')" role="button"><i class="fa fa-trash"></i> </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('backend.component.delete-modal')
@endsection

@section('js_after')
    <script>
        $('#datatable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/German.json'
            }
        });
    </script>
@endsection
