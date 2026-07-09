@props(['tableId', 'actionsColumn' => -1, 'extraConfig' => '{}'])

<script>
    $(function() {
        if (!$('#{{ $tableId }}').length) {
            return;
        }

        var extraConfig = {!! $extraConfig !!};
        var config = $.extend(true, {
            order: [[0, 'desc']],
            stripeClasses: [],
            columnDefs: [
                { orderable: false, targets: [{{ $actionsColumn }}] }
            ]
        }, extraConfig);

        $('#{{ $tableId }}').DataTable(config);
    });
</script>
