<div class="modal fade" id="pdfExportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ $exportRoute }}" method="GET">
        <div class="modal-header">
          <h5 class="modal-title">Export PDF</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Tipe Export</label>
            <select class="form-select" id="export_type" name="export_type">
              <option value="range">Range Tanggal</option>
              <option value="month">Per Bulan</option>
            </select>
          </div>

          <div id="rangeFields">
            <div class="mb-3">
              <label class="form-label">Tanggal Mulai</label>
              <input type="date" class="form-control" name="start_date">
            </div>
            <div class="mb-3">
              <label class="form-label">Tanggal Akhir</label>
              <input type="date" class="form-control" name="end_date">
            </div>
          </div>

          <div id="monthFields" style="display:none;">
            <div class="row">
              <div class="col-6 mb-3">
                <label class="form-label">Bulan</label>
                <select class="form-select" name="month">
                  @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>
                      {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-6 mb-3">
                <label class="form-label">Tahun</label>
                <select class="form-select" name="year">
                  @foreach(range(now()->year, now()->year - 5) as $y)
                    <option value="{{ $y }}" {{ now()->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Export</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const exportType = document.getElementById('export_type');
    const rangeFields = document.getElementById('rangeFields');
    const monthFields = document.getElementById('monthFields');

    const rangeInputs = rangeFields.querySelectorAll('input');
    const monthInputs = monthFields.querySelectorAll('select');

    function toggleFields() {
        const isRange = exportType.value === 'range';

        rangeFields.style.display = isRange ? 'block' : 'none';
        monthFields.style.display = isRange ? 'none' : 'block';

        rangeInputs.forEach(el => el.disabled = !isRange);
        monthInputs.forEach(el => el.disabled = isRange);
    }

    exportType.addEventListener('change', toggleFields);
    toggleFields(); // set initial state
});
</script>