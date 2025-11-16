<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AccountUpdateRequest;
use App\Interfaces\V1\AccountRepositoryInterface;
use App\Repositories\V1\AccountRepository;
use Exception;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    private AccountRepository $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }
    public function index(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
            ];

            $categories = $this->accountRepository->getAllAccount($filters);
            return ResponseHelper::jsonResponse(true, 'Data akun berhasil diambil', $categories, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $category = $this->accountRepository->edit($id);
            return ResponseHelper::jsonResponse(true, 'Detail akun berhasil diambil', $category, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountUpdateRequest $request, string $id)
    {
        try {
            $data = $request->validated(); 
            $account = $this->accountRepository->update($data, $id);

            return ResponseHelper::jsonResponse(true, 'Data akun berhasil diperbarui', $account, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
}