<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionAsset;
use App\Services\QuestionAssetService;
use Illuminate\Http\RedirectResponse;

/**
 * Class QuestionAssetController
 *
 * Quản lý xóa tệp tin media gắn với câu hỏi.
 */
class QuestionAssetController extends Controller
{
    protected QuestionAssetService $assetService;

    public function __construct(QuestionAssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * Xóa tệp tin tài nguyên
     */
    public function destroy(QuestionAsset $asset): RedirectResponse
    {
        $this->assetService->deleteAsset($asset);

        return back()->with('ok', 'Đã xóa tài nguyên thành công.');
    }
}
