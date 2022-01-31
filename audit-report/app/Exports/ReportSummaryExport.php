<?php

namespace App\Exports;

use App\Models\ConformanceReportSummary;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class ReportSummaryExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithStrictNullComparison
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $filename = '';
    public function __construct($name){
        $this->filename = $name;
    }
    public function collection()
    {
        // return ConformanceReportSummary::all();
        return ConformanceReportSummary::select('order', 'sc_name', 'level', 'wcag_version', 'pass', 'fail', 'dna', 'severity_low', 'severity_medium', 'severity_high', 'severity_na')->get();
    }
    public function headings(): array
    {
        return [
            '#',
            'WCAG - Success Criteria',
            'Level',
            'Version',
            'Pass',
            'Fail',
            'DNA',
            'Severity Low',
            'Severity Medium',
            'Severity High',
            'Severity NA'
        ];
    }
    public function registerEvents(): array
    {
        
        $styleArray = ['font' => ['bold' => true,]];
            
        
        
        return [
            AfterSheet::class    => function(AfterSheet $event)
            {
                $event->getSheet()->getDelegate()->getStyle('A1:K51')->applyFromArray(
                    array(
                        'borders' => array(
                            'allBorders' => array(
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => array('rgb' => '000000')
                            )
                        ),
                        
                    )
                );
                //
                $event->getSheet()->getDelegate()->getStyle('C1:K51')->applyFromArray(
                    ['alignment' => array(
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    )
                ]);						
                $event->getSheet()->getDelegate()->getStyle('A1:K1')->applyFromArray(['font' => ['bold' => true,]]);						

            }
        ];
    }
}
