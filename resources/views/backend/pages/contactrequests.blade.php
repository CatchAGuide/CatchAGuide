@extends('backend.layout.app')

@section('title', 'Nachrichtenanfragen')

@section('content')
    <div class="service">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <table class="table table-striped table-responsive">
                        <thead class="thead-light">
                        <tr>
                            <th>Name</th>
                            <th>Telefon</th>
                            <th>E-Mail Adresse</th>
                            <th>Nachricht</th>
                            <th>Aktion</th>
                        </tr>
                        </thead>
                        <tbody>
                    @foreach($contact_requests as $contact_request)
                            <tr>
                                <td>{{$contact_request->name}}</td>
                                <td>{{$contact_request->phone}}</td>
                                <td>{{$contact_request->email}}</td>
                                <td>{{$contact_request->message}}</td>
                                <td class="btn-group-lg">
                                    <a class="btn btn-outline-danger" title="Nachricht löschen"  href="javascript:deleteResource('{{ route('admin.contactform.delete', $contact_request->id, false) }}')" role="button"><i class="fa fa-trash"></i> </a></td>
                            </tr>
                    @endforeach
                        </tbody>
                        </table>
                    <br>
                    <br>
                    <br>
                </div>
            </div>
        </div>
    </div>
    @include('backend.component.delete-modal')
@endsection
