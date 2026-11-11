<?php

namespace App\Repositories;

use App\Models\DatasheetDownload;
use App\Repositories\Concerns\EscapesLike;
use Illuminate\Pagination\LengthAwarePaginator;

class DatasheetDownloadRepository
{
    use EscapesLike;

    public function create(array $data): DatasheetDownload
    {
        return DatasheetDownload::create($data);
    }

    public function update(DatasheetDownload $download, array $data): DatasheetDownload
    {
        $download->update($data);

        return $download;
    }

    /** @return LengthAwarePaginator<int, DatasheetDownload> */
    public function paginate(?string $search, int $perPage): LengthAwarePaginator
    {
        return DatasheetDownload::query()
            ->when($search, function ($query, string $term) {
                $like = $this->likePattern($term);
                $query->where(fn ($q) => $q->where('document_number', 'like', $like)->orWhere('sku', 'like', $like)->orWhere('product_name', 'like', $like));
            })
            ->latest()
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}
