<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PenerimaanStoreRequest;
use App\Http\Requests\V1\PenerimaanUpdateRequest;
use App\Services\V1\PenerimaanService;
use Illuminate\Http\Request;

class PenerimaanController extends Controller
{
    private PenerimaanService $penerimaanService;

    public function __construct(PenerimaanService $penerimaanService)
    {
        $this->penerimaanService = $penerimaanService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['per_page', 'sort_by', 'search']);
        $data = $this->penerimaanService->getPenerimaanList($filters, ['pending']);

        return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diambil', $data, 200);
    }

    public function store(PenerimaanStoreRequest $request)
    {
        $data = $this->penerimaanService->create($request->validated());

        return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil ditambahkan', $data, 201);
    }

    public function show(string $id)
    {
        $data = $this->penerimaanService->getPenerimaanForEdit($id);

        return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diambil', $data, 200);
    }

    public function update(PenerimaanUpdateRequest $request, string $id)
    {
        $data = $this->penerimaanService->updatePenerimaan($id, $request->validated());

        return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diperbarui', $data, 200);
    }

    public function destroy(string $id)
    {
        $this->penerimaanService->delete($id);

        return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil dihapus', null, 200);
    }
}