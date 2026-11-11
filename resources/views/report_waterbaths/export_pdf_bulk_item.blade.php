@if((int) $report->form_version >= 2)
    @include('report_waterbaths.export_pdf_v2')
@else
    @include('report_waterbaths.export_pdf')
@endif