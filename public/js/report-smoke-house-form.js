document.addEventListener('DOMContentLoaded', function () {

    const $container = $('#detailContainer');
    const routeMachines = $container.data('route-machines'); // .../__PRODUCT__
    const routeSteps = $container.data('route-steps');       // .../__MASTER__

    // ============ INIT existing blocks (edit mode) ============
    $container.find('.detail-item').each(function () {
        bindDetailEvents($(this));
    });

    // ============ ADD PRODUCT (detail block) ============
    $('#addDetail').on('click', function () {
        let idx = parseInt($container.data('index'));
        let html = $('#detail-block-template').html().replaceAll('__INDEX__', idx);
        let $newBlock = $(html);

        $container.append($newBlock);
        bindDetailEvents($newBlock);
        renumberDetails();

        $container.data('index', idx + 1);
    });

    $(document).on('click', '.remove-detail', function () {
        $(this).closest('.detail-item').remove();
        renumberDetails();
    });

    function renumberDetails() {
        $container.find('.detail-item').each(function (i) {
            $(this).find('.detail-number').first().text(i + 1);
        });
    }

    // ============ BIND PER-DETAIL EVENTS ============
    function bindDetailEvents($detail) {

        const $product = $detail.find('.product-select');
        const $machine = $detail.find('.machine-select');
        const $masterUuidInput = $detail.find('.master-uuid');
        const $stepBody = $detail.find('.step-container');
        const $showeringBody = $detail.find('.showering-container');
        const detailIndex = $detail.data('index');
        const SHOWERING_PROCESS = 'Showering & Cooling Down';

        // --- pilih product -> load machine dari master ---
        $product.off('change').on('change', function () {
            const productUuid = $(this).val();

            $machine.prop('disabled', true).html('<option value="">Memuat...</option>');
            $stepBody.html('<tr><td colspan="10" class="text-center text-muted">Pilih product & machine</td></tr>');
            $showeringBody.html('<tr><td colspan="8" class="text-center text-muted">Pilih product & machine</td></tr>');
            $masterUuidInput.val('');

            if (!productUuid) {
                $machine.html('<option value="">Pilih Product dulu</option>');
                return;
            }

            $.get(routeMachines.replace('__PRODUCT__', productUuid), function (masters) {
                let options = '<option value="">Pilih Machine</option>';
                masters.forEach(function (m) {
                    options += `<option value="${m.machine_name}" data-master-uuid="${m.uuid}">${m.machine_name}</option>`;
                });
                $machine.prop('disabled', false).html(options);
            }).fail(function () {
                $machine.html('<option value="">Gagal memuat machine</option>');
            });
        });

        // --- pilih machine -> load steps dari master ---
        $machine.off('change').on('change', function () {
            const masterUuid = $(this).find(':selected').data('master-uuid') || '';
            $masterUuidInput.val(masterUuid);
            $detail.attr('data-master-uuid', masterUuid);
            $detail.find('.process-select').attr('data-master', masterUuid);

            if (!masterUuid) {
                $stepBody.html('<tr><td colspan="10" class="text-center text-muted">Machine tidak terhubung ke master</td></tr>');
                $showeringBody.html('<tr><td colspan="8" class="text-center text-muted">Machine tidak terhubung ke master</td></tr>');
                return;
            }

            $stepBody.html('<tr><td colspan="10" class="text-center text-muted">Memuat parameter...</td></tr>');
            $showeringBody.html('<tr><td colspan="8" class="text-center text-muted">Memuat parameter...</td></tr>');

            $.get(routeSteps.replace('__MASTER__', masterUuid), function (steps) {
                $detail.data('master-steps', steps); // cache untuk rework
                renderSteps($stepBody, $showeringBody, detailIndex, steps, SHOWERING_PROCESS);
            }).fail(function () {
                $stepBody.html('<tr><td colspan="10" class="text-center text-danger">Gagal memuat parameter master</td></tr>');
                $showeringBody.html('<tr><td colspan="8" class="text-center text-danger">Gagal memuat parameter master</td></tr>');
            });
        });

        // --- tambah cooking ulang ---
        $detail.find('.add-rework').off('click').on('click', function () {
            const $reworkContainer = $detail.find('.rework-container');
            let rIdx = $reworkContainer.children('.rework-item').length;

            let html = $detail.find('template.tpl-rework').html()
                .replaceAll('__RIDX__', rIdx);

            let $newRework = $(html);
            $reworkContainer.append($newRework);
            bindReworkEvents($detail, $newRework);
        });

        $detail.find('.rework-item').each(function () {
            bindReworkEvents($detail, $(this));
        });

        const existingMasterUuid = $detail.attr('data-master-uuid');

        if (existingMasterUuid) {
            $.get(routeSteps.replace('__MASTER__', existingMasterUuid), function (steps) {
                $detail.data('master-steps', steps);
            });
        }
    }

    // ============ RENDER STEPS UTAMA DARI MASTER ============
    function renderSteps($tbody, $showeringTbody, detailIndex, steps, showeringProcess) {
        if (!steps.length) {
            $tbody.html('<tr><td colspan="10" class="text-center text-muted">Master belum punya step</td></tr>');
            $showeringTbody.html('<tr><td colspan="8" class="text-center text-muted">Master belum punya step</td></tr>');
            return;
        }

        let cookingRows = '';
        let showeringRows = '';

        steps.forEach(function (step, i) {
            const settingTemp = (step.temperature_min || step.temperature_max)
                ? `${step.temperature_min ?? ''}-${step.temperature_max ?? ''}`
                : '';

            const isShowering = step.process_name === showeringProcess;

            if (isShowering) {
                showeringRows += `
        <tr>
            <td>
                ${step.process_name}
                <input type="hidden" name="details[${detailIndex}][steps][${i}][sequence]" value="${step.sequence}">
                <input type="hidden" name="details[${detailIndex}][steps][${i}][process_name]" value="${step.process_name}">
            </td>
            <td><input class="form-control" readonly name="details[${detailIndex}][steps][${i}][setting_temp]" value="${settingTemp}"></td>
            <td><input class="form-control" name="details[${detailIndex}][steps][${i}][actual_temp]" placeholder="Mis: 12.5"></td>
            <td><input class="form-control" readonly name="details[${detailIndex}][steps][${i}][setting_time]" value="${step.time_minutes ?? ''}"></td>
            <td><input class="form-control" name="details[${detailIndex}][steps][${i}][actual_time]" placeholder="Mis: 10"></td>
            <td><input class="form-control" readonly name="details[${detailIndex}][steps][${i}][setting_rh]" value="${step.rh ?? ''}"></td>
            <td><input class="form-control" name="details[${detailIndex}][steps][${i}][actual_rh]" placeholder="Mis: 60"></td>
            <td><input class="form-control" readonly name="details[${detailIndex}][steps][${i}][setting_ct]" value="${step.core_temperature ?? ''}"></td>
            <td><input class="form-control" name="details[${detailIndex}][steps][${i}][actual_ct]" placeholder="Mis: 75"></td>
        </tr>`;
            } else {
                cookingRows += `
                <tr>
                    <td>
                        ${step.sequence}
                        <input type="hidden" name="details[${detailIndex}][steps][${i}][sequence]" value="${step.sequence}">
                    </td>
                    <td>
                        ${step.process_name}
                        <input type="hidden" name="details[${detailIndex}][steps][${i}][process_name]" value="${step.process_name}">
                    </td>
                    <td><input class="form-control" readonly name="details[${detailIndex}][steps][${i}][setting_temp]" value="${settingTemp}"></td>
                    <td><input class="form-control" name="details[${detailIndex}][steps][${i}][actual_temp]" placeholder="Mis: 12.5"></td>
                    <td><input class="form-control" readonly name="details[${detailIndex}][steps][${i}][setting_time]" value="${step.time_minutes ?? ''}"></td>
                    <td><input class="form-control" name="details[${detailIndex}][steps][${i}][actual_time]" placeholder="Mis: 10"></td>
                    <td><input class="form-control" readonly name="details[${detailIndex}][steps][${i}][setting_rh]" value="${step.rh ?? ''}"></td>
                    <td><input class="form-control" name="details[${detailIndex}][steps][${i}][actual_rh]" placeholder="Mis: 60"></td>
                    <td><input class="form-control" readonly name="details[${detailIndex}][steps][${i}][setting_ct]" value="${step.core_temperature ?? ''}"></td>
                    <td><input class="form-control" name="details[${detailIndex}][steps][${i}][actual_ct]" placeholder="Mis: 75"></td>
                </tr>`;
            }
        });

        $tbody.html(cookingRows || '<tr><td colspan="10" class="text-center text-muted">Tidak ada step cooking</td></tr>');
        $showeringTbody.html(showeringRows || '<tr><td colspan="8" class="text-center text-muted">Tidak ada step showering</td></tr>');
    }

    // ============ BIND REWORK EVENTS ============
    function bindReworkEvents($detail, $rework) {

        $rework.find('.remove-rework').off('click').on('click', function () {
            $rework.remove();
        });

        $rework.find('.add-rework-step').off('click').on('click', function () {
            const $stepContainer = $rework.find('.rework-step-container');
            let sIdx = $stepContainer.children('.rework-step-item').length;

            let html = $rework.find('template.tpl-rework-step').html()
                .replaceAll('__SIDX__', sIdx);

            let $newStep = $(html);
            $stepContainer.append($newStep);
            bindReworkStepEvents($detail, $newStep);
        });

        $rework.find('.rework-step-item').each(function () {
            bindReworkStepEvents($detail, $(this));
        });
    }

    // --- autofill setting_* rework step dari master saat pilih process ---
    function bindReworkStepEvents($detail, $stepRow) {
        $stepRow.find('.process-select').off('change').on('change', function () {
            const processName = $(this).val();
            const masterSteps = $detail.data('master-steps') || [];
            const match = masterSteps.find(s => s.process_name === processName);

            if (match) {
                const settingTemp = (match.temperature_min || match.temperature_max)
                    ? `${match.temperature_min ?? ''}-${match.temperature_max ?? ''}`
                    : '';
                $stepRow.find('.setting-temp').val(settingTemp);
                $stepRow.find('.setting-time').val(match.time_minutes ?? '');
                $stepRow.find('.setting-rh').val(match.rh ?? '');
                $stepRow.find('.setting-ct').val(match.core_temperature ?? '');
            } else {
                $stepRow.find('.setting-temp, .setting-time, .setting-rh, .setting-ct').val('');
            }
        });

        $stepRow.find('.remove-rework-step').off('click').on('click', function () {
            $stepRow.remove();
        });
    }

});