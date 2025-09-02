@if (session('toast_success'))
    <script>
        $(() => {
            toastr.options = {
                positionClass: 'toast-bottom-right',
            }
            toastr.success('{{ session('toast_success') }}');
        });
    </script>
@endif

@if (session('toast_danger'))
    <script>
        $(() => {
            toastr.options = {
                positionClass: 'toast-bottom-right',
            }
            toastr.error('{{ session('toast_danger') }}');
        });
    </script>
@endif
