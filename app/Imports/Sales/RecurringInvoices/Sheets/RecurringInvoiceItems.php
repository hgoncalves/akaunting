<?php

namespace App\Imports\Sales\RecurringInvoices\Sheets;

use App\Abstracts\Import;
use App\Http\Requests\Document\DocumentItem as Request;
use App\Models\Document\Document;
use App\Models\Document\DocumentItem as Model;

class RecurringInvoiceItems extends Import
{
    public $request_class = Request::class;

    public function model(array $row)
    {
        return new Model($row);
    }

    public function map($row): array
    {
        if ($this->isEmpty($row, 'invoice_number')) {
            return [];
        }

        $row['invoice_number'] = (string) $row['invoice_number'];

        $row = parent::map($row);

        $row['document_id'] = (int) Document::where('type', '=', Document::INVOICE_RECURRING_TYPE)
            ->number($row['invoice_number'])
            ->pluck('id')
            ->first();

        if (empty($row['item_id']) && ! empty($row['item_name'])) {
            $row['item_id'] = $this->getItemIdFromName($row);

            $row['name'] = $row['item_name'];
        }

        $row['description'] = !empty($row['item_description']) ? $row['item_description'] : '';

        $row['tax'] = (double) $row['tax'];
        $row['tax_id'] = 0;
        $row['type'] = Document::INVOICE_RECURRING_TYPE;

        return $row;
    }

    public function prepareRules(array $rules): array
    {
        $rules['invoice_number'] = 'required|string';

        unset($rules['invoice_id']);

        return $rules;
    }
}