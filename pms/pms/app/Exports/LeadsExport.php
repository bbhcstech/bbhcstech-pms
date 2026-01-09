<?php

namespace App\Exports;

use App\Models\LeadContact;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeadsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $ids;

    public function __construct($ids = [])
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        if (!empty($this->ids) && is_array($this->ids)) {
            return LeadContact::with(['owner', 'creator'])
                ->whereIn('id', $this->ids)
                ->get();
        }

        return LeadContact::with(['owner', 'creator'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Company Name',
            'Phone',
            'Mobile',
            'Website',
            'Address',
            'City',
            'State',
            'Country',
            'Lead Source',
            'Status',
            'Lead Score',
            'Tags',
            'Lead Owner',
            'Added By',
            'Created Date',
            'Updated Date',
            'Description'
        ];
    }

    public function map($lead): array
    {
        return [
            $lead->id,
            $lead->contact_name,
            $lead->email,
            $lead->company_name,
            $lead->phone,
            $lead->mobile,
            $lead->website,
            $lead->address,
            $lead->city,
            $lead->state,
            $lead->country,
            $lead->lead_source,
            $lead->status,
            $lead->lead_score,
            $lead->tags,
            $lead->owner->name ?? 'N/A',
            $lead->creator->name ?? 'N/A',
            $lead->created_at->format('Y-m-d H:i:s'),
            $lead->updated_at->format('Y-m-d H:i:s'),
            $lead->description
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
