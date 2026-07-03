<script>
$(document).ready(function () {
    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            responsive: true,
            pageLength: 25,
            language: {
                search: '<i class="fas fa-search"></i>',
                searchPlaceholder: 'Search...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_–_END_ of _TOTAL_',
                paginate: {
                    previous: '‹',
                    next: '›'
                }
            },
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });
    }
});
</script>
