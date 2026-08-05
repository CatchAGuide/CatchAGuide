$(function(e) {

    //______Basic Data Table
    $('#basic-datatable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.11.3/i18n/de_de.json"
        }
    });

    //______Basic Data Table
    $('#responsive-datatable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/de-DE.json"
        },
        pagingType: "full_numbers",
        responsive: true,
        autoWidth: false,
        order: [
            [0, 'asc']
        ],
        columnDefs: [{
                targets: 0,
                type: 'num'
            },
            {
                targets: -1,
                orderable: false
            },
            {
                targets: -2,
                orderable: true,
                type: 'string'
            }
        ]
    });

    $('#booking-datatable').DataTable({
        order: [],
        scrollX: true,
        autoWidth: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.11.3/i18n/de_de.json"
        }
    });

    //______File-Export Data Table
    if ($('#file-datatable').length) {
        var table = $('#file-datatable').DataTable({
            buttons: ['copy', 'excel', 'pdf', 'colvis'],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.11.3/i18n/de_de.json"
            }
        });
        table.buttons().container()
            .appendTo('#file-datatable_wrapper .col-md-6:eq(0)');
    }

    //______Delete Data Table
    var table = $('#delete-datatable').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.11.3/i18n/de_de.json"
        }
    });
    $('#delete-datatable tbody').on('click', 'tr', function() {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        } else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });
    $('#button').click(function() {
        table.row('.selected').remove().draw(false);
    });

    $('table').on('draw.dt', function() {
        $('.select2').select2({
            minimumResultsForSearch: Infinity,
            placeholder: 'Choose one'
        });
    });

    //______Select2
    $('.select2').select2({
        minimumResultsForSearch: Infinity
    });

});