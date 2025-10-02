<script>
    $(() => {
        const parentForm = $('select[name="parent_id"]').parent().parent();
        const workerForm = $('select[name="worker_id"]').parent().parent();
        const defective = @json($line?->defective);
        const old = @json(old('defective'));

        let toggle = (defective === true) || (old != null);
        if (toggle) {
            parentForm.removeClass('d-none');
        } else {
            workerForm.removeClass('d-none');
        }

        $('input[name="defective"]').on('switchChange.bootstrapSwitch', (e) => {
            if (toggle) {
                parentForm.addClass('d-none');
                workerForm.removeClass('d-none');
            } else {
                parentForm.removeClass('d-none');
                workerForm.addClass('d-none');
            }
            toggle = !toggle;
        });
    });
</script>
