<?php

namespace App\Services\Sales;

use App\Models\Employee;
use App\Models\SalesDocument;

/**
 * Copies a document into a new draft (row action "Duplicate"): same recipient, texts and
 * product cards with their snapshots, a fresh number/link and a new validity date.
 */
class SalesDocumentDuplicator
{
    public function __construct(
        private readonly SalesDocumentStateMapper $mapper,
        private readonly SalesDocumentWriter $writer,
    ) {}

    public function duplicate(SalesDocument $source, ?Employee $actor): SalesDocument
    {
        $header = $this->mapper->toHeader($source);
        $header['valid_until'] = $this->mapper->blankHeader()['valid_until'];

        return $this->writer->save(new SalesDocument, $header, $this->mapper->toCards($source), $actor);
    }
}
