<?php

namespace App\View\Components;

use Illuminate\View\Component;

class BulkApprovalModal extends Component
{
    public function __construct(
        public string $prefix,          // 'known' atau 'approve'
        public string $title,           // 'Produksi' atau 'QC'
        public string $color,           // 'warning' atau 'success'
        public string $icon,            // 'fa-check-double' atau 'fa-check-circle'
        public string $actionRoute,     // route name untuk submit
        public string $countRoute,      // route name untuk fetch count
        public string $label,           // label teks tombol submit
    ) {}

    public function render()
    {
        return view('components.bulk-approval-modal');
    }
}